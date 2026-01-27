import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/models/audit_log_model.dart';
import 'package:hostel_mobile/src/providers/activity_feed_provider.dart';
import 'package:hostel_mobile/src/ui/widgets/empty_state.dart';
import 'package:hostel_mobile/src/ui/widgets/error_state.dart';
import 'package:hostel_mobile/src/ui/widgets/loading_state.dart';

class UniversityAdminActivityFeedScreen extends StatefulWidget {
  const UniversityAdminActivityFeedScreen({super.key});

  @override
  State<UniversityAdminActivityFeedScreen> createState() =>
      _UniversityAdminActivityFeedScreenState();
}

class _UniversityAdminActivityFeedScreenState extends State<UniversityAdminActivityFeedScreen> {
  @override
  void initState() {
    super.initState();
    context.read<ActivityFeedProvider>().load();
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<ActivityFeedProvider>();

    if (!provider.isAvailable) {
      return const EmptyState(
        title: 'Activity feed disabled',
        description: 'Enable activity feed in settings to view this feed.',
      );
    }

    if (provider.isLoading && provider.entries.isEmpty) {
      return const LoadingState(message: 'Loading activity feed...');
    }

    if (provider.errorMessage != null && provider.entries.isEmpty) {
      return ErrorState(
        message: provider.errorMessage!,
        onRetry: () => provider.load(),
      );
    }

    return Padding(
      padding: const EdgeInsets.all(16),
      child: provider.entries.isEmpty
          ? const EmptyState(
              title: 'No activity yet',
              description: 'Recent actions will appear here.',
            )
          : RefreshIndicator(
              onRefresh: () => provider.load(),
              child: ListView.builder(
                itemCount: provider.entries.length,
                itemBuilder: (context, index) {
                  final log = provider.entries[index];
                  return _LogTile(log: log);
                },
              ),
            ),
    );
  }
}

class _LogTile extends StatelessWidget {
  final AuditLogModel log;

  const _LogTile({required this.log});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: ListTile(
        title: Text(log.action ?? 'Action'),
        subtitle: Text('${log.entityType ?? 'Entity'} #${log.entityId ?? ''}'),
        trailing: Text(
          log.createdAt?.toLocal().toString().split('.').first ?? '',
          style: Theme.of(context).textTheme.bodySmall,
        ),
      ),
    );
  }
}
