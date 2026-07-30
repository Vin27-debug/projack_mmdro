import 'package:flutter/material.dart';
import '../models/driver_profile.dart';
import '../repositories/api_repository.dart';

class DriverProfileProvider extends ChangeNotifier {
  final ApiRepository repository;

  DriverProfile? profile;

  DriverProfileProvider({required this.repository});

  Future<void> loadProfile() async {
    profile = await repository.fetchDriverProfile();
    notifyListeners();
  }
}
