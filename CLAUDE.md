# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

AI Tamil Status Creator - A Flutter + Laravel application for creating and sharing Tamil status images. The system minimizes LLM API costs through prebuilt templates, bulk AI generation, and efficient image captioning models.

## Architecture

### Core Components
- **Frontend**: Flutter mobile app (runs locally) in `flutter/` directory
- **Backend**: Laravel 11 API in `backend/` directory (Dockerized)
- **Database**: MySQL 8.0 with utf8mb4 for Tamil text support (Dockerized)
- **Cache/Queue**: Redis for session, cache, and queue management (Dockerized)
- **Storage**: MinIO (dev) or S3/Spaces (prod) for images (Dockerized)
- **AI Integration**: OpenRouter LLM + BLIP/CLIP/OFA for image captioning

### Service Communication
- Flutter app runs locally and communicates with Laravel API via REST endpoints
- Laravel uses Redis for queue jobs and caching (Docker) or file-based cache (cPanel)
- Backend services run in Docker containers connected via `status_network` (local) or cPanel hosting (production)
- Nginx reverse proxy routes requests for backend services
- **Development Server**: Flutter connects to `https://status.dreamcoderz.com` (production backend)
- **Local Testing**: Flutter can connect to `http://localhost:8000` (Docker) or `http://10.0.2.2:8000` (Android emulator)

### Environment Configuration
- **Production Backend**: https://status.dreamcoderz.com
- **Local Docker**: http://localhost:8000
- **Android Emulator**: http://10.0.2.2:8000
- **Physical Device**: http://YOUR_COMPUTER_IP:8000

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
# First time setup
make install     # Builds images, installs dependencies, generates keys
make migrate     # Run database migrations
make seed        # Load sample data

# Daily development
make up          # Start all services
make down        # Stop all services
make logs        # View all logs
make status      # Check service status
```

### Backend Development
```bash
make shell-backend         # Access Laravel container
make migrate              # Run migrations
make fresh                # Fresh migration with seeding
make cache-clear          # Clear all Laravel caches
make test-backend         # Run tests
make queue-restart        # Restart queue workers
docker-compose exec backend php artisan [command]  # Run any artisan command
```

### Flutter Development (Local)
```bash
cd flutter
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
cd flutter
export PATH="$PATH:/home/satheesh/flutter/bin"
flutter run
```

### Database Operations
```bash
make shell-mysql          # Access MySQL CLI
make backup-db           # Create database backup
make restore-db file=backup.sql  # Restore from backup
```

## Service URLs
- Backend API: http://localhost:8000
- phpMyAdmin: http://localhost:8081
- Mailhog: http://localhost:8025
- MinIO Console: http://localhost:9001
- Flutter App: Run locally via `flutter run`

## Environment Configuration

### Required Environment Files
1. `.env` - Docker service configuration
2. `backend/.env` - Laravel configuration (copy from `backend/.env.example`)
3. `flutter/.env` - Flutter configuration (copy from `flutter/.env.example`)
   - Set `API_BASE_URL=http://localhost:8000/api/v1` for local development
   - Use `http://10.0.2.2:8000/api/v1` for Android emulator
   - Use your computer's IP for physical devices

### Key Configuration Values
- **Database**: `DB_HOST=mysql`, `DB_DATABASE=status_creator`
- **Redis**: `REDIS_HOST=redis`
- **Storage**: `AWS_ENDPOINT=http://minio:9000` (development)
- **Mail**: `MAIL_HOST=mailhog` (development)

### Default Credentials
- Admin Panel: admin@example.com / admin123
- MySQL Root: root / root_secret
- MySQL User: status_user / secret_password
- MinIO: minioadmin / minioadmin

## Database Schema

The application uses MySQL with the following core tables:
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

## Docker Services

### Core Services
- `backend` - Laravel API with PHP 8.2, Nginx, Supervisor
- `mysql` - Database with Tamil support
- `redis` - Cache and queue backend
- `nginx` - Reverse proxy for routing
- **Flutter** - Runs locally on developer machine (not in Docker)

### Support Services
- `phpmyadmin` - Database management UI
- `mailhog` - Email testing interface
- `minio` - S3-compatible local storage
- `queue-worker` - Laravel queue processor
- `scheduler` - Laravel cron scheduler

## Testing

```bash
# Backend tests
make test-backend

# Run specific test
docker-compose exec backend php artisan test --filter=TestClassName

# Flutter tests
cd flutter && flutter test
```

## API Documentation

API documentation is available at:
- Interactive Swagger UI: http://localhost:8000/api/documentation
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
Modify `.env` file if ports are in use:
```env
BACKEND_PORT=8001
FLUTTER_PORT=8081
MYSQL_PORT=3307
```

### Permission Issues
```bash
chmod -R 755 backend/storage backend/bootstrap/cache
```

### Container Issues
```bash
make clean      # Remove all containers and volumes
make build      # Rebuild images
make install    # Fresh installation
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
cd flutter
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