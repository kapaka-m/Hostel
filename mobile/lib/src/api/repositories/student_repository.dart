import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/endpoints.dart';
import 'package:hostel_mobile/src/api/response_parser.dart';
import 'package:hostel_mobile/src/models/student_model.dart';
import 'package:hostel_mobile/src/models/student_room_assignment_model.dart';

class StudentCreateResult {
  final StudentModel student;
  final String generatedPassword;

  StudentCreateResult({
    required this.student,
    required this.generatedPassword,
  });
}

class StudentRepository {
  final ApiClient _client;

  StudentRepository(this._client);

  Future<StudentRoomAssignment> fetchMyRoom() async {
    final response = await _client.get(Endpoints.studentRoom);
    return StudentRoomAssignment.fromJson(ensureMap(response.data));
  }

  Future<List<StudentModel>> fetchStudents() async {
    final response = await _client.get(Endpoints.students);
    final items = extractDataList(response.data);
    return items.map(StudentModel.fromJson).toList();
  }

  Future<StudentCreateResult> createStudent(Map<String, dynamic> payload) async {
    final response = await _client.post(Endpoints.students, data: payload);
    final data = ensureMap(response.data);
    final studentJson = data['student'];
    final generatedPassword = data['generated_password'] as String? ?? '';
    if (studentJson is Map<String, dynamic>) {
      return StudentCreateResult(
        student: StudentModel.fromJson(studentJson),
        generatedPassword: generatedPassword,
      );
    }
    throw ApiException(message: 'Unexpected student payload.', statusCode: response.statusCode);
  }

  Future<StudentModel> updateStudent(int studentId, Map<String, dynamic> payload) async {
    final response = await _client.put(Endpoints.studentDetails(studentId), data: payload);
    return StudentModel.fromJson(ensureMap(response.data));
  }

  Future<void> deleteStudent(int studentId) async {
    await _client.delete(Endpoints.studentDetails(studentId));
  }
}
