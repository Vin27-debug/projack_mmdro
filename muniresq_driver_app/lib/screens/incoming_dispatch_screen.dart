import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../theme/typography.dart';
import '../widgets/custom_buttons.dart';
import '../widgets/custom_cards.dart';

class IncomingDispatchScreen extends StatelessWidget {
  const IncomingDispatchScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 16),
              Text('Incoming Dispatch', style: AppTypography.headline2),
              const SizedBox(height: 10),
              Text('Respond immediately to the assigned incident. Confirm your availability within the next 12 seconds.', style: AppTypography.bodyMedium),
              const SizedBox(height: 24),
              AppCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: const [
                        Text('Priority: Critical', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.danger)),
                        StatusChip(label: 'High Alert', color: AppColors.danger),
                      ],
                    ),
                    const SizedBox(height: 18),
                    Text('Scene location', style: AppTypography.bodySmall.copyWith(color: AppColors.textSecondary)),
                    const SizedBox(height: 8),
                    Text('Brgy. San Miguel Health Center, Route 12', style: AppTypography.headline3),
                    const SizedBox(height: 16),
                    Row(
                      children: const [
                        Expanded(child: MetricCard(title: 'ETA', value: '04 min', icon: Icons.access_time, color: AppColors.primary)),
                        SizedBox(width: 12),
                        Expanded(child: MetricCard(title: 'Distance', value: '2.8 km', icon: Icons.location_on_outlined, color: AppColors.secondary)),
                      ],
                    ),
                    const SizedBox(height: 16),
                    Text('Patient condition', style: AppTypography.bodySmall.copyWith(color: AppColors.textSecondary)),
                    const SizedBox(height: 8),
                    Text('Respiratory distress with reduced consciousness. Ensure airway support en route.', style: AppTypography.bodyMedium),
                  ],
                ),
              ),
              const Spacer(),
              Text('Tap to accept dispatch and begin navigation.', style: AppTypography.bodySmall),
              const SizedBox(height: 16),
              PrimaryButton(label: 'Accept Dispatch', onPressed: () {}),
              const SizedBox(height: 12),
              OutlinedButton(
                onPressed: () {},
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppColors.textPrimary,
                  side: const BorderSide(color: AppColors.border),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: const Text('Decline Dispatch'),
              ),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }
}
