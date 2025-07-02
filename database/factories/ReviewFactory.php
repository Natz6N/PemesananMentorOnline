<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $booking = Booking::factory()->completed()->create();
        $rating = fake()->numberBetween(3, 5); // Slightly biased towards positive ratings

        $ratingAspects = [
            'knowledge' => fake()->numberBetween(max(1, $rating - 1), 5),
            'communication' => fake()->numberBetween(max(1, $rating - 1), 5),
            'helpfulness' => fake()->numberBetween(max(1, $rating - 1), 5),
            'professionalism' => fake()->numberBetween(max(1, $rating - 1), 5),
        ];

        return [
            'booking_id' => $booking->id,
            'student_id' => $booking->student_id,
            'mentor_id' => $booking->mentor_id,
            'rating' => $rating,
            'comment' => fake()->paragraph(),
            'rating_aspects' => $ratingAspects,
            'is_anonymous' => fake()->boolean(20), // 20% chance of being anonymous
        ];
    }
}
