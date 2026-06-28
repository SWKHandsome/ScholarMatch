<?php

namespace Database\Seeders;

use App\Models\Scholarship;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScholarshipSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $scholarships = [
            // B40 Scholarships
            [
                'name' => 'JPA B40 Undergraduate Scholarship',
                'provider' => 'Jabatan Perkhidmatan Awam (JPA)',
                'description' => 'Full scholarship for B40 students pursuing undergraduate studies in local public universities.',
                'award_type' => 'Full Scholarship',
                'deadline' => '2026-12-31',
                'application_link' => 'https://www.jpa.gov.my',
                'is_active' => true,
            ],
            [
                'name' => 'Yayasan Khazanah B40 Scholarship',
                'provider' => 'Yayasan Khazanah',
                'description' => 'Comprehensive scholarship for B40 students in STEM fields at selected universities.',
                'award_type' => 'Full Scholarship',
                'deadline' => '2026-11-30',
                'application_link' => 'https://www.khazanah.com.my',
                'is_active' => true,
            ],
            [
                'name' => 'PTPTN B40 Loan Conversion',
                'provider' => 'Perbadanan Tabung Pendidikan Tinggi Nasional',
                'description' => 'Convertible loan for B40 students; converts to scholarship upon graduation with CGPA 3.5+.',
                'award_type' => 'Convertible Loan',
                'deadline' => '2026-10-31',
                'application_link' => 'https://www.ptptn.gov.my',
                'is_active' => true,
            ],

            // Merit Scholarships
            [
                'name' => 'Merit Scholarship for SPM High Achievers',
                'provider' => 'Ministry of Higher Education',
                'description' => 'Merit-based scholarship for students with 9A+ in SPM pursuing local undergraduate studies.',
                'award_type' => 'Full Scholarship',
                'deadline' => '2026-09-30',
                'application_link' => 'https://www.mohe.gov.my',
                'is_active' => true,
            ],
            [
                'name' => 'Excellence Award for STPM Top Scorers',
                'provider' => 'University Malaya',
                'description' => 'Tuition fee waiver for STPM students with CGPA 4.00 enrolling in UM.',
                'award_type' => 'Tuition Fee Waiver',
                'deadline' => '2026-08-31',
                'application_link' => 'https://www.um.edu.my',
                'is_active' => true,
            ],

            // Foundation Scholarships
            [
                'name' => 'Taylor\'s Foundation Excellence Scholarship',
                'provider' => 'Taylor\'s University',
                'description' => 'Partial scholarship for high-achieving foundation students.',
                'award_type' => 'Partial Scholarship',
                'deadline' => '2026-07-31',
                'application_link' => 'https://www.taylors.edu.my',
                'is_active' => true,
            ],
            [
                'name' => 'Sunway Foundation Scholarship',
                'provider' => 'Sunway University',
                'description' => 'Scholarship for foundation students with excellent academic records.',
                'award_type' => 'Partial Scholarship',
                'deadline' => '2026-08-15',
                'application_link' => 'https://www.sunway.edu.my',
                'is_active' => true,
            ],

            // Diploma Scholarships
            [
                'name' => 'Polytechnic Diploma Scholarship',
                'provider' => 'Department of Polytechnic Education',
                'description' => 'Financial aid for diploma students in public polytechnics.',
                'award_type' => 'Living Allowance',
                'deadline' => '2026-10-15',
                'application_link' => 'https://www.polycc.gov.my',
                'is_active' => true,
            ],
            [
                'name' => 'Community College Assistance Grant',
                'provider' => 'Department of Community College Education',
                'description' => 'Grant for diploma students from low-income families.',
                'award_type' => 'Tuition Fee Waiver',
                'deadline' => '2026-09-15',
                'application_link' => 'https://www.jpk.gov.my',
                'is_active' => true,
            ],

            // Undergraduate Scholarships
            [
                'name' => 'PETRONAS Education Sponsorship',
                'provider' => 'PETRONAS',
                'description' => 'Prestigious full sponsorship for undergraduate studies in engineering and geosciences.',
                'award_type' => 'Full Scholarship',
                'deadline' => '2026-05-31',
                'application_link' => 'https://www.petronas.com.my',
                'is_active' => true,
            ],
            [
                'name' => 'Shell Malaysia Scholarship',
                'provider' => 'Shell Malaysia',
                'description' => 'Scholarship for undergraduate students in engineering and business.',
                'award_type' => 'Full Scholarship',
                'deadline' => '2026-06-30',
                'application_link' => 'https://www.shell.com.my',
                'is_active' => true,
            ],

            // STEM Scholarships
            [
                'name' => 'MDEC Digital Technology Scholarship',
                'provider' => 'Malaysia Digital Economy Corporation',
                'description' => 'Scholarship for STEM students focusing on digital technology and ICT.',
                'award_type' => 'Full Scholarship',
                'deadline' => '2026-07-15',
                'application_link' => 'https://www.mdec.my',
                'is_active' => true,
            ],
            [
                'name' => 'Intel Malaysia STEM Scholarship',
                'provider' => 'Intel Malaysia',
                'description' => 'Scholarship for electrical engineering and computer science students.',
                'award_type' => 'Partial Scholarship',
                'deadline' => '2026-08-31',
                'application_link' => 'https://www.intel.com.my',
                'is_active' => true,
            ],

            // Public University Scholarships
            [
                'name' => 'UKM Chancellor Scholarship',
                'provider' => 'Universiti Kebangsaan Malaysia',
                'description' => 'Merit scholarship for undergraduate students at UKM.',
                'award_type' => 'Tuition Fee Waiver',
                'deadline' => '2026-09-30',
                'application_link' => 'https://www.ukm.my',
                'is_active' => true,
            ],
            [
                'name' => 'USM Vice-Chancellor Award',
                'provider' => 'Universiti Sains Malaysia',
                'description' => 'Award for high-achieving undergraduate students at USM.',
                'award_type' => 'Full Scholarship',
                'deadline' => '2026-10-31',
                'application_link' => 'https://www.usm.my',
                'is_active' => true,
            ],

            // Private University Scholarships
            [
                'name' => 'Monash University Malaysia Scholarship',
                'provider' => 'Monash University Malaysia',
                'description' => 'Merit-based scholarship for undergraduate programs at Monash Malaysia.',
                'award_type' => 'Partial Scholarship',
                'deadline' => '2026-11-15',
                'application_link' => 'https://www.monash.edu.my',
                'is_active' => true,
            ],
            [
                'name' => 'Curtin Malaysia Academic Excellence',
                'provider' => 'Curtin University Malaysia',
                'description' => 'Scholarship for high-achieving students in engineering and business.',
                'award_type' => 'Partial Scholarship',
                'deadline' => '2026-12-15',
                'application_link' => 'https://www.curtin.edu.my',
                'is_active' => true,
            ],

            // State-based Scholarship
            [
                'name' => 'Selangor State Scholarship (Biasiswa Selangor)',
                'provider' => 'Selangor State Government',
                'description' => 'Scholarship for Selangor residents pursuing higher education.',
                'award_type' => 'Living Allowance',
                'deadline' => '2026-10-31',
                'application_link' => 'https://www.selangor.gov.my',
                'is_active' => true,
            ],

            // General Financial Aid
            [
                'name' => 'Bantuan Kewangan Pelajar IPTA',
                'provider' => 'Ministry of Higher Education',
                'description' => 'Financial aid for students in public universities from low-income families.',
                'award_type' => 'Living Allowance',
                'deadline' => '2026-12-31',
                'application_link' => 'https://www.mohe.gov.my',
                'is_active' => true,
            ],
            [
                'name' => 'Zakat Education Assistance',
                'provider' => 'State Zakat Boards',
                'description' => 'Education assistance from zakat funds for eligible Muslim students.',
                'award_type' => 'Living Allowance',
                'deadline' => '2026-12-31',
                'application_link' => 'https://www.zakat.gov.my',
                'is_active' => true,
            ],
        ];

        foreach ($scholarships as $data) {
            Scholarship::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}