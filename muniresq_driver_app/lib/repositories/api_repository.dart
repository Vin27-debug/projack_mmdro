import 'package:dio/dio.dart';
import '../models/auth_response.dart';
import '../models/driver_profile.dart';
import '../models/dispatch_model.dart';
import '../models/incident_report_model.dart';
import '../models/notification_model.dart';
import '../services/api_service.dart';

class ApiRepository {
  final Dio _dio = ApiService.dio;

  Future<AuthResponse> login(String driverId, String pin) async {
    final response = await _dio.post('/api/login', data: {
      'driver_id': driverId,
      'pin': pin,
    });
    return AuthResponse.fromJson(response.data as Map<String, dynamic>);
  }

  Future<void> logout() async {
    await _dio.post('/api/logout');
  }

  Future<DriverProfile> fetchDriverProfile() async {
    final response = await _dio.get('/api/driver/profile');
    return DriverProfile.fromJson(response.data as Map<String, dynamic>);
  }

  Future<void> submitLocation(double latitude, double longitude) async {
    await _dio.post('/api/driver/location', data: {
      'latitude': latitude,
      'longitude': longitude,
    });
  }

  Future<List<DispatchModel>> fetchDispatchList() async {
    final response = await _dio.get('/api/dispatch/list');
    final items = response.data as List<dynamic>;
    return items.map((json) => DispatchModel.fromJson(json as Map<String, dynamic>)).toList();
  }

  Future<DispatchModel> fetchCurrentDispatch() async {
    final response = await _dio.get('/api/dispatch/current');
    return DispatchModel.fromJson(response.data as Map<String, dynamic>);
  }

  Future<void> acceptDispatch(String dispatchId) async {
    await _dio.post('/api/dispatch/accept', data: {'dispatch_id': dispatchId});
  }

  Future<void> markEnRoute(String dispatchId) async {
    await _dio.post('/api/dispatch/enroute', data: {'dispatch_id': dispatchId});
  }

  Future<void> markArrived(String dispatchId) async {
    await _dio.post('/api/dispatch/arrived', data: {'dispatch_id': dispatchId});
  }

  Future<void> completeDispatch(String dispatchId) async {
    await _dio.post('/api/dispatch/completed', data: {'dispatch_id': dispatchId});
  }

  Future<void> sendPanicAlert() async {
    await _dio.post('/api/panic/send');
  }

  Future<void> sendHijackAlert() async {
    await _dio.post('/api/hijack/send');
  }

  Future<void> submitIncidentReport(IncidentReport report) async {
    await _dio.post('/api/report/store', data: report.toJson());
  }

  Future<List<NotificationModel>> fetchNotifications() async {
    final response = await _dio.get('/api/notifications');
    final items = response.data as List<dynamic>;
    return items.map((json) => NotificationModel.fromJson(json as Map<String, dynamic>)).toList();
  }
}
