import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/providers/reports_provider.dart';
import 'package:hostel_mobile/src/ui/widgets/error_state.dart';
import 'package:hostel_mobile/src/ui/widgets/loading_state.dart';
import 'package:hostel_mobile/src/ui/widgets/section_header.dart';
import 'package:hostel_mobile/src/ui/widgets/stat_card.dart';
import 'package:hostel_mobile/src/ui/widgets/status_badge.dart';

class UniversityAdminOverviewScreen extends StatefulWidget {
  const UniversityAdminOverviewScreen({super.key});

  @override
  State<UniversityAdminOverviewScreen> createState() =>
      _UniversityAdminOverviewScreenState();
}

class _UniversityAdminOverviewScreenState extends State<UniversityAdminOverviewScreen> {
  @override
  void initState() {
    super.initState();
    context.read<ReportsProvider>().load();
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<ReportsProvider>();

    if (provider.isLoading) {
      return const LoadingState(message: 'Loading overview...');
    }

    if (provider.errorMessage != null || provider.overview == null) {
      return ErrorState(
        message: provider.errorMessage ?? 'Unable to load overview.',
        onRetry: () => provider.load(),
      );
    }

    final overview = provider.overview!;
    final maxTrend = overview.trend.fold<int>(0, (max, item) => item.count > max ? item.count : max);

    return Padding(
      padding: const EdgeInsets.all(16),
      child: ListView(
        children: [
          const SectionHeader(title: 'University overview'),
          const SizedBox(height: 16),
          LayoutBuilder(
            builder: (context, constraints) {
              final width = constraints.maxWidth;
              final cardWidth = width >= 900 ? 220.0 : (width / 2) - 12;
              return Wrap(
                spacing: 12,
                runSpacing: 12,
                children: [
                  SizedBox(
                    width: cardWidth,
                    child: StatCard(
                      label: 'Dorms',
                      value: overview.totals.dorms.toString(),
                      icon: Icons.apartment,
                    ),
                  ),
                  SizedBox(
                    width: cardWidth,
                    child: StatCard(
                      label: 'Students',
                      value: overview.totals.students.toString(),
                      icon: Icons.groups,
                    ),
                  ),
                  SizedBox(
                    width: cardWidth,
                    child: StatCard(
                      label: 'Rooms',
                      value: overview.totals.rooms.toString(),
                      icon: Icons.meeting_room,
                    ),
                  ),
                  SizedBox(
                    width: cardWidth,
                    child: StatCard(
                      label: 'Capacity',
                      value: overview.totals.capacity.toString(),
                      icon: Icons.event_seat,
                    ),
                  ),
                  SizedBox(
                    width: cardWidth,
                    child: StatCard(
                      label: 'Occupied',
                      value: overview.totals.occupied.toString(),
                      icon: Icons.bed,
                    ),
                  ),
                ],
              );
            },
          ),
          const SizedBox(height: 24),
          const SectionHeader(title: 'Assignments trend (last 14 days)'),
          const SizedBox(height: 8),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                children: overview.trend.map((point) {
                  final ratio = maxTrend == 0 ? 0.0 : point.count / maxTrend;
                  return Padding(
                    padding: const EdgeInsets.symmetric(vertical: 6),
                    child: Row(
                      children: [
                        SizedBox(width: 90, child: Text(point.day)),
                        const SizedBox(width: 12),
                        Expanded(
                          child: LinearProgressIndicator(value: ratio),
                        ),
                        const SizedBox(width: 12),
                        Text(point.count.toString()),
                      ],
                    ),
                  );
                }).toList(),
              ),
            ),
          ),
          const SizedBox(height: 24),
          const SectionHeader(title: 'Dorm occupancies'),
          const SizedBox(height: 8),
          ...overview.dorms.map((dorm) {
            final progress = dorm.capacity == 0 ? 0.0 : dorm.occupied / dorm.capacity;
            return Card(
              margin: const EdgeInsets.only(bottom: 12),
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(dorm.name, style: Theme.of(context).textTheme.titleMedium),
                    const SizedBox(height: 8),
                    Text('${dorm.occupied}/${dorm.capacity} occupied'),
                    const SizedBox(height: 8),
                    LinearProgressIndicator(value: progress),
                  ],
                ),
              ),
            );
          }),
          const SizedBox(height: 24),
          const SectionHeader(title: 'Ticket status'),
          const SizedBox(height: 8),
          Card(
            child: Column(
              children: overview.ticketStatusCounts.entries.map((entry) {
                return ListTile(
                  title: Text(entry.key),
                  trailing: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      StatusBadge(label: entry.key, kind: StatusKind.ticket),
                      const SizedBox(width: 8),
                      Text(entry.value.toString()),
                    ],
                  ),
                );
              }).toList(),
            ),
          ),
        ],
      ),
    );
  }
}
