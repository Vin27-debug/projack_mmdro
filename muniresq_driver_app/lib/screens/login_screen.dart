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
  final TextEditingController _passwordController = TextEditingController();
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void dispose() {
    _driverIdController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });
    try {
      final authProvider = context.read<AuthProvider>();
      final success = await authProvider.login(
        _driverIdController.text.trim(),
        _passwordController.text,
      );
      if (success && mounted) {
        Navigator.of(context).pushReplacementNamed(AppRoutes.dashboard);
      } else if (mounted) {
        setState(() => _errorMessage = authProvider.errorMessage);
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
                      controller: _passwordController,
                      obscureText: true,
                      decoration: const InputDecoration(
                        labelText: 'Account password',
                        hintText: 'Enter your password',
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
                    if (_errorMessage != null) ...[
                      Align(
                        alignment: Alignment.centerLeft,
                        child: Text(
                          _errorMessage!,
                          style: const TextStyle(color: AppColors.danger),
                        ),
                      ),
                      const SizedBox(height: 12),
                    ],
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
