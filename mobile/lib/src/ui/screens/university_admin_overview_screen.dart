import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/domain/models/report_overview_model.dart';
import 'package:hostel_mobile/src/domain/repositories/report_repository.dart';
import 'package:hostel_mobile/src/ui/components/error_card.dart';

class UniversityAdminOverviewScreen extends StatefulWidget {
  const UniversityAdminOverviewScreen({super.key});

  @override
  State<UniversityAdminOverviewScreen> createState() => _UniversityAdminOverviewScreenState();
}

class _UniversityAdminOverviewScreenState extends State<UniversityAdminOverviewScreen> {
  late final Future<ReportOverview> _overviewFuture;

  @override
  void initState() {
    super.initState();
    _overviewFuture = context.read<ReportRepository>().fetchOverview();
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<ReportOverview>(
      future: _overviewFuture,
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return const Center(child: CircularProgressIndicator());
        }

        if (snapshot.hasError) {
          return Center(child: ErrorCard(message: 'Unable to load overview.'));
        }

        final overview = snapshot.data!;
        return Padding(
          padding: const EdgeInsets.all(16.0),
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
              Text('Trend (last 14 days)', style: Theme.of(context).textTheme.titleLarge),
              ...overview.trend.map((point) {
                return ListTile(
                  title: Text(point.day),
                  trailing: Text(point.count.toString()),
                );
              }),
              const SizedBox(height: 24),
              Text('Dorm occupancies', style: Theme.of(context).textTheme.titleLarge),
              ...overview.dorms.map((dorm) => Card(
                    margin: const EdgeInsets.only(bottom: 12),
                    child: ListTile(
                      title: Text(dorm.name),
                      subtitle: Text('${dorm.occupied}/${dorm.capacity} occupied'),
                      trailing: Text('${dorm.percent}%'),
                    ),
                  )),
              const SizedBox(height: 24),
              Card(
                color: Theme.of(context).colorScheme.surfaceContainerHighest,
                child: ListTile(
                  title: const Text('Student import'),
                  subtitle: const Text('CSV import endpoint is not available on this server.'),
                ),
              ),
            ],
          ),
        );
      },
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
          padding: const EdgeInsets.all(12.0),
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
