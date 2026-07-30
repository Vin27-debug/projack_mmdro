import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../theme/typography.dart';
import '../widgets/custom_buttons.dart';
import '../widgets/custom_cards.dart';

class HospitalArrivalScreen extends StatelessWidget {
  const HospitalArrivalScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Hospital Arrival'),
        backgroundColor: AppColors.surface,
        elevation: 0,
      ),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Arrival at receiving facility', style: AppTypography.headline2),
            const SizedBox(height: 10),
            Text('Confirm handover and patient transfer status with hospital personnel.', style: AppTypography.bodyMedium),
            const SizedBox(height: 24),
            AppCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Receiving department', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                  const SizedBox(height: 16),
                  Text('General City Hospital - Emergency Bay 3', style: AppTypography.headline3),
                  const SizedBox(height: 12),
                  const Text('Report to medical team and update patient status.', style: AppTypography.bodyMedium),
                ],
              ),
            ),
            const SizedBox(height: 24),
            Row(
              children: const [
                Expanded(child: MetricCard(title: 'Handoff time', value: 'Now', icon: Icons.access_time, color: AppColors.secondary)),
                SizedBox(width: 12),
                Expanded(child: MetricCard(title: 'Handoff type', value: 'Priority', icon: Icons.priority_high, color: AppColors.danger)),
              ],
            ),
            const SizedBox(height: 24),
            const Text('Hospital instructions', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
            const SizedBox(height: 12),
            AppCard(
              child: Column(
                children: const [
                  ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: Icon(Icons.note, color: AppColors.primary),
                    title: Text('Transfer patient to bay and begin monitoring.'),
                  ),
                  ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: Icon(Icons.call, color: AppColors.secondary),
                    title: Text('Confirm receiving physician details.'),
                  ),
                ],
              ),
            ),
            const Spacer(),
            PrimaryButton(label: 'Complete Handover', onPressed: () {}),
            const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }
}
