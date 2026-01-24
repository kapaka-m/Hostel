import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';

import '../../providers/auth_provider.dart';
import 'create_dorm_admin_tab.dart';
import 'dorms_tab.dart';

class UniversityHomeScreen extends StatelessWidget {
  const UniversityHomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 2,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('University Admin'),
          actions: [
            IconButton(
              icon: const Icon(Icons.logout),
              onPressed: () async {
                await context.read<AuthProvider>().logout();
                if (context.mounted) {
                  context.go('/login');
                }
              },
            ),
          ],
          bottom: const TabBar(
            tabs: [
              Tab(text: 'Dorms'),
              Tab(text: 'Create Dorm Admin'),
            ],
          ),
        ),
        body: const TabBarView(children: [DormsTab(), CreateDormAdminTab()]),
      ),
    );
  }
}
