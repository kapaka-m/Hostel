import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/features/dorm_admin/dorm_admin_provider.dart';
import 'package:hostel_mobile/src/ui/components/empty_state.dart';
import 'package:hostel_mobile/src/ui/components/error_card.dart';

class DormAdminRoomsScreen extends StatelessWidget {
  const DormAdminRoomsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<DormAdminProvider>();

    if (provider.isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (provider.errorMessage != null) {
      return Center(child: ErrorCard(message: provider.errorMessage!));
    }

    final rooms = provider.rooms;

    if (rooms.isEmpty) {
      return const EmptyState(
        title: 'No rooms yet',
        description: 'Create rooms in the backend panel to manage them here.',
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: rooms.length,
      itemBuilder: (context, index) {
        final room = rooms[index];
        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          child: ListTile(
            title: Text('Room ${room.roomNumber}'),
            subtitle: Text('Occupancy: ${room.occupancy}/${room.capacity}'),
            trailing: Text(room.status ?? ''),
            onTap: () => context.go('/dorm-admin/rooms/${room.id}'),
          ),
        );
      },
    );
  }
}
