<?php

namespace Database\Seeders;

use App\Models\StudyField;
use Illuminate\Database\Seeder;

class StudyFieldSeeder extends Seeder
{
    public function run(): void
    {
        foreach (StudyField::defaultRows() as $row) {
            if (! StudyField::where('seed_name', $row['seed_name'])->orWhere('name', $row['name'])->exists()) {
                StudyField::create($row);
            }
        }
    }
}
