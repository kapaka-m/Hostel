import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/models/dorm_model.dart';
import 'package:hostel_mobile/src/providers/dorms_provider.dart';
import 'package:hostel_mobile/src/ui/widgets/app_text_field.dart';
import 'package:hostel_mobile/src/ui/widgets/confirm_dialog.dart';
import 'package:hostel_mobile/src/ui/widgets/empty_state.dart';
import 'package:hostel_mobile/src/ui/widgets/error_state.dart';
import 'package:hostel_mobile/src/ui/widgets/loading_state.dart';
import 'package:hostel_mobile/src/ui/widgets/section_header.dart';
import 'package:hostel_mobile/src/ui/widgets/status_badge.dart';

class UniversityAdminDormsScreen extends StatefulWidget {
  const UniversityAdminDormsScreen({super.key});

  @override
  State<UniversityAdminDormsScreen> createState() => _UniversityAdminDormsScreenState();
}

class _UniversityAdminDormsScreenState extends State<UniversityAdminDormsScreen> {
  @override
  void initState() {
    super.initState();
    context.read<DormsProvider>().load();
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<DormsProvider>();

    if (provider.isLoading && provider.dorms.isEmpty) {
      return const LoadingState(message: 'Loading dorms...');
    }

    if (provider.errorMessage != null && provider.dorms.isEmpty) {
      return ErrorState(
        message: provider.errorMessage!,
        onRetry: () => provider.load(),
      );
    }

    return Scaffold(
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _openDormForm(context),
        icon: const Icon(Icons.add),
        label: const Text('New dorm'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            const SectionHeader(title: 'Dorms'),
            const SizedBox(height: 12),
            Expanded(
              child: provider.dorms.isEmpty
                  ? const EmptyState(
                      title: 'No dorms yet',
                      description: 'Create your first dorm to start managing occupancy.',
                    )
                  : RefreshIndicator(
                      onRefresh: () => provider.load(),
                      child: ListView.builder(
                        itemCount: provider.dorms.length,
                        itemBuilder: (context, index) {
                          final dorm = provider.dorms[index];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            child: ListTile(
                              title: Text(dorm.name),
                              subtitle: Text(dorm.address ?? 'No address'),
                              trailing: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  StatusBadge(label: dorm.status, kind: StatusKind.generic),
                                  const SizedBox(width: 8),
                                  PopupMenuButton<String>(
                                    onSelected: (value) {
                                      if (value == 'edit') {
                                        _openDormForm(context, existing: dorm);
                                      } else if (value == 'delete') {
                                        _deleteDorm(context, dorm);
                                      }
                                    },
                                    itemBuilder: (context) => const [
                                      PopupMenuItem(value: 'edit', child: Text('Edit')),
                                      PopupMenuItem(value: 'delete', child: Text('Delete')),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
                    ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _openDormForm(BuildContext context, {DormModel? existing}) async {
    final result = await showDialog<_DormFormResult>(
      context: context,
      builder: (context) => _DormFormDialog(initial: existing),
    );
    if (!context.mounted) return;
    if (result == null) return;

    final payload = {
      'name': result.name,
      if (result.code.isNotEmpty) 'code': result.code,
      if (result.address.isNotEmpty) 'address': result.address,
      if (result.capacity != null) 'capacity': result.capacity,
      'status': result.status,
      if (result.contactName.isNotEmpty) 'contact_name': result.contactName,
      if (result.contactEmail.isNotEmpty) 'contact_email': result.contactEmail,
      if (result.contactPhone.isNotEmpty) 'contact_phone': result.contactPhone,
      if (result.notes.isNotEmpty) 'notes': result.notes,
    };

    final provider = context.read<DormsProvider>();
    final success = await provider.saveDorm(payload, dormId: existing?.id);
    if (!context.mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(success ? 'Dorm saved.' : provider.errorMessage ?? 'Save failed.'),
      ),
    );
  }

  Future<void> _deleteDorm(BuildContext context, DormModel dorm) async {
    final confirm = await showConfirmDialog(
      context: context,
      title: 'Delete dorm',
      message: 'Are you sure you want to delete ${dorm.name}?',
      confirmLabel: 'Delete',
    );
    if (!context.mounted) return;
    if (!confirm) return;

    final provider = context.read<DormsProvider>();
    final success = await provider.deleteDorm(dorm.id);
    if (!context.mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(success ? 'Dorm deleted.' : provider.errorMessage ?? 'Delete failed.'),
      ),
    );
  }
}

class _DormFormResult {
  final String name;
  final String code;
  final String address;
  final int? capacity;
  final String status;
  final String contactName;
  final String contactEmail;
  final String contactPhone;
  final String notes;

  _DormFormResult({
    required this.name,
    required this.code,
    required this.address,
    required this.capacity,
    required this.status,
    required this.contactName,
    required this.contactEmail,
    required this.contactPhone,
    required this.notes,
  });
}

class _DormFormDialog extends StatefulWidget {
  final DormModel? initial;

  const _DormFormDialog({this.initial});

  @override
  State<_DormFormDialog> createState() => _DormFormDialogState();
}

class _DormFormDialogState extends State<_DormFormDialog> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _nameController;
  late final TextEditingController _codeController;
  late final TextEditingController _addressController;
  late final TextEditingController _capacityController;
  late final TextEditingController _contactNameController;
  late final TextEditingController _contactEmailController;
  late final TextEditingController _contactPhoneController;
  late final TextEditingController _notesController;
  String _status = 'ACTIVE';

  @override
  void initState() {
    super.initState();
    final dorm = widget.initial;
    _nameController = TextEditingController(text: dorm?.name ?? '');
    _codeController = TextEditingController(text: dorm?.code ?? '');
    _addressController = TextEditingController(text: dorm?.address ?? '');
    _capacityController = TextEditingController(text: dorm?.capacity?.toString() ?? '');
    _contactNameController = TextEditingController(text: dorm?.contactName ?? '');
    _contactEmailController = TextEditingController(text: dorm?.contactEmail ?? '');
    _contactPhoneController = TextEditingController(text: dorm?.contactPhone ?? '');
    _notesController = TextEditingController(text: dorm?.notes ?? '');
    _status = dorm?.status ?? 'ACTIVE';
  }

  @override
  void dispose() {
    _nameController.dispose();
    _codeController.dispose();
    _addressController.dispose();
    _capacityController.dispose();
    _contactNameController.dispose();
    _contactEmailController.dispose();
    _contactPhoneController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text(widget.initial == null ? 'New dorm' : 'Edit dorm'),
      content: SizedBox(
        width: 360,
        child: Form(
          key: _formKey,
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                AppTextField(
                  controller: _nameController,
                  label: 'Name',
                  validator: (value) => value == null || value.isEmpty ? 'Required' : null,
                ),
                const SizedBox(height: 12),
                AppTextField(
                  controller: _codeController,
                  label: 'Code (optional)',
                ),
                const SizedBox(height: 12),
                AppTextField(
                  controller: _addressController,
                  label: 'Address (optional)',
                ),
                const SizedBox(height: 12),
                AppTextField(
                  controller: _capacityController,
                  label: 'Capacity (optional)',
                  keyboardType: TextInputType.number,
                ),
                const SizedBox(height: 12),
                DropdownButtonFormField<String>(
                  initialValue: _status,
                  decoration: const InputDecoration(labelText: 'Status'),
                  items: const [
                    DropdownMenuItem(value: 'ACTIVE', child: Text('Active')),
                    DropdownMenuItem(value: 'INACTIVE', child: Text('Inactive')),
                  ],
                  onChanged: (value) => setState(() => _status = value ?? 'ACTIVE'),
                ),
                const SizedBox(height: 12),
                AppTextField(
                  controller: _contactNameController,
                  label: 'Contact name (optional)',
                ),
                const SizedBox(height: 12),
                AppTextField(
                  controller: _contactEmailController,
                  label: 'Contact email (optional)',
                  keyboardType: TextInputType.emailAddress,
                ),
                const SizedBox(height: 12),
                AppTextField(
                  controller: _contactPhoneController,
                  label: 'Contact phone (optional)',
                  keyboardType: TextInputType.phone,
                ),
                const SizedBox(height: 12),
                AppTextField(
                  controller: _notesController,
                  label: 'Notes (optional)',
                  maxLines: 3,
                ),
              ],
            ),
          ),
        ),
      ),
      actions: [
        TextButton(onPressed: () => Navigator.of(context).pop(), child: const Text('Cancel')),
        ElevatedButton(onPressed: _submit, child: const Text('Save')),
      ],
    );
  }

  void _submit() {
    if (!_formKey.currentState!.validate()) return;

    final capacity = int.tryParse(_capacityController.text.trim());
    Navigator.of(context).pop(_DormFormResult(
      name: _nameController.text.trim(),
      code: _codeController.text.trim(),
      address: _addressController.text.trim(),
      capacity: capacity,
      status: _status,
      contactName: _contactNameController.text.trim(),
      contactEmail: _contactEmailController.text.trim(),
      contactPhone: _contactPhoneController.text.trim(),
      notes: _notesController.text.trim(),
    ));
  }
}
