import 'package:google_sign_in/google_sign_in.dart';
import 'package:flutter/services.dart';
import 'dart:developer' as developer;

class GoogleAuthService {
  static final GoogleAuthService _instance = GoogleAuthService._internal();
  factory GoogleAuthService() => _instance;
  GoogleAuthService._internal();

  final GoogleSignIn _googleSignIn = GoogleSignIn(
    scopes: [
      'email',
      'profile',
    ],
  );

  Future<GoogleSignInResult> signInWithGoogle() async {
    try {
      developer.log('Starting Google Sign-In process');

      // Trigger the authentication flow
      final GoogleSignInAccount? googleUser = await _googleSignIn.signIn();
      
      if (googleUser == null) {
        developer.log('Google Sign-In cancelled by user');
        return GoogleSignInResult.cancelled();
      }

      developer.log('Google user signed in: ${googleUser.email}');

      // Obtain the auth details from the request
      final GoogleSignInAuthentication googleAuth = await googleUser.authentication;

      if (googleAuth.accessToken == null || googleAuth.idToken == null) {
        developer.log('Failed to get Google authentication tokens');
        return GoogleSignInResult.error('Failed to get authentication tokens');
      }

      return GoogleSignInResult.success(
        googleUser: googleUser,
        idToken: googleAuth.idToken!,
      );

    } on PlatformException catch (e) {
      developer.log('Platform Error: ${e.code} - ${e.message}');
      return GoogleSignInResult.error(_getPlatformErrorMessage(e.code));
    } catch (e) {
      developer.log('Unknown Error: $e');
      return GoogleSignInResult.error('An unexpected error occurred: ${e.toString()}');
    }
  }

  Future<void> signOut() async {
    try {
      await _googleSignIn.signOut();
      developer.log('Google sign out successful');
    } catch (e) {
      developer.log('Error during sign out: $e');
      throw Exception('Failed to sign out: ${e.toString()}');
    }
  }

  Future<bool> isSignedIn() async {
    try {
      return await _googleSignIn.isSignedIn();
    } catch (e) {
      developer.log('Error checking sign-in status: $e');
      return false;
    }
  }

  Future<GoogleSignInAccount?> getCurrentUser() async {
    try {
      return _googleSignIn.currentUser;
    } catch (e) {
      developer.log('Error getting current user: $e');
      return null;
    }
  }


  String _getPlatformErrorMessage(String errorCode) {
    switch (errorCode) {
      case 'sign_in_canceled':
        return 'Sign in was cancelled.';
      case 'sign_in_failed':
        return 'Sign in failed. Please try again.';
      case 'network_error':
        return 'Network error. Please check your connection.';
      default:
        return 'An error occurred. Please try again.';
    }
  }
}

class GoogleSignInResult {
  final bool isSuccess;
  final bool isCancelled;
  final String? error;
  final GoogleSignInAccount? googleUser;
  final String? idToken;

  GoogleSignInResult._({
    required this.isSuccess,
    required this.isCancelled,
    this.error,
    this.googleUser,
    this.idToken,
  });

  factory GoogleSignInResult.success({
    required GoogleSignInAccount googleUser,
    required String idToken,
  }) {
    return GoogleSignInResult._(
      isSuccess: true,
      isCancelled: false,
      googleUser: googleUser,
      idToken: idToken,
    );
  }

  factory GoogleSignInResult.cancelled() {
    return GoogleSignInResult._(
      isSuccess: false,
      isCancelled: true,
    );
  }

  factory GoogleSignInResult.error(String error) {
    return GoogleSignInResult._(
      isSuccess: false,
      isCancelled: false,
      error: error,
    );
  }
}