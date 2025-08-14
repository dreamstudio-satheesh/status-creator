<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/send-otp', [\App\Http\Controllers\AuthController::class, 'sendOtp']);
        Route::post('/verify-otp', [\App\Http\Controllers\AuthController::class, 'verifyOtp']);
        Route::post('/google-login', [\App\Http\Controllers\AuthController::class, 'googleLogin']);
        Route::post('/refresh', [\App\Http\Controllers\AuthController::class, 'refresh']);
    });

    // Public content routes
    Route::prefix('public')->group(function () {
        Route::get('/themes', [\App\Http\Controllers\ThemeController::class, 'index']);
        Route::get('/themes/{theme}/templates', [\App\Http\Controllers\TemplateController::class, 'byTheme']);
        Route::get('/templates/featured', [\App\Http\Controllers\TemplateController::class, 'featured']);
        Route::get('/templates/search', [\App\Http\Controllers\TemplateController::class, 'search']);
    });
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // User management
    Route::prefix('user')->group(function () {
        Route::get('/profile', [\App\Http\Controllers\UserController::class, 'profile']);
        Route::put('/profile', [\App\Http\Controllers\UserController::class, 'updateProfile']);
        Route::get('/subscription', [\App\Http\Controllers\UserController::class, 'subscription']);
        Route::get('/usage-stats', [\App\Http\Controllers\UserController::class, 'usageStats']);
        Route::get('/creations', [\App\Http\Controllers\UserController::class, 'creations']);
        Route::delete('/account', [\App\Http\Controllers\UserController::class, 'deleteAccount']);
    });

    // Authentication management
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout']);
        Route::get('/me', [\App\Http\Controllers\AuthController::class, 'me']);
    });

    // Template management
    Route::prefix('templates')->group(function () {
        Route::get('/', [\App\Http\Controllers\TemplateController::class, 'index']);
        Route::get('/{template}', [\App\Http\Controllers\TemplateController::class, 'show']);
        Route::post('/{template}/use', [\App\Http\Controllers\TemplateController::class, 'useTemplate']);
        Route::post('/{template}/favorite', [\App\Http\Controllers\TemplateController::class, 'toggleFavorite']);
    });

    // AI Generation (Premium users)
    Route::middleware('premium')->prefix('ai')->group(function () {
        Route::post('/generate-quote', [\App\Http\Controllers\AIController::class, 'generateQuote']);
        Route::post('/caption-image', [\App\Http\Controllers\AIController::class, 'captionImage']);
        Route::get('/quota', [\App\Http\Controllers\AIController::class, 'quota']);
    });

    // User creations
    Route::prefix('creations')->group(function () {
        Route::get('/', [\App\Http\Controllers\CreationController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\CreationController::class, 'store']);
        Route::get('/{creation}', [\App\Http\Controllers\CreationController::class, 'show']);
        Route::put('/{creation}', [\App\Http\Controllers\CreationController::class, 'update']);
        Route::delete('/{creation}', [\App\Http\Controllers\CreationController::class, 'destroy']);
        Route::post('/{creation}/share', [\App\Http\Controllers\CreationController::class, 'share']);
    });

    // Subscription management
    Route::prefix('subscription')->group(function () {
        Route::get('/plans', [\App\Http\Controllers\SubscriptionController::class, 'plans']);
        Route::post('/create', [\App\Http\Controllers\SubscriptionController::class, 'create']);
        Route::post('/cancel', [\App\Http\Controllers\SubscriptionController::class, 'cancel']);
        Route::get('/history', [\App\Http\Controllers\SubscriptionController::class, 'history']);
    });

    // File uploads
    Route::prefix('uploads')->group(function () {
        Route::post('/image', [\App\Http\Controllers\UploadController::class, 'image']);
        Route::post('/avatar', [\App\Http\Controllers\UploadController::class, 'avatar']);
    });
});

// Admin routes (require admin role)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index']);
    Route::get('/analytics', [\App\Http\Controllers\Admin\DashboardController::class, 'analytics']);

    // User management
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::post('/users/{user}/toggle-premium', [\App\Http\Controllers\Admin\UserController::class, 'togglePremium']);

    // Theme management
    Route::resource('themes', \App\Http\Controllers\Admin\ThemeController::class);

    // Template management
    Route::resource('templates', \App\Http\Controllers\Admin\TemplateController::class);
    Route::post('/templates/bulk-generate', [\App\Http\Controllers\Admin\TemplateController::class, 'bulkGenerate']);

    // AI management
    Route::prefix('ai')->group(function () {
        Route::get('/usage', [\App\Http\Controllers\Admin\AIController::class, 'usage']);
        Route::get('/costs', [\App\Http\Controllers\Admin\AIController::class, 'costs']);
        Route::post('/bulk-caption', [\App\Http\Controllers\Admin\AIController::class, 'bulkCaption']);
    });

    // System settings
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index']);
    Route::put('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update']);
});

// Webhook routes (no authentication)
Route::prefix('webhooks')->group(function () {
    Route::post('/razorpay', [\App\Http\Controllers\WebhookController::class, 'razorpay']);
    Route::post('/firebase', [\App\Http\Controllers\WebhookController::class, 'firebase']);
});