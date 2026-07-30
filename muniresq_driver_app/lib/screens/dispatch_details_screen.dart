import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../theme/typography.dart';
import '../widgets/custom_buttons.dart';
import '../widgets/custom_cards.dart';

class DispatchDetailsScreen extends StatelessWidget {
  const DispatchDetailsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Dispatch Details'),
        backgroundColor: AppColors.surface,
        elevation: 0,
      ),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Dispatch #00421', style: AppTypography.headline2),
            const SizedBox(height: 10),
            Text('Assigned unit: Ambulance 07 · Driver: Reyes', style: AppTypography.bodyMedium),
            const SizedBox(height: 24),
            AppCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Incident overview', style: AppTypography.bodySmall.copyWith(color: AppColors.textSecondary)),
                  const SizedBox(height: 14),
                  Text('Cardiac emergency at residential compound. Patient is conscious but unstable.', style: AppTypography.bodyMedium),
                  const SizedBox(height: 16),
                  Row(
                    children: const [
                      Expanded(child: MetricCard(title: 'Priority', value: 'A1', icon: Icons.priority_high, color: AppColors.danger)),
                      SizedBox(width: 12),
                      Expanded(child: MetricCard(title: 'Stage', value: 'Responding', icon: Icons.directions_car, color: AppColors.secondary)),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            const Text('Route Details', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
            const SizedBox(height: 14),
            AppCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Destination', style: TextStyle(fontWeight: FontWeight.w600, color: AppColors.textSecondary)),
                  const SizedBox(height: 8),
                  Text('General City Hospital - Emergency bay 3', style: AppTypography.bodyLarge),
                  const SizedBox(height: 16),
                  const Text('Next checkpoint', style: TextStyle(fontWeight: FontWeight.w600, color: AppColors.textSecondary)),
                  const SizedBox(height: 8),
                  Text('Turn right on Roosevelt Avenue and proceed straight.', style: AppTypography.bodyMedium),
                ],
              ),
            ),
            const Spacer(),
            PrimaryButton(label: 'Start Navigation', onPressed: () {}),
            const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }
}
