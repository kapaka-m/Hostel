import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

import 'package:hostel_mobile/src/config/app_config.dart';

class ApiException implements Exception {
  final String message;
  final Map<String, List<String>> errors;
  final int? statusCode;

  ApiException({
    required this.message,
    this.errors = const {},
    this.statusCode,
  });

  @override
  String toString() => 'ApiException: $message';

  factory ApiException.fromDio(DioException exception) {
    final statusCode = exception.response?.statusCode;
    final data = exception.response?.data;
    final message = _extractMessage(data) ?? exception.message ?? 'Unknown error';
    final errors = _extractErrors(data);

    return ApiException(
      message: message,
      statusCode: statusCode,
      errors: errors,
    );
  }

  static String? _extractMessage(dynamic data) {
    if (data is Map<String, dynamic>) {
      final message = data['message'];
      if (message is String && message.isNotEmpty) {
        return message;
      }
    }
    return null;
  }

  static Map<String, List<String>> _extractErrors(dynamic data) {
    if (data is Map && data['errors'] is Map) {
      final raw = data['errors'] as Map;
      return raw.map((key, value) {
        final errors = <String>[];
        if (value is Iterable) {
          for (final item in value) {
            if (item is String) {
              errors.add(item);
            }
          }
        } else if (value is String) {
          errors.add(value);
        }
        return MapEntry(key.toString(), errors);
      });
    }

    return {};
  }
}

class ApiClient {
  final Dio _dio;
  String? _token;
  void Function()? onUnauthorized;

  ApiClient()
      : _dio = Dio(BaseOptions(
          baseUrl: AppConfig.baseUrl,
          connectTimeout: const Duration(seconds: 15),
          receiveTimeout: const Duration(seconds: 15),
          responseType: ResponseType.json,
        )) {
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = _token;
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          handler.next(options);
        },
      ),
    );
  }

  void setToken(String? token) {
    _token = token;
  }

  Future<Response<T>> get<T>(String path, {Map<String, dynamic>? query}) {
    return _send(_dio.get(path, queryParameters: query));
  }

  Future<Response<T>> post<T>(String path, {Map<String, dynamic>? data}) {
    return _send(_dio.post(path, data: data));
  }

  Future<Response<T>> put<T>(String path, {Map<String, dynamic>? data}) {
    return _send(_dio.put(path, data: data));
  }

  Future<Response<T>> patch<T>(String path, {Map<String, dynamic>? data}) {
    return _send(_dio.patch(path, data: data));
  }

  Future<Response<T>> delete<T>(String path, {Map<String, dynamic>? data}) {
    return _send(_dio.delete(path, data: data));
  }

  Future<Response<T>> _send<T>(Future<Response<T>> request) async {
    try {
      final response = await request;
      _logCorrelation(response.headers.value('x-correlation-id'));
      return response;
    } on DioException catch (exception) {
      _logCorrelation(exception.response?.headers.value('x-correlation-id'));
      if (exception.response?.statusCode == 401) {
        onUnauthorized?.call();
      }
      throw ApiException.fromDio(exception);
    }
  }

  void _logCorrelation(String? correlationId) {
    if (correlationId != null && kDebugMode) {
      debugPrint('Correlation ID: $correlationId');
    }
  }
}
