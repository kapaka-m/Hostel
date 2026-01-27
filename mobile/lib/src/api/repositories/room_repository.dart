import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/endpoints.dart';
import 'package:hostel_mobile/src/api/response_parser.dart';
import 'package:hostel_mobile/src/models/room_model.dart';
import 'package:hostel_mobile/src/models/student_model.dart';

class RoomRepository {
  final ApiClient _client;

  RoomRepository(this._client);

  Future<List<RoomModel>> fetchRooms({int? floorId}) async {
    final response = await _client.get(
      Endpoints.rooms,
      query: floorId != null ? {'floor_id': floorId} : null,
    );
    final items = extractDataList(response.data);
    return items.map(RoomModel.fromJson).toList();
  }

  Future<RoomModel> fetchRoom(int id) async {
    final response = await _client.get(Endpoints.roomDetails(id));
    return RoomModel.fromJson(ensureMap(response.data));
  }

  Future<RoomModel> createRoom(Map<String, dynamic> payload) async {
    final response = await _client.post(Endpoints.rooms, data: payload);
    return RoomModel.fromJson(ensureMap(response.data));
  }

  Future<RoomModel> updateRoom(int id, Map<String, dynamic> payload) async {
    final response = await _client.put(Endpoints.roomDetails(id), data: payload);
    return RoomModel.fromJson(ensureMap(response.data));
  }

  Future<void> deleteRoom(int id) async {
    await _client.delete(Endpoints.roomDetails(id));
  }

  Future<List<StudentModel>> fetchStudents() async {
    final response = await _client.get(Endpoints.students);
    final items = extractDataList(response.data);
    return items.map(StudentModel.fromJson).toList();
  }

  Future<List<StudentModel>> fetchOccupants(int roomId) async {
    final response = await _client.get(Endpoints.roomOccupants(roomId));
    final items = extractDataList(response.data);
    return items.map(StudentModel.fromJson).toList();
  }

  Future<void> assignStudent(int roomId, int studentId) async {
    await _client.post(Endpoints.assignStudent(roomId), data: {
      'student_id': studentId,
    });
  }
}
