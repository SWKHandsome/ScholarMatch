<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentUserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'student@scholarmatch.test'],
            [
                'name' => 'Test Student',
                'password' => Hash::make('password'),
                'role' => User::ROLE_STUDENT,
                'email_verified_at' => now(),
            ]
        );
    }
}