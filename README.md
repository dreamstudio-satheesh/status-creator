# AI Tamil Status Creator

A cost-efficient Flutter + Laravel application for creating and sharing Tamil status images for WhatsApp, Instagram, and other social platforms. The system minimizes LLM API costs using **prebuilt templates**, **bulk AI generation by admin**, and a **small image captioning model**.

---

## 🌟 Features

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

## 🚀 Quick Start

### Prerequisites
- **PHP 8.2+** with required extensions
- **Composer** for PHP dependencies
- **Flutter SDK 3.x+** for mobile development
- **SQLite** (included with PHP) or **MySQL** for production
- **Node.js & NPM** for admin panel assets

### 1. Clone Repository
```bash
git clone https://github.com/dreamstudio-satheesh/status-creator.git
cd status-creator
```

### 2. Backend Setup (Laravel)
```bash
cd backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations and seed data
php artisan migrate --seed

# Build admin panel assets
npm install && npm run build

# Start Laravel server
php artisan serve
```

### 3. Mobile App Setup (Flutter)
```bash
cd ../mobileapp

# Install Flutter dependencies
flutter pub get

# Copy environment file
cp .env.example .env

# Check Flutter setup
flutter doctor

# Run on connected device/emulator
flutter run
```

### 4. Access the Application
- **Backend API**: http://localhost:8000/api/v1
- **Admin Panel**: http://localhost:8000/admin
- **API Documentation**: http://localhost:8000/api/documentation
- **Mobile App**: Run via `flutter run`

---

## 📱 Mobile Development Setup

### Android Development
1. **Install Android Studio** with Android SDK
2. **Setup Android emulator** or connect physical device
3. **Enable Developer Options & USB Debugging** on physical device
4. **Accept Android licenses**: `flutter doctor --android-licenses`

### iOS Development (macOS only)  
1. **Install Xcode** from App Store
2. **Install CocoaPods**: `sudo gem install cocoapods`
3. **Setup iOS simulator** or connect physical device

### Wireless ADB for WSL/Linux Users
Since WSL doesn't support USB passthrough:

```bash
# Enable Wireless Debugging on Android device
# Settings → Developer Options → Wireless debugging

# Pair device (first time only)
adb pair 192.168.x.x:port pairing_code

# Connect to device  
adb connect 192.168.x.x:port

# Verify connection
adb devices
flutter devices
```

---

## 🛠️ Architecture

### Tech Stack
- **Frontend**: Flutter 3.x (Dart)
- **Backend**: Laravel 11 (PHP 8.2+)
- **Database**: SQLite (development) / MySQL (production)  
- **Cache**: File-based cache / Database cache
- **Storage**: Local filesystem
- **Authentication**: Laravel Sanctum, MSG91 OTP, Google OAuth
- **AI**: OpenRouter LLM, Hugging Face image captioning
- **Payments**: Razorpay / Stripe integration

### Project Structure
```
status-creator/
├── backend/                 # Laravel 11 API
│   ├── app/                # Application code
│   ├── database/           # Migrations, seeders, SQLite
│   ├── routes/             # API routes
│   ├── resources/          # Admin panel views & assets
│   └── storage/            # File storage
├── mobileapp/              # Flutter mobile app  
│   ├── lib/               # Dart source code
│   ├── assets/            # Images, fonts, animations
│   ├── android/           # Android project files
│   └── ios/               # iOS project files
└── README.md              # This documentation
```

---

## 📝 Development Commands

### Backend (Laravel)
```bash
cd backend

# Development server
php artisan serve --port=8000

# Database operations
php artisan migrate              # Run migrations
php artisan migrate:fresh --seed # Fresh database with sample data
php artisan db:seed --class=ThemeSeeder  # Seed specific data

# Queue operations (separate terminal)
php artisan queue:work           # Process background jobs

# Cache management
php artisan cache:clear          # Clear application cache
php artisan config:clear         # Clear config cache
php artisan route:clear          # Clear route cache

# Testing
php artisan test                 # Run tests
php artisan tinker               # Laravel REPL
```

### Frontend (Flutter)
```bash
cd mobileapp

# Development
flutter pub get                  # Install dependencies
flutter run                      # Run on connected device/emulator
flutter clean                    # Clean project

# Building
flutter build apk                # Build debug APK
flutter build apk --release      # Build release APK
flutter build appbundle          # Build for Play Store
flutter build ios               # Build for iOS (macOS only)

# Code generation
flutter packages pub run build_runner build    # Generate code
flutter packages pub run build_runner watch   # Watch and generate

# Testing
flutter test                     # Run all tests
flutter test --coverage         # Test with coverage
```

---

## 🔧 Configuration

### Environment Files
1. **`backend/.env`** - Laravel configuration
   ```env
   APP_NAME="Tamil Status Creator"
   APP_ENV=production
   APP_URL=https://status.dreamcoderz.com
   
   # Database (SQLite for development)
   DB_CONNECTION=sqlite
   
   # Cache & Sessions
   CACHE_STORE=file
   SESSION_DRIVER=database
   QUEUE_CONNECTION=database
   
   # SMS Service (MSG91)
   MSG91_API_KEY=your_msg91_api_key
   
   # AI Services
   OPENROUTER_API_KEY=your_openrouter_api_key
   HUGGINGFACE_API_KEY=your_huggingface_api_key
   ```

2. **`mobileapp/.env`** - Flutter configuration
   ```env
   # Production Backend
   API_BASE_URL=https://status.dreamcoderz.com/api/v1
   
   # Local Development  
   # API_BASE_URL=http://localhost:8000/api/v1          # Local web
   # API_BASE_URL=http://10.0.2.2:8000/api/v1          # Android emulator
   # API_BASE_URL=http://YOUR_COMPUTER_IP:8000/api/v1   # Physical device
   
   # App Configuration
   APP_NAME=Tamil Status Creator
   DEBUG_MODE=true
   ENABLE_ANALYTICS=false
   ```

### Default Credentials
- **Admin Panel**: admin@example.com / admin123
- **Test Mobile**: 6379108040
- **MSG91 API**: 464494A5TVsNXX0r68a5173cP1

---

## 🔒 Production Deployment

### cPanel Hosting
The application is production-ready for cPanel hosting:

1. **Upload Files**: Upload `backend/` contents to public_html
2. **Database**: Create MySQL database and update `.env`
3. **Composer**: Run `composer install --no-dev --optimize-autoloader`
4. **Environment**: Set `APP_ENV=production` and `APP_DEBUG=false`
5. **Permissions**: Set storage and cache folders to 755
6. **Assets**: Run `npm run build` for admin panel assets

### Flutter App Deployment
- **Android**: Build APK/AAB and upload to Play Store
- **iOS**: Build IPA and upload to App Store
- **Configuration**: Update `.env` with production API URLs

---

## 🌐 API Documentation

### Key Endpoints
- **Authentication**: `/api/v1/auth/*`
- **Themes**: `/api/v1/public/themes`
- **Templates**: `/api/v1/public/templates`
- **User Management**: `/api/v1/user/*`
- **AI Generation**: `/api/v1/ai/*`

### Documentation Access
- **Interactive Swagger UI**: 
  - Production: https://status.dreamcoderz.com/api/documentation
  - Local: http://localhost:8000/api/documentation
- **Postman Collection**: `backend/docs/postman_collection.json`
- **Markdown Docs**: `backend/docs/API_DOCUMENTATION.md`

---

## 🐛 Troubleshooting

### Backend Issues
```bash
# Permission issues
chmod -R 755 backend/storage backend/bootstrap/cache

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Reinstall dependencies
composer install

# Database issues
php artisan migrate:fresh --seed
```

### Flutter Issues
```bash
# Clean and reinstall
flutter clean
flutter pub get

# Check setup
flutter doctor -v

# Device connection
flutter devices
adb devices
```

### Connection Issues
- **Cannot connect to backend**: Check if Laravel server is running
- **Android emulator**: Use `10.0.2.2:8000` instead of `localhost:8000`  
- **Physical device**: Ensure same network, use computer's IP address
- **CORS errors**: Backend allows Flutter app origins

---

## 📚 Additional Resources

- **Flutter Documentation**: https://flutter.dev/docs
- **Laravel Documentation**: https://laravel.com/docs
- **API Testing**: Use Postman collection in `backend/docs/`
- **Admin Panel**: Access at `/admin` with default credentials

---

## 🤝 Contributing

1. Fork the repository
2. Create feature branch: `git checkout -b feature/new-feature`
3. Commit changes: `git commit -m 'Add new feature'`
4. Push to branch: `git push origin feature/new-feature`
5. Submit pull request

---

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

---

## 📞 Support

- **Production URL**: https://status.dreamcoderz.com
- **Documentation**: See CLAUDE.md files in each directory
- **Issues**: Create GitHub issues for bug reports and feature requests