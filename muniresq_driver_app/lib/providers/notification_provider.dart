import 'package:flutter/material.dart';
import '../models/notification_model.dart';
import '../repositories/api_repository.dart';

class NotificationProvider extends ChangeNotifier {
  final ApiRepository repository;

  List<NotificationModel> notifications = [];

  NotificationProvider({required this.repository});

  Future<void> loadNotifications() async {
    notifications = await repository.fetchNotifications();
    notifyListeners();
  }
}
