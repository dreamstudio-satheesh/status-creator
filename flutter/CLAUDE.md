# CLAUDE.md - Flutter App

This file provides guidance to Claude Code when working with the Flutter application code in this directory.

## Project Overview

**Tamil Status Creator** - A Flutter mobile application for creating and sharing AI-generated Tamil status images. This app connects to the Laravel backend API for user authentication, template management, and AI-powered content generation.

## Architecture

### Folder Structure
```
lib/
├── core/                 # Core functionality and configuration
│   ├── constants/        # App constants, routes, API endpoints
│   ├── network/          # API client, interceptors, error handling  
│   ├── storage/          # Secure storage service
│   ├── routing/          # GoRouter configuration
│   ├── utils/            # Utility functions and helpers
│   └── exceptions/       # Custom exception classes
├── features/             # Feature-based modules
│   ├── auth/            # Authentication (login, register, OTP)
│   ├── templates/       # Template browsing and management
│   ├── editor/          # Image editor and status creation
│   ├── profile/         # User profile and settings
│   └── subscription/    # Premium subscription management
├── shared/              # Shared components across features
│   ├── widgets/         # Reusable UI components
│   ├── models/          # Data models and DTOs
│   └── services/        # Shared business logic services
└── main.dart           # Application entry point
```

## Key Technologies

### State Management
- **Provider/Riverpod**: For state management across the app
- **GoRouter**: For navigation and routing with authentication guards

### Networking & API
- **Dio**: HTTP client for API communication
- **API Base URL**: `http://localhost:8000/api/v1` (development)
- **Laravel Backend Integration**: Full REST API integration
- **Auto Token Refresh**: Automatic JWT token management

### Storage & Security
- **Flutter Secure Storage**: For sensitive data (tokens, user info)
- **Shared Preferences**: For app settings and cache
- **Biometric Authentication**: Optional fingerprint/face unlock

### UI & Design
- **Material Design 3**: Modern Material Design components
- **Google Fonts**: Typography with Tamil font support
- **Custom Themes**: Light/dark theme support
- **Responsive Design**: Adaptive layouts for different screen sizes

### Image & Media
- **Image Picker**: Camera and gallery image selection
- **Image Cropper**: Image editing and cropping
- **Photo View**: Image viewer with zoom and pan
- **Screenshot**: Capture edited status images

### AI & Content
- **AI Quote Generation**: Integration with Laravel AI services
- **Tamil Text Support**: Proper Tamil font rendering
- **Template System**: Pre-built and AI-generated templates

## Essential Commands

### Development
```bash
# Install dependencies
flutter pub get

# Run app (debug mode)
flutter run

# Run on specific device
flutter run -d <device_id>

# Hot reload during development
# Press 'r' in terminal or save files

# Build for testing
flutter build apk --debug

# Build for release
flutter build apk --release
```

### Code Generation
```bash
# Generate code (for models, routing, etc.)
flutter packages pub run build_runner build

# Watch for changes and generate
flutter packages pub run build_runner watch
```

### Testing
```bash
# Run all tests
flutter test

# Run specific test
flutter test test/specific_test.dart

# Test coverage
flutter test --coverage
```

### Environment Management
```bash
# Run with specific environment
flutter run --dart-define-from-file=.env

# Build with environment
flutter build apk --dart-define-from-file=.env
```

## Configuration

### Environment Variables (.env)
- `API_BASE_URL`: Backend API URL
- `LARAVEL_BASE_URL`: Laravel backend URL  
- `DEBUG_MODE`: Enable/disable debug features
- `ENABLE_ANALYTICS`: Firebase analytics toggle
- `RAZORPAY_KEY_ID`: Payment gateway key (optional)

### API Integration
- **Authentication**: JWT tokens with auto-refresh
- **Endpoints**: Defined in `lib/core/constants/app_constants.dart`
- **Error Handling**: Custom exceptions with user-friendly messages
- **Offline Support**: Cached data and offline mode

### Navigation Flow
```
Splash → Onboarding → Login/Register → Home
              ↓
         Main App (Bottom Navigation)
         ├── Home (Featured templates)
         ├── Templates (Browse by theme)
         ├── Editor (Create/edit status)
         ├── My Work (User creations)
         └── Profile (Settings, subscription)
```

## Development Guidelines

### Code Style
- **Clean Architecture**: Feature-based organization
- **SOLID Principles**: Maintainable and scalable code
- **Error Handling**: Comprehensive try-catch with user feedback
- **Logging**: Debug logs for development, minimal in production

### State Management Patterns
- **Providers**: For app-wide state (auth, theme, settings)
- **Local State**: For widget-specific state (forms, animations)
- **Repository Pattern**: For data access and API integration

### UI/UX Patterns
- **Loading States**: Shimmer loading for content
- **Error States**: User-friendly error messages with retry
- **Empty States**: Informative empty state screens
- **Pull-to-Refresh**: Standard refresh patterns

### Performance
- **Image Optimization**: Cached network images
- **Lazy Loading**: Paginated lists and infinite scroll
- **Memory Management**: Proper disposal of resources
- **Bundle Size**: Tree shaking and code splitting

## API Integration

### Authentication Flow
1. **OTP Login**: Phone number → OTP → JWT tokens
2. **Google Sign-in**: Google OAuth → Backend verification → JWT tokens  
3. **Token Refresh**: Automatic token refresh on 401 responses
4. **Logout**: Clear tokens and redirect to login

### Data Models
- **User**: Profile, subscription, preferences
- **Template**: ID, theme, image URL, metadata
- **Theme**: Category with template collections
- **Creation**: User-generated status images
- **Subscription**: Premium plan details

### Caching Strategy
- **Templates**: Cache popular templates locally
- **Images**: Network image caching with expiration
- **User Data**: Secure storage for profile and settings
- **API Responses**: Short-term caching for performance

## Testing Strategy

### Unit Tests
- **Models**: Data parsing and validation
- **Services**: API calls and business logic  
- **Utilities**: Helper functions and calculations

### Widget Tests
- **Screens**: UI rendering and interaction
- **Components**: Reusable widget behavior
- **Navigation**: Route transitions and guards

### Integration Tests
- **User Flows**: Complete user journeys
- **API Integration**: Backend connectivity
- **Authentication**: Login/logout flows

## Build & Deployment

### Debug Builds
- **Development**: Local testing and debugging
- **Internal Testing**: Sharing with team members

### Release Builds
- **Staging**: Pre-production testing
- **Production**: Play Store and App Store releases

### Build Configurations
- **Android**: Signing keys, ProGuard, app bundle
- **iOS**: Certificates, provisioning profiles
- **Web**: PWA configuration (if enabled)

## Troubleshooting

### Common Issues
- **Dependency Conflicts**: Run `flutter pub deps` to check
- **Build Errors**: Clean project with `flutter clean`
- **API Issues**: Check backend connectivity and CORS
- **Authentication**: Verify token format and expiration

### Development Setup
- **Flutter SDK**: Version 3.29.2 or later
- **Android Studio**: For Android development and emulators
- **VS Code**: Recommended editor with Flutter extension
- **Device/Emulator**: For testing and debugging

### Performance Issues  
- **Memory Leaks**: Use Flutter Inspector to debug
- **Slow Rendering**: Profile with Flutter DevTools
- **Network Issues**: Check API response times and caching

## Security Considerations

### Data Protection
- **Secure Storage**: Sensitive data encrypted
- **Token Management**: Secure JWT handling
- **Network Security**: HTTPS and certificate pinning
- **Input Validation**: Sanitize user inputs

### Privacy
- **User Data**: Minimal collection and secure storage
- **Analytics**: Optional and anonymized
- **Permissions**: Request only necessary permissions
- **Data Retention**: Clear policies for data cleanup

## Future Enhancements

### Planned Features
- **Offline Mode**: Full offline editing capabilities
- **Social Sharing**: Direct sharing to social platforms
- **Voice Input**: Tamil voice-to-text for quotes
- **AR Features**: Augmented reality status creation
- **Collaboration**: Share templates with friends

### Technical Improvements
- **Performance**: Advanced caching and optimization
- **Accessibility**: Screen reader and accessibility support
- **Internationalization**: Multi-language support
- **Analytics**: Advanced user behavior tracking
- **Testing**: Increased test coverage and automation

## Support & Resources

### Documentation
- **Flutter Docs**: https://flutter.dev/docs
- **Material Design**: https://m3.material.io/
- **Dart Language**: https://dart.dev/guides

### Development Tools
- **Flutter Inspector**: Widget tree debugging
- **DevTools**: Performance and memory profiling  
- **Android Studio**: IDE and emulator management
- **VS Code**: Lightweight development environment

This Flutter app is designed to work seamlessly with the Laravel backend, providing a smooth and feature-rich experience for Tamil status creation and sharing.