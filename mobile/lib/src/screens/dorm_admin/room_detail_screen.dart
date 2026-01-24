import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/room.dart';
import '../../models/student.dart';
import '../../providers/auth_provider.dart';

class RoomDetailScreen extends StatefulWidget {
  const RoomDetailScreen({super.key, required this.roomId});

  final int roomId;

  @override
  State<RoomDetailScreen> createState() => _RoomDetailScreenState();
}

class _RoomDetailScreenState extends State<RoomDetailScreen> {
  Room? _room;
  List<Student> _occupants = [];
  List<Student> _students = [];
  int? _selectedStudentId;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() {
      _loading = true;
    });

    try {
      final api = context.read<AuthProvider>().api;
      final results = await Future.wait([
        api.getRoom(widget.roomId),
        api.getRoomOccupants(widget.roomId),
        api.getStudents(),
      ]);
      _room = results[0] as Room;
      _occupants = results[1] as List<Student>;
      _students = results[2] as List<Student>;
      _selectedStudentId ??= _students.isNotEmpty ? _students.first.id : null;
    } catch (error) {
      _showMessage(error);
    } finally {
      if (mounted) {
        setState(() {
          _loading = false;
        });
      }
    }
  }

  void _showMessage(Object error) {
    if (!mounted) {
      return;
    }
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(error.toString().replaceFirst('Exception: ', ''))),
    );
  }

  Future<void> _assignStudent() async {
    if (_selectedStudentId == null) {
      _showMessage('Select a student');
      return;
    }

    try {
      await context.read<AuthProvider>().api.assignStudent(
        roomId: widget.roomId,
        studentId: _selectedStudentId!,
      );
      await _loadData();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Student assigned successfully.')),
        );
      }
    } catch (error) {
      _showMessage(error);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    final room = _room;
    if (room == null) {
      return const Scaffold(body: Center(child: Text('Room not found.')));
    }

    return Scaffold(
      appBar: AppBar(title: Text('Room ${room.roomNumber}')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: ListTile(
              title: Text('Status: ${room.status}'),
              subtitle: Text('Occupancy: ${room.occupancy}/${room.capacity}'),
            ),
          ),
          const SizedBox(height: 16),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Assign Student',
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<int>(
                    initialValue: _selectedStudentId,
                    decoration: const InputDecoration(
                      border: OutlineInputBorder(),
                      labelText: 'Student',
                    ),
                    items: _students
                        .map(
                          (student) => DropdownMenuItem(
                            value: student.id,
                            child: Text(
                              '${student.fullName} (${student.studentNo})',
                            ),
                          ),
                        )
                        .toList(),
                    onChanged: (value) {
                      setState(() {
                        _selectedStudentId = value;
                      });
                    },
                  ),
                  const SizedBox(height: 12),
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton(
                      onPressed: _assignStudent,
                      child: const Text('Assign'),
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          Text('Occupants', style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 8),
          ..._occupants.map((student) {
            return Card(
              child: ListTile(
                title: Text(student.fullName),
                subtitle: Text('${student.studentNo} | ${student.email ?? ''}'),
                trailing: Text(student.phone ?? ''),
              ),
            );
          }),
          if (_occupants.isEmpty)
            const Padding(
              padding: EdgeInsets.only(top: 8),
              child: Text('No occupants yet.'),
            ),
        ],
      ),
    );
  }
}
