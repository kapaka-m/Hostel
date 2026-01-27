import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/auth/auth_provider.dart';
import 'package:hostel_mobile/src/models/announcement_model.dart';
import 'package:hostel_mobile/src/providers/announcements_provider.dart';
import 'package:hostel_mobile/src/ui/widgets/error_state.dart';
import 'package:hostel_mobile/src/ui/widgets/loading_state.dart';
import 'package:hostel_mobile/src/ui/widgets/info_row.dart';
import 'package:hostel_mobile/src/ui/widgets/status_badge.dart';

class AnnouncementDetailScreen extends StatefulWidget {
  final int announcementId;
  final String basePath;

  const AnnouncementDetailScreen({
    super.key,
    required this.announcementId,
    required this.basePath,
  });

  @override
  State<AnnouncementDetailScreen> createState() => _AnnouncementDetailScreenState();
}

class _AnnouncementDetailScreenState extends State<AnnouncementDetailScreen> {
  late Future<AnnouncementModel?> _future;

  @override
  void initState() {
    super.initState();
    _future = context.read<AnnouncementsProvider>().fetchDetail(widget.announcementId);
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final canManage = auth.user?.isDormAdmin == true ||
        auth.user?.isUniversityAdmin == true ||
        auth.user?.isSuperAdmin == true;

    return FutureBuilder<AnnouncementModel?>(
      future: _future,
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return const LoadingState(message: 'Loading announcement...');
        }

        if (snapshot.hasError || snapshot.data == null) {
          return ErrorState(
            message: 'Unable to load announcement.',
            onRetry: () {
              setState(() {
                _future = context.read<AnnouncementsProvider>().fetchDetail(widget.announcementId);
              });
            },
          );
        }

        final announcement = snapshot.data!;

        return Padding(
          padding: const EdgeInsets.all(16),
          child: ListView(
            children: [
              Row(
                children: [
                  IconButton(
                    onPressed: () => context.pop(),
                    icon: const Icon(Icons.arrow_back),
                  ),
                  const SizedBox(width: 8),
                  Text('Announcement', style: Theme.of(context).textTheme.titleLarge),
                  const Spacer(),
                  if (canManage)
                    IconButton(
                      icon: const Icon(Icons.edit),
                      onPressed: () => context.go('${widget.basePath}/${announcement.id}/edit'),
                    ),
                ],
              ),
              const SizedBox(height: 16),
              Text(announcement.title, style: Theme.of(context).textTheme.headlineSmall),
              const SizedBox(height: 8),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  StatusBadge(
                    label: announcement.currentStatus ?? announcement.status ?? 'Unknown',
                    kind: StatusKind.announcement,
                  ),
                  if (announcement.audience != null)
                    StatusBadge(label: announcement.audience, kind: StatusKind.generic),
                ],
              ),
              const SizedBox(height: 12),
              if (announcement.dorm != null)
                InfoRow(label: 'Dorm', value: announcement.dorm?.name ?? ''),
              const SizedBox(height: 16),
              Text(announcement.body, style: Theme.of(context).textTheme.bodyLarge),
            ],
          ),
        );
      },
    );
  }
}
