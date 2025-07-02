<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all completed bookings that don't have reviews yet
        $completedBookings = Booking::where('status', 'completed')
            ->whereDoesntHave('review')
            ->get();

        foreach ($completedBookings as $booking) {
            // 80% chance of having a review for completed sessions
            if (fake()->boolean(80)) {
                $rating = fake()->numberBetween(3, 5); // Slightly biased towards positive ratings

                $ratingAspects = [
                    'knowledge' => fake()->numberBetween(max(1, $rating - 1), 5),
                    'communication' => fake()->numberBetween(max(1, $rating - 1), 5),
                    'helpfulness' => fake()->numberBetween(max(1, $rating - 1), 5),
                    'professionalism' => fake()->numberBetween(max(1, $rating - 1), 5),
                ];

                Review::create([
                    'booking_id' => $booking->id,
                    'student_id' => $booking->student_id,
                    'mentor_id' => $booking->mentor_id,
                    'rating' => $rating,
                    'comment' => fake()->paragraph(),
                    'rating_aspects' => $ratingAspects,
                    'is_anonymous' => fake()->boolean(20), // 20% chance of being anonymous
                ]);

                // Update mentor profile rating average
                $mentorProfile = $booking->mentorProfile;
                $reviews = Review::where('mentor_id', $booking->mentor_id)->get();
                $totalRating = $reviews->sum('rating');
                $ratingAverage = $totalRating / $reviews->count();

                $mentorProfile->update([
                    'rating_average' => round($ratingAverage, 2),
                    'total_reviews' => $reviews->count(),
                ]);
            }
        }

        // Create specific reviews for demo mentor
        $demoBooking = Booking::where('status', 'completed')
            ->whereHas('mentor', function($query) {
                $query->where('email', 'mentor@example.com');
            })
            ->whereDoesntHave('review')
            ->first();

        if ($demoBooking) {
            Review::create([
                'booking_id' => $demoBooking->id,
                'student_id' => $demoBooking->student_id,
                'mentor_id' => $demoBooking->mentor_id,
                'rating' => 5,
                'comment' => 'The mentor was extremely knowledgeable and patient. They explained complex concepts in a way that was easy to understand. The session was very productive and I learned a lot. Highly recommended!',
                'rating_aspects' => [
                    'knowledge' => 5,
                    'communication' => 5,
                    'helpfulness' => 5,
                    'professionalism' => 5,
                ],
                'is_anonymous' => false,
            ]);

            // Update mentor profile rating
            $mentorProfile = $demoBooking->mentorProfile;
            $reviews = Review::where('mentor_id', $demoBooking->mentor_id)->get();
            $totalRating = $reviews->sum('rating');
            $ratingAverage = $totalRating / $reviews->count();

            $mentorProfile->update([
                'rating_average' => round($ratingAverage, 2),
                'total_reviews' => $reviews->count(),
            ]);
        }
    }
}
