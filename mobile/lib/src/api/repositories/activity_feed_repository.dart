import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/endpoints.dart';
import 'package:hostel_mobile/src/api/response_parser.dart';
import 'package:hostel_mobile/src/models/audit_log_model.dart';

class ActivityFeedRepository {
  final ApiClient _client;

  ActivityFeedRepository(this._client);

  Future<List<AuditLogModel>> fetchActivityFeed() async {
    final response = await _client.get(Endpoints.activityFeed);
    final items = extractDataList(response.data);
    return items.map(AuditLogModel.fromJson).toList();
  }
}
