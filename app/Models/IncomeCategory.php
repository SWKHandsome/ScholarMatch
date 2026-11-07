<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

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
        return self::matchingCategory($income, self::configuredCategories())?->name;
    }

    public static function configuredCategories(): Collection
    {
        return self::orderBy('min_income')->orderBy('id')->get();
    }

    public static function matchingCategory(float $income, Collection $categories): ?self
    {
        if (! is_finite($income) || $income < 0) {
            return null;
        }

        return $categories->first(fn (self $category) => $income >= (float) $category->min_income
            && ($category->max_income === null || $income <= (float) $category->max_income));
    }

    public static function amountRules(): array
    {
        // Matches the database's DECIMAL(12, 2) storage, without rounding before classification.
        return ['numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'];
    }

    public function rangeLabel(): string
    {
        $minimum = 'RM '.number_format((float) $this->min_income, 2);

        return $this->max_income === null
            ? $minimum.'/month and above'
            : $minimum.' – RM '.number_format((float) $this->max_income, 2).'/month';
    }

    public static function synchronizeStudentProfiles(): int
    {
        $categories = self::configuredCategories();
        $changed = 0;
        StudentProfile::select(['id', 'household_income', 'income_category'])->chunkById(200, function ($profiles) use ($categories, &$changed) {
            foreach ($profiles as $profile) {
                $category = self::matchingCategory((float) $profile->household_income, $categories)?->name;
                if ($profile->income_category !== $category) {
                    // Avoid overwriting a profile whose income changed after this batch was read.
                    $changed += StudentProfile::whereKey($profile->id)->where('household_income', $profile->household_income)
                        ->update(['income_category' => $category]);
                }
            }
        });

        return $changed;
    }
}
