import 'dart:async';

import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../core/app_navigator.dart';
import '../core/routes.dart';
import '../models/auth_response.dart';
import '../repositories/api_repository.dart';
import '../services/api_service.dart';
import 'location_provider.dart';

class AuthProvider extends ChangeNotifier {
  final ApiRepository repository;
  final LocationProvider locationProvider;

  bool isAuthenticated = false;
  String token = '';
  String? errorMessage;

  AuthProvider({required this.repository, required this.locationProvider}) {
    ApiService.onAuthenticationExpired = _handleExpiredToken;
  }

  Future<void> restoreSession() async {
    await ApiService.restoreAuthToken();
    isAuthenticated =
        ApiService.dio.options.headers['Authorization'] is String;
    notifyListeners();
  }

  Future<bool> login(String driverId, String password) async {
    errorMessage = null;
    try {
      final authResponse = await repository.login(driverId, password);
      token = authResponse.accessToken;
      await ApiService.setAuthToken(token);
      isAuthenticated = true;
      notifyListeners();
      return true;
    } on DioException catch (error) {
      errorMessage = switch (error.response?.statusCode) {
        422 || 429 => 'Check your Driver ID and password, then try again.',
        403 => 'This driver account is not active or authorized.',
        _ => 'Unable to connect to MuniResQ. Check your network and try again.',
      };
      notifyListeners();
      return false;
    } on FormatException {
      errorMessage = 'The server returned an invalid sign-in response.';
      notifyListeners();
      return false;
    } on PlatformException {
      errorMessage = 'Secure sign-in storage is unavailable on this device.';
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    await locationProvider.stopTracking();
    try {
      await repository.logout();
    } finally {
      token = '';
      isAuthenticated = false;
      await ApiService.clearAuthToken();
      notifyListeners();
      appNavigatorKey.currentState?.pushNamedAndRemoveUntil(
        AppRoutes.login,
        (route) => false,
      );
    }
  }

  void _handleExpiredToken() {
    unawaited(locationProvider.stopTracking());
    token = '';
    isAuthenticated = false;
    notifyListeners();
    appNavigatorKey.currentState?.pushNamedAndRemoveUntil(
      AppRoutes.login,
      (route) => false,
    );
  }
}
