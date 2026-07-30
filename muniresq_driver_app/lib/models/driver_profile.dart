class DriverProfile {
  final String id;
  final String name;
  final String unit;
  final String rank;
  final String email;
  final String phone;
  final String certificationStatus;
  final int yearsActive;
  final int completedMissions;

  DriverProfile({
    required this.id,
    required this.name,
    required this.unit,
    required this.rank,
    required this.email,
    required this.phone,
    required this.certificationStatus,
    required this.yearsActive,
    required this.completedMissions,
  });

  factory DriverProfile.fromJson(Map<String, dynamic> json) {
    return DriverProfile(
      id: json['id']?.toString() ?? '',
      name: json['name'] as String? ?? '',
      unit: json['unit'] as String? ?? '',
      rank: json['rank'] as String? ?? '',
      email: json['email'] as String? ?? '',
      phone: json['phone'] as String? ?? '',
      certificationStatus: json['certification_status'] as String? ?? 'Active',
      yearsActive: json['years_active'] as int? ?? 0,
      completedMissions: json['completed_missions'] as int? ?? 0,
    );
  }
}
