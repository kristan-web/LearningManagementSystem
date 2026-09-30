<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Guardian;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@example.com',
            'password' => Hash::make('password'),
            'role' => 'Student',
            'status' => 'Active',
            'must_change_password' => false,
        ]);

        $guardian = Guardian::first();

        Student::create([
            'user_id' => $user->user_id,
            'guardian_id' => $guardian ? $guardian->guardian_id : null,
            'lrn' => '123456789012',
            'student_number' => '2026-0001',
            'grade_level' => '11',
        ]);
    }
}
