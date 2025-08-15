# AI Tamil Status Creator App

A cost-efficient Flutter + Laravel application for creating and sharing Tamil status images for WhatsApp, Instagram, and other social platforms.  
The system minimizes LLM API costs using **prebuilt templates**, **bulk AI generation by admin**, and a **small image captioning model**.

---

## Features

- **Authentication**
  - Mobile OTP login via MSG91
  - Google Sign-In
  - Laravel Sanctum token authentication

- **Templates & Themes**
  - Prebuilt templates categorized by themes (Love, Motivation, Sad, etc.)
  - Admin uploads background images and generates Tamil quotes via AI once
  - Free users pick and edit prebuilt templates without triggering LLM calls
  - Premium users can generate quotes via AI (daily quota limits apply)

- **AI Pipeline**
  - Small image captioning model (BLIP, CLIP, OFA) for low-cost description
  - Minimal token LLM prompt via OpenRouter for Tamil quote generation
  - Bulk pre-generation for cost savings

- **Editor**
  - Change fonts, colors, alignment, background image, and text
  - Add logo, tagline, and branding

- **Sharing**
  - One-click share to WhatsApp, Instagram, Facebook
  - Save to gallery

- **Admin Panel**
  - Built with TailwindCSS + Blade + js
  - CRUD for templates, themes, users, and subscriptions
  - Bulk AI generation tool
  - Usage analytics

---

## 📱 Flutter Local Setup

### Prerequisites for Mobile Development
1. **Flutter SDK**: Version 3.x or later
   - Download from https://flutter.dev/docs/get-started/install
   - Add Flutter to PATH: `export PATH="$PATH:/path/to/flutter/bin"`

2. **Android Development**
   - Android Studio with Android SDK
   - Android emulator or physical device with USB debugging
   - Accept Android licenses: `flutter doctor --android-licenses`

3. **iOS Development (macOS only)**
   - Xcode from App Store
   - CocoaPods: `sudo gem install cocoapods`
   - iOS simulator or physical device

4. **Verify Installation**
   ```bash
   flutter doctor  # Check all requirements
   ```

### Connecting Flutter to Docker Backend
1. Update `flutter/.env` file:
   ```env
   API_BASE_URL=http://localhost:8000/api/v1
   LARAVEL_BASE_URL=http://localhost:8000
   ```

2. For Android emulator, use:
   ```env
   API_BASE_URL=http://10.0.2.2:8000/api/v1  # Android emulator localhost
   ```

3. For physical device on same network:
   ```env
   API_BASE_URL=http://YOUR_COMPUTER_IP:8000/api/v1
   ```

---

## Tech Stack

- **Frontend:** Flutter 3.x
- **Backend:** Laravel 11 (API-first)
- **Database:** MySQL
- **Authentication:** Laravel Sanctum, MSG91 OTP, Google OAuth
- **AI:** BLIP/CLIP/OFA for captions, OpenRouter LLM for quotes
- **File Storage:** AWS S3 / DigitalOcean Spaces
- **Payments:** Razorpay / Stripe
- **Notifications:** Firebase Cloud Messaging

---

## 🐳 Docker Installation (Backend Services)

### Prerequisites
- Docker Engine 20.10+
- Docker Compose 2.0+
- Flutter SDK 3.x (for mobile app development)
- Android Studio / Xcode (for mobile development)
- 4GB RAM minimum
- 10GB free disk space

### Quick Start

1. **Clone repository**
   ```bash
   git clone git@github.com:dreamstudio-satheesh/status-creator.git
   cd status-creator
   ```

2. **Copy environment files**
   ```bash
   cp .env.example .env
   cp backend/.env.example backend/.env
   cp flutter/.env.example flutter/.env
   ```

3. **Start all services**
   ```bash
   make install  # First time setup
   make up       # Start services
   ```

4. **Run migrations**
   ```bash
   make migrate
   make seed     # Optional: Load sample data
   ```

5. **Access the services**
   - Backend API: http://localhost:8000
   - phpMyAdmin: http://localhost:8081
   - Mailhog: http://localhost:8025
   - MinIO Console: http://localhost:9001

6. **Setup Flutter locally for mobile development**
   ```bash
   cd flutter
   flutter pub get
   
   # For Android development
   flutter run  # Run on connected device/emulator
   flutter build apk  # Build APK
   
   # For iOS development (macOS only)
   flutter run  # Run on iOS simulator
   flutter build ios  # Build for iOS
   ```

---

## 🛠️ Docker Services

### Core Services
- **backend**: Laravel 11 API (PHP 8.2, Nginx, Supervisor)
- **mysql**: MySQL 8.0 with Tamil (utf8mb4) support
- **redis**: Redis cache and queue backend
- **nginx**: Reverse proxy for routing
- **Flutter**: Run locally on your machine for Android/iOS development

### Development Tools
- **phpmyadmin**: Database management UI
- **mailhog**: Email testing interface
- **minio**: S3-compatible local storage
- **queue-worker**: Laravel queue processor
- **scheduler**: Laravel task scheduler

---

## 📝 Useful Commands

### Docker Management
```bash
make up          # Start all services
make down        # Stop all services
make restart     # Restart all services
make logs        # View all logs
make status      # Check service status
make clean       # Remove containers and volumes
```

### Backend Commands
```bash
make shell-backend    # Access backend shell
make migrate         # Run migrations
make seed           # Seed database
make fresh          # Fresh migration with seeding
make cache-clear    # Clear all caches
make test-backend   # Run tests
make npm-build      # Build admin panel assets
```

### Flutter Commands (Run locally)
```bash
cd flutter

# Development
flutter pub get           # Install dependencies
flutter run               # Run on connected device/emulator
flutter clean             # Clean project

# Building
flutter build apk         # Build debug APK
flutter build apk --release  # Build release APK
flutter build appbundle   # Build for Play Store
flutter build ios         # Build for iOS (macOS only)

# Connect to Docker backend
# Update flutter/.env with:
# API_BASE_URL=http://localhost:8000/api/v1
```

### Database Commands
```bash
make shell-mysql  # Access MySQL shell
make backup-db    # Backup database
make restore-db file=backup.sql  # Restore database
```

---

## 🔧 Configuration

### Environment Variables
Edit `.env` files in root, `backend/`, and `flutter/` directories:

- **Root `.env`**: Docker service configurations
- **`backend/.env`**: Laravel application settings
- **`flutter/.env`**: Flutter app configuration

### Default Credentials
- **Admin Panel**: admin@example.com / admin123
- **MySQL Root**: root / root_secret
- **MySQL User**: status_user / secret_password
- **MinIO**: minioadmin / minioadmin

---

## 📁 Project Structure
```
statusapp/
├── backend/              # Laravel 11 API (Dockerized)
│   ├── app/             # Application code
│   ├── database/        # Migrations & seeds
│   ├── routes/          # API routes
│   ├── storage/         # File storage
│   └── docker/          # Docker configs
├── flutter/             # Flutter mobile app (Run locally)
│   ├── lib/            # Dart source code
│   ├── assets/         # Images, fonts
│   ├── android/        # Android project files
│   ├── ios/            # iOS project files
│   └── pubspec.yaml    # Dependencies
├── mysql/              # Database setup
│   ├── init.sql       # Initial schema
│   └── my.cnf         # MySQL config
├── nginx/              # Reverse proxy
│   └── nginx.conf     # Nginx config
├── docker-compose.yml  # Service orchestration
├── Makefile           # Helper commands
└── README.md          # Documentation
```

---

## 🚀 Production Deployment

For production deployment, configure your environment variables for production use and deploy using your preferred container orchestration platform.

---

## 🔒 Security Notes

1. Change all default passwords before production
2. Use SSL certificates for HTTPS
3. Configure firewall rules
4. Enable Laravel production optimizations
5. Set proper CORS headers
6. Implement rate limiting

---

## 📚 API Documentation

After starting the services, API documentation is available at:
- Swagger UI: http://localhost:8000/api/documentation
- Postman Collection: `backend/docs/postman_collection.json`

---

## 🐛 Troubleshooting

### Port Conflicts
If ports are already in use, modify the `.env` file:
```env
BACKEND_PORT=8001
FLUTTER_PORT=8081
MYSQL_PORT=3307
```

### Permission Issues
```bash
sudo chown -R $USER:$USER .
chmod -R 755 backend/storage
chmod -R 755 backend/bootstrap/cache
```

### Container Issues
```bash
make clean      # Clean everything
make build      # Rebuild images
make install    # Fresh installation
