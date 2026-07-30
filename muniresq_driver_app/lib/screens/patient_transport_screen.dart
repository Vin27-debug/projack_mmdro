import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../theme/typography.dart';
import '../widgets/custom_buttons.dart';
import '../widgets/custom_cards.dart';

class PatientTransportScreen extends StatelessWidget {
  const PatientTransportScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Patient Transport'),
        backgroundColor: AppColors.surface,
        elevation: 0,
      ),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Transport mode', style: AppTypography.headline2),
            const SizedBox(height: 10),
            Text('Secure patient and monitor vital signs en route to the receiving hospital.', style: AppTypography.bodyMedium),
            const SizedBox(height: 24),
            AppCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Status summary', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                  const SizedBox(height: 16),
                  const ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: Icon(Icons.monitor_heart, color: AppColors.danger),
                    title: Text('Vitals stable with support'),
                  ),
                  const ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: Icon(Icons.local_hospital, color: AppColors.secondary),
                    title: Text('Hospital ETA 09 min'),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            Row(
              children: const [
                Expanded(child: MetricCard(title: 'Current speed', value: '58 km/h', icon: Icons.speed, color: AppColors.primary)),
                SizedBox(width: 12),
                Expanded(child: MetricCard(title: 'Traffic', value: 'Moderate', icon: Icons.traffic, color: AppColors.secondary)),
              ],
            ),
            const SizedBox(height: 24),
            const Text('Secure transport checklist', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
            const SizedBox(height: 12),
            AppCard(
              child: Column(
                children: const [
                  ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: Icon(Icons.check_circle_outline, color: AppColors.success),
                    title: Text('Patient safely secured'),
                  ),
                  ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: Icon(Icons.check_circle_outline, color: AppColors.success),
                    title: Text('Airway and oxygen checked'),
                  ),
                  ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: Icon(Icons.check_circle_outline, color: AppColors.success),
                    title: Text('Communications confirmed'),
                  ),
                ],
              ),
            ),
            const Spacer(),
            PrimaryButton(label: 'Update Hospital ETA', onPressed: () {}),
            const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }
}
