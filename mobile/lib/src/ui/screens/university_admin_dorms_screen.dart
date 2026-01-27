import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/domain/repositories/dorm_repository.dart';
import 'package:hostel_mobile/src/features/university_admin/university_admin_provider.dart';
import 'package:hostel_mobile/src/ui/components/empty_state.dart';
import 'package:hostel_mobile/src/ui/components/error_card.dart';

class UniversityAdminDormsScreen extends StatefulWidget {
  const UniversityAdminDormsScreen({super.key});

  @override
  State<UniversityAdminDormsScreen> createState() => _UniversityAdminDormsScreenState();
}

class _UniversityAdminDormsScreenState extends State<UniversityAdminDormsScreen> {
  late final UniversityAdminDormsProvider _provider;
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _codeController = TextEditingController();
  final _capacityController = TextEditingController();
  final _addressController = TextEditingController();
  final _contactController = TextEditingController();
  String _status = 'ACTIVE';

  @override
  void initState() {
    super.initState();
    _provider = UniversityAdminDormsProvider(context.read<DormRepository>());
    _provider.loadDorms();
  }

  @override
  void dispose() {
    _nameController.dispose();
    _codeController.dispose();
    _capacityController.dispose();
    _addressController.dispose();
    _contactController.dispose();
    _provider.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    final payload = {
      'name': _nameController.text.trim(),
      'code': _codeController.text.trim(),
      'address': _addressController.text.trim(),
      'capacity': int.tryParse(_capacityController.text) ?? 0,
      'status': _status,
      'contact_name': _contactController.text.trim(),
    };

    await _provider.submitDorm(payload);
    if (!mounted) {
      return;
    }
    ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Dorm saved.')));
    _formKey.currentState?.reset();
  }

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider<UniversityAdminDormsProvider>.value(
      value: _provider,
      child: Consumer<UniversityAdminDormsProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.errorMessage != null) {
            return Center(child: ErrorCard(message: provider.errorMessage!));
          }

          final dorms = provider.dorms;

          return Padding(
            padding: const EdgeInsets.all(16.0),
            child: ListView(
              children: [
                Text('Dormitories', style: Theme.of(context).textTheme.headlineSmall),
                const SizedBox(height: 16),
                if (dorms.isEmpty)
                  const EmptyState(
                    title: 'No dorms yet',
                    description: 'Create your first dorm to start managing occupancy.',
                  )
                else
                  ...dorms.map((dorm) => Card(
                        margin: const EdgeInsets.only(bottom: 12),
                        child: ListTile(
                          title: Text(dorm.name),
                          subtitle: Text(dorm.address ?? 'No address'),
                          trailing: Text(dorm.status ?? 'Unknown'),
                        ),
                      )),
                const SizedBox(height: 24),
                Text('Add or update dorm', style: Theme.of(context).textTheme.titleLarge),
                const SizedBox(height: 12),
                Form(
                  key: _formKey,
                  child: Column(
                    children: [
                      TextFormField(
                        controller: _nameController,
                        decoration: const InputDecoration(labelText: 'Name'),
                        validator: (value) => value?.isEmpty ?? true ? 'Name is required' : null,
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: _codeController,
                        decoration: const InputDecoration(labelText: 'Code'),
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: _addressController,
                        decoration: const InputDecoration(labelText: 'Address'),
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: _capacityController,
                        decoration: const InputDecoration(labelText: 'Capacity'),
                        keyboardType: TextInputType.number,
                      ),
                      const SizedBox(height: 12),
                      DropdownButtonFormField<String>(
                        initialValue: _status,
                        items: const [
                          DropdownMenuItem(value: 'ACTIVE', child: Text('Active')),
                          DropdownMenuItem(value: 'INACTIVE', child: Text('Inactive')),
                        ],
                        onChanged: (value) => setState(() => _status = value ?? 'ACTIVE'),
                        decoration: const InputDecoration(labelText: 'Status'),
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: _contactController,
                        decoration: const InputDecoration(labelText: 'Contact name'),
                      ),
                      const SizedBox(height: 16),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: provider.isSubmitting ? null : _submit,
                          child: provider.isSubmitting
                              ? const SizedBox(
                                  height: 20,
                                  width: 20,
                                  child: CircularProgressIndicator(strokeWidth: 2),
                                )
                              : const Text('Save dorm'),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
