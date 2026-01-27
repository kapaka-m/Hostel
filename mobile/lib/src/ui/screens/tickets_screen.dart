import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/auth/auth_provider.dart';
import 'package:hostel_mobile/src/models/ticket_model.dart';
import 'package:hostel_mobile/src/providers/tickets_provider.dart';
import 'package:hostel_mobile/src/ui/widgets/empty_state.dart';
import 'package:hostel_mobile/src/ui/widgets/error_state.dart';
import 'package:hostel_mobile/src/ui/widgets/loading_state.dart';
import 'package:hostel_mobile/src/ui/widgets/paginated_list_view.dart';

class TicketsScreen extends StatefulWidget {
  final String basePath;

  const TicketsScreen({
    super.key,
    required this.basePath,
  });

  @override
  State<TicketsScreen> createState() => _TicketsScreenState();
}

class _TicketsScreenState extends State<TicketsScreen> {
  final _searchController = TextEditingController();
  String? _status;
  String? _priority;

  @override
  void initState() {
    super.initState();
    context.read<TicketsProvider>().load(refresh: true);
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<TicketsProvider>();
    final auth = context.watch<AuthProvider>();
    final canCreate = auth.user?.isDormAdmin == true ||
        auth.user?.isUniversityAdmin == true ||
        auth.user?.isSuperAdmin == true;

    if (provider.isLoading && provider.items.isEmpty) {
      return const LoadingState(message: 'Loading tickets...');
    }

    if (provider.errorMessage != null && provider.items.isEmpty) {
      return ErrorState(
        message: provider.errorMessage!,
        onRetry: () => provider.load(refresh: true),
      );
    }

    return Scaffold(
      floatingActionButton: canCreate
          ? FloatingActionButton.extended(
              onPressed: () => context.go('${widget.basePath}/new'),
              icon: const Icon(Icons.add),
              label: const Text('New'),
            )
          : null,
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            _FilterRow(
              controller: _searchController,
              status: _status,
              priority: _priority,
              onApply: () {
                provider.setFilters({
                  if (_searchController.text.trim().isNotEmpty) 'q': _searchController.text.trim(),
                  if (_status != null) 'status': _status,
                  if (_priority != null) 'priority': _priority,
                });
              },
              onStatusChanged: (value) => setState(() => _status = value),
              onPriorityChanged: (value) => setState(() => _priority = value),
              onClear: () {
                setState(() {
                  _searchController.clear();
                  _status = null;
                  _priority = null;
                });
                provider.setFilters({});
              },
            ),
            const SizedBox(height: 12),
            Expanded(
              child: PaginatedListView<TicketModel>(
                items: provider.items,
                hasMore: provider.hasMore,
                isLoadingMore: provider.isLoadingMore,
                onLoadMore: provider.loadMore,
                onRefresh: () => provider.load(refresh: true),
                emptyState: const EmptyState(
                  title: 'No tickets',
                  description: 'Tickets will appear here once created.',
                ),
                itemBuilder: (context, ticket) => Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  child: ListTile(
                    title: Text(ticket.subject),
                    subtitle: Text(
                      'Status: ${ticket.status ?? 'Unknown'} | Priority: ${ticket.priority ?? 'MEDIUM'}',
                    ),
                    onTap: () => context.go('${widget.basePath}/${ticket.id}'),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _FilterRow extends StatelessWidget {
  final TextEditingController controller;
  final String? status;
  final String? priority;
  final VoidCallback onApply;
  final VoidCallback onClear;
  final ValueChanged<String?> onStatusChanged;
  final ValueChanged<String?> onPriorityChanged;

  const _FilterRow({
    required this.controller,
    required this.status,
    required this.priority,
    required this.onApply,
    required this.onClear,
    required this.onStatusChanged,
    required this.onPriorityChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        TextField(
          controller: controller,
          decoration: const InputDecoration(
            labelText: 'Search',
            hintText: 'Subject or category',
          ),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: DropdownButtonFormField<String?>(
                key: ValueKey('status-$status'),
                initialValue: status,
                decoration: const InputDecoration(labelText: 'Status'),
                items: const [
                  DropdownMenuItem<String?>(value: null, child: Text('All statuses')),
                  DropdownMenuItem<String?>(value: 'OPEN', child: Text('Open')),
                  DropdownMenuItem<String?>(value: 'IN_PROGRESS', child: Text('In progress')),
                  DropdownMenuItem<String?>(value: 'RESOLVED', child: Text('Resolved')),
                  DropdownMenuItem<String?>(value: 'CLOSED', child: Text('Closed')),
                ],
                onChanged: onStatusChanged,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: DropdownButtonFormField<String?>(
                key: ValueKey('priority-$priority'),
                initialValue: priority,
                decoration: const InputDecoration(labelText: 'Priority'),
                items: const [
                  DropdownMenuItem<String?>(value: null, child: Text('All priorities')),
                  DropdownMenuItem<String?>(value: 'LOW', child: Text('Low')),
                  DropdownMenuItem<String?>(value: 'MEDIUM', child: Text('Medium')),
                  DropdownMenuItem<String?>(value: 'HIGH', child: Text('High')),
                  DropdownMenuItem<String?>(value: 'URGENT', child: Text('Urgent')),
                ],
                onChanged: onPriorityChanged,
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: OutlinedButton(
                onPressed: onClear,
                child: const Text('Clear'),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: ElevatedButton(
                onPressed: onApply,
                child: const Text('Apply'),
              ),
            ),
          ],
        ),
      ],
    );
  }
}
