<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key_name' => 'free_daily_limit',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Daily AI generation limit for free users'
            ],
            [
                'key_name' => 'premium_daily_limit',
                'value' => '100',
                'type' => 'integer',
                'description' => 'Daily AI generation limit for premium users'
            ],
            [
                'key_name' => 'max_upload_size_mb',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Maximum upload size in MB'
            ],
            [
                'key_name' => 'watermark_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable watermark on free user creations'
            ],
            [
                'key_name' => 'maintenance_mode',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Enable maintenance mode'
            ],
            [
                'key_name' => 'openrouter_model',
                'value' => 'meta-llama/llama-3.2-3b-instruct:free',
                'type' => 'string',
                'description' => 'OpenRouter model to use'
            ],
            [
                'key_name' => 'caption_model',
                'value' => 'Salesforce/blip-image-captioning-base',
                'type' => 'string',
                'description' => 'Image captioning model'
            ],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::create($setting);
        }
    }
}
