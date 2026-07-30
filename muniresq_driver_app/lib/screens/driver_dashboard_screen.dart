import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/dispatch_provider.dart';
import '../theme/colors.dart';
import '../theme/typography.dart';
import '../widgets/custom_buttons.dart';
import '../widgets/custom_cards.dart';
import '../widgets/notification_tile.dart';
import '../widgets/emergency_action_card.dart';

class DriverDashboardScreen extends StatefulWidget {
  const DriverDashboardScreen({super.key});

  @override
  State<DriverDashboardScreen> createState() => _DriverDashboardScreenState();
}

class _DriverDashboardScreenState extends State<DriverDashboardScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<DispatchProvider>().loadCurrentDispatch();
    });
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<DispatchProvider>();
    final activeDispatch = provider.currentDispatch;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Driver Dashboard'),
        actions: [
          IconButton(onPressed: () {}, icon: const Icon(Icons.notifications_none, color: AppColors.textPrimary)),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Unit 07 • On Duty', style: AppTypography.headline3),
            const SizedBox(height: 10),
            Row(
              children: const [
                Chip(label: Text('Ready for dispatch'), backgroundColor: Color(0xFFE3F9F0)),
                SizedBox(width: 8),
                StatusChip(label: 'GPS Active', color: AppColors.success),
              ],
            ),
            const SizedBox(height: 24),
            AppCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Current Assignment', style: AppTypography.bodySmall.copyWith(color: AppColors.textSecondary)),
                  const SizedBox(height: 18),
                  if (activeDispatch != null) ...[
                    Text('Dispatch ${activeDispatch.id}', style: AppTypography.headline3),
                    const SizedBox(height: 12),
                    Text(activeDispatch.location, style: AppTypography.bodyMedium),
                    const SizedBox(height: 12),
                    Text('Priority • ${activeDispatch.priority}', style: AppTypography.bodyMedium.copyWith(fontWeight: FontWeight.w700)),
                  ] else ...[
                    Text('No active dispatches', style: AppTypography.headline3),
                    const SizedBox(height: 12),
                    Text('Your unit is ready for new assignments from the command center.', style: AppTypography.bodyMedium),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 24),
            AppCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Route Snapshot', style: AppTypography.bodySmall.copyWith(color: AppColors.textSecondary)),
                  const SizedBox(height: 16),
                  Container(
                    height: 180,
                    decoration: BoxDecoration(
                      color: AppColors.border,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Center(child: Icon(Icons.map, color: AppColors.textSecondary, size: 48)),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            Row(
              children: const [
                Expanded(child: MetricCard(title: 'Active dispatches', value: '0', icon: Icons.local_shipping, color: AppColors.primary)),
                SizedBox(width: 12),
                Expanded(child: MetricCard(title: 'Completed', value: '18', icon: Icons.check_circle_outline, color: AppColors.success)),
              ],
            ),
            const SizedBox(height: 16),
            Row(
              children: const [
                Expanded(child: MetricCard(title: 'Response time', value: '06:12', icon: Icons.timer, color: AppColors.secondary)),
                SizedBox(width: 12),
                Expanded(child: MetricCard(title: 'Status', value: 'Ready', icon: Icons.radio_button_checked, color: AppColors.success)),
              ],
            ),
            const SizedBox(height: 24),
            const Text('Quick Actions', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(child: PrimaryButton(label: 'Panic', onPressed: () {})),
                const SizedBox(width: 12),
                Expanded(child: DangerButton(label: 'Hijack', onPressed: () {})),
              ],
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(child: SecondaryButton(label: 'Report Incident', onPressed: () {})),
                const SizedBox(width: 12),
                Expanded(child: SecondaryButton(label: 'Navigation', onPressed: () {})),
              ],
            ),
            const SizedBox(height: 24),
            const Text('Recent Notifications', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
            const SizedBox(height: 16),
            const NotificationTile(
              title: 'Dispatch accepted',
              subtitle: 'Dispatch 00421 is now active.',
              time: '2m ago',
              isUnread: true,
              icon: Icons.check_circle,
            ),
            const NotificationTile(
              title: 'Maintenance reminder',
              subtitle: 'Unit 07 is due for vehicle inspection.',
              time: '1h ago',
              isUnread: false,
              icon: Icons.build,
            ),
            const SizedBox(height: 24),
            const Text('Emergency Actions', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
            const SizedBox(height: 16),
            GridView.count(
              physics: const NeverScrollableScrollPhysics(),
              shrinkWrap: true,
              crossAxisCount: 2,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
              childAspectRatio: 1.0,
              children: [
                EmergencyActionCard(
                  title: 'Panic Alert',
                  subtitle: 'Notify command center immediately.',
                  icon: Icons.warning_amber_rounded,
                  color: AppColors.danger,
                  onTap: () {},
                ),
                EmergencyActionCard(
                  title: 'Hijack Alert',
                  subtitle: 'Send emergency hijack notification.',
                  icon: Icons.lock,
                  color: AppColors.primary,
                  onTap: () {},
                ),
                EmergencyActionCard(
                  title: 'Incident Report',
                  subtitle: 'Submit event details to ops.',
                  icon: Icons.report,
                  color: AppColors.secondary,
                  onTap: () {},
                ),
                EmergencyActionCard(
                  title: 'Settings',
                  subtitle: 'Adjust your driver preferences.',
                  icon: Icons.settings,
                  color: AppColors.secondary,
                  onTap: () {},
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
