import 'package:hostel_mobile/src/data/api/api_client.dart';

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
