import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/endpoints.dart';
import 'package:hostel_mobile/src/api/response_parser.dart';
import 'package:hostel_mobile/src/models/floor_model.dart';

class FloorRepository {
  final ApiClient _client;

  FloorRepository(this._client);

  Future<List<FloorModel>> fetchFloors() async {
    final response = await _client.get(Endpoints.floors);
    final items = extractDataList(response.data);
    return items.map(FloorModel.fromJson).toList();
  }

  Future<FloorModel> createFloor(Map<String, dynamic> payload) async {
    final response = await _client.post(Endpoints.floors, data: payload);
    return FloorModel.fromJson(ensureMap(response.data));
  }

  Future<FloorModel> updateFloor(int id, Map<String, dynamic> payload) async {
    final response = await _client.put(Endpoints.floorDetails(id), data: payload);
    return FloorModel.fromJson(ensureMap(response.data));
  }

  Future<void> deleteFloor(int id) async {
    await _client.delete(Endpoints.floorDetails(id));
  }
}
