<?php

namespace Database\Seeders;

use App\Models\Guardian;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuardianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a user account for the guardian first
        $user = User::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'janesmith@example.com',
            'password' => Hash::make('password'),
            'role' => 'Guardian', // Assuming 'Guardian' is a valid role
            'status' => 'Active',
            'must_change_password' => false,
        ]);

        Guardian::create([
            'user_id' => $user->user_id,
            'full_name' => 'Jane Smith',
            'relationship' => 'Parent',
            'contact_number' => '09123456789',
        ]);
    }
}
