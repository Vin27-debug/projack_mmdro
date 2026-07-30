import 'package:flutter/material.dart';
import '../repositories/api_repository.dart';

class LocationProvider extends ChangeNotifier {
  final ApiRepository repository;

  LocationProvider({required this.repository});

  Future<void> updateLocation(double latitude, double longitude) async {
    await repository.submitLocation(latitude, longitude);
  }
}
