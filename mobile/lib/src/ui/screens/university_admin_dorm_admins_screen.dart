import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/models/dorm_model.dart';
import 'package:hostel_mobile/src/providers/dorm_admins_provider.dart';
import 'package:hostel_mobile/src/providers/dorms_provider.dart';
import 'package:hostel_mobile/src/ui/widgets/app_text_field.dart';
import 'package:hostel_mobile/src/ui/widgets/error_state.dart';
import 'package:hostel_mobile/src/ui/widgets/loading_state.dart';
import 'package:hostel_mobile/src/ui/widgets/section_header.dart';

class UniversityAdminDormAdminsScreen extends StatefulWidget {
  const UniversityAdminDormAdminsScreen({super.key});

  @override
  State<UniversityAdminDormAdminsScreen> createState() =>
      _UniversityAdminDormAdminsScreenState();
}

class _UniversityAdminDormAdminsScreenState
    extends State<UniversityAdminDormAdminsScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  int? _selectedDormId;

  @override
  void initState() {
    super.initState();
    context.read<DormsProvider>().load();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final dormsProvider = context.watch<DormsProvider>();
    final adminsProvider = context.watch<DormAdminsProvider>();

    if (dormsProvider.isLoading && dormsProvider.dorms.isEmpty) {
      return const LoadingState(message: 'Loading dorms...');
    }

    if (dormsProvider.errorMessage != null && dormsProvider.dorms.isEmpty) {
      return ErrorState(
        message: dormsProvider.errorMessage!,
        onRetry: () => dormsProvider.load(),
      );
    }

    final dorms = dormsProvider.dorms;

    return Padding(
      padding: const EdgeInsets.all(16),
      child: ListView(
        children: [
          const SectionHeader(title: 'Dorm administrators'),
          const SizedBox(height: 12),
          const Card(
            child: ListTile(
              title: Text('Listing dorm admins is not available in the API.'),
              subtitle: Text('Use the backend admin panel to review existing assignments.'),
            ),
          ),
          const SizedBox(height: 24),
          const SectionHeader(title: 'Invite a dorm admin'),
          const SizedBox(height: 12),
          if (adminsProvider.errorMessage != null)
            ErrorState(message: adminsProvider.errorMessage!, onRetry: null),
          Form(
            key: _formKey,
            child: Column(
              children: [
                DropdownButtonFormField<int>(
                  key: ValueKey('dorm-${_selectedDormId ?? 'none'}'),
                  initialValue: _selectedDormId,
                  items: dorms
                      .map((DormModel dorm) =>
                          DropdownMenuItem(value: dorm.id, child: Text(dorm.name)))
                      .toList(),
                  decoration: const InputDecoration(labelText: 'Dorm'),
                  onChanged:
                      adminsProvider.isSubmitting ? null : (value) => setState(() => _selectedDormId = value),
                  validator: (value) => value == null ? 'Select a dorm' : null,
                ),
                const SizedBox(height: 12),
                AppTextField(
                  controller: _nameController,
                  label: 'Name',
                  validator: (value) => value == null || value.isEmpty ? 'Name is required' : null,
                ),
                const SizedBox(height: 12),
                AppTextField(
                  controller: _emailController,
                  label: 'Email',
                  keyboardType: TextInputType.emailAddress,
                  validator: (value) => value == null || value.isEmpty ? 'Email is required' : null,
                ),
                const SizedBox(height: 12),
                AppTextField(
                  controller: _passwordController,
                  label: 'Password',
                  obscureText: true,
                  validator: (value) =>
                      (value?.length ?? 0) < 6 ? 'Password must be at least 6 characters' : null,
                ),
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: adminsProvider.isSubmitting ? null : _submit,
                    child: adminsProvider.isSubmitting
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : const Text('Invite dorm admin'),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate() || _selectedDormId == null) {
      return;
    }

    final provider = context.read<DormAdminsProvider>();
    final success = await provider.createDormAdmin(
      dormId: _selectedDormId!,
      name: _nameController.text.trim(),
      email: _emailController.text.trim(),
      password: _passwordController.text.trim(),
    );

    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Dorm admin invited.')),
      );
      _formKey.currentState?.reset();
      setState(() => _selectedDormId = null);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(provider.errorMessage ?? 'Unable to invite dorm admin.')),
      );
    }
  }
}
