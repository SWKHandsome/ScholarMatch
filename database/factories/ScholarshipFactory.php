<?php

namespace Database\Factories;

use App\Models\Scholarship;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScholarshipFactory extends Factory
{
    protected $model = Scholarship::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'provider' => $this->faker->company(),
            'description' => $this->faker->paragraph(),
            'award_type' => $this->faker->randomElement(['Full Scholarship', 'Partial Scholarship', 'Tuition Fee Waiver', 'Living Allowance', 'Loan', 'Convertible Loan']),
            'deadline' => $this->faker->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'application_link' => $this->faker->url(),
            'is_active' => true,
        ];
    }
}