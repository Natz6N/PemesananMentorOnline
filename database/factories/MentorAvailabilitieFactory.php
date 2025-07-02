<?php

namespace Database\Factories;

use App\Models\MentorProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MentorAvailabilitie>
 */
class MentorAvailabilitieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $day = fake()->randomElement($days);

        // Ensure a reasonable time range
        $startHour = fake()->numberBetween(8, 18);
        $endHour = fake()->numberBetween($startHour + 1, min($startHour + 6, 22));

        $startTime = sprintf('%02d:00:00', $startHour);
        $endTime = sprintf('%02d:00:00', $endHour);

        return [
            'mentor_profile_id' => MentorProfile::factory(),
            'day_of_week' => $day,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_active' => fake()->boolean(90), // 90% chance to be active
        ];
    }
}
