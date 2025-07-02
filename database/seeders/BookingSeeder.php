<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\MentorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get demo student
        $demoStudent = User::where('email', 'student@example.com')->first();

        // Get demo mentor
        $demoMentor = User::where('email', 'mentor@example.com')->first();
        $demoMentorProfile = $demoMentor ? MentorProfile::where('user_id', $demoMentor->id)->first() : null;

        if ($demoStudent && $demoMentorProfile) {
            // Create pending booking for demo student with demo mentor
            $scheduledAt = now()->addDays(2)->setHour(10)->setMinute(0)->setSecond(0);
            $bookingCode = 'BK-' . date('Ymd', $scheduledAt->getTimestamp()) . '-' . strtoupper(Str::random(6));

            Booking::create([
                'booking_code' => $bookingCode,
                'student_id' => $demoStudent->id,
                'mentor_id' => $demoMentor->id,
                'mentor_profile_id' => $demoMentorProfile->id,
                'scheduled_at' => $scheduledAt,
                'duration_minutes' => 60,
                'total_amount' => $demoMentorProfile->hourly_rate,
                'session_topic' => 'Introduction to Laravel and API Development',
                'student_notes' => 'I would like to learn about RESTful API design and implementation using Laravel. I have basic PHP knowledge but I am new to Laravel.',
                'mentor_notes' => null,
                'status' => 'pending',
                'meeting_link' => null,
            ]);

            // Create confirmed booking for demo student with demo mentor
            $scheduledAt = now()->addDays(5)->setHour(14)->setMinute(0)->setSecond(0);
            $bookingCode = 'BK-' . date('Ymd', $scheduledAt->getTimestamp()) . '-' . strtoupper(Str::random(6));

            Booking::create([
                'booking_code' => $bookingCode,
                'student_id' => $demoStudent->id,
                'mentor_id' => $demoMentor->id,
                'mentor_profile_id' => $demoMentorProfile->id,
                'scheduled_at' => $scheduledAt,
                'duration_minutes' => 90,
                'total_amount' => ($demoMentorProfile->hourly_rate / 60) * 90,
                'session_topic' => 'Building a Vue.js Frontend for Laravel',
                'student_notes' => 'I want to learn how to create a Vue.js frontend that communicates with a Laravel backend API.',
                'mentor_notes' => 'I will prepare materials on Vue.js components, Axios for API calls, and state management.',
                'status' => 'confirmed',
                'meeting_link' => 'https://meet.google.com/' . Str::random(10),
            ]);

            // Create completed booking in the past
            $scheduledAt = now()->subDays(10)->setHour(15)->setMinute(0)->setSecond(0);
            $bookingCode = 'BK-' . date('Ymd', $scheduledAt->getTimestamp()) . '-' . strtoupper(Str::random(6));

            Booking::create([
                'booking_code' => $bookingCode,
                'student_id' => $demoStudent->id,
                'mentor_id' => $demoMentor->id,
                'mentor_profile_id' => $demoMentorProfile->id,
                'scheduled_at' => $scheduledAt,
                'duration_minutes' => 60,
                'total_amount' => $demoMentorProfile->hourly_rate,
                'session_topic' => 'Getting Started with Laravel',
                'student_notes' => 'I am completely new to Laravel and need guidance on getting started.',
                'mentor_notes' => 'We covered Laravel installation, basic routing, controllers, and Blade templates.',
                'status' => 'completed',
                'meeting_link' => 'https://meet.google.com/' . Str::random(10),
            ]);
        }

        // Create random bookings
        $students = User::where('role', 'student')->get();
        $mentorProfiles = MentorProfile::all();

        foreach ($mentorProfiles as $mentorProfile) {
            // Each mentor gets 0-5 random bookings
            $bookingCount = rand(0, 5);

            for ($i = 0; $i < $bookingCount; $i++) {
                $student = $students->random();
                $mentor = User::find($mentorProfile->user_id);

                if ($student && $mentor) {
                    $status = fake()->randomElement(['pending', 'confirmed', 'completed', 'cancelled']);
                    $duration = fake()->randomElement([30, 60, 90, 120]);
                    $totalAmount = ($mentorProfile->hourly_rate / 60) * $duration;

                    // Date logic based on status
                    if ($status === 'completed') {
                        $scheduledAt = fake()->dateTimeBetween('-30 days', '-1 day');
                    } elseif ($status === 'pending' || $status === 'confirmed') {
                        $scheduledAt = fake()->dateTimeBetween('+1 day', '+30 days');
                    } else { // cancelled
                        $scheduledAt = fake()->dateTimeBetween('-10 days', '+20 days');
                    }

                    $bookingCode = 'BK-' . date('Ymd', $scheduledAt->getTimestamp()) . '-' . strtoupper(Str::random(6));

                    Booking::factory()->create([
                        'booking_code' => $bookingCode,
                        'student_id' => $student->id,
                        'mentor_id' => $mentor->id,
                        'mentor_profile_id' => $mentorProfile->id,
                        'scheduled_at' => $scheduledAt,
                        'duration_minutes' => $duration,
                        'total_amount' => $totalAmount,
                        'status' => $status,
                    ]);
                }
            }
        }
    }
}
