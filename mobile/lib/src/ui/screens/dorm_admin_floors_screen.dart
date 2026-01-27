import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/models/floor_model.dart';
import 'package:hostel_mobile/src/providers/floors_provider.dart';
import 'package:hostel_mobile/src/ui/widgets/app_text_field.dart';
import 'package:hostel_mobile/src/ui/widgets/confirm_dialog.dart';
import 'package:hostel_mobile/src/ui/widgets/empty_state.dart';
import 'package:hostel_mobile/src/ui/widgets/error_state.dart';
import 'package:hostel_mobile/src/ui/widgets/loading_state.dart';

class DormAdminFloorsScreen extends StatefulWidget {
  const DormAdminFloorsScreen({super.key});

  @override
  State<DormAdminFloorsScreen> createState() => _DormAdminFloorsScreenState();
}

class _DormAdminFloorsScreenState extends State<DormAdminFloorsScreen> {
  @override
  void initState() {
    super.initState();
    context.read<FloorsProvider>().load();
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<FloorsProvider>();

    if (provider.isLoading && provider.floors.isEmpty) {
      return const LoadingState(message: 'Loading floors...');
    }

    if (provider.errorMessage != null && provider.floors.isEmpty) {
      return ErrorState(
        message: provider.errorMessage!,
        onRetry: () => provider.load(),
      );
    }

    return Scaffold(
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => _openFloorForm(context),
        icon: const Icon(Icons.add),
        label: const Text('New floor'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: provider.floors.isEmpty
            ? const EmptyState(
                title: 'No floors yet',
                description: 'Create floors to organize rooms.',
              )
            : RefreshIndicator(
                onRefresh: () => provider.load(),
                child: ListView.builder(
                  itemCount: provider.floors.length,
                  itemBuilder: (context, index) {
                    final floor = provider.floors[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      child: ListTile(
                        title: Text('Floor ${floor.number}'),
                        subtitle: Text(
                          'Bathrooms: ${floor.bathrooms ?? 0} | Kitchens: ${floor.kitchens ?? 0}',
                        ),
                        trailing: PopupMenuButton<String>(
                          onSelected: (value) {
                            if (value == 'edit') {
                              _openFloorForm(context, existing: floor);
                            } else if (value == 'delete') {
                              _deleteFloor(context, floor);
                            }
                          },
                          itemBuilder: (context) => const [
                            PopupMenuItem(value: 'edit', child: Text('Edit')),
                            PopupMenuItem(value: 'delete', child: Text('Delete')),
                          ],
                        ),
                      ),
                    );
                  },
                ),
              ),
      ),
    );
  }

  Future<void> _openFloorForm(BuildContext context, {FloorModel? existing}) async {
    final result = await showDialog<_FloorFormResult>(
      context: context,
      builder: (context) => _FloorFormDialog(initial: existing),
    );
    if (!context.mounted) return;
    if (result == null) return;

    final payload = {
      'number': result.number,
      'bathrooms': result.bathrooms,
      'kitchens': result.kitchens,
      'showers': result.showers,
    };

    final provider = context.read<FloorsProvider>();
    final success = await provider.saveFloor(payload, floorId: existing?.id);
    if (!context.mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(success ? 'Floor saved.' : provider.errorMessage ?? 'Save failed.')),
    );
  }

  Future<void> _deleteFloor(BuildContext context, FloorModel floor) async {
    final confirm = await showConfirmDialog(
      context: context,
      title: 'Delete floor',
      message: 'Are you sure you want to delete floor ${floor.number}?',
      confirmLabel: 'Delete',
    );
    if (!context.mounted) return;
    if (!confirm) return;

    final provider = context.read<FloorsProvider>();
    final success = await provider.deleteFloor(floor.id);
    if (!context.mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(success ? 'Floor deleted.' : provider.errorMessage ?? 'Delete failed.'),
      ),
    );
  }
}

class _FloorFormResult {
  final int number;
  final int bathrooms;
  final int kitchens;
  final int showers;

  _FloorFormResult({
    required this.number,
    required this.bathrooms,
    required this.kitchens,
    required this.showers,
  });
}

class _FloorFormDialog extends StatefulWidget {
  final FloorModel? initial;

  const _FloorFormDialog({this.initial});

  @override
  State<_FloorFormDialog> createState() => _FloorFormDialogState();
}

class _FloorFormDialogState extends State<_FloorFormDialog> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _numberController;
  late final TextEditingController _bathroomsController;
  late final TextEditingController _kitchensController;
  late final TextEditingController _showersController;

  @override
  void initState() {
    super.initState();
    _numberController =
        TextEditingController(text: widget.initial?.number.toString() ?? '');
    _bathroomsController =
        TextEditingController(text: widget.initial?.bathrooms?.toString() ?? '4');
    _kitchensController =
        TextEditingController(text: widget.initial?.kitchens?.toString() ?? '2');
    _showersController =
        TextEditingController(text: widget.initial?.showers?.toString() ?? '2');
  }

  @override
  void dispose() {
    _numberController.dispose();
    _bathroomsController.dispose();
    _kitchensController.dispose();
    _showersController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text(widget.initial == null ? 'New floor' : 'Edit floor'),
      content: SizedBox(
        width: 360,
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              AppTextField(
                controller: _numberController,
                label: 'Floor number',
                keyboardType: TextInputType.number,
                validator: (value) => value == null || value.isEmpty ? 'Required' : null,
              ),
              const SizedBox(height: 12),
              AppTextField(
                controller: _bathroomsController,
                label: 'Bathrooms',
                keyboardType: TextInputType.number,
              ),
              const SizedBox(height: 12),
              AppTextField(
                controller: _kitchensController,
                label: 'Kitchens',
                keyboardType: TextInputType.number,
              ),
              const SizedBox(height: 12),
              AppTextField(
                controller: _showersController,
                label: 'Showers',
                keyboardType: TextInputType.number,
              ),
            ],
          ),
        ),
      ),
      actions: [
        TextButton(onPressed: () => Navigator.of(context).pop(), child: const Text('Cancel')),
        ElevatedButton(onPressed: _submit, child: const Text('Save')),
      ],
    );
  }

  void _submit() {
    if (!_formKey.currentState!.validate()) return;

    final number = int.tryParse(_numberController.text.trim()) ?? 0;
    final bathrooms = int.tryParse(_bathroomsController.text.trim()) ?? 0;
    final kitchens = int.tryParse(_kitchensController.text.trim()) ?? 0;
    final showers = int.tryParse(_showersController.text.trim()) ?? 0;

    Navigator.of(context).pop(_FloorFormResult(
      number: number,
      bathrooms: bathrooms,
      kitchens: kitchens,
      showers: showers,
    ));
  }
}
