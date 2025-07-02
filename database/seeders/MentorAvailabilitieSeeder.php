<?php

namespace Database\Seeders;

use App\Models\MentorAvailabilitie;
use App\Models\MentorProfile;
use Illuminate\Database\Seeder;

class MentorAvailabilitieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mentorProfiles = MentorProfile::all();

        foreach ($mentorProfiles as $profile) {
            // Each mentor has 3-7 availability slots
            $availabilityCount = rand(3, 7);
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            $availableDays = array_rand(array_flip($days), min(count($days), $availabilityCount));

            if (!is_array($availableDays)) {
                $availableDays = [$availableDays];
            }

            foreach ($availableDays as $day) {
                // Morning slot (8 AM - 12 PM)
                if (rand(0, 1)) {
                    $startHour = rand(8, 10);
                    $endHour = rand($startHour + 1, 12);

                    MentorAvailabilitie::create([
                        'mentor_profile_id' => $profile->id,
                        'day_of_week' => $day,
                        'start_time' => sprintf('%02d:00:00', $startHour),
                        'end_time' => sprintf('%02d:00:00', $endHour),
                        'is_active' => true,
                    ]);
                }

                // Afternoon slot (1 PM - 5 PM)
                if (rand(0, 1)) {
                    $startHour = rand(13, 15);
                    $endHour = rand($startHour + 1, 17);

                    MentorAvailabilitie::create([
                        'mentor_profile_id' => $profile->id,
                        'day_of_week' => $day,
                        'start_time' => sprintf('%02d:00:00', $startHour),
                        'end_time' => sprintf('%02d:00:00', $endHour),
                        'is_active' => true,
                    ]);
                }

                // Evening slot (6 PM - 10 PM)
                if (rand(0, 1)) {
                    $startHour = rand(18, 20);
                    $endHour = rand($startHour + 1, 22);

                    MentorAvailabilitie::create([
                        'mentor_profile_id' => $profile->id,
                        'day_of_week' => $day,
                        'start_time' => sprintf('%02d:00:00', $startHour),
                        'end_time' => sprintf('%02d:00:00', $endHour),
                        'is_active' => true,
                    ]);
                }
            }
        }

        // Special availability for demo mentor
        $demoMentor = MentorProfile::whereHas('user', function($query) {
            $query->where('email', 'mentor@example.com');
        })->first();

        if ($demoMentor) {
            // Clear existing availabilities for demo mentor
            MentorAvailabilitie::where('mentor_profile_id', $demoMentor->id)->delete();

            $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
            foreach ($weekdays as $day) {
                MentorAvailabilitie::create([
                    'mentor_profile_id' => $demoMentor->id,
                    'day_of_week' => $day,
                    'start_time' => '09:00:00',
                    'end_time' => '12:00:00',
                    'is_active' => true,
                ]);

                MentorAvailabilitie::create([
                    'mentor_profile_id' => $demoMentor->id,
                    'day_of_week' => $day,
                    'start_time' => '13:00:00',
                    'end_time' => '17:00:00',
                    'is_active' => true,
                ]);
            }

            // Weekend availability
            MentorAvailabilitie::create([
                'mentor_profile_id' => $demoMentor->id,
                'day_of_week' => 'saturday',
                'start_time' => '10:00:00',
                'end_time' => '15:00:00',
                'is_active' => true,
            ]);
        }
    }
}
