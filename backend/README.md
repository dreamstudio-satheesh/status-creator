# AI Tamil Status Creator - Backend API

A Laravel 11 API backend for the AI Tamil Status Creator application, providing authentication, content management, AI integration, and admin panel functionality.

## Features

### Core API Features
- **User Authentication** - JWT-based authentication with email verification
- **Theme Management** - Categorized status templates (Love, Motivation, Sad, etc.)
- **Template System** - Pre-generated and AI-generated status templates
- **AI Integration** - OpenRouter LLM + Hugging Face image captioning
- **File Upload** - Image upload with MinIO/S3 storage
- **Subscription System** - Premium user management with quotas
- **Feedback System** - User ratings and feedback collection
- **Admin Panel** - Complete Laravel Blade admin interface

### Admin Panel Features
- **Dashboard Analytics** - User growth, revenue, system health monitoring
- **User Management** - CRUD operations with premium subscription controls
- **Content Management** - Theme and template administration
- **AI Management** - Bulk generation, usage tracking, cost monitoring
- **System Settings** - Configuration, backup/restore, cache management
- **Activity Logging** - Admin action tracking and audit trails

## Tech Stack

- **Framework**: Laravel 11 (PHP 8.2)
- **Database**: MySQL 8.0 with utf8mb4 for Tamil text support
- **Cache/Queue**: Redis for sessions, caching, and background jobs
- **Storage**: MinIO (dev) / S3/Digital Ocean Spaces (prod)
- **AI Services**: OpenRouter API, Hugging Face API
- **Frontend**: TailwindCSS with Laravel Vite
- **Authentication**: Laravel Sanctum + Custom Admin Guard

## Installation

### Prerequisites
- PHP 8.2+
- Composer
- MySQL 8.0+
- Redis
- Node.js & npm

### Setup Steps

1. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

4. **Storage Links**
   ```bash
   php artisan storage:link
   ```

5. **Asset Building**
   ```bash
   npm run build
   ```

## Environment Configuration

### Required Environment Variables

```env
# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=status_creator
DB_USERNAME=root
DB_PASSWORD=root_secret

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Storage (MinIO for dev)
AWS_ACCESS_KEY_ID=minioadmin
AWS_SECRET_ACCESS_KEY=minioadmin
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=status-images
AWS_ENDPOINT=http://minio:9000

# AI Services
OPENROUTER_API_KEY=your_openrouter_api_key
OPENROUTER_MODEL=meta-llama/llama-3.2-3b-instruct:free
HUGGINGFACE_API_KEY=your_huggingface_api_key
CAPTION_MODEL=Salesforce/blip-image-captioning-base

# Mail (Development)
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
```

## API Endpoints

### Authentication
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `POST /api/verify-email` - Email verification
- `POST /api/forgot-password` - Password reset request
- `POST /api/reset-password` - Password reset

### User Management
- `GET /api/profile` - Get user profile
- `PUT /api/profile` - Update user profile
- `POST /api/upgrade-premium` - Upgrade to premium

### Themes & Templates
- `GET /api/themes` - List all themes
- `GET /api/themes/{id}/templates` - Get theme templates
- `GET /api/templates/{id}` - Get specific template

### AI Generation
- `POST /api/generate-custom` - Generate custom status (premium)
- `POST /api/caption-image` - Generate image caption

### File Upload
- `POST /api/upload` - Upload image files
- `GET /api/uploads/{filename}` - Serve uploaded files

### Feedback
- `POST /api/feedback` - Submit user feedback
- `GET /api/feedback` - Get user's feedback history

## Admin Panel

Access the admin panel at `/admin` with default credentials:
- **Email**: admin@example.com
- **Password**: admin123

### Admin Features
- **Dashboard** - Analytics, charts, system health monitoring
- **User Management** - View, edit, premium controls
- **Theme Management** - CRUD operations for status themes
- **Template Management** - Upload, edit, analytics for templates
- **AI Management** - Bulk generation, usage tracking, cost monitoring
- **Settings** - System configuration, backup/restore
- **Activity Logs** - Admin action tracking

## Development Commands

```bash
# Start development server
php artisan serve

# Run queue workers
php artisan queue:work

# Run scheduled tasks
php artisan schedule:run

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Database operations
php artisan migrate:fresh --seed
php artisan db:seed --class=ThemeSeeder

# Asset compilation
npm run dev        # Development with hot reload
npm run build      # Production build
```

## Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

## Queue Management

The application uses Redis queues for background processing:

```bash
# Start queue workers
php artisan queue:work

# Monitor queue status
php artisan queue:monitor

# Clear failed jobs
php artisan queue:flush
```

## Caching Strategy

- **Config Cache**: Application configuration
- **Route Cache**: Route definitions for performance
- **Dashboard Stats**: 5-minute cache for admin analytics
- **User Sessions**: Redis-based session management

## Security Features

- **Rate Limiting**: API endpoint rate limiting
- **CSRF Protection**: Laravel CSRF tokens
- **Input Validation**: Comprehensive request validation
- **SQL Injection Prevention**: Eloquent ORM protection
- **Admin Activity Logging**: Audit trail for admin actions

## File Storage

### Development (MinIO)
- **Endpoint**: http://localhost:9001
- **Console**: MinIO admin interface
- **Bucket**: status-images

### Production (S3/Spaces)
- Configure `AWS_*` environment variables
- Automatic CDN integration available

## Performance Optimization

- **Database Indexing**: Optimized queries with proper indexes
- **Eager Loading**: Reduced N+1 queries
- **Cache Layers**: Multi-level caching strategy
- **Queue Processing**: Background job processing
- **Asset Optimization**: Minified CSS/JS with Vite

## Monitoring & Logging

- **Application Logs**: `storage/logs/laravel.log`
- **Queue Monitoring**: Built-in queue monitoring
- **Performance Tracking**: Query time monitoring
- **Error Tracking**: Comprehensive error logging

## Deployment

### Docker Deployment
```bash
# Build and start services
docker-compose up -d

# Run migrations
docker-compose exec backend php artisan migrate

# Generate admin user
docker-compose exec backend php artisan db:seed --class=AdminSeeder
```

### Production Deployment
1. Set production environment variables
2. Run `composer install --no-dev --optimize-autoloader`
3. Run `php artisan config:cache`
4. Run `php artisan route:cache`
5. Set up queue workers with Supervisor
6. Configure web server (Nginx/Apache)

## Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions:
- Create an issue in the repository
- Check the documentation in `/docs`
- Review the admin panel help section
