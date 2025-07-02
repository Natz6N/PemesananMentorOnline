<?php

namespace Database\Seeders;

use App\Models\MentorProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MentorProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a profile for the demo mentor
        $demoMentor = User::where('email', 'mentor@example.com')->first();

        if ($demoMentor) {
            MentorProfile::create([
                'user_id' => $demoMentor->id,
                'bio' => 'I am a professional software developer with over 8 years of experience in web and mobile development. I have worked with various technologies including Laravel, Vue.js, React Native, and Flutter. I am passionate about teaching and helping others grow in their tech journey.',
                'expertise' => ['Laravel', 'PHP', 'Vue.js', 'JavaScript', 'API Development'],
                'experience_years' => 8,
                'education' => 'Master of Computer Science',
                'current_position' => 'Senior Software Engineer',
                'company' => 'Tech Solutions Inc.',
                'achievements' => 'Built and scaled multiple web applications serving thousands of users. Speaker at local tech conferences. Open source contributor.',
                'hourly_rate' => 350000, // Rp. 350,000
                'timezone' => 'Asia/Jakarta',
                'languages' => ['English', 'Indonesian'],
                'status' => 'approved',
                'rating_average' => 4.8,
                'total_reviews' => 24,
                'total_sessions' => 37,
                'is_available' => true,
            ]);
        }

        // Create profiles for all mentors who don't have one yet
        $mentorUsers = User::where('role', 'mentor')
            ->whereDoesntHave('mentorProfile')
            ->get();

        foreach ($mentorUsers as $mentor) {
            MentorProfile::factory()->create([
                'user_id' => $mentor->id,
            ]);
        }
    }
}
