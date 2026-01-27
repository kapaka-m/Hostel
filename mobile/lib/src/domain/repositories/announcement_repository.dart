import 'package:hostel_mobile/src/data/api/api_client.dart';
import 'package:hostel_mobile/src/data/api/endpoints.dart';
import 'package:hostel_mobile/src/domain/models/announcement_model.dart';
import 'package:hostel_mobile/src/domain/repositories/response_parser.dart';

class AnnouncementRepository {
  final ApiClient _client;

  AnnouncementRepository(this._client);

  Future<List<AnnouncementModel>> fetchAnnouncements({Map<String, dynamic>? filters}) async {
    final response = await _client.get(Endpoints.announcements, query: filters);
    final items = extractDataList(response.data);
    return items.map(AnnouncementModel.fromJson).toList();
  }
}
