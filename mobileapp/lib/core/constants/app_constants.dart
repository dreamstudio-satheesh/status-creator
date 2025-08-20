class AppConstants {
  // App Info
  static const String appName = 'Tamil Status Creator';
  static const String appVersion = '1.0.0';
  static const String appDescription = 'AI-powered Tamil status image creator';
  
  // API Configuration
  static const String apiBaseUrl = String.fromEnvironment('API_BASE_URL', defaultValue: 'https://status.dreamcoderz.com/api/v1');
  static const String laravelBaseUrl = String.fromEnvironment('LARAVEL_BASE_URL', defaultValue: 'https://status.dreamcoderz.com');
  static const String storageBaseUrl = String.fromEnvironment('STORAGE_BASE_URL', defaultValue: 'https://status.dreamcoderz.com/storage');
  static const int apiTimeout = int.fromEnvironment('API_TIMEOUT', defaultValue: 30000);
  
  // Storage Keys
  static const String accessTokenKey = 'access_token';
  static const String refreshTokenKey = 'refresh_token';
  static const String userDataKey = 'user_data';
  static const String themeKey = 'app_theme';
  static const String languageKey = 'app_language';
  static const String onboardingKey = 'onboarding_completed';
  
  // Pagination
  static const int defaultPageSize = 20;
  static const int maxPageSize = 100;
  
  // Image Configuration
  static const int maxImageSize = 5 * 1024 * 1024; // 5MB
  static const List<String> supportedImageFormats = ['jpg', 'jpeg', 'png', 'webp'];
  static const int thumbnailSize = 300;
  static const int previewSize = 800;
  
  // Cache Configuration
  static const int imageCacheTimeout = 7 * 24 * 60 * 60 * 1000; // 7 days
  static const int dataCacheTimeout = 1 * 60 * 60 * 1000; // 1 hour
  
  // Tamil Text Configuration
  static const String defaultTamilFont = 'Tamil';
  static const String fallbackTamilFont = 'Catamaran';
  static const List<String> supportedTamilFonts = ['Tamil', 'Catamaran', 'Latha'];
  
  // Subscription
  static const String premiumPlanId = 'premium_monthly';
  static const double premiumPrice = 99.0; // ₹99
  
  // Social Sharing
  static const String shareText = 'Check out this Tamil status created with Tamil Status Creator';
  static const String playStoreUrl = 'https://play.google.com/store/apps/details?id=com.tamilstatus.creator';
  static const String shareUrlBase = 'https://status.dreamcoderz.com/share';
  
  // Feature Flags
  static const bool enableBiometric = bool.fromEnvironment('ENABLE_BIOMETRIC', defaultValue: true);
  static const bool enableOfflineMode = bool.fromEnvironment('ENABLE_OFFLINE_MODE', defaultValue: true);
  static const bool enableAnalytics = bool.fromEnvironment('ENABLE_ANALYTICS', defaultValue: false);
  static const bool debugMode = bool.fromEnvironment('DEBUG_MODE', defaultValue: false);
}

class ApiEndpoints {
  // Authentication
  static const String login = '/auth/login';
  static const String register = '/auth/register';
  static const String logout = '/auth/logout';
  static const String refreshToken = '/auth/refresh';
  static const String sendOtp = '/auth/send-otp';
  static const String verifyOtp = '/auth/verify-otp';
  static const String resetPassword = '/auth/reset-password';
  
  // Profile
  static const String profile = '/user/profile';
  static const String updateProfile = '/user/profile';
  static const String uploadAvatar = '/user/avatar';
  
  // Templates
  static const String templates = '/templates';
  static const String featuredTemplates = '/templates/featured';
  static const String searchTemplates = '/templates/search';
  static const String templateDetails = '/templates';  // /{id}
  static const String favoriteTemplate = '/templates'; // /{id}/favorite
  static const String useTemplate = '/templates';      // /{id}/use
  
  // Themes
  static const String themes = '/themes';
  static const String themeTemplates = '/themes';      // /{id}/templates
  
  // AI Generation
  static const String generateQuote = '/ai/generate-quote';
  static const String captionImage = '/ai/caption-image';
  static const String aiQuota = '/ai/quota';
  
  // User Creations
  static const String creations = '/creations';
  static const String createStatus = '/creations';
  static const String updateCreation = '/creations';   // /{id}
  static const String deleteCreation = '/creations';   // /{id}
  static const String shareCreation = '/creations';    // /{id}/share
  
  // Subscription
  static const String subscriptionPlans = '/subscription/plans';
  static const String createSubscription = '/subscription/create';
  static const String subscriptionStatus = '/subscription/status';
  static const String cancelSubscription = '/subscription/cancel';
  
  // Feedback
  static const String submitFeedback = '/feedback/submit';
  static const String appRating = '/feedback/app-rating';
  
  // Uploads
  static const String uploadImage = '/uploads/image';
  static const String uploadLimits = '/uploads/limits';
}

class Routes {
  // Authentication
  static const String splash = '/';
  static const String onboarding = '/onboarding';
  static const String login = '/login';
  static const String register = '/register';
  static const String otpVerification = '/otp-verification';
  static const String forgotPassword = '/forgot-password';
  
  // Main App
  static const String home = '/home';
  static const String templates = '/templates';
  static const String editor = '/editor';
  static const String profile = '/profile';
  static const String creations = '/creations';
  
  // Templates
  static const String templateDetails = '/template-details';
  static const String themeTemplates = '/theme-templates';
  static const String searchTemplates = '/search-templates';
  
  // Premium
  static const String subscription = '/subscription';
  static const String paymentSuccess = '/payment-success';
  static const String paymentFailure = '/payment-failure';
  
  // Settings
  static const String settings = '/settings';
  static const String about = '/about';
  static const String feedback = '/feedback';
  static const String help = '/help';
}

class ErrorMessages {
  static const String networkError = 'Network connection failed. Please check your internet.';
  static const String serverError = 'Server error occurred. Please try again later.';
  static const String unauthorizedError = 'Session expired. Please login again.';
  static const String validationError = 'Please check your input and try again.';
  static const String unknownError = 'Something went wrong. Please try again.';
  
  // Authentication
  static const String invalidCredentials = 'Invalid email or password.';
  static const String accountNotVerified = 'Please verify your account first.';
  static const String accountBlocked = 'Your account has been blocked.';
  
  // Templates
  static const String templateNotFound = 'Template not found.';
  static const String quotaExceeded = 'Daily AI generation quota exceeded.';
  static const String subscriptionRequired = 'Premium subscription required for this feature.';
  
  // File Upload
  static const String fileTooLarge = 'File size too large. Maximum size is 5MB.';
  static const String unsupportedFormat = 'Unsupported file format.';
  static const String uploadFailed = 'File upload failed. Please try again.';
}