<?php

namespace Database\Seeders;

use App\Models\IncomeCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IncomeCategorySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        IncomeCategory::upsert([
            ['name' => 'B40', 'min_income' => 0, 'max_income' => 3169.00],
            ['name' => 'M40', 'min_income' => 3170.00, 'max_income' => 6339.00],
            ['name' => 'T20', 'min_income' => 6340.00, 'max_income' => null],
        ], ['name']);
    }
}