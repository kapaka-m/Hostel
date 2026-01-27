import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/models/floor_model.dart';
import 'package:hostel_mobile/src/models/room_model.dart';
import 'package:hostel_mobile/src/providers/floors_provider.dart';
import 'package:hostel_mobile/src/providers/rooms_provider.dart';
import 'package:hostel_mobile/src/ui/widgets/app_text_field.dart';
import 'package:hostel_mobile/src/ui/widgets/confirm_dialog.dart';
import 'package:hostel_mobile/src/ui/widgets/empty_state.dart';
import 'package:hostel_mobile/src/ui/widgets/error_state.dart';
import 'package:hostel_mobile/src/ui/widgets/loading_state.dart';
import 'package:hostel_mobile/src/ui/widgets/section_header.dart';
import 'package:hostel_mobile/src/ui/widgets/status_badge.dart';

class DormAdminRoomsScreen extends StatefulWidget {
  const DormAdminRoomsScreen({super.key});

  @override
  State<DormAdminRoomsScreen> createState() => _DormAdminRoomsScreenState();
}

class _DormAdminRoomsScreenState extends State<DormAdminRoomsScreen> {
  int? _selectedFloorId;

  @override
  void initState() {
    super.initState();
    context.read<RoomsProvider>().load();
    context.read<FloorsProvider>().load();
  }

  @override
  Widget build(BuildContext context) {
    final roomsProvider = context.watch<RoomsProvider>();
    final floorsProvider = context.watch<FloorsProvider>();

    if (roomsProvider.isLoading && roomsProvider.rooms.isEmpty) {
      return const LoadingState(message: 'Loading rooms...');
    }

    if (roomsProvider.errorMessage != null && roomsProvider.rooms.isEmpty) {
      return ErrorState(
        message: roomsProvider.errorMessage!,
        onRetry: () => roomsProvider.load(floorId: _selectedFloorId),
      );
    }

    final rooms = roomsProvider.rooms;

    return Scaffold(
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            const SectionHeader(title: 'Rooms'),
            const SizedBox(height: 12),
            _FiltersRow(
              floors: floorsProvider.floors,
              selectedFloorId: _selectedFloorId,
              onFloorChanged: (value) {
                setState(() => _selectedFloorId = value);
                roomsProvider.load(floorId: value);
              },
              onCreate: () => _openRoomForm(context),
            ),
            const SizedBox(height: 12),
            Expanded(
              child: rooms.isEmpty
                  ? const EmptyState(
                      title: 'No rooms yet',
                      description: 'Create rooms to start assigning students.',
                    )
                  : RefreshIndicator(
                      onRefresh: () => roomsProvider.load(floorId: _selectedFloorId),
                      child: ListView.builder(
                        itemCount: rooms.length,
                        itemBuilder: (context, index) {
                          final room = rooms[index];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 12),
                            child: ListTile(
                              title: Text('Room ${room.roomNumber}'),
                              subtitle: Text('Occupancy: ${room.occupancy}/${room.capacity}'),
                              trailing: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  StatusBadge(label: room.status, kind: StatusKind.room),
                                  const SizedBox(width: 8),
                                  PopupMenuButton<String>(
                                    onSelected: (value) {
                                      if (value == 'edit') {
                                        _openRoomForm(context, existing: room);
                                      } else if (value == 'delete') {
                                        _deleteRoom(context, room);
                                      }
                                    },
                                    itemBuilder: (context) => const [
                                      PopupMenuItem(value: 'edit', child: Text('Edit')),
                                      PopupMenuItem(value: 'delete', child: Text('Delete')),
                                    ],
                                  ),
                                ],
                              ),
                              onTap: () => context.go('/dorm-admin/rooms/${room.id}'),
                            ),
                          );
                        },
                      ),
                    ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _openRoomForm(BuildContext context, {RoomModel? existing}) async {
    final floorsProvider = context.read<FloorsProvider>();
    if (floorsProvider.floors.isEmpty) {
      await floorsProvider.load();
      if (!context.mounted) return;
    }

    final result = await showDialog<_RoomFormResult>(
      context: context,
      builder: (context) => _RoomFormDialog(
        floors: floorsProvider.floors,
        initial: existing,
      ),
    );

    if (!context.mounted) return;
    if (result == null) return;
    final payload = {
      'floor_id': result.floorId,
      'room_number': result.roomNumber,
      'capacity': result.capacity,
    };

    final roomsProvider = context.read<RoomsProvider>();
    final success = await roomsProvider.saveRoom(payload, roomId: existing?.id);
    if (!context.mounted) return;
    if (success) {
      await roomsProvider.load(floorId: _selectedFloorId);
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(existing == null ? 'Room created.' : 'Room updated.')),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(roomsProvider.errorMessage ?? 'Unable to save room.')),
      );
    }
  }

  Future<void> _deleteRoom(BuildContext context, RoomModel room) async {
    final confirm = await showConfirmDialog(
      context: context,
      title: 'Delete room',
      message: 'Are you sure you want to delete room ${room.roomNumber}?',
      confirmLabel: 'Delete',
    );
    if (!context.mounted) return;
    if (!confirm) return;

    final provider = context.read<RoomsProvider>();
    final success = await provider.deleteRoom(room.id);
    if (!context.mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(success ? 'Room deleted.' : provider.errorMessage ?? 'Delete failed.'),
      ),
    );
  }
}

class _FiltersRow extends StatelessWidget {
  final List<FloorModel> floors;
  final int? selectedFloorId;
  final ValueChanged<int?> onFloorChanged;
  final VoidCallback onCreate;

  const _FiltersRow({
    required this.floors,
    required this.selectedFloorId,
    required this.onFloorChanged,
    required this.onCreate,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: DropdownButtonFormField<int?>(
            initialValue: selectedFloorId,
            decoration: const InputDecoration(labelText: 'Filter by floor'),
            items: [
              const DropdownMenuItem<int?>(
                value: null,
                child: Text('All floors'),
              ),
              ...floors.map(
                (floor) => DropdownMenuItem<int?>(
                  value: floor.id,
                  child: Text('Floor ${floor.number}'),
                ),
              ),
            ],
            onChanged: onFloorChanged,
          ),
        ),
        const SizedBox(width: 12),
        ElevatedButton.icon(
          onPressed: onCreate,
          icon: const Icon(Icons.add),
          label: const Text('New room'),
        ),
      ],
    );
  }
}

class _RoomFormResult {
  final int floorId;
  final String roomNumber;
  final int capacity;

  _RoomFormResult({
    required this.floorId,
    required this.roomNumber,
    required this.capacity,
  });
}

class _RoomFormDialog extends StatefulWidget {
  final List<FloorModel> floors;
  final RoomModel? initial;

  const _RoomFormDialog({
    required this.floors,
    this.initial,
  });

  @override
  State<_RoomFormDialog> createState() => _RoomFormDialogState();
}

class _RoomFormDialogState extends State<_RoomFormDialog> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _roomNumberController;
  late final TextEditingController _capacityController;
  int? _floorId;

  @override
  void initState() {
    super.initState();
    _roomNumberController = TextEditingController(text: widget.initial?.roomNumber ?? '');
    _capacityController =
        TextEditingController(text: widget.initial?.capacity.toString() ?? '4');
    _floorId = widget.initial?.floorId ?? (widget.floors.isNotEmpty ? widget.floors.first.id : null);
  }

  @override
  void dispose() {
    _roomNumberController.dispose();
    _capacityController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text(widget.initial == null ? 'New room' : 'Edit room'),
      content: SizedBox(
        width: 360,
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              DropdownButtonFormField<int>(
                initialValue: _floorId,
                decoration: const InputDecoration(labelText: 'Floor'),
                items: widget.floors
                    .map((floor) => DropdownMenuItem<int>(
                          value: floor.id,
                          child: Text('Floor ${floor.number}'),
                        ))
                    .toList(),
                onChanged: (value) => setState(() => _floorId = value),
                validator: (value) => value == null ? 'Select a floor' : null,
              ),
              const SizedBox(height: 12),
              AppTextField(
                controller: _roomNumberController,
                label: 'Room number',
                validator: (value) => value == null || value.isEmpty ? 'Required' : null,
              ),
              const SizedBox(height: 12),
              AppTextField(
                controller: _capacityController,
                label: 'Capacity',
                keyboardType: TextInputType.number,
                validator: (value) => value == null || value.isEmpty ? 'Required' : null,
              ),
            ],
          ),
        ),
      ),
      actions: [
        TextButton(onPressed: () => Navigator.of(context).pop(), child: const Text('Cancel')),
        ElevatedButton(
          onPressed: _submit,
          child: const Text('Save'),
        ),
      ],
    );
  }

  void _submit() {
    if (!_formKey.currentState!.validate()) return;
    final capacity = int.tryParse(_capacityController.text.trim()) ?? 0;
    Navigator.of(context).pop(_RoomFormResult(
      floorId: _floorId!,
      roomNumber: _roomNumberController.text.trim(),
      capacity: capacity,
    ));
  }
}
