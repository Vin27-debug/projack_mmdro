import 'package:flutter/material.dart';
import '../models/dispatch_model.dart';
import '../repositories/api_repository.dart';

class DispatchProvider extends ChangeNotifier {
  final ApiRepository repository;

  List<DispatchModel> dispatchList = [];
  DispatchModel? currentDispatch;

  DispatchProvider({required this.repository});

  Future<void> loadDispatchList() async {
    dispatchList = await repository.fetchDispatchList();
    notifyListeners();
  }

  Future<void> loadCurrentDispatch() async {
    currentDispatch = await repository.fetchCurrentDispatch();
    notifyListeners();
  }

  Future<void> acceptDispatch(String dispatchId) async {
    await repository.acceptDispatch(dispatchId);
    await loadCurrentDispatch();
  }

  Future<void> markEnRoute(String dispatchId) async {
    await repository.markEnRoute(dispatchId);
    await loadCurrentDispatch();
  }

  Future<void> markArrived(String dispatchId) async {
    await repository.markArrived(dispatchId);
    await loadCurrentDispatch();
  }

  Future<void> completeDispatch(String dispatchId) async {
    await repository.completeDispatch(dispatchId);
    await loadCurrentDispatch();
  }
}
