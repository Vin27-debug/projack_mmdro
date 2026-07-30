import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../theme/typography.dart';
import '../widgets/custom_cards.dart';

class DispatchHistoryScreen extends StatelessWidget {
  const DispatchHistoryScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Dispatch History'),
        backgroundColor: AppColors.surface,
        elevation: 0,
      ),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Recent missions', style: AppTypography.headline2),
            const SizedBox(height: 12),
            Text('Review completed dispatch records and performance logs.', style: AppTypography.bodyMedium),
            const SizedBox(height: 24),
            Expanded(
              child: ListView(
                children: [
                  AppCard(
                    child: ListTile(
                      contentPadding: EdgeInsets.zero,
                      title: const Text('Dispatch 00420'),
                      subtitle: const Text('Completed • General City Hospital'),
                      trailing: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: const [
                          Text('32 min', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                          SizedBox(height: 4),
                          Text('12 km', style: TextStyle(color: AppColors.textSecondary)),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  AppCard(
                    child: ListTile(
                      contentPadding: EdgeInsets.zero,
                      title: const Text('Dispatch 00419'),
                      subtitle: const Text('Completed • Regional Medical Center'),
                      trailing: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: const [
                          Text('28 min', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                          SizedBox(height: 4),
                          Text('9.5 km', style: TextStyle(color: AppColors.textSecondary)),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  AppCard(
                    child: ListTile(
                      contentPadding: EdgeInsets.zero,
                      title: const Text('Dispatch 00418'),
                      subtitle: const Text('Completed • City Clinic'),
                      trailing: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: const [
                          Text('36 min', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                          SizedBox(height: 4),
                          Text('14 km', style: TextStyle(color: AppColors.textSecondary)),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
