import 'package:flutter_test/flutter_test.dart';

import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/repositories/auth_repository.dart';
import 'package:hostel_mobile/src/auth/auth_provider.dart';
import 'package:hostel_mobile/src/auth/token_storage.dart';
import 'package:hostel_mobile/src/models/user_model.dart';
import 'package:hostel_mobile/src/models/user_role.dart';
import 'package:hostel_mobile/src/routing/app_router.dart';

class FakeAuthRepository implements AuthRepository {
  final UserModel user;

  FakeAuthRepository(this.user);

  @override
  Future<AuthSession> login({required String email, required String password}) async {
    return AuthSession(token: 'token', user: user);
  }

  @override
  Future<void> logout() async {}

  @override
  Future<UserModel> me() async => user;
}

class FakeTokenStorage implements TokenStorage {
  @override
  Future<void> clearSession() async {}

  @override
  Future<StoredSession?> readSession() async => null;

  @override
  Future<void> saveSession(StoredSession session) async {}
}

void main() {
  test('roleHome returns student path', () {
    expect(roleHome(UserRole.student), '/student/home');
    expect(roleHome(UserRole.dormAdmin), '/dorm-admin/rooms');
    expect(roleHome(UserRole.universityAdmin), '/university-admin/overview');
    expect(roleHome(UserRole.superAdmin), '/university-admin/overview');
    expect(roleHome(null), '/login');
  });

  test('rolePrefix enforces role-safe prefixes', () {
    expect(rolePrefix(UserRole.student), '/student');
    expect(rolePrefix(UserRole.dormAdmin), '/dorm-admin');
    expect(rolePrefix(UserRole.universityAdmin), '/university-admin');
    expect(rolePrefix(UserRole.superAdmin), '/university-admin');
    expect(rolePrefix(null), isNull);
  });

  test('student role is redirected away from admin routes', () async {
    final user = UserModel(id: 1, name: 'Student', email: 's@example.com', role: 'STUDENT');
    final provider = AuthProvider(
      FakeAuthRepository(user),
      FakeTokenStorage(),
      ApiClient(),
    );
    await provider.login(email: 's@example.com', password: 'pass');

    final redirect = authRedirect(provider, '/university-admin/overview');
    expect(redirect, '/student/home');
  });

  test('logged in users are redirected from login to role home', () async {
    final user = UserModel(id: 2, name: 'Admin', email: 'a@example.com', role: 'UNIVERSITY_ADMIN');
    final provider = AuthProvider(
      FakeAuthRepository(user),
      FakeTokenStorage(),
      ApiClient(),
    );
    await provider.login(email: 'a@example.com', password: 'pass');

    final redirect = authRedirect(provider, '/login');
    expect(redirect, '/university-admin/overview');
  });
}

