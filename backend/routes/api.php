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
    Route::prefix('auth')->middleware('throttle:auth')->group(function () {
        Route::post('/send-otp', [\App\Http\Controllers\AuthController::class, 'sendOtp']);
        Route::post('/verify-otp', [\App\Http\Controllers\AuthController::class, 'verifyOtp']);
        Route::post('/resend-otp', [\App\Http\Controllers\AuthController::class, 'resendOtp']);
        Route::get('/google/redirect', [\App\Http\Controllers\AuthController::class, 'googleRedirect']);
        Route::get('/google/callback', [\App\Http\Controllers\AuthController::class, 'googleCallback']);
    });

    // Password reset routes
    Route::prefix('password')->middleware('throttle:auth')->group(function () {
        Route::post('/send-reset-otp', [\App\Http\Controllers\PasswordResetController::class, 'sendResetOtp']);
        Route::post('/verify-reset-otp', [\App\Http\Controllers\PasswordResetController::class, 'verifyResetOtp']);
        Route::post('/reset', [\App\Http\Controllers\PasswordResetController::class, 'resetPassword']);
        Route::post('/email-reset-link', [\App\Http\Controllers\PasswordResetController::class, 'sendEmailResetLink']);
    });

    // Public content routes
    Route::prefix('public')->group(function () {
        Route::get('/themes', [\App\Http\Controllers\ThemeController::class, 'index']);
        Route::get('/themes/{theme}', [\App\Http\Controllers\ThemeController::class, 'show']);
        Route::get('/themes/{theme}/templates', [\App\Http\Controllers\TemplateController::class, 'byTheme']);
        Route::get('/templates', [\App\Http\Controllers\TemplateController::class, 'index']);
        Route::get('/templates/{template}', [\App\Http\Controllers\TemplateController::class, 'show']);
        Route::get('/templates/featured', [\App\Http\Controllers\TemplateController::class, 'featured']);
        Route::get('/templates/search', [\App\Http\Controllers\TemplateController::class, 'search']);
        Route::get('/templates/{template}/ratings', [\App\Http\Controllers\TemplateController::class, 'getTemplateRatings']);
        Route::get('/faq', [\App\Http\Controllers\FeedbackController::class, 'faq']);
        Route::get('/contact', [\App\Http\Controllers\FeedbackController::class, 'contactInfo']);
    });
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // User management
    Route::prefix('user')->middleware('throttle:profile')->group(function () {
        Route::get('/profile', [\App\Http\Controllers\UserController::class, 'profile']);
        Route::put('/profile', [\App\Http\Controllers\UserController::class, 'updateProfile']);
        Route::get('/subscription', [\App\Http\Controllers\UserController::class, 'subscription']);
        Route::get('/usage-stats', [\App\Http\Controllers\UserController::class, 'usageStats']);
        Route::get('/creations', [\App\Http\Controllers\UserController::class, 'creations']);
        Route::get('/dashboard', [\App\Http\Controllers\UserController::class, 'dashboard']);
        Route::get('/preferences', [\App\Http\Controllers\UserController::class, 'preferences']);
        Route::put('/preferences', [\App\Http\Controllers\UserController::class, 'updatePreferences']);
        Route::delete('/account', [\App\Http\Controllers\UserController::class, 'deleteAccount']);
    });

    // Feedback and Support
    Route::prefix('feedback')->middleware('throttle:profile')->group(function () {
        Route::post('/submit', [\App\Http\Controllers\FeedbackController::class, 'submit']);
        Route::get('/', [\App\Http\Controllers\FeedbackController::class, 'index']);
        Route::get('/{feedback}', [\App\Http\Controllers\FeedbackController::class, 'show']);
        Route::post('/app-rating', [\App\Http\Controllers\FeedbackController::class, 'submitAppRating']);
    });

    // File uploads
    Route::prefix('uploads')->group(function () {
        Route::post('/avatar', [\App\Http\Controllers\UploadController::class, 'avatar']);
        Route::post('/image', [\App\Http\Controllers\UploadController::class, 'image']);
        Route::delete('/file', [\App\Http\Controllers\UploadController::class, 'deleteFile']);
        Route::get('/limits', [\App\Http\Controllers\UploadController::class, 'getUploadLimits']);
    });

    // Authentication management
    Route::prefix('auth')->middleware('throttle:profile')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout']);
        Route::get('/profile', [\App\Http\Controllers\AuthController::class, 'profile']);
        Route::put('/profile', [\App\Http\Controllers\AuthController::class, 'updateProfile']);
        Route::post('/reset-quota', [\App\Http\Controllers\AuthController::class, 'resetQuota']);
    });

    // Email verification routes
    Route::prefix('email')->middleware('throttle:profile')->group(function () {
        Route::post('/verification-notification', [\App\Http\Controllers\EmailVerificationController::class, 'sendVerificationEmail']);
        Route::get('/verify/{id}/{hash}', [\App\Http\Controllers\EmailVerificationController::class, 'verifyEmail'])->name('verification.verify');
        Route::get('/verification-status', [\App\Http\Controllers\EmailVerificationController::class, 'checkVerificationStatus']);
    });

    // Template management
    Route::prefix('templates')->group(function () {
        Route::get('/', [\App\Http\Controllers\TemplateController::class, 'index']);
        Route::get('/favorites', [\App\Http\Controllers\TemplateController::class, 'favorites']);
        Route::get('/{template}', [\App\Http\Controllers\TemplateController::class, 'show']);
        Route::post('/{template}/use', [\App\Http\Controllers\TemplateController::class, 'useTemplate']);
        Route::post('/{template}/favorite', [\App\Http\Controllers\TemplateController::class, 'toggleFavorite']);
        Route::post('/{template}/rate', [\App\Http\Controllers\TemplateController::class, 'rateTemplate']);
        Route::get('/{template}/ratings', [\App\Http\Controllers\TemplateController::class, 'getTemplateRatings']);
    });

    // Theme management
    Route::prefix('themes')->group(function () {
        Route::get('/', [\App\Http\Controllers\ThemeController::class, 'index']);
        Route::get('/{theme}', [\App\Http\Controllers\ThemeController::class, 'show']);
        Route::get('/{theme}/templates', [\App\Http\Controllers\TemplateController::class, 'byTheme']);
    });

    // AI Generation (with quota checking)
    Route::middleware('ai_quota')->prefix('ai')->group(function () {
        Route::post('/generate-quote', [\App\Http\Controllers\AIController::class, 'generateQuote']);
        Route::post('/caption-image', [\App\Http\Controllers\AIController::class, 'captionImage']);
        Route::post('/regenerate', [\App\Http\Controllers\AIController::class, 'regenerate']);
    });

    // AI Information (no quota needed)
    Route::prefix('ai')->group(function () {
        Route::get('/quota', [\App\Http\Controllers\AIController::class, 'quota']);
        Route::get('/models', [\App\Http\Controllers\AIController::class, 'models']);
        Route::get('/usage', [\App\Http\Controllers\AIController::class, 'usage']);
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