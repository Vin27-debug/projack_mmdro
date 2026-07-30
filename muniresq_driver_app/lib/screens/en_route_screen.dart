import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../theme/typography.dart';
import '../widgets/custom_buttons.dart';
import '../widgets/custom_cards.dart';

class EnRouteScreen extends StatelessWidget {
  const EnRouteScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('En Route'),
        backgroundColor: AppColors.surface,
        elevation: 0,
      ),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            AppCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Heading to the scene', style: AppTypography.bodySmall.copyWith(color: AppColors.textSecondary)),
                  const SizedBox(height: 12),
                  Text('Estimated arrival in 3 minutes.', style: AppTypography.headline3),
                  const SizedBox(height: 18),
                  Row(
                    children: const [
                      Expanded(child: MetricCard(title: 'Distance left', value: '1.2 km', icon: Icons.location_pin, color: AppColors.primary)),
                      SizedBox(width: 12),
                      Expanded(child: MetricCard(title: 'Traffic', value: 'Light', icon: Icons.traffic, color: AppColors.secondary)),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            Expanded(
              child: AppCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Scene readiness', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                    const SizedBox(height: 16),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: const [
                        Icon(Icons.shield, color: AppColors.secondary),
                        Text('Confirm scene safety and patient prep.', style: AppTypography.bodyMedium),
                      ],
                    ),
                    const SizedBox(height: 18),
                    const Text('Checklist', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.textSecondary)),
                    const SizedBox(height: 12),
                    const ListTile(
                      contentPadding: EdgeInsets.zero,
                      leading: Icon(Icons.check_circle_outline, color: AppColors.primary),
                      title: Text('Radio contact established'),
                    ),
                    const ListTile(
                      contentPadding: EdgeInsets.zero,
                      leading: Icon(Icons.check_circle_outline, color: AppColors.primary),
                      title: Text('Patient report received'),
                    ),
                    const ListTile(
                      contentPadding: EdgeInsets.zero,
                      leading: Icon(Icons.check_circle_outline, color: AppColors.primary),
                      title: Text('Route and traffic are clear'),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
            PrimaryButton(label: 'Mark Arrived', onPressed: () {}),
          ],
        ),
      ),
    );
  }
}
