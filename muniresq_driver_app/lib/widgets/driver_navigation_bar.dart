import 'package:flutter/material.dart';
import '../core/routes.dart';
import '../theme/colors.dart';

class DriverNavigationBar extends StatelessWidget {
  const DriverNavigationBar({super.key, required this.currentIndex});

  final int currentIndex;

  static const _routes = [
    AppRoutes.dashboard,
    AppRoutes.dispatchHistory,
    AppRoutes.notifications,
    AppRoutes.profile,
  ];

  @override
  Widget build(BuildContext context) {
    return BottomNavigationBar(
      currentIndex: currentIndex,
      type: BottomNavigationBarType.fixed,
      selectedItemColor: AppColors.primary,
      unselectedItemColor: AppColors.textSecondary,
      onTap: (index) {
        if (index != currentIndex) {
          Navigator.of(context).pushReplacementNamed(_routes[index]);
        }
      },
      items: const [
        BottomNavigationBarItem(
          icon: Icon(Icons.dashboard_outlined),
          label: 'Dashboard',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.assignment_outlined),
          label: 'Dispatches',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.notifications_outlined),
          label: 'Alerts',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.person_outline),
          label: 'Profile',
        ),
      ],
    );
  }
}
