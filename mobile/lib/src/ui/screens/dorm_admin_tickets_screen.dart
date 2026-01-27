import 'package:flutter/material.dart';

import 'package:hostel_mobile/src/ui/screens/tickets_screen.dart';

class DormAdminTicketsScreen extends StatelessWidget {
  const DormAdminTicketsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return const TicketsScreen(basePath: '/dorm-admin/tickets');
  }
}
