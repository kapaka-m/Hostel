import 'package:flutter_test/flutter_test.dart';

import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/auth/token_storage.dart';
import 'package:hostel_mobile/src/models/user_model.dart';
import 'package:hostel_mobile/src/api/repositories/auth_repository.dart';
import 'package:hostel_mobile/src/auth/auth_provider.dart';
import 'package:hostel_mobile/src/routing/app_router.dart';

class FakeAuthRepository implements AuthRepository {
  late AuthSession sessionToReturn;
  late UserModel meUser;

  @override
  Future<AuthSession> login({required String email, required String password}) async {
    return sessionToReturn;
  }

  @override
  Future<void> logout() async {}

  @override
  Future<UserModel> me() async => meUser;
}

class FakeTokenStorage implements TokenStorage {
  StoredSession? saved;

  @override
  Future<void> clearSession() async {
    saved = null;
  }

  @override
  Future<StoredSession?> readSession() async => saved;

  @override
  Future<void> saveSession(StoredSession session) async {
    saved = session;
  }
}

class FakeApiClient extends ApiClient {
  String? lastToken;

  FakeApiClient() : super();

  @override
  void setToken(String? token) {
    lastToken = token;
    super.setToken(token);
  }
}

void main() {
  test('login stores session and token', () async {
    final repo = FakeAuthRepository();
    final storage = FakeTokenStorage();
    final client = FakeApiClient();
    final user = UserModel(id: 1, name: 'Test', email: 'test@example.com', role: 'STUDENT');
    repo.sessionToReturn = AuthSession(token: 'abc123', user: user);
    repo.meUser = user;

    final provider = AuthProvider(repo, storage, client);

    await provider.login(email: 'test@example.com', password: 'password');

    expect(provider.isAuthenticated, isTrue);
    expect(provider.user?.email, 'test@example.com');
    expect(storage.saved?.token, 'abc123');
    expect(client.lastToken, 'abc123');
  });

  test('401 handling clears session and redirects to login', () async {
    final repo = FakeAuthRepository();
    final storage = FakeTokenStorage();
    final client = FakeApiClient();
    final user = UserModel(id: 1, name: 'Test', email: 'test@example.com', role: 'STUDENT');
    repo.sessionToReturn = AuthSession(token: 'abc123', user: user);
    repo.meUser = user;

    final provider = AuthProvider(repo, storage, client);
    await provider.login(email: 'test@example.com', password: 'password');
    expect(provider.isAuthenticated, isTrue);

    provider.handleUnauthorized();
    expect(provider.isAuthenticated, isFalse);
    expect(authRedirect(provider, '/student/home'), '/login');
  });
}

