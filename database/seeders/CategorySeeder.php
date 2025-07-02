<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create predefined categories
        $categories = [
            [
                'name' => 'Web Development',
                'description' => 'Learn web development from experienced mentors. Topics include HTML, CSS, JavaScript, React, Vue.js, and more.',
                'icon' => 'fa-code',
            ],
            [
                'name' => 'Mobile Development',
                'description' => 'Learn mobile app development for iOS and Android platforms. Topics include Swift, Kotlin, Flutter, React Native, and more.',
                'icon' => 'fa-mobile-alt',
            ],
            [
                'name' => 'Data Science',
                'description' => 'Learn data science and analytics from experienced professionals. Topics include Python, R, SQL, machine learning, and more.',
                'icon' => 'fa-chart-bar',
            ],
            [
                'name' => 'UI/UX Design',
                'description' => 'Learn UI/UX design principles and tools from professional designers. Topics include Figma, Adobe XD, user research, and more.',
                'icon' => 'fa-paint-brush',
            ],
            [
                'name' => 'DevOps',
                'description' => 'Learn DevOps practices and tools from experienced engineers. Topics include Docker, Kubernetes, CI/CD, and more.',
                'icon' => 'fa-server',
            ],
            [
                'name' => 'Cybersecurity',
                'description' => 'Learn cybersecurity principles and practices from security experts. Topics include penetration testing, secure coding, and more.',
                'icon' => 'fa-shield-alt',
            ],
            [
                'name' => 'Product Management',
                'description' => 'Learn product management principles and methodologies from experienced product managers.',
                'icon' => 'fa-tasks',
            ],
            [
                'name' => 'Artificial Intelligence',
                'description' => 'Learn AI and machine learning concepts and applications from AI specialists and researchers.',
                'icon' => 'fa-robot',
            ],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'icon' => $category['icon'],
                'is_active' => true,
            ]);
        }

        // Create some random categories
        Category::factory()->count(5)->create();
    }
}
