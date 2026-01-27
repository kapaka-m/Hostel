import 'package:hostel_mobile/src/data/api/api_client.dart';
import 'package:hostel_mobile/src/data/api/endpoints.dart';
import 'package:hostel_mobile/src/domain/models/report_overview_model.dart';
import 'package:hostel_mobile/src/domain/repositories/response_parser.dart';

class ReportRepository {
  final ApiClient _client;

  ReportRepository(this._client);

  Future<ReportOverview> fetchOverview() async {
    final response = await _client.get(Endpoints.reportsOverview);
    return ReportOverview.fromJson(ensureMap(response.data));
  }
}
