import 'package:flutter/material.dart';

import 'package:hostel_mobile/src/ui/screens/tickets_screen.dart';

class UniversityAdminTicketsScreen extends StatelessWidget {
  const UniversityAdminTicketsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return const TicketsScreen(basePath: '/university-admin/tickets');
  }
}
