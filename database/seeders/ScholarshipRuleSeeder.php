<?php

namespace Database\Seeders;

use App\Models\Scholarship;
use App\Models\ScholarshipRule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScholarshipRuleSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $rules = [
            // JPA B40 Undergraduate Scholarship
            [
                'scholarship_name' => 'JPA B40 Undergraduate Scholarship',
                'required_nationality' => 'Malaysian',
                'required_study_level' => 'Undergraduate',
                'required_income_category' => 'B40',
                'max_household_income' => 3169.00,
                'min_cgpa' => 3.00,
                'required_institution_type' => 'Public University',
            ],
            // Yayasan Khazanah B40 Scholarship
            [
                'scholarship_name' => 'Yayasan Khazanah B40 Scholarship',
                'required_nationality' => 'Malaysian',
                'required_study_level' => 'Undergraduate',
                'required_income_category' => 'B40',
                'max_household_income' => 3169.00,
                'min_cgpa' => 3.50,
                'required_field_of_study' => 'STEM',
                'required_institution_type' => 'Public University',
            ],
            // PTPTN B40 Loan Conversion
            [
                'scholarship_name' => 'PTPTN B40 Loan Conversion',
                'required_nationality' => 'Malaysian',
                'required_study_level' => 'Undergraduate',
                'required_income_category' => 'B40',
                'max_household_income' => 3169.00,
                'min_cgpa' => 2.50,
            ],
            // Merit Scholarship for SPM High Achievers
            [
                'scholarship_name' => 'Merit Scholarship for SPM High Achievers',
                'required_nationality' => 'Malaysian',
                'required_study_level' => 'Undergraduate',
                'min_spm_as' => 9,
                'required_institution_type' => 'Public University',
            ],
            // Excellence Award for STPM Top Scorers
            [
                'scholarship_name' => 'Excellence Award for STPM Top Scorers',
                'required_nationality' => 'Malaysian',
                'required_study_level' => 'Undergraduate',
                'min_cgpa' => 4.00,
                'required_institution_type' => 'Public University',
            ],
            // Taylor's Foundation Excellence Scholarship
            [
                'scholarship_name' => 'Taylor\'s Foundation Excellence Scholarship',
                'required_nationality' => 'Malaysian',
                'required_study_level' => 'Foundation',
                'min_cgpa' => 3.50,
                'required_institution_type' => 'Private University',
            ],
            // Sunway Foundation Scholarship
            [
                'scholarship_name' => 'Sunway Foundation Scholarship',
                'required_nationality' => 'Malaysian',
                'required_study_level' => 'Foundation',
                'min_cgpa' => 3.30,
                'required_institution_type' => 'Private University',
            ],
            // Polytechnic Diploma Scholarship
            [
                'scholarship_name' => 'Polytechnic Diploma Scholarship',
                'required_nationality' => 'Malaysian',
                'required_study_level' => 'Diploma',
                'required_institution_type' => 'Public University',
                'max_household_income' => 5000.00,
                'min_cgpa' => 2.50,
            ],
            // Community College Assistance Grant
            [
                'scholarship_name' => 'Community College Assistance Grant',
                'required_nationality' => 'Malaysian',
                'required_study_level' => 'Diploma',
                'required_institution_type' => 'Public University',
                'required_income_category' => 'B40',
                'max_household_income' => 3169.00,
            ],
            // PETRONAS Education Sponsorship
            [
                'scholarship_name' => 'PETRONAS Education Sponsorship',
                'required_nationality' => 'Malaysian',
                'required_study_level' => 'Undergraduate',
                'min_spm_as' => 8,
                'min_cgpa' => 3.75,
                'required_field_of_study' => 'Engineering',
            ],
            // Shell Malaysia Scholarship
            [
                'scholarship_name' => 'Shell Malaysia Scholarship',
                'required_nationality' => 'Malaysian',
                'required_study_level' => 'Undergraduate',
                'min_cgpa' => 3.50,
                'required_field_of_study' => 'Engineering',
            ],
            // MDEC Digital Technology Scholarship
            [
                'scholarship_name' => 'MDEC Digital Technology Scholarship',
                'required_nationality' => 'Malaysian',
                'required_study_level' => 'Undergraduate',
                'min_cgpa' => 3.30,
                'required_field_of_study' => 'Computer Science',
            ],
            // Intel Malaysia STEM Scholarship
            [
                'scholarship_name' => 'Intel Malaysia STEM Scholarship',
                'required_nationality' => 'Malaysian',
                'required_study_level' => 'Undergraduate',
                'min_cgpa' => 3.20,
                'required_field_of_study' => 'Engineering',
            ],
            // UKM Chancellor Scholarship
            [
                'scholarship_name' => 'UKM Chancellor Scholarship',
                'required_nationality' => 'Malaysian',
                'required_study_level' => 'Undergraduate',
                'min_cgpa' => 3.50,
                'required_institution_type' => 'Public University',
            ],
            // USM Vice-Chancellor Award
            [
                'scholarship_name' => 'USM Vice-Chancellor Award',
                'required_nationality' => 'Malaysian',
                'required_study_level' => 'Undergraduate',
                'min_cgpa' => 3.75,
                'required_institution_type' => 'Public University',
            ],
            // Monash University Malaysia Scholarship
            [
                'scholarship_name' => 'Monash University Malaysia Scholarship',
                'required_nationality' => 'Malaysian',
                'required_study_level' => 'Undergraduate',
                'min_cgpa' => 3.50,
                'required_institution_type' => 'Private University',
            ],
            // Curtin Malaysia Academic Excellence
            [
                'scholarship_name' => 'Curtin Malaysia Academic Excellence',
                'required_nationality' => 'Malaysian',
                'required_study_level' => 'Undergraduate',
                'min_cgpa' => 3.40,
                'required_institution_type' => 'Private University',
            ],
            // Selangor State Scholarship
            [
                'scholarship_name' => 'Selangor State Scholarship (Biasiswa Selangor)',
                'required_nationality' => 'Malaysian',
                'required_study_level' => 'Undergraduate',
                'required_income_category' => 'B40',
                'max_household_income' => 3169.00,
                'min_cgpa' => 2.75,
            ],
            // Bantuan Kewangan Pelajar IPTA
            [
                'scholarship_name' => 'Bantuan Kewangan Pelajar IPTA',
                'required_nationality' => 'Malaysian',
                'required_study_level' => 'Undergraduate',
                'required_income_category' => 'B40',
                'max_household_income' => 3169.00,
                'required_institution_type' => 'Public University',
                'min_cgpa' => 2.00,
            ],
            // Zakat Education Assistance
            [
                'scholarship_name' => 'Zakat Education Assistance',
                'required_nationality' => 'Malaysian',
                'required_study_level' => 'Undergraduate',
                'required_income_category' => 'B40',
                'max_household_income' => 3169.00,
                'min_cgpa' => 2.00,
            ],
        ];

        foreach ($rules as $data) {
            $scholarship = Scholarship::where('name', $data['scholarship_name'])->first();
            if ($scholarship) {
                unset($data['scholarship_name']);
                $data['scholarship_id'] = $scholarship->id;
                $rule = ScholarshipRule::updateOrCreate(
                    ['scholarship_id' => $scholarship->id],
                    $data
                );
                $rule->qualificationRoutes()->delete();
                $rule->qualificationRoutes()->createMany($rule->legacyAcademicRoutes());
            }
        }
    }
}
