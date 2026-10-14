import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../core/routes.dart';
import '../providers/auth_provider.dart';
import '../theme/colors.dart';
import '../theme/typography.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  String? _startupError;

  @override
  void initState() {
    super.initState();
    _continue();
  }

  Future<void> _continue() async {
    await Future<void>.delayed(const Duration(seconds: 2));
    if (!mounted) return;

    try {
      await context.read<AuthProvider>().restoreSession();
    } on PlatformException {
      if (mounted) {
        setState(() {
          _startupError = 'Secure sign-in storage is unavailable. Retry or sign in again.';
        });
      }
      return;
    } on MissingPluginException {
      if (mounted) {
        setState(() {
          _startupError = 'Android secure storage is not installed in this app build.';
        });
      }
      return;
    }

    if (!mounted) return;
    Navigator.of(context).pushReplacementNamed(
      context.read<AuthProvider>().isAuthenticated
          ? AppRoutes.dashboard
          : AppRoutes.login,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const SizedBox(height: 24),
              const SizedBox.shrink(),
              Column(
                children: [
                  Container(
                    width: 104,
                    height: 104,
                    decoration: BoxDecoration(
                      color: AppColors.primary,
                      borderRadius: BorderRadius.circular(24),
                    ),
                    child: const Center(
                      child: Icon(Icons.health_and_safety, color: Colors.white, size: 46),
                    ),
                  ),
                  const SizedBox(height: 24),
                  Text('MuniResQ Driver', style: AppTypography.headline1.copyWith(color: AppColors.textPrimary)),
                  const SizedBox(height: 8),
                  Text('Municipal Emergency Response System', style: AppTypography.bodyMedium),
                ],
              ),
              Padding(
                padding: const EdgeInsets.only(bottom: 24),
                child: Column(
                  children: [
                    if (_startupError == null)
                      const CircularProgressIndicator(color: AppColors.primary)
                    else
                      TextButton(
                        onPressed: _continue,
                        child: const Text('Retry secure sign-in'),
                      ),
                    const SizedBox(height: 16),
                    Text(
                      _startupError ?? 'Connecting to command center...',
                      style: AppTypography.bodySmall,
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
