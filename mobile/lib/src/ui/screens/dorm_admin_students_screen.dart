import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/models/student_model.dart';
import 'package:hostel_mobile/src/providers/students_provider.dart';
import 'package:hostel_mobile/src/ui/widgets/app_text_field.dart';
import 'package:hostel_mobile/src/ui/widgets/confirm_dialog.dart';
import 'package:hostel_mobile/src/ui/widgets/empty_state.dart';
import 'package:hostel_mobile/src/ui/widgets/error_state.dart';
import 'package:hostel_mobile/src/ui/widgets/loading_state.dart';
import 'package:hostel_mobile/src/ui/widgets/section_header.dart';

class DormAdminStudentsScreen extends StatefulWidget {
  const DormAdminStudentsScreen({super.key});

  @override
  State<DormAdminStudentsScreen> createState() => _DormAdminStudentsScreenState();
}

class _DormAdminStudentsScreenState extends State<DormAdminStudentsScreen> {
  @override
  void initState() {
    super.initState();
    context.read<StudentsProvider>().load();
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<StudentsProvider>();

    if (provider.isLoading && provider.students.isEmpty) {
      return const LoadingState(message: 'Loading students...');
    }

    if (provider.errorMessage != null && provider.students.isEmpty) {
      return ErrorState(
        message: provider.errorMessage!,
        onRetry: () => provider.load(),
      );
    }

    return Scaffold(
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            SectionHeader(
              title: 'Students',
              actionLabel: 'New student',
              onAction: () => _openStudentForm(context),
            ),
            const SizedBox(height: 12),
            Expanded(
              child: provider.students.isEmpty
                  ? const EmptyState(
                      title: 'No students yet',
                      description: 'Create students to assign rooms.',
                    )
                  : RefreshIndicator(
                      onRefresh: () => provider.load(),
                      child: ListView.builder(
                        itemCount: provider.students.length,
                        itemBuilder: (context, index) {
                          final student = provider.students[index];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            child: ListTile(
                              title: Text(student.fullName),
                              subtitle: Text(student.email ?? student.studentNo),
                              trailing: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Text(student.studentNo),
                                  PopupMenuButton<String>(
                                    onSelected: (value) {
                                      if (value == 'edit') {
                                        _openStudentForm(context, existing: student);
                                      } else if (value == 'delete') {
                                        _deleteStudent(context, student);
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

  Future<void> _openStudentForm(BuildContext context, {StudentModel? existing}) async {
    final result = await showDialog<_StudentFormResult>(
      context: context,
      builder: (context) => _StudentFormDialog(initial: existing),
    );

    if (!context.mounted) return;
    if (result == null) return;
    final payload = {
      'full_name': result.fullName,
      'student_no': result.studentNo,
      'email': result.email,
      if (result.phone != null) 'phone': result.phone,
    };

    final provider = context.read<StudentsProvider>();
    final success = existing == null
        ? await provider.createStudent(payload)
        : await provider.updateStudent(existing.id, payload);

    if (!context.mounted) return;

    if (success) {
      if (existing == null && provider.generatedPassword != null) {
        await showDialog<void>(
          context: context,
          builder: (context) => AlertDialog(
            title: const Text('Student created'),
            content: Text('Generated password: ${provider.generatedPassword}'),
            actions: [
              TextButton(
                onPressed: () => Navigator.of(context).pop(),
                child: const Text('Close'),
              ),
            ],
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(existing == null ? 'Student created.' : 'Student updated.')),
        );
      }
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(provider.errorMessage ?? 'Unable to save student.')),
      );
    }
  }

  Future<void> _deleteStudent(BuildContext context, StudentModel student) async {
    final confirm = await showConfirmDialog(
      context: context,
      title: 'Delete student',
      message: 'Are you sure you want to delete ${student.fullName}?',
      confirmLabel: 'Delete',
    );
    if (!context.mounted) return;
    if (!confirm) return;

    final provider = context.read<StudentsProvider>();
    final success = await provider.deleteStudent(student.id);
    if (!context.mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(success ? 'Student deleted.' : provider.errorMessage ?? 'Delete failed.'),
      ),
    );
  }
}

class _StudentFormResult {
  final String fullName;
  final String studentNo;
  final String email;
  final String? phone;

  _StudentFormResult({
    required this.fullName,
    required this.studentNo,
    required this.email,
    this.phone,
  });
}

class _StudentFormDialog extends StatefulWidget {
  final StudentModel? initial;

  const _StudentFormDialog({this.initial});

  @override
  State<_StudentFormDialog> createState() => _StudentFormDialogState();
}

class _StudentFormDialogState extends State<_StudentFormDialog> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _nameController;
  late final TextEditingController _studentNoController;
  late final TextEditingController _emailController;
  late final TextEditingController _phoneController;

  @override
  void initState() {
    super.initState();
    _nameController = TextEditingController(text: widget.initial?.fullName ?? '');
    _studentNoController = TextEditingController(text: widget.initial?.studentNo ?? '');
    _emailController = TextEditingController(text: widget.initial?.email ?? '');
    _phoneController = TextEditingController(text: widget.initial?.phone ?? '');
  }

  @override
  void dispose() {
    _nameController.dispose();
    _studentNoController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text(widget.initial == null ? 'New student' : 'Edit student'),
      content: SizedBox(
        width: 360,
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              AppTextField(
                controller: _nameController,
                label: 'Full name',
                validator: (value) => value == null || value.isEmpty ? 'Required' : null,
              ),
              const SizedBox(height: 12),
              AppTextField(
                controller: _studentNoController,
                label: 'Student number',
                validator: (value) => value == null || value.isEmpty ? 'Required' : null,
              ),
              const SizedBox(height: 12),
              AppTextField(
                controller: _emailController,
                label: 'Email',
                keyboardType: TextInputType.emailAddress,
                validator: (value) => value == null || value.isEmpty ? 'Required' : null,
              ),
              const SizedBox(height: 12),
              AppTextField(
                controller: _phoneController,
                label: 'Phone (optional)',
                keyboardType: TextInputType.phone,
              ),
            ],
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
    Navigator.of(context).pop(_StudentFormResult(
      fullName: _nameController.text.trim(),
      studentNo: _studentNoController.text.trim(),
      email: _emailController.text.trim(),
      phone: _phoneController.text.trim().isEmpty ? null : _phoneController.text.trim(),
    ));
  }
}
