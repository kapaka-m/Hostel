import 'package:flutter/material.dart';

import 'package:hostel_mobile/src/ui/components/empty_state.dart';

class StudentTicketsStubScreen extends StatelessWidget {
  const StudentTicketsStubScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return const Padding(
      padding: EdgeInsets.all(32.0),
      child: EmptyState(
        title: 'Tickets not supported',
        description: 'This backend does not expose ticket APIs for students yet.',
      ),
    );
  }
}
