import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';

import 'config/app_config.dart';
import 'providers/auth_provider.dart';
import 'services/api_service.dart';
import 'screens/dorm_admin/dorm_home_screen.dart';
import 'screens/dorm_admin/room_detail_screen.dart';
import 'screens/login_screen.dart';
import 'screens/splash_screen.dart';
import 'screens/student/student_home_screen.dart';
import 'screens/university_admin/university_home_screen.dart';

class HostelApp extends StatefulWidget {
  const HostelApp({super.key});

  @override
  State<HostelApp> createState() => _HostelAppState();
}

class _HostelAppState extends State<HostelApp> {
  late final ApiService _apiService;
  late final AuthProvider _authProvider;
  late final RouterNotifier _routerNotifier;
  late final GoRouter _router;

  @override
  void initState() {
    super.initState();
    _apiService = ApiService(baseUrl: AppConfig.baseUrl);
    _authProvider = AuthProvider(_apiService);
    _routerNotifier = RouterNotifier(_authProvider);
    _router = _createRouter();
  }

  @override
  void dispose() {
    _routerNotifier.dispose();
    _authProvider.dispose();
    super.dispose();
  }

  GoRouter _createRouter() {
    return GoRouter(
      refreshListenable: _routerNotifier,
      routes: [
        GoRoute(path: '/', builder: (context, state) => const SplashScreen()),
        GoRoute(
          path: '/login',
          builder: (context, state) => const LoginScreen(),
        ),
        GoRoute(
          path: '/university',
          builder: (context, state) => const UniversityHomeScreen(),
        ),
        GoRoute(
          path: '/dorm',
          builder: (context, state) => const DormHomeScreen(),
        ),
        GoRoute(
          path: '/dorm/rooms/:id',
          builder: (context, state) {
            final id = int.parse(state.pathParameters['id']!);
            return RoomDetailScreen(roomId: id);
          },
        ),
        GoRoute(
          path: '/student',
          builder: (context, state) => const StudentHomeScreen(),
        ),
      ],
      redirect: (context, state) {
        final path = state.uri.path;
        if (!_authProvider.isInitialized) {
          return path == '/' ? null : '/';
        }

        final isLoggedIn = _authProvider.isAuthenticated;
        if (!isLoggedIn) {
          return path == '/login' ? null : '/login';
        }

        final role = _authProvider.user?.role ?? '';
        final home = _homeForRole(role);

        if (path == '/' || path == '/login') {
          return home;
        }

        if (role == 'UNIVERSITY_ADMIN' && !path.startsWith('/university')) {
          return home;
        }
        if (role == 'DORM_ADMIN' && !path.startsWith('/dorm')) {
          return home;
        }
        if (role == 'STUDENT' && !path.startsWith('/student')) {
          return home;
        }

        return null;
      },
    );
  }

  String _homeForRole(String role) {
    switch (role) {
      case 'UNIVERSITY_ADMIN':
        return '/university';
      case 'DORM_ADMIN':
        return '/dorm';
      case 'STUDENT':
        return '/student';
      default:
        return '/login';
    }
  }

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [ChangeNotifierProvider.value(value: _authProvider)],
      child: MaterialApp.router(
        title: 'Hostel',
        theme: ThemeData(
          colorScheme: ColorScheme.fromSeed(seedColor: Colors.indigo),
          useMaterial3: true,
        ),
        routerConfig: _router,
      ),
    );
  }
}

class RouterNotifier extends ChangeNotifier {
  RouterNotifier(this._authProvider) {
    _authProvider.addListener(notifyListeners);
  }

  final AuthProvider _authProvider;

  @override
  void dispose() {
    _authProvider.removeListener(notifyListeners);
    super.dispose();
  }
}
