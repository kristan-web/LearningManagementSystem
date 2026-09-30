<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'first_name' => 'Lance',
            'last_name' => 'Villanueva',
            'email' => 'lance@admin.com',
            'password' => bcrypt('Admin123'),
            'role' => 'Admin',
            'must_change_password' => false,
        ]);

        $this->call([
            GuardianSeeder::class,
            StudentSeeder::class,
            AssignmentSeeder::class,
            QuizSeeder::class,
            LearningMaterialSeeder::class,
        ]);
    }
}
