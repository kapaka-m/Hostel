import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:go_router/go_router.dart';

import 'package:hostel_mobile/src/auth/auth_provider.dart';
import 'package:hostel_mobile/src/models/announcement_model.dart';
import 'package:hostel_mobile/src/models/student_room_assignment_model.dart';
import 'package:hostel_mobile/src/providers/student_provider.dart';
import 'package:hostel_mobile/src/ui/strings.dart';
import 'package:hostel_mobile/src/ui/widgets/empty_state.dart';
import 'package:hostel_mobile/src/ui/widgets/error_state.dart';
import 'package:hostel_mobile/src/ui/widgets/info_row.dart';
import 'package:hostel_mobile/src/ui/widgets/loading_state.dart';
import 'package:hostel_mobile/src/ui/widgets/section_header.dart';
import 'package:hostel_mobile/src/ui/widgets/status_badge.dart';

class StudentHomeScreen extends StatefulWidget {
  const StudentHomeScreen({super.key});

  @override
  State<StudentHomeScreen> createState() => _StudentHomeScreenState();
}

class _StudentHomeScreenState extends State<StudentHomeScreen> {
  @override
  void initState() {
    super.initState();
    context.read<StudentProvider>().load();
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    return Consumer<StudentProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading) {
          return const LoadingState(message: 'Loading your dashboard...');
        }

        if (provider.errorMessage != null) {
          return ErrorState(
            message: provider.errorMessage!,
            onRetry: () => provider.load(),
          );
        }

        final assignment = provider.assignment;
        return Padding(
          padding: const EdgeInsets.all(16.0),
          child: ListView(
            children: [
              _HeroCard(name: auth.user?.name),
              const SizedBox(height: 20),
              const SectionHeader(title: 'Quick actions'),
              const SizedBox(height: 12),
              Wrap(
                spacing: 12,
                runSpacing: 12,
                children: [
                  _QuickActionCard(
                    icon: Icons.home_work,
                    label: 'My room',
                    onTap: () => context.go('/student/room'),
                  ),
                  _QuickActionCard(
                    icon: Icons.campaign,
                    label: 'Announcements',
                    onTap: () => context.go('/student/announcements'),
                  ),
                  _QuickActionCard(
                    icon: Icons.person,
                    label: 'Profile',
                    onTap: () => context.go('/student/profile'),
                  ),
                ],
              ),
              const SizedBox(height: 24),
              SectionHeader(
                title: 'My room',
                actionLabel: 'View details',
                onAction: () => context.go('/student/room'),
              ),
              const SizedBox(height: 12),
              if (assignment == null || assignment.room == null)
                const EmptyState(
                  title: 'No room assignment yet',
                  description: 'Contact your dorm admin to secure housing.',
                )
              else
                _RoomCard(assignment: assignment),
              const SizedBox(height: 24),
              SectionHeader(
                title: 'Announcements',
                actionLabel: AppStrings.viewAll,
                onAction: () => context.go('/student/announcements'),
              ),
              const SizedBox(height: 12),
              if (provider.announcements.isEmpty)
                const EmptyState(
                  title: 'No announcements',
                  description: 'Important updates will appear here.',
                )
              else
                ...provider.announcements
                    .map((announcement) => _AnnouncementPreview(announcement: announcement)),
            ],
          ),
        );
      },
    );
  }
}

class _HeroCard extends StatelessWidget {
  final String? name;

  const _HeroCard({this.name});

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final greetingName = (name?.trim().isNotEmpty ?? false) ? name!.split(' ').first : 'Student';
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(20),
        gradient: LinearGradient(
          colors: [
            scheme.primary.withAlpha(217),
            scheme.tertiary.withAlpha(191),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Welcome back, $greetingName',
              style: Theme.of(context)
                  .textTheme
                  .headlineSmall
                  ?.copyWith(color: scheme.onPrimary)),
          const SizedBox(height: 6),
          Text(
            'Here is a quick snapshot of your stay.',
            style:
                Theme.of(context).textTheme.bodyMedium?.copyWith(color: scheme.onPrimary),
          ),
        ],
      ),
    );
  }
}

class _QuickActionCard extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  const _QuickActionCard({
    required this.icon,
    required this.label,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 150,
      child: Card(
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Icon(icon, size: 28),
                const SizedBox(height: 12),
                Text(label, style: Theme.of(context).textTheme.titleSmall),
                const SizedBox(height: 4),
                Text('Open', style: Theme.of(context).textTheme.labelSmall),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _RoomCard extends StatelessWidget {
  final StudentRoomAssignment assignment;

  const _RoomCard({
    required this.assignment,
  });

  @override
  Widget build(BuildContext context) {
    final room = assignment.room;
    final dorm = assignment.dorm;
    final floor = assignment.floor;
    final capacity = room?.capacity ?? 0;
    final occupancy = room?.occupancy ?? 0;
    final progress = capacity > 0 ? occupancy / capacity : 0.0;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Assigned Room', style: Theme.of(context).textTheme.titleMedium),
                StatusBadge(label: room?.status, kind: StatusKind.room),
              ],
            ),
            const SizedBox(height: 8),
            InfoRow(label: 'Dorm', value: dorm?.name ?? 'Unknown'),
            const SizedBox(height: 8),
            InfoRow(label: 'Floor', value: floor?.number.toString() ?? 'N/A'),
            const SizedBox(height: 8),
            InfoRow(label: 'Room', value: room?.roomNumber ?? 'N/A'),
            const SizedBox(height: 8),
            InfoRow(label: 'Capacity', value: '$occupancy / $capacity occupied'),
            const SizedBox(height: 8),
            LinearProgressIndicator(value: progress, minHeight: 8),
            const SizedBox(height: 12),
            Text('Occupants', style: Theme.of(context).textTheme.titleSmall),
            Wrap(
              spacing: 8,
              children: assignment.occupants
                  .map((student) => Chip(label: Text(student.fullName)))
                  .toList(),
            ),
          ],
        ),
      ),
    );
  }
}

class _AnnouncementPreview extends StatelessWidget {
  final AnnouncementModel announcement;

  const _AnnouncementPreview({
    required this.announcement,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: ListTile(
        title: Text(announcement.title),
        subtitle: Text(announcement.body, maxLines: 2, overflow: TextOverflow.ellipsis),
        trailing: StatusBadge(
          label: announcement.currentStatus ?? announcement.status,
          kind: StatusKind.announcement,
        ),
        onTap: () => context.go('/student/announcements/${announcement.id}'),
      ),
    );
  }
}

