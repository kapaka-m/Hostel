import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/auth/auth_provider.dart';
import 'package:hostel_mobile/src/ui/widgets/info_row.dart';
import 'package:hostel_mobile/src/ui/widgets/section_header.dart';

class StudentProfileScreen extends StatelessWidget {
  const StudentProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;

    return Padding(
      padding: const EdgeInsets.all(16),
      child: ListView(
        children: [
          const SectionHeader(title: 'Profile'),
          const SizedBox(height: 12),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      CircleAvatar(
                        radius: 24,
                        child: Text(_initials(user?.name)),
                      ),
                      const SizedBox(width: 12),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(user?.name ?? 'Student',
                              style: Theme.of(context).textTheme.titleMedium),
                          Text(user?.email ?? 'No email',
                              style: Theme.of(context).textTheme.bodySmall),
                        ],
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  InfoRow(label: 'Role', value: user?.role ?? 'STUDENT'),
                  const SizedBox(height: 8),
                  InfoRow(
                    label: 'University ID',
                    value: user?.universityId?.toString() ?? 'N/A',
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          Card(
            child: ListTile(
              leading: const Icon(Icons.logout),
              title: const Text('Sign out'),
              onTap: auth.logout,
            ),
          ),
        ],
      ),
    );
  }

  String _initials(String? name) {
    if (name == null || name.trim().isEmpty) {
      return 'S';
    }
    final parts = name.trim().split(RegExp(r'\s+'));
    if (parts.length == 1) {
      return parts.first.substring(0, 1).toUpperCase();
    }
    return (parts[0].substring(0, 1) + parts[1].substring(0, 1)).toUpperCase();
  }
}
