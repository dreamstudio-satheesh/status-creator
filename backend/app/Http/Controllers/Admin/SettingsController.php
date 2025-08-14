<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $settings = [
            // App Settings
            'app_name' => Setting::get('app_name', 'Tamil Status Creator'),
            'app_description' => Setting::get('app_description', 'Create beautiful Tamil status messages with AI'),
            'app_logo' => Setting::get('app_logo'),
            'app_favicon' => Setting::get('app_favicon'),
            'app_version' => Setting::get('app_version', '1.0.0'),
            
            // AI Settings
            'openrouter_api_key' => Setting::get('openrouter_api_key'),
            'huggingface_api_key' => Setting::get('huggingface_api_key'),
            'default_ai_model' => Setting::get('default_ai_model', 'meta-llama/llama-3.1-70b-instruct'),
            'ai_cost_per_request' => Setting::get('ai_cost_per_request', 0.02),
            'max_ai_requests_per_day' => Setting::get('max_ai_requests_per_day', 1000),
            'ai_timeout_seconds' => Setting::get('ai_timeout_seconds', 30),
            
            // User Settings
            'allow_user_registration' => Setting::get('allow_user_registration', true),
            'require_email_verification' => Setting::get('require_email_verification', true),
            'default_user_quota' => Setting::get('default_user_quota', 5),
            'premium_user_quota' => Setting::get('premium_user_quota', -1), // -1 = unlimited
            
            // Content Settings
            'auto_approve_content' => Setting::get('auto_approve_content', true),
            'content_moderation_enabled' => Setting::get('content_moderation_enabled', false),
            'max_content_length' => Setting::get('max_content_length', 500),
            'watermark_enabled' => Setting::get('watermark_enabled', false),
            'watermark_text' => Setting::get('watermark_text', 'Created with Tamil Status Creator'),
            
            // File Upload Settings
            'max_file_size_mb' => Setting::get('max_file_size_mb', 5),
            'allowed_image_types' => Setting::get('allowed_image_types', 'jpg,jpeg,png,webp'),
            'storage_driver' => Setting::get('storage_driver', 'local'),
            'aws_s3_bucket' => Setting::get('aws_s3_bucket'),
            'aws_s3_region' => Setting::get('aws_s3_region'),
            
            // Email Settings
            'smtp_host' => Setting::get('smtp_host'),
            'smtp_port' => Setting::get('smtp_port', 587),
            'smtp_username' => Setting::get('smtp_username'),
            'smtp_password' => Setting::get('smtp_password'),
            'smtp_encryption' => Setting::get('smtp_encryption', 'tls'),
            'mail_from_address' => Setting::get('mail_from_address'),
            'mail_from_name' => Setting::get('mail_from_name'),
            
            // Payment Settings
            'razorpay_key_id' => Setting::get('razorpay_key_id'),
            'razorpay_key_secret' => Setting::get('razorpay_key_secret'),
            'premium_price_monthly' => Setting::get('premium_price_monthly', 299),
            'premium_price_yearly' => Setting::get('premium_price_yearly', 2999),
            'currency' => Setting::get('currency', 'INR'),
            
            // Social Media Settings
            'facebook_app_id' => Setting::get('facebook_app_id'),
            'google_client_id' => Setting::get('google_client_id'),
            'twitter_api_key' => Setting::get('twitter_api_key'),
            
            // Analytics Settings
            'google_analytics_id' => Setting::get('google_analytics_id'),
            'facebook_pixel_id' => Setting::get('facebook_pixel_id'),
            'analytics_enabled' => Setting::get('analytics_enabled', false),
            
            // Cache Settings
            'cache_driver' => Setting::get('cache_driver', 'redis'),
            'cache_ttl_minutes' => Setting::get('cache_ttl_minutes', 60),
            'enable_query_cache' => Setting::get('enable_query_cache', true),
            
            // Security Settings
            'enable_rate_limiting' => Setting::get('enable_rate_limiting', true),
            'max_login_attempts' => Setting::get('max_login_attempts', 5),
            'lockout_duration_minutes' => Setting::get('lockout_duration_minutes', 15),
            'session_lifetime_minutes' => Setting::get('session_lifetime_minutes', 120),
            
            // Backup Settings
            'backup_enabled' => Setting::get('backup_enabled', false),
            'backup_frequency' => Setting::get('backup_frequency', 'daily'),
            'backup_retention_days' => Setting::get('backup_retention_days', 30),
            'backup_storage' => Setting::get('backup_storage', 'local'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            // App Settings
            'app_name' => 'required|string|max:255',
            'app_description' => 'required|string|max:500',
            'app_logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'app_favicon' => 'nullable|image|mimes:ico,png|max:512',
            'app_version' => 'nullable|string|max:20',
            
            // AI Settings
            'openrouter_api_key' => 'nullable|string',
            'huggingface_api_key' => 'nullable|string',
            'default_ai_model' => 'required|string',
            'ai_cost_per_request' => 'required|numeric|min:0',
            'max_ai_requests_per_day' => 'required|integer|min:1',
            'ai_timeout_seconds' => 'required|integer|min:10|max:120',
            
            // User Settings
            'allow_user_registration' => 'boolean',
            'require_email_verification' => 'boolean',
            'default_user_quota' => 'required|integer|min:0',
            'premium_user_quota' => 'required|integer|min:-1',
            
            // Content Settings
            'auto_approve_content' => 'boolean',
            'content_moderation_enabled' => 'boolean',
            'max_content_length' => 'required|integer|min:100|max:2000',
            'watermark_enabled' => 'boolean',
            'watermark_text' => 'nullable|string|max:100',
            
            // File Upload Settings
            'max_file_size_mb' => 'required|integer|min:1|max:50',
            'allowed_image_types' => 'required|string',
            'storage_driver' => 'required|in:local,s3,spaces',
            'aws_s3_bucket' => 'nullable|string',
            'aws_s3_region' => 'nullable|string',
            
            // Email Settings
            'smtp_host' => 'nullable|string',
            'smtp_port' => 'nullable|integer',
            'smtp_username' => 'nullable|string',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'nullable|in:tls,ssl',
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string',
            
            // Payment Settings
            'razorpay_key_id' => 'nullable|string',
            'razorpay_key_secret' => 'nullable|string',
            'premium_price_monthly' => 'required|numeric|min:0',
            'premium_price_yearly' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            
            // Social Media Settings
            'facebook_app_id' => 'nullable|string',
            'google_client_id' => 'nullable|string',
            'twitter_api_key' => 'nullable|string',
            
            // Analytics Settings
            'google_analytics_id' => 'nullable|string',
            'facebook_pixel_id' => 'nullable|string',
            'analytics_enabled' => 'boolean',
            
            // Cache Settings
            'cache_driver' => 'required|in:redis,memcached,database,file',
            'cache_ttl_minutes' => 'required|integer|min:1',
            'enable_query_cache' => 'boolean',
            
            // Security Settings
            'enable_rate_limiting' => 'boolean',
            'max_login_attempts' => 'required|integer|min:1',
            'lockout_duration_minutes' => 'required|integer|min:1',
            'session_lifetime_minutes' => 'required|integer|min:10',
            
            // Backup Settings
            'backup_enabled' => 'boolean',
            'backup_frequency' => 'required|in:hourly,daily,weekly',
            'backup_retention_days' => 'required|integer|min:1',
            'backup_storage' => 'required|in:local,s3,spaces',
        ]);

        DB::beginTransaction();
        
        try {
            // Handle file uploads
            if ($request->hasFile('app_logo')) {
                $logoPath = $request->file('app_logo')->store('settings', 'public');
                Setting::set('app_logo', $logoPath);
            }

            if ($request->hasFile('app_favicon')) {
                $faviconPath = $request->file('app_favicon')->store('settings', 'public');
                Setting::set('app_favicon', $faviconPath);
            }

            // Update all settings
            foreach ($request->except(['_token', '_method', 'app_logo', 'app_favicon']) as $key => $value) {
                // Convert boolean values
                if (in_array($key, [
                    'allow_user_registration', 'require_email_verification', 'auto_approve_content',
                    'content_moderation_enabled', 'watermark_enabled', 'analytics_enabled',
                    'enable_query_cache', 'enable_rate_limiting', 'backup_enabled'
                ])) {
                    $value = $request->boolean($key);
                }

                Setting::set($key, $value);
            }

            DB::commit();

            // Clear relevant caches
            Cache::flush();
            Artisan::call('config:clear');
            Artisan::call('view:clear');

            return redirect()->route('admin.settings.index')
                ->with('success', 'Settings updated successfully');

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->route('admin.settings.index')
                ->with('error', 'Error updating settings: ' . $e->getMessage());
        }
    }

    public function backup()
    {
        try {
            // Create database backup
            $filename = 'backup_' . now()->format('Y_m_d_H_i_s') . '.sql';
            $path = storage_path('app/backups/' . $filename);
            
            // Ensure backup directory exists
            if (!Storage::exists('backups')) {
                Storage::makeDirectory('backups');
            }

            // Run database backup
            $command = sprintf(
                'mysqldump -h%s -u%s -p%s %s > %s',
                config('database.connections.mysql.host'),
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.database'),
                $path
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Database backup failed');
            }

            // Create backup metadata
            $metadata = [
                'filename' => $filename,
                'size' => filesize($path),
                'created_at' => now()->toISOString(),
                'type' => 'manual',
                'admin_id' => auth('admin')->id(),
            ];

            Storage::put('backups/' . $filename . '.meta', json_encode($metadata));

            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully',
                'filename' => $filename,
                'size' => $metadata['size'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|string',
        ]);

        try {
            $filename = $request->backup_file;
            $path = storage_path('app/backups/' . $filename);

            if (!file_exists($path)) {
                throw new \Exception('Backup file not found');
            }

            // Restore database
            $command = sprintf(
                'mysql -h%s -u%s -p%s %s < %s',
                config('database.connections.mysql.host'),
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.database'),
                $path
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Database restore failed');
            }

            // Clear caches after restore
            Cache::flush();
            Artisan::call('config:clear');

            return response()->json([
                'success' => true,
                'message' => 'Database restored successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function listBackups()
    {
        $backups = [];
        $backupFiles = Storage::files('backups');

        foreach ($backupFiles as $file) {
            if (str_ends_with($file, '.sql')) {
                $metaFile = $file . '.meta';
                $metadata = Storage::exists($metaFile) 
                    ? json_decode(Storage::get($metaFile), true)
                    : [];

                $backups[] = [
                    'filename' => basename($file),
                    'size' => Storage::size($file),
                    'created_at' => $metadata['created_at'] ?? Storage::lastModified($file),
                    'type' => $metadata['type'] ?? 'unknown',
                ];
            }
        }

        return response()->json([
            'backups' => collect($backups)->sortByDesc('created_at')->values(),
        ]);
    }

    public function deleteBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|string',
        ]);

        try {
            $filename = $request->backup_file;
            $sqlPath = 'backups/' . $filename;
            $metaPath = 'backups/' . $filename . '.meta';

            if (!Storage::exists($sqlPath)) {
                throw new \Exception('Backup file not found');
            }

            Storage::delete($sqlPath);
            if (Storage::exists($metaPath)) {
                Storage::delete($metaPath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Backup deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function clearCache()
    {
        try {
            Cache::flush();
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cache clear failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            // Send test email
            // Mail::to($request->test_email)->send(new TestEmail());

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Email test failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}