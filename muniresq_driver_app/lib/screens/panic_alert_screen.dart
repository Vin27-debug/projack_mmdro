import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../theme/typography.dart';
import '../widgets/custom_buttons.dart';

class PanicAlertScreen extends StatelessWidget {
  const PanicAlertScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              const SizedBox(height: 40),
              Container(
                decoration: BoxDecoration(color: AppColors.danger, borderRadius: BorderRadius.circular(24)),
                padding: const EdgeInsets.symmetric(vertical: 28, horizontal: 20),
                child: Column(
                  children: [
                    const Icon(Icons.warning_amber, size: 72, color: Colors.white),
                    const SizedBox(height: 20),
                    Text('Panic Alert Activated', style: AppTypography.headline3.copyWith(color: Colors.white)),
                    const SizedBox(height: 12),
                    Text('All units and command center have been notified. Immediate response activated.', style: AppTypography.bodyMedium.copyWith(color: Colors.white70), textAlign: TextAlign.center),
                  ],
                ),
              ),
              const SizedBox(height: 30),
              Text('Emergency communication will remain open until command confirms unit safety.', style: AppTypography.bodyMedium, textAlign: TextAlign.center),
              const SizedBox(height: 24),
              AppCard(
                child: Column(
                  children: const [
                    ListTile(
                      leading: Icon(Icons.signal_cellular_alt, color: AppColors.textPrimary),
                      title: Text('Signal status'),
                      subtitle: Text('Strong'),
                    ),
                    Divider(color: AppColors.border),
                    ListTile(
                      leading: Icon(Icons.timer, color: AppColors.textPrimary),
                      title: Text('Dispatch support ETA'),
                      subtitle: Text('3 min'),
                    ),
                  ],
                ),
              ),
              const Spacer(),
              PrimaryButton(label: 'Confirm safe', onPressed: () {}),
              const SizedBox(height: 16),
              SecondaryButton(label: 'Cancel alert', onPressed: () {}),
            ],
          ),
        ),
      ),
    );
  }
}
