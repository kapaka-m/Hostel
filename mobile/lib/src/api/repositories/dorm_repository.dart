import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/endpoints.dart';
import 'package:hostel_mobile/src/api/response_parser.dart';
import 'package:hostel_mobile/src/models/dorm_model.dart';

class DormRepository {
  final ApiClient _client;

  DormRepository(this._client);

  Future<List<DormModel>> fetchDorms() async {
    final response = await _client.get(Endpoints.dorms);
    final items = extractDataList(response.data);
    return items.map(DormModel.fromJson).toList();
  }

  Future<DormModel> createDorm(Map<String, dynamic> payload) async {
    final response = await _client.post(Endpoints.dorms, data: payload);
    return DormModel.fromJson(ensureMap(response.data));
  }

  Future<DormModel> updateDorm(int dormId, Map<String, dynamic> payload) async {
    final response = await _client.put(Endpoints.dormDetails(dormId), data: payload);
    return DormModel.fromJson(ensureMap(response.data));
  }

  Future<void> deleteDorm(int dormId) async {
    await _client.delete(Endpoints.dormDetails(dormId));
  }

  Future<void> inviteDormAdmin({
    required int dormId,
    required String name,
    required String email,
    required String password,
  }) async {
    await _client.post(Endpoints.inviteDormAdmin(dormId), data: {
      'name': name,
      'email': email,
      'password': password,
    });
  }
}
