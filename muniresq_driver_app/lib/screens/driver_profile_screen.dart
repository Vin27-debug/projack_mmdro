import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../theme/typography.dart';
import '../widgets/custom_buttons.dart';
import '../widgets/custom_cards.dart';

class DriverProfileScreen extends StatelessWidget {
  const DriverProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Driver Profile'),
        backgroundColor: AppColors.surface,
        elevation: 0,
      ),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 82,
                  height: 82,
                  decoration: BoxDecoration(color: AppColors.border, borderRadius: BorderRadius.circular(24)),
                  child: const Icon(Icons.person, size: 48, color: AppColors.textSecondary),
                ),
                const SizedBox(width: 16),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Driver Reyes', style: AppTypography.headline3),
                    const SizedBox(height: 4),
                    Text('Ambulance 07 • EMT-II', style: AppTypography.bodyMedium),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 24),
            AppCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: const [
                  ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: Icon(Icons.email, color: AppColors.primary),
                    title: Text('reyes.driver@muniresq.gov.ph'),
                  ),
                  ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: Icon(Icons.phone, color: AppColors.secondary),
                    title: Text('+63 917 123 4567'),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            Row(
              children: const [
                Expanded(child: MetricCard(title: 'Years active', value: '4', icon: Icons.calendar_today, color: AppColors.primary)),
                SizedBox(width: 12),
                Expanded(child: MetricCard(title: 'Missions', value: '198', icon: Icons.local_hospital, color: AppColors.success)),
              ],
            ),
            const SizedBox(height: 24),
            AppCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Certification status', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                  const SizedBox(height: 12),
                  const StatusChip(label: 'Active', color: AppColors.success),
                  const SizedBox(height: 16),
                  Text('Last training: May 2026', style: AppTypography.bodyMedium),
                ],
              ),
            ),
            const Spacer(),
            PrimaryButton(label: 'Edit profile', onPressed: () {}),
            const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }
}
