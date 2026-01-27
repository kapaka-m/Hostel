import 'package:hostel_mobile/src/data/api/api_client.dart';
import 'package:hostel_mobile/src/data/api/endpoints.dart';
import 'package:hostel_mobile/src/domain/models/student_room_assignment_model.dart';
import 'package:hostel_mobile/src/domain/repositories/response_parser.dart';

class StudentRepository {
  final ApiClient _client;

  StudentRepository(this._client);

  Future<StudentRoomAssignment> fetchMyRoom() async {
    final response = await _client.get(Endpoints.studentRoom);
    final payload = ensureMap(response.data);
    return StudentRoomAssignment.fromJson(payload);
  }
}
