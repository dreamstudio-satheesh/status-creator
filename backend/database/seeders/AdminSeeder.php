<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@tamilstatus.app',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
            'is_active' => true,
            'permissions' => [
                'manage_users',
                'manage_content', 
                'view_analytics',
                'manage_admins',
                'system_settings',
            ],
        ]);

        // Create Admin user
        Admin::create([
            'name' => 'Admin User',
            'email' => 'admin.user@tamilstatus.app',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
            'permissions' => [
                'manage_users',
                'manage_content',
                'view_analytics',
            ],
        ]);

        // Create Moderator
        Admin::create([
            'name' => 'Moderator',
            'email' => 'moderator@tamilstatus.app',
            'password' => Hash::make('admin123'),
            'role' => 'moderator',
            'is_active' => true,
            'permissions' => [
                'manage_content',
                'view_analytics',
            ],
        ]);
    }
}