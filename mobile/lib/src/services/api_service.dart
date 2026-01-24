import 'package:dio/dio.dart';

import '../models/create_student_result.dart';
import '../models/dorm.dart';
import '../models/floor.dart';
import '../models/login_result.dart';
import '../models/my_room.dart';
import '../models/room.dart';
import '../models/student.dart';
import '../models/user.dart';

class ApiService {
  ApiService({required String baseUrl})
    : _dio = Dio(
        BaseOptions(
          baseUrl: baseUrl,
          contentType: Headers.jsonContentType,
          responseType: ResponseType.json,
          headers: {'Accept': 'application/json'},
        ),
      ) {
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) {
          if (_token != null && _token!.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $_token';
          }
          handler.next(options);
        },
      ),
    );
  }

  final Dio _dio;
  String? _token;

  void setToken(String? token) {
    _token = token;
  }

  dynamic _unwrapData(dynamic data) {
    if (data is Map<String, dynamic> && data.containsKey('data')) {
      return data['data'];
    }
    return data;
  }

  Exception _mapError(DioException error) {
    final data = error.response?.data;
    if (data is Map<String, dynamic>) {
      final message = data['message']?.toString();
      if (message != null && message.isNotEmpty) {
        return Exception(message);
      }
    }
    return Exception('Request failed.');
  }

  Future<LoginResult> login(String email, String password) async {
    try {
      final response = await _dio.post(
        '/api/login',
        data: {'email': email, 'password': password},
      );

      final data = response.data as Map<String, dynamic>;
      final token = data['token'] as String? ?? '';
      final userJson = data['user'] as Map<String, dynamic>;

      return LoginResult(token: token, user: User.fromJson(userJson));
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<User> me() async {
    try {
      final response = await _dio.get('/api/me');
      final data = _unwrapData(response.data) as Map<String, dynamic>;
      return User.fromJson(data);
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<void> logout() async {
    try {
      await _dio.post('/api/logout');
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<List<Dorm>> getDorms() async {
    try {
      final response = await _dio.get('/api/dorms');
      final data = _unwrapData(response.data) as List<dynamic>;
      return data
          .map((item) => Dorm.fromJson(item as Map<String, dynamic>))
          .toList();
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<Dorm> createDorm({required String name, String? address}) async {
    try {
      final response = await _dio.post(
        '/api/dorms',
        data: {'name': name, 'address': address},
      );
      final data = _unwrapData(response.data) as Map<String, dynamic>;
      return Dorm.fromJson(data);
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<Dorm> updateDorm(
    int dormId, {
    required String name,
    String? address,
  }) async {
    try {
      final response = await _dio.put(
        '/api/dorms/$dormId',
        data: {'name': name, 'address': address},
      );
      final data = _unwrapData(response.data) as Map<String, dynamic>;
      return Dorm.fromJson(data);
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<void> deleteDorm(int dormId) async {
    try {
      await _dio.delete('/api/dorms/$dormId');
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<void> createDormAdmin({
    required int dormId,
    required String name,
    required String email,
    required String password,
  }) async {
    try {
      await _dio.post(
        '/api/dorms/$dormId/create-dorm-admin',
        data: {'name': name, 'email': email, 'password': password},
      );
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<List<Floor>> getFloors() async {
    try {
      final response = await _dio.get('/api/floors');
      final data = _unwrapData(response.data) as List<dynamic>;
      return data
          .map((item) => Floor.fromJson(item as Map<String, dynamic>))
          .toList();
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<Floor> createFloor({
    required int number,
    int bathrooms = 4,
    int kitchens = 2,
    int showers = 2,
  }) async {
    try {
      final response = await _dio.post(
        '/api/floors',
        data: {
          'number': number,
          'bathrooms': bathrooms,
          'kitchens': kitchens,
          'showers': showers,
        },
      );
      final data = _unwrapData(response.data) as Map<String, dynamic>;
      return Floor.fromJson(data);
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<Floor> updateFloor(
    int floorId, {
    required int number,
    int? bathrooms,
    int? kitchens,
    int? showers,
  }) async {
    try {
      final response = await _dio.put(
        '/api/floors/$floorId',
        data: {
          'number': number,
          'bathrooms': bathrooms,
          'kitchens': kitchens,
          'showers': showers,
        },
      );
      final data = _unwrapData(response.data) as Map<String, dynamic>;
      return Floor.fromJson(data);
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<void> deleteFloor(int floorId) async {
    try {
      await _dio.delete('/api/floors/$floorId');
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<List<Room>> getRooms({int? floorId}) async {
    try {
      final response = await _dio.get(
        '/api/rooms',
        queryParameters: {if (floorId != null) 'floor_id': floorId},
      );
      final data = _unwrapData(response.data) as List<dynamic>;
      return data
          .map((item) => Room.fromJson(item as Map<String, dynamic>))
          .toList();
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<Room> getRoom(int roomId) async {
    try {
      final response = await _dio.get('/api/rooms/$roomId');
      final data = _unwrapData(response.data) as Map<String, dynamic>;
      return Room.fromJson(data);
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<Room> createRoom({
    required int floorId,
    required String roomNumber,
    int capacity = 4,
  }) async {
    try {
      final response = await _dio.post(
        '/api/rooms',
        data: {
          'floor_id': floorId,
          'room_number': roomNumber,
          'capacity': capacity,
        },
      );
      final data = _unwrapData(response.data) as Map<String, dynamic>;
      return Room.fromJson(data);
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<Room> updateRoom(
    int roomId, {
    required int floorId,
    required String roomNumber,
    int? capacity,
  }) async {
    try {
      final response = await _dio.put(
        '/api/rooms/$roomId',
        data: {
          'floor_id': floorId,
          'room_number': roomNumber,
          'capacity': capacity,
        },
      );
      final data = _unwrapData(response.data) as Map<String, dynamic>;
      return Room.fromJson(data);
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<void> deleteRoom(int roomId) async {
    try {
      await _dio.delete('/api/rooms/$roomId');
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<void> assignStudent({
    required int roomId,
    required int studentId,
  }) async {
    try {
      await _dio.post(
        '/api/rooms/$roomId/assign-student',
        data: {'student_id': studentId},
      );
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<List<Student>> getRoomOccupants(int roomId) async {
    try {
      final response = await _dio.get('/api/rooms/$roomId/occupants');
      final data = _unwrapData(response.data) as List<dynamic>;
      return data
          .map((item) => Student.fromJson(item as Map<String, dynamic>))
          .toList();
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<List<Student>> getStudents() async {
    try {
      final response = await _dio.get('/api/students');
      final data = _unwrapData(response.data) as List<dynamic>;
      return data
          .map((item) => Student.fromJson(item as Map<String, dynamic>))
          .toList();
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<Student> updateStudent(
    int studentId, {
    required String fullName,
    required String studentNo,
    required String email,
    String? phone,
  }) async {
    try {
      final response = await _dio.put(
        '/api/students/$studentId',
        data: {
          'full_name': fullName,
          'student_no': studentNo,
          'email': email,
          'phone': phone,
        },
      );
      final data = _unwrapData(response.data) as Map<String, dynamic>;
      return Student.fromJson(data);
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<void> deleteStudent(int studentId) async {
    try {
      await _dio.delete('/api/students/$studentId');
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<CreateStudentResult> createStudent({
    required String fullName,
    required String studentNo,
    required String email,
    String? phone,
  }) async {
    try {
      final response = await _dio.post(
        '/api/students',
        data: {
          'full_name': fullName,
          'student_no': studentNo,
          'email': email,
          'phone': phone,
        },
      );
      final data = response.data as Map<String, dynamic>;
      final studentJson = data['student'] as Map<String, dynamic>;
      final password = data['generated_password'] as String? ?? '';

      return CreateStudentResult(
        student: Student.fromJson(studentJson),
        generatedPassword: password,
      );
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }

  Future<MyRoomInfo> getMyRoom() async {
    try {
      final response = await _dio.get('/api/student/my-room');
      final data = response.data as Map<String, dynamic>;
      return MyRoomInfo.fromJson(data);
    } on DioException catch (error) {
      throw _mapError(error);
    }
  }
}
