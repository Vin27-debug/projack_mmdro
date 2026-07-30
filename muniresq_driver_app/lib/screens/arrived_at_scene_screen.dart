import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../theme/typography.dart';
import '../widgets/custom_buttons.dart';
import '../widgets/custom_cards.dart';

class ArrivedAtSceneScreen extends StatelessWidget {
  const ArrivedAtSceneScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Arrived At Scene'),
        backgroundColor: AppColors.surface,
        elevation: 0,
      ),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Arrival confirmed', style: AppTypography.headline2),
            const SizedBox(height: 8),
            Text('You are on location at Brgy. San Miguel Health Center. Proceed carefully and secure the scene.', style: AppTypography.bodyMedium),
            const SizedBox(height: 24),
            AppCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Scene check-in', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                  const SizedBox(height: 16),
                  const ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: Icon(Icons.location_on, color: AppColors.primary),
                    title: Text('Location confirmed'),
                  ),
                  const ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: Icon(Icons.shield, color: AppColors.secondary),
                    title: Text('Scene safety evaluated'),
                  ),
                  const ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: Icon(Icons.medical_services, color: AppColors.success),
                    title: Text('Patient primary survey started'),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            PrimaryButton(label: 'Start Patient Contact', onPressed: () {}),
            const SizedBox(height: 12),
            SecondaryButton(label: 'Update Command', onPressed: () {}),
          ],
        ),
      ),
    );
  }
}
