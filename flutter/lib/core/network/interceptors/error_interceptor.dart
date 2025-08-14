import 'package:dio/dio.dart';
import 'dart:developer' as developer;

class ErrorInterceptor extends Interceptor {
  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    developer.log(
      'REQUEST: ${options.method} ${options.uri}',
      name: 'ApiClient',
    );
    handler.next(options);
  }

  @override
  void onResponse(Response response, ResponseInterceptorHandler handler) {
    developer.log(
      'RESPONSE: ${response.statusCode} ${response.requestOptions.uri}',
      name: 'ApiClient',
    );
    handler.next(response);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    developer.log(
      'ERROR: ${err.response?.statusCode} ${err.requestOptions.uri} - ${err.message}',
      name: 'ApiClient',
      error: err,
    );

    // Transform DioException to custom exceptions
    final customError = _mapDioExceptionToCustomError(err);
    
    // Create a new DioException with custom error details
    final modifiedError = DioException(
      requestOptions: err.requestOptions,
      response: err.response,
      type: err.type,
      error: customError,
      message: customError.toString(),
    );

    handler.next(modifiedError);
  }

  CustomApiError _mapDioExceptionToCustomError(DioException err) {
    switch (err.type) {
      case DioExceptionType.connectionTimeout:
      case DioExceptionType.sendTimeout:
      case DioExceptionType.receiveTimeout:
        return CustomApiError(
          type: ErrorType.timeout,
          message: 'Connection timeout. Please check your internet connection.',
          statusCode: null,
        );

      case DioExceptionType.badResponse:
        return _handleBadResponse(err.response);

      case DioExceptionType.cancel:
        return CustomApiError(
          type: ErrorType.cancelled,
          message: 'Request was cancelled',
          statusCode: null,
        );

      case DioExceptionType.unknown:
        if (err.error.toString().contains('SocketException') ||
            err.error.toString().contains('HandshakeException')) {
          return CustomApiError(
            type: ErrorType.network,
            message: 'No internet connection available.',
            statusCode: null,
          );
        }
        return CustomApiError(
          type: ErrorType.unknown,
          message: 'An unexpected error occurred.',
          statusCode: null,
        );

      default:
        return CustomApiError(
          type: ErrorType.unknown,
          message: 'An unexpected error occurred.',
          statusCode: null,
        );
    }
  }

  CustomApiError _handleBadResponse(Response<dynamic>? response) {
    if (response == null) {
      return CustomApiError(
        type: ErrorType.server,
        message: 'Server error occurred.',
        statusCode: null,
      );
    }

    final statusCode = response.statusCode;
    final responseData = response.data;

    // Try to extract error message from response
    String message = 'An error occurred.';
    if (responseData is Map<String, dynamic>) {
      message = responseData['message'] ?? 
                responseData['error'] ?? 
                responseData['errors']?.toString() ?? 
                message;
    }

    switch (statusCode) {
      case 400:
        return CustomApiError(
          type: ErrorType.badRequest,
          message: message.isEmpty ? 'Invalid request data.' : message,
          statusCode: statusCode,
          data: responseData,
        );

      case 401:
        return CustomApiError(
          type: ErrorType.unauthorized,
          message: message.isEmpty ? 'Session expired. Please login again.' : message,
          statusCode: statusCode,
          data: responseData,
        );

      case 403:
        return CustomApiError(
          type: ErrorType.forbidden,
          message: message.isEmpty ? 'Access denied.' : message,
          statusCode: statusCode,
          data: responseData,
        );

      case 404:
        return CustomApiError(
          type: ErrorType.notFound,
          message: message.isEmpty ? 'Resource not found.' : message,
          statusCode: statusCode,
          data: responseData,
        );

      case 422:
        return CustomApiError(
          type: ErrorType.validation,
          message: message.isEmpty ? 'Validation failed.' : message,
          statusCode: statusCode,
          data: responseData,
        );

      case 429:
        return CustomApiError(
          type: ErrorType.tooManyRequests,
          message: message.isEmpty ? 'Too many requests. Please try again later.' : message,
          statusCode: statusCode,
          data: responseData,
        );

      case 500:
      case 502:
      case 503:
      case 504:
        return CustomApiError(
          type: ErrorType.server,
          message: message.isEmpty ? 'Server error. Please try again later.' : message,
          statusCode: statusCode,
          data: responseData,
        );

      default:
        return CustomApiError(
          type: ErrorType.server,
          message: message.isEmpty ? 'An unexpected server error occurred.' : message,
          statusCode: statusCode,
          data: responseData,
        );
    }
  }
}

enum ErrorType {
  network,
  timeout,
  server,
  badRequest,
  unauthorized,
  forbidden,
  notFound,
  validation,
  tooManyRequests,
  cancelled,
  unknown,
}

class CustomApiError {
  final ErrorType type;
  final String message;
  final int? statusCode;
  final dynamic data;

  CustomApiError({
    required this.type,
    required this.message,
    this.statusCode,
    this.data,
  });

  bool get isNetworkError => type == ErrorType.network;
  bool get isTimeoutError => type == ErrorType.timeout;
  bool get isServerError => type == ErrorType.server;
  bool get isUnauthorized => type == ErrorType.unauthorized;
  bool get isValidationError => type == ErrorType.validation;
  bool get isForbidden => type == ErrorType.forbidden;
  bool get isNotFound => type == ErrorType.notFound;

  @override
  String toString() => message;

  Map<String, dynamic> toJson() => {
    'type': type.toString(),
    'message': message,
    'statusCode': statusCode,
    'data': data,
  };
}