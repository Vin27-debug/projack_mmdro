import 'package:flutter/material.dart';
import '../models/auth_response.dart';
import '../repositories/api_repository.dart';
import '../services/api_service.dart';

class AuthProvider extends ChangeNotifier {
  final ApiRepository repository;

  bool isAuthenticated = false;
  String token = '';

  AuthProvider({required this.repository});

  Future<bool> login(String driverId, String pin) async {
    final authResponse = await repository.login(driverId, pin);
    token = authResponse.accessToken;
    ApiService.setAuthToken(token);
    isAuthenticated = true;
    notifyListeners();
    return isAuthenticated;
  }

  Future<void> logout() async {
    await repository.logout();
    token = '';
    isAuthenticated = false;
    ApiService.clearAuthToken();
    notifyListeners();
  }
}
