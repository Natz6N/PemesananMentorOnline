<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MentorCategory;
use App\Models\MentorProfile;
use Illuminate\Database\Seeder;

class MentorCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mentorProfiles = MentorProfile::all();
        $categories = Category::all();

        foreach ($mentorProfiles as $profile) {
            // Each mentor has 1-3 categories
            $categoryCount = rand(1, 3);
            $randomCategories = $categories->random($categoryCount);

            foreach ($randomCategories as $category) {
                MentorCategory::create([
                    'mentor_profile_id' => $profile->id,
                    'category_id' => $category->id
                ]);
            }
        }

        // Ensure demo mentor has specific categories
        $demoMentor = MentorProfile::whereHas('user', function($query) {
            $query->where('email', 'mentor@example.com');
        })->first();

        if ($demoMentor) {
            // Get Web Development, Mobile Development categories
            $webDevCategory = Category::where('name', 'Web Development')->first();
            $mobileDevCategory = Category::where('name', 'Mobile Development')->first();

            if ($webDevCategory) {
                MentorCategory::firstOrCreate([
                    'mentor_profile_id' => $demoMentor->id,
                    'category_id' => $webDevCategory->id
                ]);
            }

            if ($mobileDevCategory) {
                MentorCategory::firstOrCreate([
                    'mentor_profile_id' => $demoMentor->id,
                    'category_id' => $mobileDevCategory->id
                ]);
            }
        }
    }
}
