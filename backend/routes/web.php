<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AuthController;

Route::get('/', function () {
    return view('welcome');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Authentication Routes
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    
    // Protected Admin Routes
    Route::middleware('auth:admin')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::get('system-health', [DashboardController::class, 'systemHealth'])->name('system-health');
        
        // User Management
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        Route::post('users/{user}/toggle-premium', [\App\Http\Controllers\Admin\UserController::class, 'togglePremium'])->name('users.toggle-premium');
        Route::post('users/bulk-action', [\App\Http\Controllers\Admin\UserController::class, 'bulkAction'])->name('users.bulk-action');
        
        // Theme Management
        Route::resource('themes', \App\Http\Controllers\Admin\ThemeController::class);
        Route::post('themes/{theme}/duplicate', [\App\Http\Controllers\Admin\ThemeController::class, 'duplicate'])->name('themes.duplicate');
        Route::post('themes/bulk-action', [\App\Http\Controllers\Admin\ThemeController::class, 'bulkAction'])->name('themes.bulk-action');
        
        // Template Management
        Route::resource('templates', \App\Http\Controllers\Admin\TemplateController::class);
        Route::post('templates/{template}/duplicate', [\App\Http\Controllers\Admin\TemplateController::class, 'duplicate'])->name('templates.duplicate');
        Route::post('templates/bulk-action', [\App\Http\Controllers\Admin\TemplateController::class, 'bulkAction'])->name('templates.bulk-action');
        Route::get('templates/{template}/analytics', [\App\Http\Controllers\Admin\TemplateController::class, 'analytics'])->name('templates.analytics');
        
        // AI Management
        Route::prefix('ai')->name('ai.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AIController::class, 'index'])->name('index');
            Route::get('bulk-generation', [\App\Http\Controllers\Admin\AIController::class, 'bulkGeneration'])->name('bulk-generation');
            Route::post('generate-bulk', [\App\Http\Controllers\Admin\AIController::class, 'generateBulk'])->name('generate-bulk');
            Route::get('usage', [\App\Http\Controllers\Admin\AIController::class, 'usage'])->name('usage');
            Route::get('costs', [\App\Http\Controllers\Admin\AIController::class, 'costs'])->name('costs');
            Route::post('bulk-caption', [\App\Http\Controllers\Admin\AIController::class, 'bulkCaption'])->name('bulk-caption');
            Route::get('test-models', [\App\Http\Controllers\Admin\AIController::class, 'testModels'])->name('test-models');
            Route::get('settings', [\App\Http\Controllers\Admin\AIController::class, 'settings'])->name('settings');
            Route::post('settings', [\App\Http\Controllers\Admin\AIController::class, 'updateSettings'])->name('settings.update');
        });
        
        // Settings Management
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('update');
            Route::post('backup', [\App\Http\Controllers\Admin\SettingsController::class, 'backup'])->name('backup');
            Route::post('restore', [\App\Http\Controllers\Admin\SettingsController::class, 'restore'])->name('restore');
            Route::get('backups', [\App\Http\Controllers\Admin\SettingsController::class, 'listBackups'])->name('backups');
            Route::delete('backups', [\App\Http\Controllers\Admin\SettingsController::class, 'deleteBackup'])->name('backups.delete');
            Route::post('clear-cache', [\App\Http\Controllers\Admin\SettingsController::class, 'clearCache'])->name('clear-cache');
            Route::post('test-email', [\App\Http\Controllers\Admin\SettingsController::class, 'testEmail'])->name('test-email');
        });
        
        // Activity Logs
        Route::prefix('activity')->name('activity.')->group(function () {
            Route::get('/', function () {
                $logs = \App\Models\ActivityLog::with(['causer', 'subject'])
                    ->latest('created_at')
                    ->paginate(50);
                return view('admin.activity.index', compact('logs'));
            })->name('index');
            
            Route::get('export', function () {
                // Export activity logs functionality
                return response()->json(['message' => 'Export functionality to be implemented']);
            })->name('export');
        });
        
        // Analytics
        Route::get('analytics', function () { return view('admin.analytics'); })->name('analytics');
        
        // Feedback Management
        Route::get('feedback', function () { return view('admin.feedback.index'); })->name('feedback.index');
        
        // Admin Management (Super Admin only)
        Route::middleware('can:manage_admins')->group(function () {
            Route::get('admins', function () { return view('admin.admins.index'); })->name('admins.index');
        });
    });
});
