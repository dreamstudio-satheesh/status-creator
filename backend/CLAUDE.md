# CLAUDE.md - Backend Development Guide

This file provides specific guidance to Claude Code when working with the Laravel backend of the AI Tamil Status Creator application.

## Project Context

This is the Laravel 11 backend API for the AI Tamil Status Creator - a comprehensive system for generating and managing Tamil status images with AI integration, user management, and a full-featured admin panel.

## Architecture Overview

### Core Components
- **API Layer**: RESTful API endpoints for Flutter app communication
- **Admin Panel**: Laravel Blade-based admin interface with TailwindCSS
- **AI Integration**: OpenRouter LLM + Hugging Face image captioning
- **Database Layer**: MySQL with Tamil text support (utf8mb4)
- **Queue System**: Redis-based background job processing
- **File Storage**: MinIO (dev) / S3 (prod) for image assets

### Key Models and Relationships
- `User` - Main user model with subscription management
- `Theme` - Content categories (Love, Motivation, etc.)
- `Template` - Status templates (prebuilt and AI-generated)
- `UserCreation` - User-generated status tracking
- `Subscription` - Premium user management
- `Admin` - Admin users with role-based permissions
- `ActivityLog` - Admin action auditing

## Development Patterns

### Controller Organization
```php
// API Controllers (for Flutter app)
app/Http/Controllers/
├── AuthController.php           // User authentication
├── ThemeController.php          // Theme management
├── TemplateController.php       // Template operations
├── AIController.php            // AI generation
├── UploadController.php        // File uploads
├── FeedbackController.php      // User feedback
└── UserController.php          // User profile

// Admin Controllers (for admin panel)
app/Http/Controllers/Admin/
├── DashboardController.php     // Analytics dashboard
├── AuthController.php          // Admin authentication
├── UserController.php          // User management
├── ThemeController.php         // Theme CRUD
├── TemplateController.php      // Template CRUD
├── AIController.php           // AI management
└── SettingsController.php     // System settings
```

### Middleware Usage
- `auth:sanctum` - API authentication
- `auth:admin` - Admin panel authentication
- `CheckPremiumUser` - Premium feature access
- `CheckAIQuota` - AI generation limits
- `AdminAuth` - Role-based admin access

### Service Layer Pattern
```php
app/Services/
├── OpenRouterService.php       // AI text generation
├── HuggingFaceService.php     // Image captioning
├── FileUploadService.php      // File handling
└── MSG91Service.php           // SMS notifications
```

## Database Conventions

### Migration Patterns
- Use `utf8mb4_unicode_ci` collation for Tamil text support
- Include proper indexes for performance
- Add foreign key constraints with cascading
- Use meaningful column names with snake_case

### Model Relationships
```php
// Example relationship patterns
class Theme extends Model
{
    public function templates()
    {
        return $this->hasMany(Template::class);
    }
    
    public function userCreations()
    {
        return $this->hasManyThrough(UserCreation::class, Template::class);
    }
}

class User extends Model
{
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }
    
    public function isPremium(): bool
    {
        return $this->subscription_type === 'premium' 
            && $this->subscription_expires_at > now();
    }
}
```

## API Development Guidelines

### Response Format
```php
// Success response
return response()->json([
    'success' => true,
    'message' => 'Operation completed successfully',
    'data' => $data,
    'meta' => $pagination ?? null
]);

// Error response  
return response()->json([
    'success' => false,
    'message' => 'Error message',
    'errors' => $validationErrors ?? null
], 400);
```

### Validation Patterns
```php
// Request validation example
$request->validate([
    'theme_id' => 'required|exists:themes,id',
    'text' => 'required|string|max:500',
    'language' => 'required|in:tamil,english',
    'is_premium' => 'sometimes|boolean'
]);
```

### Rate Limiting
- API endpoints: 60 requests/minute per user
- AI generation: 5 requests/minute for free users
- File uploads: 10 requests/minute
- Admin endpoints: No rate limiting

## Admin Panel Development

### Blade Template Structure
```
resources/views/admin/
├── layouts/
│   └── app.blade.php           // Main admin layout
├── auth/
│   └── login.blade.php         // Admin login
├── dashboard.blade.php         // Main dashboard
├── users/                      // User management views
├── themes/                     // Theme management views  
├── templates/                  // Template management views
└── settings/                   // Settings views
```

### TailwindCSS Classes
- Use custom admin color palette: `admin-*`, `primary-*`, `success-*`, `warning-*`, `danger-*`
- Component classes: `.admin-card`, `.admin-btn`, `.admin-table`, etc.
- Responsive design with `lg:` prefixes for desktop

### JavaScript Patterns
```javascript
// Admin dashboard functionality
class AdminDashboard {
    // Chart initialization with Chart.js
    initCharts() {
        // User growth, revenue, status generation charts
    }
    
    // Notification system
    showNotification(message, type, duration) {
        // Toast notifications
    }
    
    // Data table enhancements
    initDataTables() {
        // Sorting, filtering, search
    }
}
```

## AI Integration

### OpenRouter Service Usage
```php
// Generate Tamil status text
$response = app(OpenRouterService::class)->generateText([
    'theme' => 'love',
    'mood' => 'romantic',
    'length' => 'short',
    'language' => 'tamil'
]);
```

### Image Captioning
```php
// Generate image description
$caption = app(HuggingFaceService::class)->generateCaption($imageUrl);
```

### Cost Optimization
- Cache AI responses for 24 hours
- Use bulk generation for admin prebuilt templates
- Track usage per user with daily quotas
- Implement fallback to cached templates

## Queue Jobs

### Job Types
```php
app/Jobs/
├── ProcessAIGeneration.php     // Individual AI requests
├── BulkAIGeneration.php       // Admin bulk generation
└── FileCleanupJob.php         // Storage maintenance
```

### Queue Usage
```php
// Dispatch AI generation job
ProcessAIGeneration::dispatch($user, $request->all())
    ->onQueue('ai-processing');

// Bulk generation for admin
BulkAIGeneration::dispatch($themeId, $count)
    ->onQueue('bulk-ai');
```

## File Storage

### Upload Patterns
```php
// Image upload with validation
$path = $request->file('image')->store('uploads', 's3');

// Generate signed URLs for private files
$url = Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(60));
```

### Storage Organization
```
storage/
├── uploads/                    // User uploaded images
├── templates/                  // Template assets
├── generated/                  // AI generated content
└── backups/                    // System backups
```

## Testing Patterns

### Feature Tests
```php
// API endpoint testing
public function test_user_can_generate_status()
{
    $user = User::factory()->premium()->create();
    
    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/generate-custom', [
            'theme_id' => 1,
            'prompt' => 'Test prompt'
        ]);
        
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => ['text', 'image_url']
        ]);
}
```

### Admin Panel Tests  
```php
// Admin functionality testing
public function test_admin_can_view_dashboard()
{
    $admin = Admin::factory()->create();
    
    $response = $this->actingAs($admin, 'admin')
        ->get('/admin/dashboard');
        
    $response->assertStatus(200)
        ->assertViewIs('admin.dashboard')
        ->assertViewHas(['stats', 'chartData']);
}
```

## Performance Optimization

### Caching Strategy
- Dashboard stats: 5 minutes
- Theme/template lists: 1 hour  
- User profiles: 30 minutes
- AI responses: 24 hours

### Database Optimization
- Use eager loading: `with(['theme', 'user'])`
- Implement database indexes on foreign keys
- Use chunking for bulk operations
- Monitor query performance with debugbar

### Queue Optimization
- Use Redis for queue backend
- Implement job batching for bulk operations
- Set appropriate retry limits and timeouts
- Monitor failed jobs

## Security Considerations

### Authentication
- Use Laravel Sanctum for API tokens
- Implement rate limiting on auth endpoints
- Add CSRF protection for admin panel
- Use password hashing with bcrypt

### Authorization
- Implement role-based access control for admins
- Use policy classes for complex permissions
- Validate user ownership of resources
- Sanitize user inputs

### Data Protection
- Encrypt sensitive configuration
- Use HTTPS in production
- Implement proper CORS policies
- Log admin activities for auditing

## Deployment Checklist

### Production Setup
1. Set `APP_ENV=production`
2. Configure proper database credentials
3. Set up Redis for caching and queues
4. Configure S3/Spaces for file storage
5. Set up proper SSL certificates
6. Configure queue workers with Supervisor
7. Set up log rotation
8. Configure backup schedules

### Performance Tuning
1. Run `php artisan config:cache`
2. Run `php artisan route:cache`
3. Run `php artisan view:cache`
4. Enable OPcache in PHP
5. Configure Redis memory limits
6. Set up CDN for static assets

## Troubleshooting Common Issues

### AI Service Errors
- Check API keys in environment
- Verify quota limits
- Check network connectivity
- Review error logs in `storage/logs/`

### Queue Issues
- Restart queue workers after deployment
- Check Redis connection
- Monitor failed jobs table
- Verify job serialization

### File Upload Problems
- Check storage permissions
- Verify S3 credentials
- Check file size limits
- Review upload validation rules

## Essential Commands

```bash
# Development workflow
php artisan serve                    # Start development server
php artisan queue:work              # Start queue processing
php artisan migrate:fresh --seed    # Fresh database setup

# Admin operations
php artisan admin:create            # Create admin user
php artisan cache:clear             # Clear application cache
php artisan config:cache           # Cache configuration

# Database operations  
php artisan migrate                 # Run migrations
php artisan db:seed --class=ThemeSeeder  # Seed specific data
php artisan backup:run             # Create database backup

# Asset compilation
npm run dev                        # Development build
npm run build                      # Production build
npm run watch                      # Watch for changes
```

This guide should help maintain consistency and quality when working with the Laravel backend. Always refer to the existing codebase patterns and follow Laravel best practices.