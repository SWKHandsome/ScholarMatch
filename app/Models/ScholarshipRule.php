<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'required_institution_type',
        'income_rule_type',
        'study_level_rule_type',
        'field_rule_type',
        'institution_rule_type',
        'rule_payload',
    ];

    protected $casts = [
        'max_household_income' => 'decimal:2',
        'min_cgpa' => 'decimal:2',
        'min_spm_as' => 'integer',
        'min_spm_credits' => 'integer',
        'rule_payload' => 'array',
    ];

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }
}