<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentProfile extends Model
{
    use HasFactory;

    public const STATES = ['Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis', 'Pulau Pinang', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'Kuala Lumpur', 'Labuan', 'Putrajaya'];

    public static function normalizeState(mixed $state): mixed
    {
        if (! is_string($state)) {
            return $state;
        }

        return match (trim($state)) {
            'Wilayah Persekutuan Kuala Lumpur' => 'Kuala Lumpur',
            'Wilayah Persekutuan Labuan' => 'Labuan',
            'Wilayah Persekutuan Putrajaya' => 'Putrajaya',
            default => trim($state),
        };
    }

    public static function validationRules(): array
    {
        return [
            'nationality' => ['required', 'string', Rule::in(config('scholarship.nationalities'))],
            'state' => ['required', 'string', 'max:255'],
            'household_income' => ['required', ...IncomeCategory::amountRules()],
            'number_of_dependents' => ['required', 'integer', 'min:0'],
        ];
    }

    public function isComplete(): bool
    {
        return Validator::make($this->only(array_keys(self::validationRules())), self::validationRules())->passes();
    }

    protected $fillable = [
        'user_id',
        'nationality',
        'state',
        'household_income',
        'number_of_dependents',
        'income_category',
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
