<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => 'admin',
            'phone' => '08123456789',
            'status' => 'active',
        ]);

        // Create demo mentor user
        User::create([
            'name' => 'Demo Mentor',
            'email' => 'mentor@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => 'mentor',
            'phone' => '08123456790',
            'status' => 'active',
        ]);

        // Create demo student user
        User::create([
            'name' => 'Demo Student',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => 'student',
            'phone' => '08123456791',
            'status' => 'active',
        ]);

        // Create additional 10 mentors
        User::factory()->count(10)->mentor()->create();

        // Create additional 20 students
        User::factory()->count(20)->student()->create();
    }
}
