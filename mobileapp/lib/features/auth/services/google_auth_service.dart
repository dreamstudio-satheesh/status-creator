import 'package:google_sign_in/google_sign_in.dart';
import 'package:firebase_auth/firebase_auth.dart';
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
    // serverClientId: '110010026082-d5ohpap4rqetm7qu430bjei84hfcfa36.apps.googleusercontent.com',
  );

  final FirebaseAuth _firebaseAuth = FirebaseAuth.instance;

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

      // Create a new credential
      final credential = GoogleAuthProvider.credential(
        accessToken: googleAuth.accessToken,
        idToken: googleAuth.idToken,
      );

      // Sign in to Firebase with the Google credential
      final UserCredential userCredential = await _firebaseAuth.signInWithCredential(credential);
      final User? firebaseUser = userCredential.user;

      if (firebaseUser == null) {
        developer.log('Failed to sign in with Firebase');
        return GoogleSignInResult.error('Failed to authenticate with Firebase');
      }

      developer.log('Firebase authentication successful for: ${firebaseUser.email}');

      // Get Firebase ID token for backend authentication
      final String? idToken = await firebaseUser.getIdToken();
      
      if (idToken == null) {
        developer.log('Failed to get Firebase ID token');
        return GoogleSignInResult.error('Failed to get authentication token');
      }

      return GoogleSignInResult.success(
        googleUser: googleUser,
        firebaseUser: firebaseUser,
        idToken: idToken,
      );

    } on FirebaseAuthException catch (e) {
      developer.log('Firebase Auth Error: ${e.code} - ${e.message}');
      return GoogleSignInResult.error(_getFirebaseErrorMessage(e.code));
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
      await Future.wait([
        _googleSignIn.signOut(),
        _firebaseAuth.signOut(),
      ]);
      developer.log('Google and Firebase sign out successful');
    } catch (e) {
      developer.log('Error during sign out: $e');
      throw Exception('Failed to sign out: ${e.toString()}');
    }
  }

  Future<bool> isSignedIn() async {
    try {
      final googleSignedIn = await _googleSignIn.isSignedIn();
      final firebaseUser = _firebaseAuth.currentUser;
      return googleSignedIn && firebaseUser != null;
    } catch (e) {
      developer.log('Error checking sign-in status: $e');
      return false;
    }
  }

  Future<User?> getCurrentUser() async {
    try {
      return _firebaseAuth.currentUser;
    } catch (e) {
      developer.log('Error getting current user: $e');
      return null;
    }
  }

  String _getFirebaseErrorMessage(String errorCode) {
    switch (errorCode) {
      case 'account-exists-with-different-credential':
        return 'An account already exists with a different sign-in method.';
      case 'invalid-credential':
        return 'The credential is invalid or has expired.';
      case 'operation-not-allowed':
        return 'Google sign-in is not enabled for this project.';
      case 'user-disabled':
        return 'This user account has been disabled.';
      case 'user-not-found':
        return 'No user found for this account.';
      case 'wrong-password':
        return 'Wrong password provided.';
      case 'invalid-verification-code':
        return 'The verification code is invalid.';
      case 'invalid-verification-id':
        return 'The verification ID is invalid.';
      case 'network-request-failed':
        return 'Network error. Please check your connection.';
      default:
        return 'Authentication failed. Please try again.';
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
  final User? firebaseUser;
  final String? idToken;

  GoogleSignInResult._({
    required this.isSuccess,
    required this.isCancelled,
    this.error,
    this.googleUser,
    this.firebaseUser,
    this.idToken,
  });

  factory GoogleSignInResult.success({
    required GoogleSignInAccount googleUser,
    required User firebaseUser,
    required String idToken,
  }) {
    return GoogleSignInResult._(
      isSuccess: true,
      isCancelled: false,
      googleUser: googleUser,
      firebaseUser: firebaseUser,
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