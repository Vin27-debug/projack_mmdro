import 'package:flutter/material.dart';
import '../theme/colors.dart';
import '../theme/typography.dart';
import '../widgets/custom_buttons.dart';

class LiveNavigationScreen extends StatelessWidget {
  const LiveNavigationScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          Container(color: AppColors.background),
          Positioned.fill(
            child: Container(
              decoration: BoxDecoration(
                color: AppColors.border,
                borderRadius: BorderRadius.circular(0),
              ),
              child: const Center(child: Icon(Icons.map, size: 80, color: AppColors.textSecondary)),
            ),
          ),
          SafeArea(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      GestureDetector(
                        onTap: () => Navigator.pop(context),
                        child: Container(
                          width: 44,
                          height: 44,
                          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(14), boxShadow: [BoxShadow(color: AppColors.cardShadow, blurRadius: 18, offset: const Offset(0, 8))]),
                          child: const Icon(Icons.arrow_back, color: AppColors.textPrimary),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                        decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: AppColors.cardShadow, blurRadius: 18, offset: const Offset(0, 8))]),
                        child: const Text('Live Navigation', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                      ),
                      const SizedBox(width: 44),
                    ],
                  ),
                  const SizedBox(height: 22),
                  Container(
                    padding: const EdgeInsets.all(18),
                    decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(20), boxShadow: [BoxShadow(color: AppColors.cardShadow, blurRadius: 18, offset: const Offset(0, 10))]),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Route Progress', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                        const SizedBox(height: 12),
                        LinearProgressIndicator(value: 0.74, color: AppColors.primary, backgroundColor: AppColors.border),
                        const SizedBox(height: 14),
                        Row(
                          children: const [
                            Expanded(child: Text('ETA 04:08', style: AppTypography.headline3)),
                            Text('2.2 km', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.secondary)),
                          ],
                        ),
                        const SizedBox(height: 6),
                        const Text('Using fastest route with current traffic clearance.', style: AppTypography.bodyMedium),
                      ],
                    ),
                  ),
                  const Spacer(),
                  Container(
                    padding: const EdgeInsets.all(18),
                    decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(20), boxShadow: [BoxShadow(color: AppColors.cardShadow, blurRadius: 18, offset: const Offset(0, 10))]),
                    child: Column(
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: const [
                            Text('Next maneuver', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.textPrimary)),
                            Text('300 m', style: TextStyle(fontWeight: FontWeight.w700, color: AppColors.secondary)),
                          ],
                        ),
                        const SizedBox(height: 12),
                        Row(
                          children: const [
                            Icon(Icons.turn_right, color: AppColors.primary, size: 28),
                            SizedBox(width: 14),
                            Expanded(child: Text('Turn right onto National Highway.', style: AppTypography.bodyMedium)),
                          ],
                        ),
                        const SizedBox(height: 18),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                          children: [
                            ElevatedButton.icon(
                              onPressed: () {},
                              icon: const Icon(Icons.volume_up, size: 18),
                              label: const Text('Voice'),
                              style: ElevatedButton.styleFrom(backgroundColor: AppColors.secondary, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14))),
                            ),
                            ElevatedButton.icon(
                              onPressed: () {},
                              icon: const Icon(Icons.traffic, size: 18),
                              label: const Text('Traffic'),
                              style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14))),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
