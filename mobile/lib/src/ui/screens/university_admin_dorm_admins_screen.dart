import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/domain/models/dorm_model.dart';
import 'package:hostel_mobile/src/domain/repositories/dorm_repository.dart';
import 'package:hostel_mobile/src/ui/components/error_card.dart';

class UniversityAdminDormAdminsScreen extends StatefulWidget {
  const UniversityAdminDormAdminsScreen({super.key});

  @override
  State<UniversityAdminDormAdminsScreen> createState() => _UniversityAdminDormAdminsScreenState();
}

class _UniversityAdminDormAdminsScreenState extends State<UniversityAdminDormAdminsScreen> {
  late final Future<List<DormModel>> _dormsFuture;
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  int? _selectedDormId;
  bool _isSubmitting = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _dormsFuture = context.read<DormRepository>().fetchDorms();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate() || _selectedDormId == null) {
      return;
    }

    setState(() {
      _isSubmitting = true;
      _errorMessage = null;
    });

    try {
      await context.read<DormRepository>().inviteDormAdmin(
            dormId: _selectedDormId!,
            name: _nameController.text.trim(),
            email: _emailController.text.trim(),
            password: _passwordController.text,
          );
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Dorm admin invited.')));
      _formKey.currentState?.reset();
      setState(() => _selectedDormId = null);
    } catch (error) {
      if (!mounted) {
        return;
      }
      setState(() => _errorMessage = 'Unable to invite dorm admin.');
    } finally {
      if (mounted) {
        setState(() => _isSubmitting = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<List<DormModel>>(
      future: _dormsFuture,
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return const Center(child: CircularProgressIndicator());
        }

        if (snapshot.hasError) {
          return const Center(child: ErrorCard(message: 'Unable to load dorms.'));
        }

        final dorms = snapshot.data ?? [];

        return Padding(
          padding: const EdgeInsets.all(16.0),
          child: ListView(
            children: [
              Text('Dorm administrators', style: Theme.of(context).textTheme.headlineSmall),
              const SizedBox(height: 12),
              const Card(
                child: ListTile(
                  title: Text('Listing dorm admins is not available via this API.'),
                  subtitle: Text('Use the backend administration site to review existing assignments.'),
                ),
              ),
              const SizedBox(height: 24),
              Text('Invite a dorm admin', style: Theme.of(context).textTheme.titleLarge),
              const SizedBox(height: 12),
              if (_errorMessage != null) ErrorCard(message: _errorMessage!),
              Form(
                key: _formKey,
                child: Column(
                  children: [
                    DropdownButtonFormField<int>(
                      initialValue: _selectedDormId,
                      items: dorms
                          .map((dorm) => DropdownMenuItem(value: dorm.id, child: Text(dorm.name)))
                          .toList(),
                      decoration: const InputDecoration(labelText: 'Dorm'),
                      onChanged: _isSubmitting ? null : (value) => setState(() => _selectedDormId = value),
                      validator: (value) => value == null ? 'Select a dorm' : null,
                    ),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: _nameController,
                      decoration: const InputDecoration(labelText: 'Name'),
                      validator: (value) => value?.isEmpty ?? true ? 'Name is required' : null,
                    ),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: _emailController,
                      decoration: const InputDecoration(labelText: 'Email'),
                      keyboardType: TextInputType.emailAddress,
                      validator: (value) => value?.isEmpty ?? true ? 'Email is required' : null,
                    ),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: _passwordController,
                      decoration: const InputDecoration(labelText: 'Password'),
                      obscureText: true,
                    validator: (value) => (value?.length ?? 0) < 6 ? 'Password must be at least 6 characters' : null,
                    ),
                    const SizedBox(height: 16),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: _isSubmitting ? null : _submit,
                        child: _isSubmitting
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
      },
    );
  }
}
