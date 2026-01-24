import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/floor.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';

class FloorsTab extends StatefulWidget {
  const FloorsTab({super.key});

  @override
  State<FloorsTab> createState() => _FloorsTabState();
}

class _FloorsTabState extends State<FloorsTab> {
  final List<Floor> _floors = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadFloors();
  }

  Future<void> _loadFloors({ApiService? api}) async {
    final service = api ?? context.read<AuthProvider>().api;
    setState(() {
      _loading = true;
    });

    try {
      final floors = await service.getFloors();
      _floors
        ..clear()
        ..addAll(floors);
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
    if (!context.mounted) {
      return;
    }
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(error.toString().replaceFirst('Exception: ', ''))),
    );
  }

  Future<void> _showFloorDialog({Floor? floor}) async {
    final numberController = TextEditingController(
      text: floor != null ? floor.number.toString() : '',
    );
    final bathroomsController = TextEditingController(
      text: floor != null ? floor.bathrooms.toString() : '4',
    );
    final kitchensController = TextEditingController(
      text: floor != null ? floor.kitchens.toString() : '2',
    );
    final showersController = TextEditingController(
      text: floor != null ? floor.showers.toString() : '2',
    );
    final api = context.read<AuthProvider>().api;

    final result = await showDialog<bool>(
      context: context,
      builder: (context) {
        bool saving = false;
        return StatefulBuilder(
          builder: (context, setState) {
            return AlertDialog(
              title: Text(floor == null ? 'Add Floor' : 'Edit Floor'),
              content: SingleChildScrollView(
                child: Column(
                  children: [
                    TextField(
                      controller: numberController,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Number'),
                    ),
                    TextField(
                      controller: bathroomsController,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Bathrooms'),
                    ),
                    TextField(
                      controller: kitchensController,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Kitchens'),
                    ),
                    TextField(
                      controller: showersController,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Showers'),
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
                            final number =
                                int.tryParse(numberController.text) ?? 0;
                            final bathrooms =
                                int.tryParse(bathroomsController.text) ?? 4;
                            final kitchens =
                                int.tryParse(kitchensController.text) ?? 2;
                            final showers =
                                int.tryParse(showersController.text) ?? 2;

                            if (floor == null) {
                              await api.createFloor(
                                number: number,
                                bathrooms: bathrooms,
                                kitchens: kitchens,
                                showers: showers,
                              );
                            } else {
                              await api.updateFloor(
                                floor.id,
                                number: number,
                                bathrooms: bathrooms,
                                kitchens: kitchens,
                                showers: showers,
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

    numberController.dispose();
    bathroomsController.dispose();
    kitchensController.dispose();
    showersController.dispose();

    if (!mounted) {
      return;
    }

    if (result == true) {
      await _loadFloors(api: api);
    }
  }

  Future<void> _deleteFloor(Floor floor) async {
    final api = context.read<AuthProvider>().api;
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete Floor'),
        content: Text('Delete floor ${floor.number}?'),
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
      await api.deleteFloor(floor.id);
      await _loadFloors(api: api);
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
      onRefresh: _loadFloors,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _floors.length + 1,
        itemBuilder: (context, index) {
          if (index == 0) {
            return Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: FilledButton.icon(
                onPressed: () => _showFloorDialog(),
                icon: const Icon(Icons.add),
                label: const Text('Add Floor'),
              ),
            );
          }

          final floor = _floors[index - 1];
          return Card(
            child: ListTile(
              title: Text('Floor ${floor.number}'),
              subtitle: Text(
                'Bathrooms: ${floor.bathrooms}  Kitchens: ${floor.kitchens}  Showers: ${floor.showers}',
              ),
              trailing: Wrap(
                spacing: 8,
                children: [
                  IconButton(
                    icon: const Icon(Icons.edit),
                    onPressed: () => _showFloorDialog(floor: floor),
                  ),
                  IconButton(
                    icon: const Icon(Icons.delete_outline),
                    onPressed: () => _deleteFloor(floor),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
