import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/models/audit_log_model.dart';
import 'package:hostel_mobile/src/providers/audit_logs_provider.dart';
import 'package:hostel_mobile/src/ui/widgets/empty_state.dart';
import 'package:hostel_mobile/src/ui/widgets/error_state.dart';
import 'package:hostel_mobile/src/ui/widgets/loading_state.dart';
import 'package:hostel_mobile/src/ui/widgets/paginated_list_view.dart';
import 'package:hostel_mobile/src/ui/widgets/section_header.dart';

class UniversityAdminAuditLogsScreen extends StatefulWidget {
  const UniversityAdminAuditLogsScreen({super.key});

  @override
  State<UniversityAdminAuditLogsScreen> createState() =>
      _UniversityAdminAuditLogsScreenState();
}

class _UniversityAdminAuditLogsScreenState extends State<UniversityAdminAuditLogsScreen> {
  @override
  void initState() {
    super.initState();
    context.read<AuditLogsProvider>().load(refresh: true);
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<AuditLogsProvider>();

    if (!provider.isAvailable) {
      return const EmptyState(
        title: 'Audit logs disabled',
        description: 'Enable audit logs in settings to view this feed.',
      );
    }

    if (provider.isLoading && provider.logs.isEmpty) {
      return const LoadingState(message: 'Loading audit logs...');
    }

    if (provider.errorMessage != null && provider.logs.isEmpty) {
      return ErrorState(
        message: provider.errorMessage!,
        onRetry: () => provider.load(refresh: true),
      );
    }

    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          const SectionHeader(title: 'Audit logs'),
          const SizedBox(height: 12),
          Expanded(
            child: PaginatedListView<AuditLogModel>(
              items: provider.logs,
              hasMore: provider.hasMore,
              isLoadingMore: provider.isLoadingMore,
              onLoadMore: provider.loadMore,
              onRefresh: () => provider.load(refresh: true),
              emptyState: const EmptyState(
                title: 'No audit logs yet',
                description: 'Recent actions will appear here.',
              ),
              itemBuilder: (context, log) => Card(
                margin: const EdgeInsets.only(bottom: 12),
                child: ListTile(
                  title: Text(log.action ?? 'Action'),
                  subtitle: Text(
                    '${log.entityType ?? 'Entity'} #${log.entityId ?? ''}\n'
                    'Actor: ${log.actor?.name ?? 'System'}',
                  ),
                  isThreeLine: true,
                  trailing: Text(
                    log.createdAt?.toLocal().toString().split('.').first ?? '',
                    style: Theme.of(context).textTheme.bodySmall,
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
