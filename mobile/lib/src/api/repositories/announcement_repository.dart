import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/endpoints.dart';
import 'package:hostel_mobile/src/api/response_parser.dart';
import 'package:hostel_mobile/src/models/announcement_model.dart';

class AnnouncementRepository {
  final ApiClient _client;

  AnnouncementRepository(this._client);

  Future<PaginatedResult<AnnouncementModel>> fetchAnnouncements({
    Map<String, dynamic>? filters,
    int page = 1,
  }) async {
    final query = {
      if (filters != null) ...filters,
      'page': page,
    };
    final response = await _client.get(Endpoints.announcements, query: query);
    return extractPaginated(response.data, AnnouncementModel.fromJson);
  }

  Future<AnnouncementModel> fetchAnnouncement(int id) async {
    final response = await _client.get(Endpoints.announcementDetails(id));
    return AnnouncementModel.fromJson(ensureMap(response.data));
  }

  Future<AnnouncementModel> createAnnouncement(Map<String, dynamic> payload) async {
    final response = await _client.post(Endpoints.announcements, data: payload);
    return AnnouncementModel.fromJson(ensureMap(response.data));
  }

  Future<AnnouncementModel> updateAnnouncement(int id, Map<String, dynamic> payload) async {
    final response = await _client.put(Endpoints.announcementDetails(id), data: payload);
    return AnnouncementModel.fromJson(ensureMap(response.data));
  }
}
