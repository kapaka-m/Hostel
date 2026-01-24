import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';

import '../../models/floor.dart';
import '../../models/room.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';

class RoomsTab extends StatefulWidget {
  const RoomsTab({super.key});

  @override
  State<RoomsTab> createState() => _RoomsTabState();
}

class _RoomsTabState extends State<RoomsTab> {
  List<Floor> _floors = [];
  List<Room> _rooms = [];
  int? _selectedFloorId;
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
      final floors = await api.getFloors();
      _floors = floors;
      _selectedFloorId ??= floors.isNotEmpty ? floors.first.id : null;
      _rooms = await api.getRooms(floorId: _selectedFloorId);
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

  Future<void> _loadRooms({ApiService? api}) async {
    try {
      final service = api ?? context.read<AuthProvider>().api;
      _rooms = await service.getRooms(floorId: _selectedFloorId);
      if (mounted) {
        setState(() {});
      }
    } catch (error) {
      _showMessage(error);
    }
  }

  void _showMessage(Object error) {
    if (!context.mounted) {
      return;
    }
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(error.toString().replaceFirst('Exception: ', ''))),
    );
  }

  Future<void> _showRoomDialog({Room? room}) async {
    final roomNumberController = TextEditingController(
      text: room?.roomNumber ?? '',
    );
    final capacityController = TextEditingController(
      text: room != null ? room.capacity.toString() : '4',
    );
    int? selectedFloorId = room?.floorId ?? _selectedFloorId;

    final api = context.read<AuthProvider>().api;
    final result = await showDialog<bool>(
      context: context,
      builder: (context) {
        bool saving = false;
        return StatefulBuilder(
          builder: (context, setState) {
            return AlertDialog(
              title: Text(room == null ? 'Add Room' : 'Edit Room'),
              content: SingleChildScrollView(
                child: Column(
                  children: [
                    DropdownButtonFormField<int>(
                      initialValue: selectedFloorId,
                      decoration: const InputDecoration(labelText: 'Floor'),
                      items: _floors
                          .map(
                            (floor) => DropdownMenuItem(
                              value: floor.id,
                              child: Text('Floor ${floor.number}'),
                            ),
                          )
                          .toList(),
                      onChanged: (value) {
                        setState(() {
                          selectedFloorId = value;
                        });
                      },
                    ),
                    TextField(
                      controller: roomNumberController,
                      decoration: const InputDecoration(
                        labelText: 'Room Number',
                      ),
                    ),
                    TextField(
                      controller: capacityController,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Capacity'),
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: saving
                      ? null
                      : () => Navigator.pop(context, false),
                  child: const Text('Cancel'),
                ),
                FilledButton(
                  onPressed: saving
                      ? null
                      : () async {
                          setState(() {
                            saving = true;
                          });
                          try {
                            final api = context.read<AuthProvider>().api;
                            final floorId = selectedFloorId ?? 0;
                            final capacity =
                                int.tryParse(capacityController.text) ?? 4;

                            if (room == null) {
                              await api.createRoom(
                                floorId: floorId,
                                roomNumber: roomNumberController.text.trim(),
                                capacity: capacity,
                              );
                            } else {
                              await api.updateRoom(
                                room.id,
                                floorId: floorId,
                                roomNumber: roomNumberController.text.trim(),
                                capacity: capacity,
                              );
                            }

                            if (context.mounted) {
                              Navigator.pop(context, true);
                            }
                          } catch (error) {
                            if (context.mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(
                                    error.toString().replaceFirst(
                                      'Exception: ',
                                      '',
                                    ),
                                  ),
                                ),
                              );
                            }
                          } finally {
                            if (context.mounted) {
                              setState(() {
                                saving = false;
                              });
                            }
                          }
                        },
                  child: saving
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Text('Save'),
                ),
              ],
            );
          },
        );
      },
    );

    roomNumberController.dispose();
    capacityController.dispose();

    if (!mounted) {
      return;
    }

    if (result == true) {
      await _loadRooms(api: api);
    }
  }

  Future<void> _deleteRoom(Room room) async {
    final api = context.read<AuthProvider>().api;
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete Room'),
        content: Text('Delete room ${room.roomNumber}?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Delete'),
          ),
        ],
      ),
    );

    if (confirmed != true) {
      return;
    }

    if (!mounted) {
      return;
    }

    try {
      await api.deleteRoom(room.id);
      await _loadRooms(api: api);
    } catch (error) {
      _showMessage(error);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Center(child: CircularProgressIndicator());
    }

    return RefreshIndicator(
      onRefresh: _loadData,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          FilledButton.icon(
            onPressed: () => _showRoomDialog(),
            icon: const Icon(Icons.add),
            label: const Text('Add Room'),
          ),
          const SizedBox(height: 16),
          DropdownButtonFormField<int>(
            initialValue: _selectedFloorId,
            decoration: const InputDecoration(
              labelText: 'Filter by Floor',
              border: OutlineInputBorder(),
            ),
            items: _floors
                .map(
                  (floor) => DropdownMenuItem(
                    value: floor.id,
                    child: Text('Floor ${floor.number}'),
                  ),
                )
                .toList(),
            onChanged: (value) {
              setState(() {
                _selectedFloorId = value;
              });
              _loadRooms();
            },
          ),
          const SizedBox(height: 16),
          ..._rooms.map((room) {
            return Card(
              child: ListTile(
                title: Text('Room ${room.roomNumber}'),
                subtitle: Text(
                  'Status: ${room.status}  |  Occupancy: ${room.occupancy}/${room.capacity}',
                ),
                onTap: () => context.push('/dorm/rooms/${room.id}'),
                trailing: Wrap(
                  spacing: 8,
                  children: [
                    IconButton(
                      icon: const Icon(Icons.edit),
                      onPressed: () => _showRoomDialog(room: room),
                    ),
                    IconButton(
                      icon: const Icon(Icons.delete_outline),
                      onPressed: () => _deleteRoom(room),
                    ),
                  ],
                ),
              ),
            );
          }),
        ],
      ),
    );
  }
}
