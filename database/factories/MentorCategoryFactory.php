<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\MentorProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MentorCategory>
 */
class MentorCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mentor_profile_id' => MentorProfile::factory(),
            'category_id' => Category::factory(),
        ];
    }
}
