import 'package:dio/dio.dart';
import '../../constants/app_constants.dart';
import '../../storage/secure_storage.dart';

class AuthInterceptor extends Interceptor {
  final SecureStorage _secureStorage;

  AuthInterceptor(this._secureStorage);

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) async {
    // Skip auth for login, register, and public endpoints
    if (_isPublicEndpoint(options.path)) {
      return handler.next(options);
    }

    // Add access token to headers
    final token = await _secureStorage.read(AppConstants.accessTokenKey);
    if (token != null) {
      options.headers['Authorization'] = 'Bearer $token';
    }

    handler.next(options);
  }

  @override
  void onResponse(Response response, ResponseInterceptorHandler handler) {
    // Handle successful responses
    handler.next(response);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) async {
    // Handle 401 Unauthorized - token might be expired
    if (err.response?.statusCode == 401) {
      final refreshToken = await _secureStorage.read(AppConstants.refreshTokenKey);
      
      if (refreshToken != null) {
        try {
          // Attempt to refresh the token
          final newTokens = await _refreshToken(refreshToken);
          
          if (newTokens != null) {
            // Save new tokens
            await _secureStorage.write(AppConstants.accessTokenKey, newTokens['access_token']);
            await _secureStorage.write(AppConstants.refreshTokenKey, newTokens['refresh_token']);
            
            // Retry the original request with new token
            final options = err.requestOptions;
            options.headers['Authorization'] = 'Bearer ${newTokens['access_token']}';
            
            final response = await Dio().fetch(options);
            return handler.resolve(response);
          }
        } catch (e) {
          // Refresh failed, clear tokens and redirect to login
          await _clearTokens();
        }
      }
    }

    handler.next(err);
  }

  bool _isPublicEndpoint(String path) {
    const publicEndpoints = [
      '/auth/login',
      '/auth/register',
      '/auth/send-otp',
      '/auth/verify-otp',
      '/auth/reset-password',
      '/public',
      '/health',
    ];
    
    return publicEndpoints.any((endpoint) => path.contains(endpoint));
  }

  Future<Map<String, dynamic>?> _refreshToken(String refreshToken) async {
    try {
      final dio = Dio();
      final response = await dio.post(
        '${AppConstants.apiBaseUrl}/auth/refresh',
        data: {'refresh_token': refreshToken},
      );

      if (response.statusCode == 200 && response.data['success']) {
        return response.data['data'];
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  Future<void> _clearTokens() async {
    await _secureStorage.delete(AppConstants.accessTokenKey);
    await _secureStorage.delete(AppConstants.refreshTokenKey);
    await _secureStorage.delete(AppConstants.userDataKey);
  }
}