import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/auth/auth_provider.dart';
import 'package:hostel_mobile/src/ui/strings.dart';
import 'package:hostel_mobile/src/ui/widgets/app_text_field.dart';
import 'package:hostel_mobile/src/ui/widgets/buttons.dart';
import 'package:hostel_mobile/src/ui/widgets/error_card.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final errors = auth.validationErrors;

    return Scaffold(
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 400),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(AppStrings.appName, style: Theme.of(context).textTheme.headlineMedium),
                const SizedBox(height: 8),
                Text(
                  'Secure portal for students and admins',
                  style: Theme.of(context).textTheme.bodyMedium,
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 24),
                if (auth.errorMessage != null)
                  ErrorCard(message: auth.errorMessage ?? 'Unable to sign in.'),
                Form(
                  key: _formKey,
                  child: Column(
                    children: [
                      AppTextField(
                        controller: _emailController,
                        label: 'Email',
                        keyboardType: TextInputType.emailAddress,
                        errorText: _fieldError(errors, 'email'),
                        validator: (value) => value?.isEmpty ?? true ? 'Email is required' : null,
                      ),
                      const SizedBox(height: 12),
                      AppTextField(
                        controller: _passwordController,
                        label: 'Password',
                        obscureText: true,
                        errorText: _fieldError(errors, 'password'),
                        validator: (value) => value?.isEmpty ?? true ? 'Password is required' : null,
                      ),
                      const SizedBox(height: 24),
                      PrimaryButton(
                        label: AppStrings.signIn,
                        isLoading: auth.isLoading,
                        onPressed: auth.isLoading ? null : _submit,
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  String? _fieldError(Map<String, List<String>> errors, String key) {
    final entry = errors[key];
    if (entry != null && entry.isNotEmpty) {
      return entry.first;
    }
    return null;
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    final auth = context.read<AuthProvider>();

    try {
      await auth.login(
        email: _emailController.text.trim(),
        password: _passwordController.text.trim(),
      );
    } on Object {
      // errors surfaced through provider state.
    }
  }
}

