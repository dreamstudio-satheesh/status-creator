<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'mobile' => '+919876543210',
            'password' => bcrypt('admin123'),
            'subscription_type' => 'premium',
            'daily_ai_quota' => 1000,
            'email_verified_at' => now(),
        ]);
    }
}
