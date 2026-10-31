<?php

namespace App\Models;

use App\Support\AcademicQualifications;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicResult extends Model
{
    use HasFactory;

    public const EDUCATION_LEVELS = AcademicQualifications::LEVELS;

    protected $fillable = [
        'user_id', 'education_level', 'study_status', 'year_of_study', 'funding_study_level',
        'spm_as', 'spm_credits', 'cgpa', 'result_status', 'institution_type', 'field_of_study',
        'grading_scale', 'grade_counts', 'stam_rank', 'stpm_pa_grade', 'stpm_cgpa_confirmed',
    ];

    protected $casts = [
        'cgpa' => 'decimal:2', 'spm_as' => 'integer', 'spm_credits' => 'integer', 'year_of_study' => 'integer', 'grade_counts' => 'array',
        'stpm_cgpa_confirmed' => 'boolean',
    ];

    public function isReadyForRecommendations(): bool
    {
        if ($this->result_status !== 'official'
            || ! in_array($this->education_level, self::EDUCATION_LEVELS, true)
            || ! in_array($this->funding_study_level, self::EDUCATION_LEVELS, true)
            || ! in_array($this->study_status, ['studying', 'completed'], true)
            || ! in_array($this->institution_type, config('scholarship.institution_types'), true)
            || ! in_array($this->field_of_study, config('scholarship.fields_of_study'), true)) {
            return false;
        }
        if (AcademicQualifications::isGradeBased($this->education_level)) {
            if (! AcademicQualifications::validCounts($this->education_level, $this->grading_scale, $this->grade_counts)) {
                return false;
            }
            if ($this->education_level !== 'STPM') {
                return $this->study_status === 'completed';
            }
            if ($this->study_status === 'completed' && ($this->stpm_pa_grade !== null || $this->stpm_cgpa_confirmed)) {
                $calculated = AcademicQualifications::stpmCgpa($this->grade_counts, $this->stpm_pa_grade);
                if (! $this->stpm_cgpa_confirmed || $calculated === null || $this->cgpa !== $calculated) {
                    return false;
                }
            }
        }
        if ($this->education_level === 'STAM') {
            return $this->study_status === 'completed' && in_array($this->stam_rank, AcademicQualifications::RANKS, true);
        }

        return $this->cgpa !== null && $this->cgpa >= 0 && $this->cgpa <= 4
            && ($this->study_status !== 'studying'
                || ($this->year_of_study !== null && $this->year_of_study >= 1 && $this->year_of_study <= 10));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
