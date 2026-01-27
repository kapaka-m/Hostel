import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/features/dorm_admin/dorm_admin_provider.dart';
import 'package:hostel_mobile/src/ui/components/empty_state.dart';
import 'package:hostel_mobile/src/ui/components/error_card.dart';

class DormAdminTicketsScreen extends StatelessWidget {
  const DormAdminTicketsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<DormAdminProvider>();

    if (provider.isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (provider.errorMessage != null) {
      return Center(child: ErrorCard(message: provider.errorMessage!));
    }

    final tickets = provider.tickets;

    if (tickets.isEmpty) {
      return const EmptyState(
        title: 'No tickets',
        description: 'Tickets created by dorm admins surface here.',
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: tickets.length,
      itemBuilder: (context, index) {
        final ticket = tickets[index];
        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          child: ListTile(
            title: Text(ticket.subject),
            subtitle: Text('Status: ${ticket.status ?? 'Unknown'} | Priority: ${ticket.priority ?? 'Medium'}'),
          ),
        );
      },
    );
  }
}
