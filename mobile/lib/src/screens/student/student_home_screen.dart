import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';

import '../../models/my_room.dart';
import '../../providers/auth_provider.dart';

class StudentHomeScreen extends StatefulWidget {
  const StudentHomeScreen({super.key});

  @override
  State<StudentHomeScreen> createState() => _StudentHomeScreenState();
}

class _StudentHomeScreenState extends State<StudentHomeScreen> {
  MyRoomInfo? _info;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadInfo();
  }

  Future<void> _loadInfo() async {
    setState(() {
      _loading = true;
    });

    try {
      _info = await context.read<AuthProvider>().api.getMyRoom();
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Room'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () async {
              await context.read<AuthProvider>().logout();
              if (context.mounted) {
                context.go('/login');
              }
            },
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadInfo,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  if (_info?.room == null)
                    const Card(
                      child: Padding(
                        padding: EdgeInsets.all(16),
                        child: Text('No room assigned yet.'),
                      ),
                    )
                  else ...[
                    Card(
                      child: ListTile(
                        title: Text(_info?.dorm?.name ?? ''),
                        subtitle: Text(
                          'Floor ${_info?.floor?.number} | Room ${_info?.room?.roomNumber}',
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'Occupants',
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                    const SizedBox(height: 8),
                    ..._info!.occupants.map((student) {
                      return Card(
                        child: ListTile(
                          title: Text(student.fullName),
                          subtitle: Text(
                            '${student.studentNo} | ${student.email ?? ''}',
                          ),
                          trailing: Text(student.phone ?? ''),
                        ),
                      );
                    }),
                    if (_info!.occupants.isEmpty)
                      const Text('No other occupants.'),
                  ],
                ],
              ),
            ),
    );
  }
}
