import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/repositories/room_repository.dart';
import 'package:hostel_mobile/src/models/room_model.dart';
import 'package:hostel_mobile/src/models/student_model.dart';
import 'package:hostel_mobile/src/providers/rooms_provider.dart';
import 'package:hostel_mobile/src/providers/students_provider.dart';
import 'package:hostel_mobile/src/ui/widgets/error_card.dart';

class DormAdminRoomDetailScreen extends StatefulWidget {
  final int roomId;

  const DormAdminRoomDetailScreen({
    super.key,
    required this.roomId,
  });

  @override
  State<DormAdminRoomDetailScreen> createState() => _DormAdminRoomDetailScreenState();
}

class _DormAdminRoomDetailScreenState extends State<DormAdminRoomDetailScreen> {
  late final Future<RoomModel> _roomFuture;
  late Future<List<StudentModel>> _occupantsFuture;
  int? _selectedStudentId;
  bool _isAssigning = false;

  @override
  void initState() {
    super.initState();
    final repository = context.read<RoomRepository>();
    _roomFuture = repository.fetchRoom(widget.roomId);
    _occupantsFuture = repository.fetchOccupants(widget.roomId);
    context.read<StudentsProvider>().load();
  }

  Future<void> _assignStudent() async {
    final roomsProvider = context.read<RoomsProvider>();
    if (_selectedStudentId == null) {
      return;
    }

    setState(() => _isAssigning = true);
    try {
      final success = await roomsProvider.assignStudent(widget.roomId, _selectedStudentId!);
      if (!mounted) {
        return;
      }
      if (success) {
        setState(() {
          _occupantsFuture = context.read<RoomRepository>().fetchOccupants(widget.roomId);
        });
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Student assigned successfully.')),
        );
      }
    } catch (error) {
      if (!mounted) {
        return;
      }
      final message = error is ApiException ? error.message : 'Assignment failed';
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
    } finally {
      if (mounted) {
        setState(() => _isAssigning = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final studentsProvider = context.watch<StudentsProvider>();
    final students = studentsProvider.students;

    return FutureBuilder<RoomModel>(
      future: _roomFuture,
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return const Center(child: CircularProgressIndicator());
        }

        if (snapshot.hasError) {
          return Center(child: ErrorCard(message: 'Unable to load room.'));
        }

        final room = snapshot.data!;

        return Padding(
          padding: const EdgeInsets.all(16.0),
          child: ListView(
            children: [
              Text('Room ${room.roomNumber}', style: Theme.of(context).textTheme.headlineSmall),
              const SizedBox(height: 12),
              Text('Capacity: ${room.capacity}'),
              Text('Occupancy: ${room.occupancy}'),
              Text('Status: ${room.status ?? 'Unknown'}'),
              const SizedBox(height: 24),
              Text('Assign a student', style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 8),
              DropdownButtonFormField<int>(
                initialValue: _selectedStudentId,
                items: students
                    .map((student) => DropdownMenuItem(
                          value: student.id,
                          child: Text(student.fullName),
                        ))
                    .toList(),
                decoration: const InputDecoration(labelText: 'Student'),
                onChanged: _isAssigning ? null : (value) => setState(() => _selectedStudentId = value),
              ),
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _isAssigning || _selectedStudentId == null ? null : _assignStudent,
                  child: _isAssigning
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Text('Assign Student'),
                ),
              ),
              const SizedBox(height: 24),
              Text('Occupants', style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 8),
              FutureBuilder<List<StudentModel>>(
                future: _occupantsFuture,
                builder: (context, snapshot) {
                  if (snapshot.connectionState != ConnectionState.done) {
                    return const Center(child: CircularProgressIndicator());
                  }

                  if (snapshot.hasError) {
                    return const ErrorCard(message: 'Unable to load occupants.');
                  }

                  final occupants = snapshot.data;
                  if (occupants == null || occupants.isEmpty) {
                    return const Text('No occupants found');
                  }

                  return Column(
                    children: occupants
                        .map((student) => ListTile(
                              title: Text(student.fullName),
                              subtitle: Text(student.studentNo),
                            ))
                        .toList(),
                  );
                },
              ),
            ],
          ),
        );
      },
    );
  }
}

