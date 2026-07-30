import '../models/dispatch_model.dart';

abstract class DispatchRepository {
  Future<List<DispatchModel>> fetchActiveDispatches();
}

class MockDispatchRepository implements DispatchRepository {
  @override
  Future<List<DispatchModel>> fetchActiveDispatches() async {
    await Future.delayed(const Duration(milliseconds: 400));
    return [
      DispatchModel(
        id: '00421',
        location: 'Brgy. San Miguel Health Center',
        status: 'Responding',
        eta: '04 min',
        priority: 'Critical',
      ),
    ];
  }
}
