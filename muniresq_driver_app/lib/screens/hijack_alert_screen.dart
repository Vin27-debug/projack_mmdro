import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../theme/typography.dart';
import '../widgets/custom_buttons.dart';

class HijackAlertScreen extends StatelessWidget {
  const HijackAlertScreen({super.key});

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
              const SizedBox(height: 40),
              Text('Hijack Alert', style: AppTypography.headline2),
              const SizedBox(height: 14),
              Text('Your vehicle is in a hijack event. Evade if safe and await support instructions.', style: AppTypography.bodyMedium),
              const SizedBox(height: 24),
              AppCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: const [
                    ListTile(
                      leading: Icon(Icons.lock, color: AppColors.primary),
                      title: Text('Protect crew and patient'),
                      subtitle: Text('Keep doors locked and remain calm.'),
                    ),
                    Divider(color: AppColors.border),
                    ListTile(
                      leading: Icon(Icons.location_on, color: AppColors.secondary),
                      title: Text('Location sent to command'),
                      subtitle: Text('Live tracking enabled.'),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),
              Row(
                children: const [
                  Expanded(child: MetricCard(title: 'Support ETA', value: '5 min', icon: Icons.timer, color: AppColors.primary)),
                  SizedBox(width: 12),
                  Expanded(child: MetricCard(title: 'Secure status', value: 'Pending', icon: Icons.security, color: AppColors.danger)),
                ],
              ),
              const Spacer(),
              PrimaryButton(label: 'Maintain communication', onPressed: () {}),
              const SizedBox(height: 16),
              DangerButton(label: 'Force alert cancellation', onPressed: () {}),
            ],
          ),
        ),
      ),
    );
  }
}
