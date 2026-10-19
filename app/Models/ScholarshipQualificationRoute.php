<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScholarshipQualificationRoute extends Model
{
    protected $fillable = [
        'education_level', 'study_status', 'min_spm_as', 'min_spm_credits', 'min_cgpa',
        'min_year_of_study', 'max_year_of_study',
    ];

    protected $casts = [
        'min_spm_as' => 'integer', 'min_spm_credits' => 'integer', 'min_cgpa' => 'decimal:2',
        'min_year_of_study' => 'integer', 'max_year_of_study' => 'integer',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(ScholarshipRule::class, 'scholarship_rule_id');
    }
}
