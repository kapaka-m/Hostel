import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/dorm.dart';
import '../../providers/auth_provider.dart';

class CreateDormAdminTab extends StatefulWidget {
  const CreateDormAdminTab({super.key});

  @override
  State<CreateDormAdminTab> createState() => _CreateDormAdminTabState();
}

class _CreateDormAdminTabState extends State<CreateDormAdminTab> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();

  List<Dorm> _dorms = [];
  int? _selectedDormId;
  bool _loading = true;
  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    _loadDorms();
  }

  Future<void> _loadDorms() async {
    setState(() {
      _loading = true;
    });

    try {
      _dorms = await context.read<AuthProvider>().api.getDorms();
      if (_dorms.isNotEmpty) {
        _selectedDormId ??= _dorms.first.id;
      }
    } catch (error) {
      _showMessage(error);
    } finally {
      if (mounted) {
        setState(() {
          _loading = false;
        });
      }
    }
  }

  void _showMessage(Object error) {
    if (!mounted) {
      return;
    }
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(error.toString().replaceFirst('Exception: ', ''))),
    );
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (_selectedDormId == null) {
      _showMessage('Please select a dorm');
      return;
    }

    setState(() {
      _submitting = true;
    });

    try {
      await context.read<AuthProvider>().api.createDormAdmin(
        dormId: _selectedDormId!,
        name: _nameController.text.trim(),
        email: _emailController.text.trim(),
        password: _passwordController.text.trim(),
      );

      if (mounted) {
        _nameController.clear();
        _emailController.clear();
        _passwordController.clear();
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Dorm admin created successfully.')),
        );
      }
    } catch (error) {
      _showMessage(error);
    } finally {
      if (mounted) {
        setState(() {
          _submitting = false;
        });
      }
    }
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
    if (_loading) {
      return const Center(child: CircularProgressIndicator());
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Form(
        key: _formKey,
        child: Column(
          children: [
            DropdownButtonFormField<int>(
              initialValue: _selectedDormId,
              decoration: const InputDecoration(
                labelText: 'Dorm',
                border: OutlineInputBorder(),
              ),
              items: _dorms
                  .map(
                    (dorm) => DropdownMenuItem(
                      value: dorm.id,
                      child: Text(dorm.name),
                    ),
                  )
                  .toList(),
              onChanged: (value) {
                setState(() {
                  _selectedDormId = value;
                });
              },
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _nameController,
              decoration: const InputDecoration(
                labelText: 'Name',
                border: OutlineInputBorder(),
              ),
              validator: (value) {
                if (value == null || value.trim().isEmpty) {
                  return 'Name is required';
                }
                return null;
              },
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _emailController,
              decoration: const InputDecoration(
                labelText: 'Email',
                border: OutlineInputBorder(),
              ),
              validator: (value) {
                if (value == null || value.trim().isEmpty) {
                  return 'Email is required';
                }
                return null;
              },
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _passwordController,
              decoration: const InputDecoration(
                labelText: 'Password',
                border: OutlineInputBorder(),
              ),
              obscureText: true,
              validator: (value) {
                if (value == null || value.trim().isEmpty) {
                  return 'Password is required';
                }
                if (value.trim().length < 6) {
                  return 'Password must be at least 6 characters';
                }
                return null;
              },
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: FilledButton(
                onPressed: _submitting ? null : _submit,
                child: _submitting
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Text('Create Dorm Admin'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
