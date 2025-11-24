<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Topic;
use Illuminate\Support\Str;

class TopicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $topics = [
            ['name' => 'Public Safety', 'icon' => 'ri-government-line', 'color' => 'text-primary'],
            ['name' => 'Infrastructure', 'icon' => 'ri-building-line', 'color' => 'text-success'],
            ['name' => 'Education & Youth', 'icon' => 'ri-book-line', 'color' => 'text-info'],
            ['name' => 'General Discussion', 'icon' => 'ri-chat-4-line', 'color' => 'text-warning'],
            ['name' => 'Environment', 'icon' => 'ri-leaf-line', 'color' => 'text-success'],
        ];

        foreach ($topics as $topic) {
            Topic::create([
                'name' => $topic['name'],
                'slug' => Str::slug($topic['name']),
                'icon' => $topic['icon'],
                'color' => $topic['color'],
            ]);
        }
    }
}
