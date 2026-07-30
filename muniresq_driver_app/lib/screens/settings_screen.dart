import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../theme/typography.dart';
import '../widgets/custom_cards.dart';

class SettingsScreen extends StatelessWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Settings'),
        backgroundColor: AppColors.surface,
        elevation: 0,
      ),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('System preferences', style: AppTypography.headline2),
            const SizedBox(height: 10),
            Text('Adjust app behavior for alerts, navigation, and communications.', style: AppTypography.bodyMedium),
            const SizedBox(height: 24),
            AppCard(
              child: Column(
                children: const [
                  ListTile(
                    leading: Icon(Icons.notifications, color: AppColors.primary),
                    title: Text('Notifications'),
                    trailing: Icon(Icons.chevron_right, color: AppColors.textSecondary),
                  ),
                  Divider(color: AppColors.border),
                  ListTile(
                    leading: Icon(Icons.navigation, color: AppColors.secondary),
                    title: Text('Navigation settings'),
                    trailing: Icon(Icons.chevron_right, color: AppColors.textSecondary),
                  ),
                  Divider(color: AppColors.border),
                  ListTile(
                    leading: Icon(Icons.shield, color: AppColors.success),
                    title: Text('Safety protocols'),
                    trailing: Icon(Icons.chevron_right, color: AppColors.textSecondary),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            AppCard(
              child: Column(
                children: const [
                  ListTile(
                    leading: Icon(Icons.lock, color: AppColors.danger),
                    title: Text('Security settings'),
                    trailing: Icon(Icons.chevron_right, color: AppColors.textSecondary),
                  ),
                  Divider(color: AppColors.border),
                  ListTile(
                    leading: Icon(Icons.headset_mic, color: AppColors.primary),
                    title: Text('Support contacts'),
                    trailing: Icon(Icons.chevron_right, color: AppColors.textSecondary),
                  ),
                ],
              ),
            ),
            const Spacer(),
            Align(
              alignment: Alignment.center,
              child: Text('Version 1.0.0 • MuniResQ Driver App', style: AppTypography.bodySmall),
            ),
          ],
        ),
      ),
    );
  }
}
