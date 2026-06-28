<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomeCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'min_income',
        'max_income',
    ];

    protected $casts = [
        'min_income' => 'decimal:2',
        'max_income' => 'decimal:2',
    ];

    public function studentProfiles(): HasMany
    {
        return $this->hasMany(StudentProfile::class, 'income_category', 'name');
    }

    public static function classifyIncome(float $income): ?string
    {
        $category = self::where('min_income', '<=', $income)
            ->where(function ($query) use ($income) {
                $query->where('max_income', '>=', $income)
                    ->orWhereNull('max_income');
            })
            ->first();

        return $category?->name;
    }
}