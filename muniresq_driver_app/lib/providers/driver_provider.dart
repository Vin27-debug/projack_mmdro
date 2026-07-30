import 'package:flutter/material.dart';
import '../models/dispatch_model.dart';
import '../repositories/dispatch_repository.dart';

class DriverProvider extends ChangeNotifier {
  final DispatchRepository repository;

  bool isSignedIn = false;
  bool isApproved = false;
  List<DispatchModel> activeDispatches = [];

  DriverProvider({required this.repository});

  Future<bool> signIn(String driverId, String pin) async {
    await Future.delayed(const Duration(milliseconds: 500));
    isSignedIn = true;
    isApproved = false;
    notifyListeners();
    return isSignedIn;
  }

  Future<void> requestApproval() async {
    await Future.delayed(const Duration(seconds: 1));
    isApproved = true;
    notifyListeners();
  }

  Future<void> loadActiveDispatches() async {
    activeDispatches = await repository.fetchActiveDispatches();
    notifyListeners();
  }
}
