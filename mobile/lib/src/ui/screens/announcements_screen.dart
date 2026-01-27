import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/auth/auth_provider.dart';
import 'package:hostel_mobile/src/models/announcement_model.dart';
import 'package:hostel_mobile/src/providers/announcements_provider.dart';
import 'package:hostel_mobile/src/ui/widgets/empty_state.dart';
import 'package:hostel_mobile/src/ui/widgets/error_state.dart';
import 'package:hostel_mobile/src/ui/widgets/loading_state.dart';
import 'package:hostel_mobile/src/ui/widgets/paginated_list_view.dart';

class AnnouncementsScreen extends StatefulWidget {
  final String basePath;

  const AnnouncementsScreen({
    super.key,
    required this.basePath,
  });

  @override
  State<AnnouncementsScreen> createState() => _AnnouncementsScreenState();
}

class _AnnouncementsScreenState extends State<AnnouncementsScreen> {
  String? _status;
  String? _audience;

  @override
  void initState() {
    super.initState();
    context.read<AnnouncementsProvider>().load(refresh: true);
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final provider = context.watch<AnnouncementsProvider>();
    final canManage = auth.user?.isDormAdmin == true ||
        auth.user?.isUniversityAdmin == true ||
        auth.user?.isSuperAdmin == true;

    if (provider.isLoading && provider.items.isEmpty) {
      return const LoadingState(message: 'Loading announcements...');
    }

    if (provider.errorMessage != null && provider.items.isEmpty) {
      return ErrorState(
        message: provider.errorMessage!,
        onRetry: () => provider.load(refresh: true),
      );
    }

    return Scaffold(
      floatingActionButton: canManage
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
            _FiltersRow(
              status: _status,
              audience: _audience,
              onChanged: (status, audience) {
                setState(() {
                  _status = status;
                  _audience = audience;
                });
                provider.setFilters({
                  if (status != null) 'status': status,
                  if (audience != null) 'audience': audience,
                });
              },
            ),
            const SizedBox(height: 12),
            Expanded(
              child: PaginatedListView<AnnouncementModel>(
                items: provider.items,
                hasMore: provider.hasMore,
                isLoadingMore: provider.isLoadingMore,
                onLoadMore: provider.loadMore,
                onRefresh: () => provider.load(refresh: true),
                emptyState: const EmptyState(
                  title: 'No announcements',
                  description: 'Important updates will appear here.',
                ),
                itemBuilder: (context, announcement) => Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  child: ListTile(
                    title: Text(announcement.title),
                    subtitle: Text(
                      announcement.body,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    trailing: Text(announcement.currentStatus ?? ''),
                    onTap: () => context.go('${widget.basePath}/${announcement.id}'),
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

class _FiltersRow extends StatelessWidget {
  final String? status;
  final String? audience;
  final void Function(String? status, String? audience) onChanged;

  const _FiltersRow({
    required this.status,
    required this.audience,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: DropdownButtonFormField<String?>(
            initialValue: status,
            decoration: const InputDecoration(labelText: 'Status'),
            items: const [
              DropdownMenuItem<String?>(value: null, child: Text('All statuses')),
              DropdownMenuItem<String?>(value: 'DRAFT', child: Text('Draft')),
              DropdownMenuItem<String?>(value: 'PUBLISHED', child: Text('Published')),
              DropdownMenuItem<String?>(value: 'SCHEDULED', child: Text('Scheduled')),
              DropdownMenuItem<String?>(value: 'EXPIRED', child: Text('Expired')),
            ],
            onChanged: (value) => onChanged(value, audience),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: DropdownButtonFormField<String?>(
            initialValue: audience,
            decoration: const InputDecoration(labelText: 'Audience'),
            items: const [
              DropdownMenuItem<String?>(value: null, child: Text('All audiences')),
              DropdownMenuItem<String?>(value: 'UNIVERSITY', child: Text('University')),
              DropdownMenuItem<String?>(value: 'DORM', child: Text('Dorm')),
            ],
            onChanged: (value) => onChanged(status, value),
          ),
        ),
      ],
    );
  }
}
