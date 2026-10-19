<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScholarshipRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'scholarship_id',
        'required_nationality',
        'required_study_level',
        'required_income_category',
        'max_household_income',
        'min_spm_as',
        'min_spm_credits',
        'min_cgpa',
        'required_field_of_study',
        'required_fields_of_study',
        'required_institution_type',
        'accepted_education_levels',
        'required_study_status',
        'min_year_of_study',
        'max_year_of_study',
    ];

    protected $casts = [
        'max_household_income' => 'decimal:2',
        'min_cgpa' => 'decimal:2',
        'min_spm_as' => 'integer',
        'min_spm_credits' => 'integer',
        'accepted_education_levels' => 'array',
        'min_year_of_study' => 'integer',
        'max_year_of_study' => 'integer',
        'required_fields_of_study' => 'array',
    ];

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function qualificationRoutes(): HasMany
    {
        return $this->hasMany(ScholarshipQualificationRoute::class)->orderBy('id');
    }

    public function academicRoutes(): array
    {
        if ($this->qualificationRoutes->isNotEmpty()) {
            return $this->qualificationRoutes->map(fn ($route) => $route->only([
                'education_level', 'study_status', 'min_spm_as', 'min_spm_credits', 'min_cgpa',
                'min_year_of_study', 'max_year_of_study',
            ]))->all();
        }

        return $this->legacyAcademicRoutes();
    }

    // Compatibility for legacy imports and seed data that still use the old columns.
    public function legacyAcademicRoutes(): array
    {
        $levels = $this->acceptedEducationLevels();
        if (! $levels && ! $this->required_study_status && $this->min_year_of_study === null && $this->max_year_of_study === null) {
            return [];
        }

        return array_map(fn ($level) => [
            'education_level' => $level,
            'study_status' => $this->required_study_status ?: ($level === 'SPM' ? 'completed' : 'any'),
            'min_spm_as' => $level === 'SPM' ? $this->min_spm_as : null,
            'min_spm_credits' => $level === 'SPM' ? $this->min_spm_credits : null,
            'min_cgpa' => $level !== 'SPM' ? $this->min_cgpa : null,
            'min_year_of_study' => $this->min_year_of_study,
            'max_year_of_study' => $this->max_year_of_study,
        ], $levels ?: AcademicResult::EDUCATION_LEVELS);
    }

    /**
     * Use the new multi-field setting when available, while retaining support
     * for scholarship rules created before this feature was added.
     */
    public function supportedFieldsOfStudy(): array
    {
        if (! empty($this->required_fields_of_study)) {
            return $this->required_fields_of_study;
        }

        return $this->required_field_of_study ? [$this->required_field_of_study] : [];
    }

    public function acceptedEducationLevels(): array
    {
        if (! empty($this->accepted_education_levels)) {
            return $this->accepted_education_levels;
        }

        // Legacy rules infer accepted result types from their configured thresholds.
        $levels = [];
        if ($this->min_spm_as !== null || $this->min_spm_credits !== null) {
            $levels[] = 'SPM';
        }
        if ($this->min_cgpa !== null) {
            $levels = array_merge($levels, array_values(array_diff(AcademicResult::EDUCATION_LEVELS, ['SPM'])));
        }

        return $levels;
    }
}
