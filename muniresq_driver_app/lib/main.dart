import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:muniresq_driver_app/core/routes.dart';
import 'package:muniresq_driver_app/providers/auth_provider.dart';
import 'package:muniresq_driver_app/providers/dispatch_provider.dart';
import 'package:muniresq_driver_app/providers/driver_profile_provider.dart';
import 'package:muniresq_driver_app/providers/location_provider.dart';
import 'package:muniresq_driver_app/providers/notification_provider.dart';
import 'package:muniresq_driver_app/repositories/api_repository.dart';
import 'package:muniresq_driver_app/screens/approval_pending_screen.dart';
import 'package:muniresq_driver_app/screens/driver_dashboard_screen.dart';
import 'package:muniresq_driver_app/screens/incoming_dispatch_screen.dart';
import 'package:muniresq_driver_app/screens/login_screen.dart';
import 'package:muniresq_driver_app/screens/splash_screen.dart';
import 'package:muniresq_driver_app/screens/dispatch_details_screen.dart';
import 'package:muniresq_driver_app/screens/live_navigation_screen.dart';
import 'package:muniresq_driver_app/screens/en_route_screen.dart';
import 'package:muniresq_driver_app/screens/arrived_at_scene_screen.dart';
import 'package:muniresq_driver_app/screens/patient_transport_screen.dart';
import 'package:muniresq_driver_app/screens/hospital_arrival_screen.dart';
import 'package:muniresq_driver_app/screens/complete_response_screen.dart';
import 'package:muniresq_driver_app/screens/incident_report_screen.dart';
import 'package:muniresq_driver_app/screens/dispatch_history_screen.dart';
import 'package:muniresq_driver_app/screens/emergency_actions_screen.dart';
import 'package:muniresq_driver_app/screens/panic_alert_screen.dart';
import 'package:muniresq_driver_app/screens/hijack_alert_screen.dart';
import 'package:muniresq_driver_app/screens/driver_profile_screen.dart';
import 'package:muniresq_driver_app/screens/settings_screen.dart';
import 'package:muniresq_driver_app/screens/notification_center_screen.dart';
import 'package:muniresq_driver_app/theme/theme.dart';

void main() {
  runApp(
    MultiProvider(
      providers: [
        Provider<ApiRepository>(create: (_) => ApiRepository()),
        ChangeNotifierProvider<AuthProvider>(
          create: (context) => AuthProvider(repository: context.read<ApiRepository>()),
        ),
        ChangeNotifierProvider<DispatchProvider>(
          create: (context) => DispatchProvider(repository: context.read<ApiRepository>()),
        ),
        ChangeNotifierProvider<DriverProfileProvider>(
          create: (context) => DriverProfileProvider(repository: context.read<ApiRepository>()),
        ),
        ChangeNotifierProvider<LocationProvider>(
          create: (context) => LocationProvider(repository: context.read<ApiRepository>()),
        ),
        ChangeNotifierProvider<NotificationProvider>(
          create: (context) => NotificationProvider(repository: context.read<ApiRepository>()),
        ),
      ],
      child: const MuniResQDriverApp(),
    ),
  );
}

class MuniResQDriverApp extends StatelessWidget {
  const MuniResQDriverApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'MuniResQ Driver',
      theme: AppTheme.lightTheme,
      initialRoute: AppRoutes.splash,
      routes: {
        AppRoutes.splash: (_) => const SplashScreen(),
        AppRoutes.login: (_) => const LoginScreen(),
        AppRoutes.approvalPending: (_) => const ApprovalPendingScreen(),
        AppRoutes.dashboard: (_) => const DriverDashboardScreen(),
        AppRoutes.incomingDispatch: (_) => const IncomingDispatchScreen(),
        AppRoutes.dispatchDetails: (_) => const DispatchDetailsScreen(),
        AppRoutes.liveNavigation: (_) => const LiveNavigationScreen(),
        AppRoutes.enRoute: (_) => const EnRouteScreen(),
        AppRoutes.arrivedAtScene: (_) => const ArrivedAtSceneScreen(),
        AppRoutes.patientTransport: (_) => const PatientTransportScreen(),
        AppRoutes.hospitalArrival: (_) => const HospitalArrivalScreen(),
        AppRoutes.completeResponse: (_) => const CompleteResponseScreen(),
        AppRoutes.incidentReport: (_) => const IncidentReportScreen(),
        AppRoutes.dispatchHistory: (_) => const DispatchHistoryScreen(),
        AppRoutes.emergencyActions: (_) => const EmergencyActionsScreen(),
        AppRoutes.panicAlert: (_) => const PanicAlertScreen(),
        AppRoutes.hijackAlert: (_) => const HijackAlertScreen(),
        AppRoutes.profile: (_) => const DriverProfileScreen(),
        AppRoutes.settings: (_) => const SettingsScreen(),
        AppRoutes.notifications: (_) => const NotificationCenterScreen(),
      },
    );
  }
}
