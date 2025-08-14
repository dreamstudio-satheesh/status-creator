import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../features/auth/screens/login_screen.dart';
import '../../features/auth/screens/register_screen.dart';
import '../../features/auth/screens/otp_verification_screen.dart';
import '../../features/auth/screens/forgot_password_screen.dart';
import '../../features/templates/screens/home_screen.dart';
import '../../features/templates/screens/templates_screen.dart';
import '../../features/templates/screens/template_details_screen.dart';
import '../../features/templates/screens/theme_templates_screen.dart';
import '../../features/editor/screens/editor_screen.dart';
import '../../features/profile/screens/profile_screen.dart';
import '../../features/profile/screens/settings_screen.dart';
import '../../features/subscription/screens/subscription_screen.dart';
import '../../shared/screens/splash_screen.dart';
import '../../shared/screens/onboarding_screen.dart';
import '../../shared/screens/error_screen.dart';
import '../constants/app_constants.dart';
import '../storage/secure_storage.dart';

class AppRouter {
  static final SecureStorage _secureStorage = SecureStorage();
  
  static final GoRouter router = GoRouter(
    initialLocation: Routes.splash,
    debugLogDiagnostics: AppConstants.debugMode,
    redirect: _redirect,
    routes: [
      // Splash & Onboarding
      GoRoute(
        path: Routes.splash,
        name: 'splash',
        builder: (context, state) => const SplashScreen(),
      ),
      GoRoute(
        path: Routes.onboarding,
        name: 'onboarding',
        builder: (context, state) => const OnboardingScreen(),
      ),

      // Authentication Routes
      GoRoute(
        path: Routes.login,
        name: 'login',
        builder: (context, state) => const LoginScreen(),
        routes: [
          GoRoute(
            path: 'register',
            name: 'register',
            builder: (context, state) => const RegisterScreen(),
          ),
          GoRoute(
            path: 'forgot-password',
            name: 'forgot-password',
            builder: (context, state) => const ForgotPasswordScreen(),
          ),
        ],
      ),
      GoRoute(
        path: Routes.otpVerification,
        name: 'otp-verification',
        builder: (context, state) {
          final extra = state.extra as Map<String, dynamic>?;
          return OtpVerificationScreen(
            phoneNumber: extra?['phoneNumber'] ?? '',
            isRegistration: extra?['isRegistration'] ?? false,
          );
        },
      ),

      // Main App Routes (Requires Authentication)
      ShellRoute(
        builder: (context, state, child) {
          return MainShell(child: child);
        },
        routes: [
          // Home
          GoRoute(
            path: Routes.home,
            name: 'home',
            builder: (context, state) => const HomeScreen(),
          ),

          // Templates
          GoRoute(
            path: Routes.templates,
            name: 'templates',
            builder: (context, state) => const TemplatesScreen(),
            routes: [
              GoRoute(
                path: 'details/:templateId',
                name: 'template-details',
                builder: (context, state) {
                  final templateId = state.pathParameters['templateId']!;
                  return TemplateDetailsScreen(templateId: templateId);
                },
              ),
              GoRoute(
                path: 'theme/:themeId',
                name: 'theme-templates',
                builder: (context, state) {
                  final themeId = state.pathParameters['themeId']!;
                  final themeName = state.uri.queryParameters['name'] ?? 'Templates';
                  return ThemeTemplatesScreen(
                    themeId: themeId,
                    themeName: themeName,
                  );
                },
              ),
            ],
          ),

          // Editor
          GoRoute(
            path: Routes.editor,
            name: 'editor',
            builder: (context, state) {
              final extra = state.extra as Map<String, dynamic>?;
              return EditorScreen(
                templateId: extra?['templateId'],
                imageUrl: extra?['imageUrl'],
                isEdit: extra?['isEdit'] ?? false,
              );
            },
          ),

          // Profile & Settings
          GoRoute(
            path: Routes.profile,
            name: 'profile',
            builder: (context, state) => const ProfileScreen(),
            routes: [
              GoRoute(
                path: 'settings',
                name: 'settings',
                builder: (context, state) => const SettingsScreen(),
              ),
              GoRoute(
                path: 'subscription',
                name: 'subscription',
                builder: (context, state) => const SubscriptionScreen(),
              ),
            ],
          ),

          // User Creations
          GoRoute(
            path: Routes.creations,
            name: 'creations',
            builder: (context, state) => const UserCreationsScreen(),
          ),
        ],
      ),

      // Error Route
      GoRoute(
        path: '/error',
        name: 'error',
        builder: (context, state) {
          final error = state.extra as String? ?? 'Unknown error occurred';
          return ErrorScreen(errorMessage: error);
        },
      ),
    ],
    errorBuilder: (context, state) => ErrorScreen(
      errorMessage: 'Page not found: ${state.uri.path}',
    ),
  );

  // Navigation helper methods
  static void pushNamed(String name, {Map<String, String>? pathParameters, Object? extra}) {
    router.pushNamed(name, pathParameters: pathParameters, extra: extra);
  }

  static void goNamed(String name, {Map<String, String>? pathParameters, Object? extra}) {
    router.goNamed(name, pathParameters: pathParameters, extra: extra);
  }

  static void pushReplacementNamed(String name, {Map<String, String>? pathParameters, Object? extra}) {
    router.pushReplacementNamed(name, pathParameters: pathParameters, extra: extra);
  }

  static void pop<T>([T? result]) {
    router.pop(result);
  }

  static bool canPop() {
    return router.canPop();
  }

  // Authentication-based redirect logic
  static Future<String?> _redirect(BuildContext context, GoRouterState state) async {
    // Check if user is authenticated
    final isAuthenticated = await _isUserAuthenticated();
    
    // Check if onboarding is completed
    final isOnboardingCompleted = await _isOnboardingCompleted();
    
    final currentPath = state.uri.path;

    // Handle splash screen logic
    if (currentPath == Routes.splash) {
      if (!isOnboardingCompleted) {
        return Routes.onboarding;
      }
      return isAuthenticated ? Routes.home : Routes.login;
    }

    // Handle onboarding
    if (currentPath == Routes.onboarding) {
      if (isOnboardingCompleted) {
        return isAuthenticated ? Routes.home : Routes.login;
      }
      return null; // Stay on onboarding
    }

    // Handle authentication routes
    if (_isAuthRoute(currentPath)) {
      if (isAuthenticated) {
        return Routes.home; // Redirect authenticated users away from auth screens
      }
      return null; // Allow access to auth screens
    }

    // Handle protected routes
    if (_isProtectedRoute(currentPath)) {
      if (!isAuthenticated) {
        return Routes.login; // Redirect unauthenticated users to login
      }
      return null; // Allow access to protected routes
    }

    return null; // No redirect needed
  }

  static bool _isAuthRoute(String path) {
    const authRoutes = [
      Routes.login,
      Routes.register,
      Routes.otpVerification,
      Routes.forgotPassword,
    ];
    return authRoutes.any((route) => path.startsWith(route));
  }

  static bool _isProtectedRoute(String path) {
    const protectedRoutes = [
      Routes.home,
      Routes.templates,
      Routes.editor,
      Routes.profile,
      Routes.creations,
      Routes.subscription,
      Routes.settings,
    ];
    return protectedRoutes.any((route) => path.startsWith(route));
  }

  static Future<bool> _isUserAuthenticated() async {
    try {
      final token = await _secureStorage.read(AppConstants.accessTokenKey);
      return token != null && token.isNotEmpty;
    } catch (e) {
      return false;
    }
  }

  static Future<bool> _isOnboardingCompleted() async {
    try {
      final completed = await _secureStorage.readBool(AppConstants.onboardingKey);
      return completed ?? false;
    } catch (e) {
      return false;
    }
  }
}

// Shell widget for main app navigation
class MainShell extends StatelessWidget {
  final Widget child;

  const MainShell({Key? key, required this.child}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: child,
      bottomNavigationBar: _buildBottomNavigationBar(context),
    );
  }

  Widget _buildBottomNavigationBar(BuildContext context) {
    final currentPath = GoRouterState.of(context).uri.path;
    
    return BottomNavigationBar(
      type: BottomNavigationBarType.fixed,
      currentIndex: _getCurrentIndex(currentPath),
      onTap: (index) => _onTabTapped(context, index),
      items: const [
        BottomNavigationBarItem(
          icon: Icon(Icons.home_outlined),
          activeIcon: Icon(Icons.home),
          label: 'Home',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.grid_view_outlined),
          activeIcon: Icon(Icons.grid_view),
          label: 'Templates',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.edit_outlined),
          activeIcon: Icon(Icons.edit),
          label: 'Editor',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.bookmark_outline),
          activeIcon: Icon(Icons.bookmark),
          label: 'My Work',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.person_outline),
          activeIcon: Icon(Icons.person),
          label: 'Profile',
        ),
      ],
    );
  }

  int _getCurrentIndex(String path) {
    if (path.startsWith(Routes.home)) return 0;
    if (path.startsWith(Routes.templates)) return 1;
    if (path.startsWith(Routes.editor)) return 2;
    if (path.startsWith(Routes.creations)) return 3;
    if (path.startsWith(Routes.profile)) return 4;
    return 0; // Default to home
  }

  void _onTabTapped(BuildContext context, int index) {
    switch (index) {
      case 0:
        context.go(Routes.home);
        break;
      case 1:
        context.go(Routes.templates);
        break;
      case 2:
        context.go(Routes.editor);
        break;
      case 3:
        context.go(Routes.creations);
        break;
      case 4:
        context.go(Routes.profile);
        break;
    }
  }
}

// Placeholder screens (to be implemented)
class UserCreationsScreen extends StatelessWidget {
  const UserCreationsScreen({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      body: Center(
        child: Text('User Creations Screen'),
      ),
    );
  }
}