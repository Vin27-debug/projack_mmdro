import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../theme/typography.dart';
import '../widgets/custom_buttons.dart';
import '../widgets/emergency_action_card.dart';

class EmergencyActionsScreen extends StatelessWidget {
  const EmergencyActionsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Emergency Actions'),
        backgroundColor: AppColors.surface,
        elevation: 0,
      ),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Emergency response tools', style: AppTypography.headline2),
            const SizedBox(height: 10),
            Text('Activate emergency protocols instantly for critical situations.', style: AppTypography.bodyMedium),
            const SizedBox(height: 24),
            GridView.count(
              crossAxisCount: 1,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              childAspectRatio: 5,
              children: [
                EmergencyActionCard(
                  title: 'Panic Alert',
                  subtitle: 'Send full emergency notification.',
                  icon: Icons.warning_amber,
                  color: AppColors.danger,
                  onTap: () {},
                ),
                EmergencyActionCard(
                  title: 'Hijack Alert',
                  subtitle: 'Trigger hijack response workflow.',
                  icon: Icons.lock,
                  color: AppColors.primary,
                  onTap: () {},
                ),
                EmergencyActionCard(
                  title: 'Incident Report',
                  subtitle: 'Submit an incident summary.',
                  icon: Icons.report,
                  color: AppColors.secondary,
                  onTap: () {},
                ),
                EmergencyActionCard(
                  title: 'Medical Support',
                  subtitle: 'Request remote medical backup.',
                  icon: Icons.medical_services,
                  color: AppColors.success,
                  onTap: () {},
                ),
              ],
            ),
            const Spacer(),
            SecondaryButton(label: 'Return to dashboard', onPressed: () {}),
          ],
        ),
      ),
    );
  }
}
