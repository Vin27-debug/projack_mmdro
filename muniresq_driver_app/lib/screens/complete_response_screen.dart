import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../theme/typography.dart';
import '../widgets/custom_buttons.dart';
import '../widgets/custom_cards.dart';

class CompleteResponseScreen extends StatelessWidget {
  const CompleteResponseScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Complete Response'),
        backgroundColor: AppColors.surface,
        elevation: 0,
      ),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Response complete', style: AppTypography.headline2),
            const SizedBox(height: 10),
            Text('Finalize your report and return unit readiness status to operations.', style: AppTypography.bodyMedium),
            const SizedBox(height: 24),
            AppCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Incident summary', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                  const SizedBox(height: 16),
                  Text('Patient transferred. Unit clearing the hospital and returning to standby.', style: AppTypography.bodyMedium),
                  const SizedBox(height: 16),
                  const Divider(height: 1, color: AppColors.border),
                  const SizedBox(height: 16),
                  const Text('Total time on mission: 42 min', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.textSecondary)),
                  const SizedBox(height: 8),
                  const Text('Mileage: 18.4 km', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.textSecondary)),
                ],
              ),
            ),
            const SizedBox(height: 24),
            AppCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Action required', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                  const SizedBox(height: 16),
                  const ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: Icon(Icons.assignment, color: AppColors.primary),
                    title: Text('Submit incident report and status update.'),
                  ),
                  const ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: Icon(Icons.local_gas_station, color: AppColors.secondary),
                    title: Text('Confirm refueling or maintenance needs.'),
                  ),
                ],
              ),
            ),
            const Spacer(),
            PrimaryButton(label: 'Submit Final Report', onPressed: () {}),
            const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }
}
