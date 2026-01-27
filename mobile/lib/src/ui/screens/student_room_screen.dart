import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/models/student_room_assignment_model.dart';
import 'package:hostel_mobile/src/providers/student_provider.dart';
import 'package:hostel_mobile/src/ui/widgets/empty_state.dart';
import 'package:hostel_mobile/src/ui/widgets/error_state.dart';
import 'package:hostel_mobile/src/ui/widgets/info_row.dart';
import 'package:hostel_mobile/src/ui/widgets/loading_state.dart';
import 'package:hostel_mobile/src/ui/widgets/section_header.dart';
import 'package:hostel_mobile/src/ui/widgets/status_badge.dart';

class StudentRoomScreen extends StatefulWidget {
  const StudentRoomScreen({super.key});

  @override
  State<StudentRoomScreen> createState() => _StudentRoomScreenState();
}

class _StudentRoomScreenState extends State<StudentRoomScreen> {
  @override
  void initState() {
    super.initState();
    context.read<StudentProvider>().load();
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<StudentProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading) {
          return const LoadingState(message: 'Loading room details...');
        }

        if (provider.errorMessage != null) {
          return ErrorState(
            message: provider.errorMessage!,
            onRetry: () => provider.load(),
          );
        }

        final assignment = provider.assignment;
        if (assignment == null || assignment.room == null) {
          return const Padding(
            padding: EdgeInsets.all(16),
            child: EmptyState(
              title: 'No room assignment',
              description: 'Contact your dorm admin to get assigned to a room.',
            ),
          );
        }

        return Padding(
          padding: const EdgeInsets.all(16),
          child: ListView(
            children: [
              const SectionHeader(title: 'Room details'),
              const SizedBox(height: 12),
              _RoomDetailsCard(assignment: assignment),
              const SizedBox(height: 24),
              const SectionHeader(title: 'Room occupants'),
              const SizedBox(height: 12),
              ...assignment.occupants.map(
                (student) => Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  child: ListTile(
                    leading: CircleAvatar(
                      child: Text(student.fullName.isNotEmpty
                          ? student.fullName.substring(0, 1).toUpperCase()
                          : 'S'),
                    ),
                    title: Text(student.fullName),
                    subtitle: Text(student.email ?? 'No email'),
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}

class _RoomDetailsCard extends StatelessWidget {
  final StudentRoomAssignment assignment;

  const _RoomDetailsCard({required this.assignment});

  @override
  Widget build(BuildContext context) {
    final dorm = assignment.dorm;
    final floor = assignment.floor;
    final room = assignment.room;
    final capacity = room?.capacity ?? 0;
    final occupancy = room?.occupancy ?? 0;
    final progress = capacity > 0 ? occupancy / capacity : 0.0;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Assigned room', style: Theme.of(context).textTheme.titleMedium),
                StatusBadge(label: room?.status, kind: StatusKind.room),
              ],
            ),
            const SizedBox(height: 12),
            InfoRow(label: 'Dorm', value: dorm?.name ?? 'Unknown'),
            const SizedBox(height: 8),
            InfoRow(label: 'Floor', value: floor?.number.toString() ?? 'N/A'),
            const SizedBox(height: 8),
            InfoRow(label: 'Room', value: room?.roomNumber ?? 'N/A'),
            const SizedBox(height: 8),
            InfoRow(label: 'Capacity', value: '$occupancy / $capacity occupied'),
            const SizedBox(height: 8),
            LinearProgressIndicator(value: progress, minHeight: 8),
          ],
        ),
      ),
    );
  }
}
