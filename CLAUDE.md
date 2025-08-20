# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

AI Tamil Status Creator - A Flutter + Laravel application for creating and sharing Tamil status images. The system minimizes LLM API costs through prebuilt templates, bulk AI generation, and efficient image captioning models.

## Architecture

### Core Components
- **Frontend**: Flutter mobile app (runs locally) in `mobileapp/` directory
- **Backend**: Laravel 11 API in `backend/` directory (Production-ready with SQLite)
- **Database**: SQLite for development, MySQL for production with utf8mb4 for Tamil text support
- **Cache/Queue**: File-based cache and database queues (no Redis dependency)
- **Storage**: Local file storage for images
- **AI Integration**: OpenRouter LLM + BLIP/CLIP/OFA for image captioning

### Service Communication
- Flutter app runs locally and communicates with Laravel API via REST endpoints
- Laravel uses file-based cache and database queues for simplicity
- **Production Server**: https://status.dreamcoderz.com (cPanel hosting)
- **Local Development**: Laravel can run via `php artisan serve` on http://localhost:8000

### Claude Development Configuration
This project is configured for development with Claude Code (claude.ai/code) using the production server at `https://status.dreamcoderz.com`. 

**Key Features:**
- Real-time backend API testing with live server
- No local Docker setup required for basic development
- Instant API response testing and debugging
- Production-like environment for development

**Claude Environment Setup:**
- Backend API: `https://status.dreamcoderz.com/api/v1`
- Admin Panel: `https://status.dreamcoderz.com/admin`
- Health Check: `https://status.dreamcoderz.com/api/v1/health`
- Flutter app configured to use production backend by default

## Essential Commands

### Development Workflow
```bash
# Backend setup (first time)
cd backend
composer install          # Install PHP dependencies
php artisan key:generate   # Generate application key
php artisan migrate --seed # Setup database with sample data

# Daily backend development
cd backend
php artisan serve         # Start Laravel development server
php artisan queue:work    # Start queue processing (separate terminal)
php artisan cache:clear   # Clear caches when needed
```

### Backend Development
```bash
cd backend
php artisan migrate       # Run database migrations
php artisan migrate:fresh --seed  # Fresh migration with seeding
php artisan cache:clear   # Clear all Laravel caches
php artisan test          # Run tests
php artisan tinker        # Laravel REPL
php artisan queue:work    # Start queue workers
```

### Flutter Development (Local)
```bash
cd mobileapp
flutter pub get           # Install dependencies
flutter clean             # Clean project
flutter run               # Run on connected device/emulator
flutter build apk         # Build debug APK
flutter build apk --release  # Build release APK
flutter build appbundle   # Build for Play Store
flutter build ios         # Build for iOS (macOS only)
flutter install           # Install APK on connected device
```

**Quick Start for WSL:**
```bash
# Connect to Android device wirelessly
adb connect 192.168.x.x:port

# Run with hot reload
cd mobileapp
export PATH="$PATH:/home/satheesh/flutter/bin"
flutter run
```

### Database Operations
```bash
cd backend
sqlite3 database/database.sqlite  # Access SQLite CLI
php artisan db:seed --class=ThemeSeeder  # Seed specific data
```

## Service URLs
- Production Backend API: https://status.dreamcoderz.com/api/v1
- Production Admin Panel: https://status.dreamcoderz.com/admin
- Local Backend API: http://localhost:8000 (when running `php artisan serve`)
- Flutter App: Run locally via `flutter run`

## Environment Configuration

### Required Environment Files
1. `backend/.env` - Laravel configuration (copy from `backend/.env.example` for production settings)
2. `mobileapp/.env` - Flutter configuration (copy from `mobileapp/.env.example`)
   - Set `API_BASE_URL=https://status.dreamcoderz.com/api/v1` for production backend
   - Use `http://localhost:8000/api/v1` for local development
   - Use `http://10.0.2.2:8000/api/v1` for Android emulator

### Key Configuration Values
- **Database**: SQLite (development) - `DB_CONNECTION=sqlite`
- **Cache**: File-based - `CACHE_STORE=file` or `CACHE_STORE=database`
- **Queue**: Database - `QUEUE_CONNECTION=database`
- **Storage**: Local filesystem - `FILESYSTEM_DISK=local`

### Default Credentials
- Admin Panel: admin@example.com / admin123
- MSG91 API Key: 464494A5TVsNXX0r68a5173cP1
- Test Mobile: 6379108040

## Database Schema

The application uses SQLite (development) or MySQL (production) with the following core tables:
- `users` - User accounts with subscription management
- `themes` - Template categories (Love, Motivation, etc.)
- `templates` - Pre-generated status templates
- `user_creations` - User-generated status images
- `subscriptions` - Premium subscription tracking
- `ai_generation_logs` - AI usage tracking for cost management
- `settings` - Application configuration settings
- `fonts` - Font management for status creation
- `admins` - Admin panel user accounts
- `activity_logs` - Admin activity tracking

All tables support Tamil text via utf8mb4 charset.

## AI Integration Points

### Image Captioning
- Models: BLIP/CLIP/OFA via Hugging Face API
- Used for generating image descriptions before LLM processing
- Configured in `HUGGINGFACE_API_KEY` and `CAPTION_MODEL`

### Quote Generation
- Provider: OpenRouter API
- Model: `meta-llama/llama-3.2-3b-instruct:free` (configurable)
- Configured in `OPENROUTER_API_KEY` and `OPENROUTER_MODEL`

### Cost Optimization Strategy
1. Admin bulk-generates templates once
2. Free users select from pre-generated templates (no API calls)
3. Premium users have daily quotas for custom generation
4. Small captioning models minimize token usage

## Application Architecture

### Core Services
- **Backend** - Laravel 11 API with PHP 8.2+ (runs locally with `php artisan serve`)
- **Database** - SQLite (development) or MySQL (production) with Tamil support
- **Cache** - File-based or database cache (no Redis dependency)
- **Queue** - Database-based job queues
- **Flutter** - Runs locally on developer machine

### Development Tools
- **Queue Worker** - `php artisan queue:work` for background jobs
- **Scheduler** - `php artisan schedule:work` for cron tasks
- **Storage** - Local file storage for images and assets

## Testing

```bash
# Backend tests
cd backend
php artisan test

# Run specific test
php artisan test --filter=TestClassName

# Flutter tests
cd mobileapp && flutter test
```

## API Documentation

API documentation is available at:
- Interactive Swagger UI: https://status.dreamcoderz.com/api/documentation (production)
- Interactive Swagger UI: http://localhost:8000/api/documentation (local)
- Postman Collection: `backend/docs/postman_collection.json`
- Markdown Documentation: `backend/docs/API_DOCUMENTATION.md`

Key API endpoints:
- Authentication: `/api/v1/auth/*`
- User Management: `/api/v1/user/*`
- Themes & Templates: `/api/v1/public/themes/*`, `/api/v1/public/templates/*`
- AI Generation: `/api/v1/ai/*`
- File Upload: `/api/v1/uploads/*`

## Troubleshooting

### Port Conflicts
If port 8000 is in use, start Laravel on different port:
```bash
cd backend
php artisan serve --port=8001
```

### Permission Issues
```bash
chmod -R 755 backend/storage backend/bootstrap/cache
```

### Laravel Issues
```bash
cd backend
php artisan cache:clear     # Clear all caches
php artisan config:clear    # Clear config cache
php artisan route:clear     # Clear route cache
composer install           # Reinstall dependencies
```

### Flutter SDK Setup
Flutter runs locally on your development machine. Ensure Flutter SDK is installed:
```bash
# Check Flutter installation
flutter doctor

# For WSL users, add to PATH:
export PATH="$PATH:/home/satheesh/flutter/bin"
```

### Wireless ADB Connection (WSL/Linux)
WSL doesn't support USB passthrough, so use wireless debugging:

```bash
# First time - pair device:
adb pair 192.168.x.x:pairing_port pairing_code

# Connect to device:
adb connect 192.168.x.x:connection_port

# Verify:
adb devices
flutter devices

# Run Flutter app:
cd mobileapp
flutter run
```

**Note**: Connection port changes when wireless debugging is toggled. If connection drops, get new port from phone settings and reconnect.

## Development Guidelines

### Code Organization
- **Backend**: Laravel follows standard MVC pattern with additional Services and Jobs
- **Frontend**: Flutter uses feature-based folder structure with Provider for state management
- **Shared Logic**: Common constants and utilities in each project's respective directories

### Security Considerations
- All sensitive data should be stored in environment variables
- API uses Laravel Sanctum for authentication with Bearer tokens
- Rate limiting implemented for all endpoints
- Input validation on both frontend and backend
- HTTPS should be used in production

### Performance Optimization
- Use caching for frequently accessed data (Redis)
- Implement proper database indexing
- Use queue workers for time-consuming operations
- Optimize image assets and use appropriate formats

## Deployment

For production deployment:
1. Set `APP_ENV=production` in `backend/.env`
2. Configure proper SSL certificates
3. Use production database (not Docker MySQL)
4. Set up proper S3/Spaces for file storage
5. Configure proper email service (not Mailhog)
6. Set up monitoring and logging
7. Implement proper backup strategies