<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MentorProfile>
 */
class MentorProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $expertiseOptions = [
            'PHP', 'Laravel', 'JavaScript', 'Vue.js', 'React', 'Node.js',
            'Python', 'Django', 'Data Science', 'Machine Learning',
            'UI/UX Design', 'Product Management', 'DevOps', 'Flutter',
            'Mobile Development', 'Java', 'Kotlin', 'Swift'
        ];

        $expertise = fake()->randomElements($expertiseOptions, rand(2, 5));

        $languages = ['English'];
        if(fake()->boolean(70)) {
            $languages[] = 'Indonesian';
        }
        if(fake()->boolean(30)) {
            $languages[] = fake()->randomElement(['Spanish', 'French', 'German', 'Mandarin', 'Japanese']);
        }

        return [
            'user_id' => User::factory()->mentor(),
            'bio' => fake()->paragraphs(3, true),
            'expertise' => $expertise,
            'experience_years' => fake()->numberBetween(1, 15),
            'education' => fake()->randomElement(['Bachelor Degree', 'Master Degree', 'PhD', 'Self-taught']),
            'current_position' => fake()->jobTitle(),
            'company' => fake()->company(),
            'achievements' => fake()->sentences(3, true),
            'hourly_rate' => fake()->numberBetween(5, 100) * 10000,
            'timezone' => fake()->randomElement(['Asia/Jakarta', 'Asia/Singapore', 'America/New_York', 'Europe/London']),
            'languages' => $languages,
            'status' => fake()->randomElement(['pending', 'approved', 'approved', 'approved']), // Higher probability for approved
            'rejection_reason' => null,
            'rating_average' => fake()->randomFloat(1, 3.5, 5.0),
            'total_reviews' => fake()->numberBetween(0, 50),
            'total_sessions' => fake()->numberBetween(0, 100),
            'is_available' => fake()->boolean(80), // 80% chance to be available
        ];
    }
}
