import 'package:hostel_mobile/src/api/api_client.dart';

class PaginationMeta {
  final int currentPage;
  final int lastPage;
  final int perPage;
  final int total;

  const PaginationMeta({
    required this.currentPage,
    required this.lastPage,
    required this.perPage,
    required this.total,
  });

  bool get hasNextPage => currentPage < lastPage;

  factory PaginationMeta.fromJson(Map<String, dynamic> json) {
    return PaginationMeta(
      currentPage: json['current_page'] as int? ?? 1,
      lastPage: json['last_page'] as int? ?? 1,
      perPage: json['per_page'] as int? ?? 0,
      total: json['total'] as int? ?? 0,
    );
  }
}

class PaginatedResult<T> {
  final List<T> items;
  final PaginationMeta? meta;

  const PaginatedResult({
    required this.items,
    required this.meta,
  });
}

Map<String, dynamic> ensureMap(dynamic value) {
  if (value is Map<String, dynamic>) {
    return value;
  }
  throw ApiException(message: 'Unexpected response payload.');
}

List<Map<String, dynamic>> extractDataList(dynamic value) {
  if (value is Map<String, dynamic> && value['data'] is Iterable<dynamic>) {
    final raw = value['data'] as Iterable<dynamic>;
    return raw
        .whereType<Map<String, dynamic>>()
        .map((item) => item)
        .toList();
  }

  if (value is Iterable<dynamic>) {
    return value
        .whereType<Map<String, dynamic>>()
        .map((item) => item)
        .toList();
  }

  throw ApiException(message: 'Expected list payload.');
}

PaginatedResult<T> extractPaginated<T>(
  dynamic value,
  T Function(Map<String, dynamic> json) builder,
) {
  if (value is Map<String, dynamic> && value['data'] is Iterable) {
    final raw = value['data'] as Iterable<dynamic>;
    final items = raw.whereType<Map<String, dynamic>>().map(builder).toList();
    final metaRaw = value['meta'];
    final meta = metaRaw is Map<String, dynamic> ? PaginationMeta.fromJson(metaRaw) : null;
    return PaginatedResult(items: items, meta: meta);
  }

  if (value is Iterable) {
    final items = value.whereType<Map<String, dynamic>>().map(builder).toList();
    return PaginatedResult(items: items, meta: null);
  }

  throw ApiException(message: 'Expected paginated payload.');
}

