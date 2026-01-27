import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/domain/models/ticket_model.dart';
import 'package:hostel_mobile/src/domain/repositories/ticket_repository.dart';
import 'package:hostel_mobile/src/ui/components/empty_state.dart';
import 'package:hostel_mobile/src/ui/components/error_card.dart';

class UniversityAdminTicketsScreen extends StatefulWidget {
  const UniversityAdminTicketsScreen({super.key});

  @override
  State<UniversityAdminTicketsScreen> createState() => _UniversityAdminTicketsScreenState();
}

class _UniversityAdminTicketsScreenState extends State<UniversityAdminTicketsScreen> {
  late final Future<List<TicketModel>> _ticketsFuture;

  @override
  void initState() {
    super.initState();
    _ticketsFuture = context.read<TicketRepository>().fetchTickets();
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<List<TicketModel>>(
      future: _ticketsFuture,
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return const Center(child: CircularProgressIndicator());
        }

        if (snapshot.hasError) {
          return const Center(child: ErrorCard(message: 'Unable to load tickets.'));
        }

        final tickets = snapshot.data ?? [];
        if (tickets.isEmpty) {
          return const EmptyState(
            title: 'No tickets',
            description: 'No tickets have been created yet.',
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
                subtitle: Text('Status: ${ticket.status ?? 'Unknown'}'),
                trailing: Text(ticket.priority ?? ''),
              ),
            );
          },
        );
      },
    );
  }
}
