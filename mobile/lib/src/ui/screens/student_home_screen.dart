import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/domain/models/announcement_model.dart';
import 'package:hostel_mobile/src/domain/models/student_room_assignment_model.dart';
import 'package:hostel_mobile/src/domain/repositories/announcement_repository.dart';
import 'package:hostel_mobile/src/domain/repositories/student_repository.dart';
import 'package:hostel_mobile/src/features/student/student_provider.dart';
import 'package:hostel_mobile/src/ui/components/empty_state.dart';
import 'package:hostel_mobile/src/ui/components/error_card.dart';

class StudentHomeScreen extends StatefulWidget {
  const StudentHomeScreen({super.key});

  @override
  State<StudentHomeScreen> createState() => _StudentHomeScreenState();
}

class _StudentHomeScreenState extends State<StudentHomeScreen> {
  late final StudentProvider _provider;

  @override
  void initState() {
    super.initState();
    _provider = StudentProvider(
      context.read<StudentRepository>(),
      context.read<AnnouncementRepository>(),
    );
    _provider.load();
  }

  @override
  void dispose() {
    _provider.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider<StudentProvider>.value(
      value: _provider,
      child: Consumer<StudentProvider>(
        builder: (context, provider, child) {
          if (provider.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          if (provider.errorMessage != null) {
            return Center(child: ErrorCard(message: provider.errorMessage!));
          }

          final assignment = provider.assignment;
          return Padding(
            padding: const EdgeInsets.all(16.0),
            child: ListView(
              children: [
                Text('Dashboard', style: Theme.of(context).textTheme.headlineSmall),
                const SizedBox(height: 16),
                if (assignment == null || assignment.room == null)
                  const EmptyState(
                    title: 'No room assignment yet',
                    description: 'Contact your dorm admin to secure housing.',
                  )
                else
                  _RoomCard(assignment: assignment),
                const SizedBox(height: 24),
                Text('Announcements', style: Theme.of(context).textTheme.titleLarge),
                const SizedBox(height: 12),
                if (provider.announcements.isEmpty)
                  const EmptyState(
                    title: 'No announcements',
                    description: 'Important updates will appear here.',
                  )
                else
                  ...provider.announcements
                      .map((announcement) => _AnnouncementPreview(announcement: announcement))
                      ,
              ],
            ),
          );
        },
      ),
    );
  }
}

class _RoomCard extends StatelessWidget {
  final StudentRoomAssignment assignment;

  const _RoomCard({
    required this.assignment,
  });

  @override
  Widget build(BuildContext context) {
    final room = assignment.room;
    final dorm = assignment.dorm;
    final floor = assignment.floor;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Assigned Room', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            Text('Dorm: ${dorm?.name ?? 'Unknown'}'),
            Text('Floor: ${floor?.number ?? 'N/A'}'),
            Text('Room: ${room?.roomNumber ?? 'N/A'}'),
            Text('Capacity: ${room?.capacity ?? 0} | Occupancy: ${room?.occupancy ?? 0}'),
            const SizedBox(height: 12),
            Text('Occupants', style: Theme.of(context).textTheme.titleSmall),
            Wrap(
              spacing: 8,
              children: assignment.occupants
                  .map((student) => Chip(label: Text(student.fullName)))
                  .toList(),
            ),
          ],
        ),
      ),
    );
  }
}

class _AnnouncementPreview extends StatelessWidget {
  final AnnouncementModel announcement;

  const _AnnouncementPreview({
    required this.announcement,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: ListTile(
        title: Text(announcement.title),
        subtitle: Text(announcement.body, maxLines: 2, overflow: TextOverflow.ellipsis),
        trailing: Text(announcement.currentStatus ?? ''),
      ),
    );
  }
}
