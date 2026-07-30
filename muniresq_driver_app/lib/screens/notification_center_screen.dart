import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../theme/typography.dart';
import '../widgets/notification_tile.dart';

class NotificationCenterScreen extends StatelessWidget {
  const NotificationCenterScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Notification Center'),
        backgroundColor: AppColors.surface,
        elevation: 0,
      ),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Notifications', style: AppTypography.headline2),
            const SizedBox(height: 12),
            Text('Review recent alerts and operational updates.', style: AppTypography.bodyMedium),
            const SizedBox(height: 24),
            Expanded(
              child: ListView(
                children: const [
                  NotificationTile(
                    title: 'New dispatch assigned',
                    subtitle: 'Dispatch 00422 requires immediate response.',
                    time: 'Now',
                    isUnread: true,
                    icon: Icons.flash_on,
                  ),
                  NotificationTile(
                    title: 'Command update',
                    subtitle: 'Traffic clearance has been approved on your route.',
                    time: '15m ago',
                    isUnread: false,
                    icon: Icons.info,
                  ),
                  NotificationTile(
                    title: 'Crew reminder',
                    subtitle: 'Secure medical supplies before departure.',
                    time: '1h ago',
                    isUnread: false,
                    icon: Icons.medical_services,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
