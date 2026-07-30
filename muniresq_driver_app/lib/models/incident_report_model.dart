class IncidentReport {
  final String dispatchId;
  final String description;
  final String incidentType;
  final String status;

  IncidentReport({
    required this.dispatchId,
    required this.description,
    required this.incidentType,
    required this.status,
  });

  Map<String, dynamic> toJson() {
    return {
      'dispatch_id': dispatchId,
      'description': description,
      'incident_type': incidentType,
      'status': status,
    };
  }
}
