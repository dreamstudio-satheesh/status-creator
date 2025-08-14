# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

AI Tamil Status Creator - A Flutter + Laravel application for creating and sharing Tamil status images. The system minimizes LLM API costs through prebuilt templates, bulk AI generation, and efficient image captioning models.

## Architecture

### Core Components
- **Frontend**: Flutter mobile/web app in `flutter/` directory
- **Backend**: Laravel 11 API in `backend/` directory  
- **Database**: MySQL 8.0 with utf8mb4 for Tamil text support
- **Cache/Queue**: Redis for session, cache, and queue management
- **Storage**: MinIO (dev) or S3/Spaces (prod) for images
- **AI Integration**: OpenRouter LLM + BLIP/CLIP/OFA for image captioning

### Service Communication
- Flutter app communicates with Laravel API via REST endpoints
- Laravel uses Redis for queue jobs and caching
- All services run in Docker containers connected via `status_network`
- Nginx reverse proxy routes requests between services

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

### Flutter Development
```bash
make shell-flutter         # Access Flutter container
make flutter-clean        # Clean Flutter project
make flutter-build-web    # Build for web
make flutter-build-apk    # Build APK
flutter run -d web-server --web-port=8080 --web-hostname=0.0.0.0  # Run with hot reload
```

### Database Operations
```bash
make shell-mysql          # Access MySQL CLI
make backup-db           # Create database backup
make restore-db file=backup.sql  # Restore from backup
```

## Service URLs
- Flutter App: http://localhost:8080
- Backend API: http://localhost:8000
- phpMyAdmin: http://localhost:8081
- Mailhog: http://localhost:8025
- MinIO Console: http://localhost:9001

## Environment Configuration

### Required Environment Files
1. `.env` - Docker service configuration
2. `backend/.env` - Laravel configuration (copy from `backend/.env.example`)
3. `flutter/.env` - Flutter configuration (copy from `flutter/.env.example`)

### Key Configuration Values
- **Database**: `DB_HOST=mysql`, `DB_DATABASE=status_creator`
- **Redis**: `REDIS_HOST=redis`
- **Storage**: `AWS_ENDPOINT=http://minio:9000` (development)
- **Mail**: `MAIL_HOST=mailhog` (development)

### Default Credentials
- Admin Panel: admin@example.com / admin123
- MySQL Root: root / root_secret
- MinIO: minioadmin / minioadmin

## Database Schema

The application uses MySQL with the following core tables:
- `users` - User accounts with subscription management
- `themes` - Template categories (Love, Motivation, etc.)
- `templates` - Pre-generated status templates
- `user_creations` - User-generated status images
- `subscriptions` - Premium subscription tracking
- `ai_generation_logs` - AI usage tracking for cost management

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
- `flutter` - Flutter development server with hot reload
- `mysql` - Database with Tamil support
- `redis` - Cache and queue backend
- `nginx` - Reverse proxy for routing

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
docker-compose exec flutter flutter test
```

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

### Flutter SDK in WSL
Flutter is installed at `/home/satheesh/flutter/bin`. To use in new terminals:
```bash
export PATH="$PATH:/home/satheesh/flutter/bin"
```