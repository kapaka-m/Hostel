import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/endpoints.dart';
import 'package:hostel_mobile/src/api/response_parser.dart';
import 'package:hostel_mobile/src/models/audit_log_model.dart';

class AuditLogRepository {
  final ApiClient _client;

  AuditLogRepository(this._client);

  Future<PaginatedResult<AuditLogModel>> fetchAuditLogs({int page = 1}) async {
    final response = await _client.get(Endpoints.auditLogs, query: {'page': page});
    return extractPaginated(response.data, AuditLogModel.fromJson);
  }
}
