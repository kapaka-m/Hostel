import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/providers/reports_provider.dart';
import 'package:hostel_mobile/src/ui/widgets/error_state.dart';
import 'package:hostel_mobile/src/ui/widgets/loading_state.dart';

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
    return Padding(
      padding: const EdgeInsets.all(16),
      child: ListView(
        children: [
          Text('University overview', style: Theme.of(context).textTheme.headlineSmall),
          const SizedBox(height: 16),
          Wrap(
            spacing: 12,
            runSpacing: 12,
            children: [
              _MetricCard(label: 'Dorms', value: overview.totals.dorms.toString()),
              _MetricCard(label: 'Students', value: overview.totals.students.toString()),
              _MetricCard(label: 'Rooms', value: overview.totals.rooms.toString()),
              _MetricCard(label: 'Capacity', value: overview.totals.capacity.toString()),
              _MetricCard(label: 'Occupied', value: overview.totals.occupied.toString()),
            ],
          ),
          const SizedBox(height: 24),
          Text('Assignments trend (last 14 days)',
              style: Theme.of(context).textTheme.titleLarge),
          const SizedBox(height: 8),
          ...overview.trend.map((point) {
            return ListTile(
              title: Text(point.day),
              trailing: Text(point.count.toString()),
            );
          }),
          const SizedBox(height: 24),
          Text('Dorm occupancies', style: Theme.of(context).textTheme.titleLarge),
          const SizedBox(height: 8),
          ...overview.dorms.map((dorm) => Card(
                margin: const EdgeInsets.only(bottom: 12),
                child: ListTile(
                  title: Text(dorm.name),
                  subtitle: Text('${dorm.occupied}/${dorm.capacity} occupied'),
                  trailing: Text('${dorm.percent}%'),
                ),
              )),
          const SizedBox(height: 24),
          Text('Ticket status', style: Theme.of(context).textTheme.titleLarge),
          const SizedBox(height: 8),
          ...overview.ticketStatusCounts.entries.map((entry) => ListTile(
                title: Text(entry.key),
                trailing: Text(entry.value.toString()),
              )),
        ],
      ),
    );
  }
}

class _MetricCard extends StatelessWidget {
  final String label;
  final String value;

  const _MetricCard({
    required this.label,
    required this.value,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 140,
      child: Card(
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Column(
            children: [
              Text(value, style: Theme.of(context).textTheme.headlineSmall),
              Text(label, style: Theme.of(context).textTheme.bodyMedium),
            ],
          ),
        ),
      ),
    );
  }
}
