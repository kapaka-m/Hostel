class ReportTotals {
  final int dorms;
  final int students;
  final int rooms;
  final int capacity;
  final int occupied;

  ReportTotals({
    required this.dorms,
    required this.students,
    required this.rooms,
    required this.capacity,
    required this.occupied,
  });
}

class TrendPoint {
  final String day;
  final int count;

  TrendPoint({
    required this.day,
    required this.count,
  });
}

class DormOccupancy {
  final int id;
  final String name;
  final int capacity;
  final int occupied;
  final int percent;

  DormOccupancy({
    required this.id,
    required this.name,
    required this.capacity,
    required this.occupied,
    required this.percent,
  });
}

class ReportOverview {
  final ReportTotals totals;
  final List<TrendPoint> trend;
  final Map<String, int> ticketStatusCounts;
  final List<DormOccupancy> dorms;

  ReportOverview({
    required this.totals,
    required this.trend,
    required this.ticketStatusCounts,
    required this.dorms,
  });

  factory ReportOverview.fromJson(Map<String, dynamic> json) {
    final totals = json['totals'] as Map<String, dynamic>? ?? {};
    final trend = (json['trend'] as Iterable<dynamic>?)?.cast<Map<String, dynamic>>() ?? const [];
    final dorms = (json['dorms'] as Iterable<dynamic>?)?.cast<Map<String, dynamic>>() ?? const [];
    final ticketCounts = <String, int>{};

    if (json['ticket_status_counts'] is Map<String, dynamic>) {
      (json['ticket_status_counts'] as Map<String, dynamic>).forEach((key, value) {
        ticketCounts[key] = value is int ? value : int.tryParse('$value') ?? 0;
      });
    }

    return ReportOverview(
      totals: ReportTotals(
        dorms: totals['dorms'] as int? ?? 0,
        students: totals['students'] as int? ?? 0,
        rooms: totals['rooms'] as int? ?? 0,
        capacity: totals['capacity'] as int? ?? 0,
        occupied: totals['occupied'] as int? ?? 0,
      ),
      trend: trend
          .map((entry) => TrendPoint(
                day: entry['day'] as String? ?? '',
                count: entry['count'] as int? ?? 0,
              ))
          .toList(),
      ticketStatusCounts: ticketCounts,
      dorms: dorms
          .map((entry) => DormOccupancy(
                id: entry['id'] as int? ?? 0,
                name: entry['name'] as String? ?? '',
                capacity: entry['capacity'] as int? ?? 0,
                occupied: entry['occupied'] as int? ?? 0,
                percent: entry['percent'] as int? ?? 0,
              ))
          .toList(),
    );
  }
}
