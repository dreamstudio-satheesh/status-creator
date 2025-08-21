import 'package:dio/dio.dart';
import 'dart:developer' as developer;

class AuthApiService {
  static final AuthApiService _instance = AuthApiService._internal();
  factory AuthApiService() => _instance;
  AuthApiService._internal();

  final Dio _dio = Dio();

  // Base URL - should come from environment variables
  static const String baseUrl = 'https://status.dreamcoderz.com/api/v1';

  Future<GoogleAuthResponse> authenticateWithGoogle({
    required String idToken,
    required String email,
    required String name,
    String? mobile,
    String? avatar,
  }) async {
    try {
      developer.log('Sending Google authentication request to backend');

      final response = await _dio.post(
        '$baseUrl/auth/google/authenticate',
        data: {
          'id_token': idToken,
          'email': email,
          'name': name,
          'mobile': mobile,
          'avatar': avatar,
        },
        options: Options(
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
        ),
      );

      developer.log('Backend response status: ${response.statusCode}');

      if (response.statusCode == 200) {
        final data = response.data;
        return GoogleAuthResponse.success(
          token: data['token'],
          user: AuthUser.fromJson(data['user']),
          message: data['message'] ?? 'Authentication successful',
        );
      } else {
        return GoogleAuthResponse.error(
          'Authentication failed with status: ${response.statusCode}',
        );
      }
    } on DioException catch (e) {
      developer.log('Dio error during Google authentication: ${e.message}');
      
      if (e.response != null) {
        final errorData = e.response!.data;
        final errorMessage = errorData['message'] ?? 'Authentication failed';
        return GoogleAuthResponse.error(errorMessage);
      }
      
      return GoogleAuthResponse.error('Network error: ${e.message}');
    } catch (e) {
      developer.log('Unexpected error during Google authentication: $e');
      return GoogleAuthResponse.error('Unexpected error: ${e.toString()}');
    }
  }

  Future<OtpResponse> sendOtp(String mobile) async {
    try {
      developer.log('Sending OTP request for mobile: $mobile');

      final response = await _dio.post(
        '$baseUrl/auth/send-otp',
        data: {'mobile': mobile},
        options: Options(
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
        ),
      );

      if (response.statusCode == 200) {
        final data = response.data;
        return OtpResponse.success(
          message: data['message'] ?? 'OTP sent successfully',
          expiresIn: data['expires_in'] ?? 300,
          developmentOtp: data['development_otp'], // Only in development
        );
      } else {
        return OtpResponse.error('Failed to send OTP');
      }
    } on DioException catch (e) {
      developer.log('Dio error during OTP send: ${e.message}');
      
      if (e.response != null) {
        final errorData = e.response!.data;
        final errorMessage = errorData['message'] ?? 'Failed to send OTP';
        return OtpResponse.error(errorMessage);
      }
      
      return OtpResponse.error('Network error: ${e.message}');
    } catch (e) {
      developer.log('Unexpected error during OTP send: $e');
      return OtpResponse.error('Unexpected error: ${e.toString()}');
    }
  }

  Future<OtpVerifyResponse> verifyOtp(String mobile, String otp) async {
    try {
      developer.log('Verifying OTP for mobile: $mobile');

      final response = await _dio.post(
        '$baseUrl/auth/verify-otp',
        data: {
          'mobile': mobile,
          'otp': otp,
        },
        options: Options(
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
        ),
      );

      if (response.statusCode == 200) {
        final data = response.data;
        return OtpVerifyResponse.success(
          token: data['token'],
          user: AuthUser.fromJson(data['user']),
          message: data['message'] ?? 'OTP verified successfully',
        );
      } else {
        return OtpVerifyResponse.error('OTP verification failed');
      }
    } on DioException catch (e) {
      developer.log('Dio error during OTP verification: ${e.message}');
      
      if (e.response != null) {
        final errorData = e.response!.data;
        final errorMessage = errorData['message'] ?? 'OTP verification failed';
        return OtpVerifyResponse.error(errorMessage);
      }
      
      return OtpVerifyResponse.error('Network error: ${e.message}');
    } catch (e) {
      developer.log('Unexpected error during OTP verification: $e');
      return OtpVerifyResponse.error('Unexpected error: ${e.toString()}');
    }
  }
}

// Response models
class GoogleAuthResponse {
  final bool isSuccess;
  final String? token;
  final AuthUser? user;
  final String message;

  GoogleAuthResponse._({
    required this.isSuccess,
    this.token,
    this.user,
    required this.message,
  });

  factory GoogleAuthResponse.success({
    required String token,
    required AuthUser user,
    required String message,
  }) {
    return GoogleAuthResponse._(
      isSuccess: true,
      token: token,
      user: user,
      message: message,
    );
  }

  factory GoogleAuthResponse.error(String message) {
    return GoogleAuthResponse._(
      isSuccess: false,
      message: message,
    );
  }
}

class OtpResponse {
  final bool isSuccess;
  final String message;
  final int? expiresIn;
  final String? developmentOtp;

  OtpResponse._({
    required this.isSuccess,
    required this.message,
    this.expiresIn,
    this.developmentOtp,
  });

  factory OtpResponse.success({
    required String message,
    int? expiresIn,
    String? developmentOtp,
  }) {
    return OtpResponse._(
      isSuccess: true,
      message: message,
      expiresIn: expiresIn,
      developmentOtp: developmentOtp,
    );
  }

  factory OtpResponse.error(String message) {
    return OtpResponse._(
      isSuccess: false,
      message: message,
    );
  }
}

class OtpVerifyResponse {
  final bool isSuccess;
  final String? token;
  final AuthUser? user;
  final String message;

  OtpVerifyResponse._({
    required this.isSuccess,
    this.token,
    this.user,
    required this.message,
  });

  factory OtpVerifyResponse.success({
    required String token,
    required AuthUser user,
    required String message,
  }) {
    return OtpVerifyResponse._(
      isSuccess: true,
      token: token,
      user: user,
      message: message,
    );
  }

  factory OtpVerifyResponse.error(String message) {
    return OtpVerifyResponse._(
      isSuccess: false,
      message: message,
    );
  }
}

// User model
class AuthUser {
  final int id;
  final String name;
  final String email;
  final String? mobile;
  final String? avatar;
  final String subscriptionType;
  final DateTime? subscriptionExpiresAt;
  final int dailyAiQuota;
  final int dailyAiUsed;
  final bool isPremium;

  AuthUser({
    required this.id,
    required this.name,
    required this.email,
    this.mobile,
    this.avatar,
    required this.subscriptionType,
    this.subscriptionExpiresAt,
    required this.dailyAiQuota,
    required this.dailyAiUsed,
    required this.isPremium,
  });

  factory AuthUser.fromJson(Map<String, dynamic> json) {
    return AuthUser(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      mobile: json['mobile'],
      avatar: json['avatar'],
      subscriptionType: json['subscription_type'],
      subscriptionExpiresAt: json['subscription_expires_at'] != null
          ? DateTime.parse(json['subscription_expires_at'])
          : null,
      dailyAiQuota: json['daily_ai_quota'],
      dailyAiUsed: json['daily_ai_used'],
      isPremium: json['is_premium'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'mobile': mobile,
      'avatar': avatar,
      'subscription_type': subscriptionType,
      'subscription_expires_at': subscriptionExpiresAt?.toIso8601String(),
      'daily_ai_quota': dailyAiQuota,
      'daily_ai_used': dailyAiUsed,
      'is_premium': isPremium,
    };
  }
}