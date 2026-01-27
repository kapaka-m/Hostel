import 'package:hostel_mobile/src/data/api/api_client.dart';
import 'package:hostel_mobile/src/data/api/endpoints.dart';
import 'package:hostel_mobile/src/domain/models/room_model.dart';
import 'package:hostel_mobile/src/domain/models/student_model.dart';
import 'package:hostel_mobile/src/domain/repositories/response_parser.dart';

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
    final payload = ensureMap(response.data);
    return RoomModel.fromJson(payload);
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
