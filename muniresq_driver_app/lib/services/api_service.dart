import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class ApiService {
  ApiService._();

  static const String baseUrl = String.fromEnvironment(
    'MUNIRESQ_API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000',
  );
  static const String _tokenKey = 'muniresq_driver_access_token';
  static const FlutterSecureStorage _secureStorage = FlutterSecureStorage();
  static VoidCallback? onAuthenticationExpired;

  static final Dio dio = _createDio();

  static Dio _createDio() {
    final uri = Uri.parse(baseUrl);
    if (!kDebugMode && uri.scheme != 'https') {
      throw StateError('MuniResQ API requests require HTTPS in release builds.');
    }

    final client = Dio(
      BaseOptions(
        baseUrl: baseUrl,
        connectTimeout: const Duration(seconds: 15),
        receiveTimeout: const Duration(seconds: 15),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      ),
    );

    client.interceptors.add(
      InterceptorsWrapper(
        onError: (error, handler) async {
          if (error.response?.statusCode == 401 &&
              error.requestOptions.path != '/api/mobile/login') {
            await clearAuthToken();
            onAuthenticationExpired?.call();
          }
          handler.next(error);
        },
      ),
    );

    return client;
  }

  static Future<void> restoreAuthToken() async {
    dio.options.headers.remove('Authorization');
    final token = await _secureStorage.read(key: _tokenKey);
    if (token != null && token.isNotEmpty) {
      dio.options.headers['Authorization'] = 'Bearer $token';
    }
  }

  static Future<void> setAuthToken(String token) async {
    await _secureStorage.write(key: _tokenKey, value: token);
    dio.options.headers['Authorization'] = 'Bearer $token';
  }

  static Future<void> clearAuthToken() async {
    await _secureStorage.delete(key: _tokenKey);
    dio.options.headers.remove('Authorization');
  }
}
