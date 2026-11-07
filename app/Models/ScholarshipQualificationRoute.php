<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScholarshipQualificationRoute extends Model
{
    protected $fillable = [
        'education_level', 'study_status', 'min_spm_as', 'min_spm_credits', 'min_cgpa',
        'min_year_of_study', 'max_year_of_study',
        'grading_scale', 'grade_requirements', 'min_stam_rank', 'uec_average', 'stpm_average', 'uec_total',
    ];

    protected $casts = [
        'min_spm_as' => 'integer', 'min_spm_credits' => 'integer', 'min_cgpa' => 'decimal:2',
        'min_year_of_study' => 'integer', 'max_year_of_study' => 'integer',
        'grade_requirements' => 'array', 'uec_average' => 'array', 'stpm_average' => 'array', 'uec_total' => 'array',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(ScholarshipRule::class, 'scholarship_rule_id');
    }
}
