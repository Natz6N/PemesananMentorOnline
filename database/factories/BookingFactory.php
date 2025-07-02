<?php

namespace Database\Factories;

use App\Models\MentorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mentorProfile = MentorProfile::factory()->create();
        $mentor = User::find($mentorProfile->user_id);
        $student = User::factory()->student()->create();
        $duration = fake()->randomElement([30, 60, 90, 120]);
        $hourlyRate = $mentorProfile->hourly_rate;
        $totalAmount = ($hourlyRate / 60) * $duration;

        // Generate a future date for scheduling
        $scheduledAt = fake()->dateTimeBetween('+1 day', '+30 days');

        $status = fake()->randomElement([
            'pending', 'pending', 'confirmed', 'confirmed', 'confirmed',
            'completed', 'completed', 'cancelled'
        ]);

        $bookingCode = 'BK-' . date('Ymd', $scheduledAt->getTimestamp()) . '-' . strtoupper(Str::random(6));

        return [
            'booking_code' => $bookingCode,
            'student_id' => $student->id,
            'mentor_id' => $mentor->id,
            'mentor_profile_id' => $mentorProfile->id,
            'scheduled_at' => $scheduledAt,
            'duration_minutes' => $duration,
            'total_amount' => $totalAmount,
            'session_topic' => fake()->sentence(),
            'student_notes' => fake()->paragraph(),
            'mentor_notes' => $status !== 'pending' ? fake()->paragraph() : null,
            'status' => $status,
            'meeting_link' => $status === 'confirmed' ? 'https://meet.google.com/' . Str::random(10) : null,
            'cancellation_reason' => $status === 'cancelled' ? fake()->sentence() : null,
            'cancelled_at' => $status === 'cancelled' ? fake()->dateTimeBetween('-10 days', 'now') : null,
        ];
    }

    /**
     * Indicate that the booking is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'mentor_notes' => null,
            'meeting_link' => null,
            'cancellation_reason' => null,
            'cancelled_at' => null,
        ]);
    }

    /**
     * Indicate that the booking is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'mentor_notes' => fake()->paragraph(),
            'meeting_link' => 'https://meet.google.com/' . Str::random(10),
            'cancellation_reason' => null,
            'cancelled_at' => null,
        ]);
    }

    /**
     * Indicate that the booking is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'mentor_notes' => fake()->paragraph(),
            'meeting_link' => 'https://meet.google.com/' . Str::random(10),
            'cancellation_reason' => null,
            'cancelled_at' => null,
        ]);
    }

    /**
     * Indicate that the booking is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'meeting_link' => null,
            'cancellation_reason' => fake()->sentence(),
            'cancelled_at' => fake()->dateTimeBetween('-10 days', 'now'),
        ]);
    }
}
