import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';

import '../../providers/auth_provider.dart';
import 'floors_tab.dart';
import 'rooms_tab.dart';
import 'students_tab.dart';

class DormHomeScreen extends StatefulWidget {
  const DormHomeScreen({super.key});

  @override
  State<DormHomeScreen> createState() => _DormHomeScreenState();
}

class _DormHomeScreenState extends State<DormHomeScreen> {
  int _index = 0;

  final List<Widget> _pages = const [FloorsTab(), RoomsTab(), StudentsTab()];

  final List<String> _titles = const ['Floors', 'Rooms', 'Students'];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_titles[_index]),
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
      body: IndexedStack(index: _index, children: _pages),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _index,
        onTap: (value) {
          setState(() {
            _index = value;
          });
        },
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.layers), label: 'Floors'),
          BottomNavigationBarItem(
            icon: Icon(Icons.meeting_room),
            label: 'Rooms',
          ),
          BottomNavigationBarItem(icon: Icon(Icons.people), label: 'Students'),
        ],
      ),
    );
  }
}
