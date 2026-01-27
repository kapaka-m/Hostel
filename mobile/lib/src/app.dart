import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/data/api/api_client.dart';
import 'package:hostel_mobile/src/domain/repositories/announcement_repository.dart';
import 'package:hostel_mobile/src/domain/repositories/dorm_repository.dart';
import 'package:hostel_mobile/src/domain/repositories/report_repository.dart';
import 'package:hostel_mobile/src/domain/repositories/room_repository.dart';
import 'package:hostel_mobile/src/domain/repositories/student_repository.dart';
import 'package:hostel_mobile/src/domain/repositories/ticket_repository.dart';
import 'package:hostel_mobile/src/features/auth/auth_provider.dart';
import 'package:hostel_mobile/src/routing/app_router.dart';
import 'package:hostel_mobile/src/ui/theme/app_theme.dart';
import 'package:hostel_mobile/src/ui/theme/theme_provider.dart';

class HostelApp extends StatefulWidget {
  final AuthProvider authProvider;
  final ApiClient apiClient;

  const HostelApp({
    super.key,
    required this.authProvider,
    required this.apiClient,
  });

  @override
  State<HostelApp> createState() => _HostelAppState();
}

class _HostelAppState extends State<HostelApp> {
  late final GoRouter _router;

  @override
  void initState() {
    super.initState();
    _router = createAppRouter(widget.authProvider);
  }

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider<AuthProvider>.value(value: widget.authProvider),
        ChangeNotifierProvider<ThemeProvider>(create: (_) => ThemeProvider()),
        Provider<ApiClient>.value(value: widget.apiClient),
        Provider<AnnouncementRepository>(
          create: (_) => AnnouncementRepository(widget.apiClient),
        ),
        Provider<StudentRepository>(
          create: (_) => StudentRepository(widget.apiClient),
        ),
        Provider<RoomRepository>(
          create: (_) => RoomRepository(widget.apiClient),
        ),
        Provider<TicketRepository>(
          create: (_) => TicketRepository(widget.apiClient),
        ),
        Provider<DormRepository>(
          create: (_) => DormRepository(widget.apiClient),
        ),
        Provider<ReportRepository>(
          create: (_) => ReportRepository(widget.apiClient),
        ),
      ],
      child: Consumer<ThemeProvider>(
        builder: (context, theme, child) {
          return MaterialApp.router(
            debugShowCheckedModeBanner: false,
            routerConfig: _router,
            theme: AppTheme.lightTheme,
            darkTheme: AppTheme.darkTheme,
            themeMode: theme.mode,
          );
        },
      ),
    );
  }
}
