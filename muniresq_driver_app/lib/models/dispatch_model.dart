class DispatchModel {
  final String id;
  final String location;
  final String status;
  final String eta;
  final String priority;

  DispatchModel({
    required this.id,
    required this.location,
    required this.status,
    required this.eta,
    required this.priority,
  });

  factory DispatchModel.fromJson(Map<String, dynamic> json) {
    return DispatchModel(
      id: json['id']?.toString() ?? json['dispatch_id']?.toString() ?? '',
      location: json['location'] as String? ?? json['scene_location'] as String? ?? '',
      status: json['status'] as String? ?? 'pending',
      eta: json['eta'] as String? ?? 'N/A',
      priority: json['priority'] as String? ?? 'Normal',
    );
  }
}
