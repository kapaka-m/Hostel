import 'package:flutter/material.dart';
import 'package:hostel_mobile/src/app.dart';
import 'package:hostel_mobile/src/data/api/api_client.dart';
import 'package:hostel_mobile/src/data/storage/token_storage.dart';
import 'package:hostel_mobile/src/domain/repositories/auth_repository.dart';
import 'package:hostel_mobile/src/features/auth/auth_provider.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  final tokenStorage = createTokenStorage();
  final apiClient = ApiClient();
  final authRepository = AuthRepository(apiClient);
  final authProvider = AuthProvider(
    authRepository,
    tokenStorage,
    apiClient,
  );

  apiClient.onUnauthorized = authProvider.handleUnauthorized;
  await authProvider.initialize();

  runApp(HostelApp(
    authProvider: authProvider,
    apiClient: apiClient,
  ));
}
