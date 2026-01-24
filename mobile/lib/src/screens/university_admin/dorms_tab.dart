import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/dorm.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';

class DormsTab extends StatefulWidget {
  const DormsTab({super.key});

  @override
  State<DormsTab> createState() => _DormsTabState();
}

class _DormsTabState extends State<DormsTab> {
  final List<Dorm> _dorms = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadDorms();
  }

  Future<void> _loadDorms({ApiService? api}) async {
    setState(() {
      _loading = true;
    });

    try {
      final service = api ?? context.read<AuthProvider>().api;
      final dorms = await service.getDorms();
      _dorms
        ..clear()
        ..addAll(dorms);
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

  Future<void> _showDormDialog({Dorm? dorm}) async {
    final nameController = TextEditingController(text: dorm?.name ?? '');
    final addressController = TextEditingController(text: dorm?.address ?? '');

    final api = context.read<AuthProvider>().api;
    final result = await showDialog<bool>(
      context: context,
      builder: (context) {
        bool saving = false;
        return StatefulBuilder(
          builder: (context, setState) {
            return AlertDialog(
              title: Text(dorm == null ? 'Create Dorm' : 'Edit Dorm'),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  TextField(
                    controller: nameController,
                    decoration: const InputDecoration(labelText: 'Name'),
                  ),
                  TextField(
                    controller: addressController,
                    decoration: const InputDecoration(labelText: 'Address'),
                  ),
                ],
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
                            if (dorm == null) {
                              await api.createDorm(
                                name: nameController.text.trim(),
                                address: addressController.text.trim().isEmpty
                                    ? null
                                    : addressController.text.trim(),
                              );
                            } else {
                              await api.updateDorm(
                                dorm.id,
                                name: nameController.text.trim(),
                                address: addressController.text.trim().isEmpty
                                    ? null
                                    : addressController.text.trim(),
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

    nameController.dispose();
    addressController.dispose();

    if (!mounted) {
      return;
    }

    if (result == true) {
      await _loadDorms(api: api);
    }
  }

  Future<void> _deleteDorm(Dorm dorm) async {
    final api = context.read<AuthProvider>().api;
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete Dorm'),
        content: Text('Delete ${dorm.name}?'),
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
      await api.deleteDorm(dorm.id);
      await _loadDorms(api: api);
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
      onRefresh: _loadDorms,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _dorms.length + 1,
        itemBuilder: (context, index) {
          if (index == 0) {
            return Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: FilledButton.icon(
                onPressed: () => _showDormDialog(),
                icon: const Icon(Icons.add),
                label: const Text('Add Dorm'),
              ),
            );
          }

          final dorm = _dorms[index - 1];
          return Card(
            child: ListTile(
              title: Text(dorm.name),
              subtitle: Text(dorm.address ?? 'No address'),
              trailing: Wrap(
                spacing: 8,
                children: [
                  IconButton(
                    icon: const Icon(Icons.edit),
                    onPressed: () => _showDormDialog(dorm: dorm),
                  ),
                  IconButton(
                    icon: const Icon(Icons.delete_outline),
                    onPressed: () => _deleteDorm(dorm),
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
