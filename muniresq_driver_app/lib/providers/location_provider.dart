import 'dart:async';

import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:geolocator/geolocator.dart';
import '../repositories/api_repository.dart';

class LocationProvider extends ChangeNotifier {
  final ApiRepository repository;
  StreamSubscription<Position>? _positionSubscription;
  DateTime? _lastSentAt;
  bool _sending = false;

  LocationProvider({required this.repository});

  String statusMessage = 'GPS tracking is not active.';
  bool isTracking = false;
  bool permissionPermanentlyDenied = false;

  Future<void> startTracking() async {
    if (_positionSubscription != null) return;

    _setStatus('Checking location permission...');
    try {
      permissionPermanentlyDenied = false;
      if (!await Geolocator.isLocationServiceEnabled()) {
        _setStatus('Turn on device location services to share your position.');
        return;
      }

      var permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }
      if (permission == LocationPermission.deniedForever) {
        permissionPermanentlyDenied = true;
        _setStatus('Location permission is blocked. Enable it in Android app settings.');
        return;
      }
      if (permission != LocationPermission.whileInUse &&
          permission != LocationPermission.always) {
        _setStatus('Location permission was denied. Allow location to share your position.');
        return;
      }

      _positionSubscription = Geolocator.getPositionStream(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
          distanceFilter: 0,
        ),
      ).listen(
        _sendPosition,
        onError: (Object _) {
          isTracking = false;
          _setStatus('Unable to read device location. Check location settings.');
          unawaited(stopTracking());
        },
      );
      isTracking = true;
      _setStatus('Waiting for a device GPS fix...');
    } on PlatformException {
      isTracking = false;
      _setStatus('Location service is unavailable on this device.');
    }
  }

  Future<bool> openAppSettings() {
    return Geolocator.openAppSettings();
  }

  Future<void> stopTracking() async {
    isTracking = false;
    final subscription = _positionSubscription;
    _positionSubscription = null;
    await subscription?.cancel();
    notifyListeners();
  }

  Future<void> _sendPosition(Position position) async {
    final now = DateTime.now();
    if (_sending ||
        (_lastSentAt != null &&
            now.difference(_lastSentAt!) < const Duration(seconds: 15))) {
      return;
    }

    _sending = true;
    _lastSentAt = now;
    try {
      await repository.submitLocation(
        latitude: position.latitude,
        longitude: position.longitude,
        accuracy: position.accuracy,
        recordedAt: position.timestamp,
      );
      _setStatus('GPS location shared with command.');
    } on DioException catch (error) {
      switch (error.response?.statusCode) {
        case 401:
          _setStatus('Your session expired. Sign in again to resume GPS sharing.');
          await stopTracking();
          break;
        case 403:
          _setStatus('This driver account is no longer authorized to share GPS.');
          await stopTracking();
          break;
        default:
          _setStatus('GPS update failed. Check your network; updates will retry.');
          break;
      }
    } finally {
      _sending = false;
    }
  }

  void _setStatus(String value) {
    statusMessage = value;
    notifyListeners();
  }

  @override
  void dispose() {
    unawaited(_positionSubscription?.cancel());
    super.dispose();
  }
}
