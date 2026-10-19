<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicResult extends Model
{
    use HasFactory;

    public const EDUCATION_LEVELS = ['SPM', 'STPM', 'Foundation', 'Matriculation', 'Diploma', 'Undergraduate'];

    protected $fillable = [
        'user_id', 'education_level', 'study_status', 'year_of_study', 'funding_study_level',
        'spm_as', 'spm_credits', 'cgpa', 'result_status',
    ];

    protected $casts = [
        'cgpa' => 'decimal:2', 'spm_as' => 'integer', 'spm_credits' => 'integer', 'year_of_study' => 'integer',
    ];

    public function isReadyForRecommendations(): bool
    {
        if ($this->result_status !== 'official'
            || ! in_array($this->education_level, self::EDUCATION_LEVELS, true)
            || ! in_array($this->funding_study_level, self::EDUCATION_LEVELS, true)
            || ! in_array($this->study_status, ['studying', 'completed'], true)) {
            return false;
        }
        if ($this->education_level === 'SPM') {
            return $this->study_status === 'completed'
                && $this->spm_as !== null && $this->spm_credits !== null
                && $this->spm_as >= 0 && $this->spm_credits >= $this->spm_as && $this->spm_credits <= 12;
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
