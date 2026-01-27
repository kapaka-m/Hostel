import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/data/api/api_client.dart';
import 'package:hostel_mobile/src/data/storage/token_storage.dart';
import 'package:hostel_mobile/src/domain/models/user_model.dart';
import 'package:hostel_mobile/src/domain/repositories/auth_repository.dart';
import 'package:hostel_mobile/src/features/auth/auth_provider.dart';
import 'package:hostel_mobile/src/ui/screens/login_screen.dart';

class FakeAuthRepository implements AuthRepository {
  @override
  Future<AuthSession> login({required String email, required String password}) async {
    return AuthSession(
      token: 'fake',
      user: UserModel(id: 1, name: 'Fake', email: 'fake@example.com', role: 'STUDENT'),
    );
  }

  @override
  Future<void> logout() async {}

  @override
  Future<UserModel> me() async {
    return UserModel(id: 1, name: 'Fake', email: 'fake@example.com', role: 'STUDENT');
  }
}

class FakeTokenStorage implements TokenStorage {
  @override
  Future<void> clearSession() async {}

  @override
  Future<StoredSession?> readSession() async => null;

  @override
  Future<void> saveSession(StoredSession session) async {}
}

class FakeApiClient extends ApiClient {}

void main() {
  testWidgets('login screen renders form fields', (tester) async {
    final provider = AuthProvider(FakeAuthRepository(), FakeTokenStorage(), FakeApiClient());

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider<AuthProvider>.value(value: provider),
        ],
        child: const MaterialApp(home: LoginScreen()),
      ),
    );

    await tester.pumpAndSettle();

    expect(find.text('Hostel Mobile'), findsOneWidget);
    expect(find.byType(TextFormField), findsNWidgets(2));
    expect(find.text('Sign in'), findsOneWidget);
  });
}
