class NotificationModel {
  final String id;
  final String title;
  final String body;
  final String timestamp;
  final bool isUnread;

  NotificationModel({
    required this.id,
    required this.title,
    required this.body,
    required this.timestamp,
    required this.isUnread,
  });

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    return NotificationModel(
      id: json['id']?.toString() ?? '',
      title: json['title'] as String? ?? '',
      body: json['body'] as String? ?? '',
      timestamp: json['created_at'] as String? ?? json['timestamp'] as String? ?? '',
      isUnread: json['is_read'] == null ? true : !(json['is_read'] as bool),
    );
  }
}
