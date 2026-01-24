import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/create_student_result.dart';
import '../../models/student.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';

class StudentsTab extends StatefulWidget {
  const StudentsTab({super.key});

  @override
  State<StudentsTab> createState() => _StudentsTabState();
}

class _StudentsTabState extends State<StudentsTab> {
  List<Student> _students = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadStudents();
  }

  Future<void> _loadStudents({ApiService? api}) async {
    setState(() {
      _loading = true;
    });

    try {
      final service = api ?? context.read<AuthProvider>().api;
      _students = await service.getStudents();
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
    if (!context.mounted) {
      return;
    }
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(error.toString().replaceFirst('Exception: ', ''))),
    );
  }

  Future<void> _showStudentDialog({Student? student}) async {
    final fullNameController = TextEditingController(text: student?.fullName);
    final studentNoController = TextEditingController(text: student?.studentNo);
    final emailController = TextEditingController(text: student?.email);
    final phoneController = TextEditingController(text: student?.phone ?? '');

    final api = context.read<AuthProvider>().api;
    final result = await showDialog<Object?>(
      context: context,
      builder: (context) {
        bool saving = false;
        return StatefulBuilder(
          builder: (context, setState) {
            return AlertDialog(
              title: Text(student == null ? 'Add Student' : 'Edit Student'),
              content: SingleChildScrollView(
                child: Column(
                  children: [
                    TextField(
                      controller: fullNameController,
                      decoration: const InputDecoration(labelText: 'Full Name'),
                    ),
                    TextField(
                      controller: studentNoController,
                      decoration: const InputDecoration(
                        labelText: 'Student No',
                      ),
                    ),
                    TextField(
                      controller: emailController,
                      decoration: const InputDecoration(labelText: 'Email'),
                    ),
                    TextField(
                      controller: phoneController,
                      decoration: const InputDecoration(labelText: 'Phone'),
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: saving
                      ? null
                      : () => Navigator.pop(context, false),
                  child: const Text('Cancel'),
                ),
                FilledButton(
                  onPressed: saving
                      ? null
                      : () async {
                          setState(() {
                            saving = true;
                          });
                          try {
                            final api = context.read<AuthProvider>().api;
                            if (student == null) {
                              final result = await api.createStudent(
                                fullName: fullNameController.text.trim(),
                                studentNo: studentNoController.text.trim(),
                                email: emailController.text.trim(),
                                phone: phoneController.text.trim().isEmpty
                                    ? null
                                    : phoneController.text.trim(),
                              );
                              if (context.mounted) {
                                Navigator.pop(context, result);
                              }
                            } else {
                              await api.updateStudent(
                                student.id,
                                fullName: fullNameController.text.trim(),
                                studentNo: studentNoController.text.trim(),
                                email: emailController.text.trim(),
                                phone: phoneController.text.trim().isEmpty
                                    ? null
                                    : phoneController.text.trim(),
                              );
                              if (context.mounted) {
                                Navigator.pop(context, true);
                              }
                            }
                          } catch (error) {
                            if (context.mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(
                                    error.toString().replaceFirst(
                                      'Exception: ',
                                      '',
                                    ),
                                  ),
                                ),
                              );
                            }
                          } finally {
                            if (context.mounted) {
                              setState(() {
                                saving = false;
                              });
                            }
                          }
                        },
                  child: saving
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Text('Save'),
                ),
              ],
            );
          },
        );
      },
    );

    fullNameController.dispose();
    studentNoController.dispose();
    emailController.dispose();
    phoneController.dispose();

    if (!mounted) {
      return;
    }

    if (result == true) {
      await _loadStudents(api: api);
    } else if (result is CreateStudentResult) {
      await _loadStudents(api: api);
      if (mounted) {
        showDialog<void>(
          context: context,
          builder: (context) => AlertDialog(
            title: const Text('Student Created'),
            content: Text('Generated password: ${result.generatedPassword}'),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('OK'),
              ),
            ],
          ),
        );
      }
    }
  }

  Future<void> _deleteStudent(Student student) async {
    final api = context.read<AuthProvider>().api;
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete Student'),
        content: Text('Delete ${student.fullName}?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Delete'),
          ),
        ],
      ),
    );

    if (confirmed != true) {
      return;
    }

    if (!mounted) {
      return;
    }

    try {
      await api.deleteStudent(student.id);
      await _loadStudents(api: api);
    } catch (error) {
      _showMessage(error);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Center(child: CircularProgressIndicator());
    }

    return RefreshIndicator(
      onRefresh: _loadStudents,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _students.length + 1,
        itemBuilder: (context, index) {
          if (index == 0) {
            return Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: FilledButton.icon(
                onPressed: () => _showStudentDialog(),
                icon: const Icon(Icons.add),
                label: const Text('Add Student'),
              ),
            );
          }

          final student = _students[index - 1];
          return Card(
            child: ListTile(
              title: Text(student.fullName),
              subtitle: Text('${student.studentNo} | ${student.email ?? ''}'),
              trailing: Wrap(
                spacing: 8,
                children: [
                  IconButton(
                    icon: const Icon(Icons.edit),
                    onPressed: () => _showStudentDialog(student: student),
                  ),
                  IconButton(
                    icon: const Icon(Icons.delete_outline),
                    onPressed: () => _deleteStudent(student),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
