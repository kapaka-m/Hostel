import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/features/dorm_admin/dorm_admin_provider.dart';
import 'package:hostel_mobile/src/ui/components/empty_state.dart';
import 'package:hostel_mobile/src/ui/components/error_card.dart';

class DormAdminStudentsScreen extends StatelessWidget {
  const DormAdminStudentsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<DormAdminProvider>();

    if (provider.isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (provider.errorMessage != null) {
      return Center(child: ErrorCard(message: provider.errorMessage!));
    }

    final students = provider.students;

    if (students.isEmpty) {
      return const EmptyState(
        title: 'No students in this dorm',
        description: 'Student records appear here once they are created.',
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: students.length,
      itemBuilder: (context, index) {
        final student = students[index];
        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          child: ListTile(
            title: Text(student.fullName),
            subtitle: Text('${student.studentNo} • ${student.email ?? ''}'),
          ),
        );
      },
    );
  }
}
