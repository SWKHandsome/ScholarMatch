<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nationality',
        'state',
        'household_income',
        'number_of_dependents',
        'income_category',
        'institution_type',
        'field_of_study',
    ];

    protected $casts = [
        'household_income' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicResult(): HasOne
    {
        return $this->hasOne(AcademicResult::class, 'user_id', 'user_id');
    }

    public function incomeCategory(): BelongsTo
    {
        return $this->belongsTo(IncomeCategory::class, 'income_category', 'name');
    }

    public function savedScholarships()
    {
        return $this->hasMany(SavedScholarship::class, 'user_id', 'user_id');
    }
}