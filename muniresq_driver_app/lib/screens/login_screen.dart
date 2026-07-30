import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../core/routes.dart';
import '../providers/auth_provider.dart';
import '../theme/colors.dart';
import '../theme/typography.dart';
import '../widgets/custom_buttons.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final TextEditingController _driverIdController = TextEditingController();
  final TextEditingController _pinController = TextEditingController();
  bool _isLoading = false;

  @override
  void dispose() {
    _driverIdController.dispose();
    _pinController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    setState(() => _isLoading = true);
    try {
      final authProvider = context.read<AuthProvider>();
      final success = await authProvider.login(
        _driverIdController.text.trim(),
        _pinController.text.trim(),
      );
      if (success && mounted) {
        Navigator.of(context).pushReplacementNamed(AppRoutes.approvalPending);
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 32),
              Text('Welcome back, Driver', style: AppTypography.headline2),
              const SizedBox(height: 10),
              Text('Secure access to your active response units and dispatches.', style: AppTypography.bodyMedium),
              const SizedBox(height: 32),
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(24),
                  boxShadow: [BoxShadow(color: AppColors.cardShadow, blurRadius: 18, offset: const Offset(0, 10))],
                ),
                child: Column(
                  children: [
                    TextField(
                      controller: _driverIdController,
                      decoration: const InputDecoration(
                        labelText: 'Driver ID',
                        hintText: 'Enter your ID',
                      ),
                    ),
                    const SizedBox(height: 18),
                    TextField(
                      controller: _pinController,
                      obscureText: true,
                      decoration: const InputDecoration(
                        labelText: 'PIN',
                        hintText: '••••••',
                      ),
                    ),
                    const SizedBox(height: 20),
                    Row(
                      children: [
                        Checkbox(value: true, onChanged: (_) {}),
                        Expanded(child: Text('Keep me signed in', style: AppTypography.bodySmall)),
                      ],
                    ),
                    const SizedBox(height: 18),
                    PrimaryButton(
                      label: _isLoading ? 'Signing In...' : 'Sign In',
                      onPressed: _isLoading ? null : _handleLogin,
                    ),
                  ],
                ),
              ),
              const Spacer(),
              Center(
                child: TextButton(
                  onPressed: () {},
                  child: const Text('Need assistance with login?', style: TextStyle(color: AppColors.secondary)),
                ),
              ),
              const SizedBox(height: 16),
            ],
          ),
        ),
      ),
    );
  }
}
