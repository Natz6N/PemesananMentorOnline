<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call seeders in the correct order to respect foreign key constraints
        $this->call([
            // 1. First seed users
            UserSeeder::class,

            // 2. Seed categories
            CategorySeeder::class,

            // 3. Seed mentor profiles
            MentorProfileSeeder::class,

            // 4. Seed mentor categories
            MentorCategorySeeder::class,

            // 5. Seed mentor availabilities
            MentorAvailabilitieSeeder::class,

            // 6. Seed bookings
            BookingSeeder::class,

            // 7. Seed payments for bookings
            PaymentSeeder::class,

            // 8. Seed reviews for completed bookings
            ReviewSeeder::class,
        ]);
    }
}
