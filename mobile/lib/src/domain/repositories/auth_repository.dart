
import 'package:hostel_mobile/src/data/api/api_client.dart';
import 'package:hostel_mobile/src/data/api/endpoints.dart';
import 'package:hostel_mobile/src/domain/models/user_model.dart';

class AuthSession {
  final String token;
  final UserModel user;

  AuthSession({
    required this.token,
    required this.user,
  });
}

class AuthRepository {
  final ApiClient _client;

  AuthRepository(this._client);

  Future<AuthSession> login({
    required String email,
    required String password,
  }) async {
    final response = await _client.post(Endpoints.login, data: {
      'email': email,
      'password': password,
    });

    final data = _castJson(response.data);
    final token = data['token'] as String?;
    final userJson = data['user'] as Map<String, dynamic>?;

    if (token == null || userJson == null) {
      throw ApiException(
        message: 'Invalid response from server.',
        statusCode: response.statusCode,
      );
    }

    return AuthSession(
      token: token,
      user: UserModel.fromJson(userJson),
    );
  }

  Future<UserModel> me() async {
    final response = await _client.get(Endpoints.me);
    final data = _castJson(response.data);
    return UserModel.fromJson(data);
  }

  Future<void> logout() async {
    await _client.post(Endpoints.logout);
  }

  Map<String, dynamic> _castJson(dynamic value) {
    if (value is Map<String, dynamic>) {
      return value;
    }

    throw ApiException(message: 'Unexpected response format.');
  }
}
