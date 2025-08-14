<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $themes = [
            [
                'name' => 'Love',
                'slug' => 'love',
                'name_ta' => 'காதல்',
                'description' => 'Romantic and love quotes',
                'color' => '#FF69B4',
                'order_index' => 1,
            ],
            [
                'name' => 'Motivation',
                'slug' => 'motivation',
                'name_ta' => 'ஊக்கம்',
                'description' => 'Motivational and inspirational quotes',
                'color' => '#FFD700',
                'order_index' => 2,
            ],
            [
                'name' => 'Sad',
                'slug' => 'sad',
                'name_ta' => 'சோகம்',
                'description' => 'Emotional and sad quotes',
                'color' => '#4169E1',
                'order_index' => 3,
            ],
            [
                'name' => 'Friendship',
                'slug' => 'friendship',
                'name_ta' => 'நட்பு',
                'description' => 'Friendship quotes',
                'color' => '#32CD32',
                'order_index' => 4,
            ],
            [
                'name' => 'Life',
                'slug' => 'life',
                'name_ta' => 'வாழ்க்கை',
                'description' => 'Life philosophy quotes',
                'color' => '#FF8C00',
                'order_index' => 5,
            ],
            [
                'name' => 'Success',
                'slug' => 'success',
                'name_ta' => 'வெற்றி',
                'description' => 'Success and achievement quotes',
                'color' => '#9370DB',
                'order_index' => 6,
            ],
            [
                'name' => 'Family',
                'slug' => 'family',
                'name_ta' => 'குடும்பம்',
                'description' => 'Family and relationship quotes',
                'color' => '#DC143C',
                'order_index' => 7,
            ],
            [
                'name' => 'Morning',
                'slug' => 'morning',
                'name_ta' => 'காலை வணக்கம்',
                'description' => 'Good morning wishes',
                'color' => '#FFA500',
                'order_index' => 8,
            ],
            [
                'name' => 'Night',
                'slug' => 'night',
                'name_ta' => 'இரவு வணக்கம்',
                'description' => 'Good night wishes',
                'color' => '#191970',
                'order_index' => 9,
            ],
            [
                'name' => 'Festival',
                'slug' => 'festival',
                'name_ta' => 'பண்டிகை',
                'description' => 'Festival wishes and greetings',
                'color' => '#FF1493',
                'order_index' => 10,
            ],
        ];

        foreach ($themes as $theme) {
            \App\Models\Theme::create($theme);
        }
    }
}
