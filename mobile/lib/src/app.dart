import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/repositories/activity_feed_repository.dart';
import 'package:hostel_mobile/src/api/repositories/announcement_repository.dart';
import 'package:hostel_mobile/src/api/repositories/audit_log_repository.dart';
import 'package:hostel_mobile/src/api/repositories/dorm_repository.dart';
import 'package:hostel_mobile/src/api/repositories/floor_repository.dart';
import 'package:hostel_mobile/src/api/repositories/report_repository.dart';
import 'package:hostel_mobile/src/api/repositories/room_repository.dart';
import 'package:hostel_mobile/src/api/repositories/settings_repository.dart';
import 'package:hostel_mobile/src/api/repositories/student_repository.dart';
import 'package:hostel_mobile/src/api/repositories/ticket_repository.dart';
import 'package:hostel_mobile/src/auth/auth_provider.dart';
import 'package:hostel_mobile/src/providers/activity_feed_provider.dart';
import 'package:hostel_mobile/src/providers/announcements_provider.dart';
import 'package:hostel_mobile/src/providers/audit_logs_provider.dart';
import 'package:hostel_mobile/src/providers/dorm_admins_provider.dart';
import 'package:hostel_mobile/src/providers/dorms_provider.dart';
import 'package:hostel_mobile/src/providers/floors_provider.dart';
import 'package:hostel_mobile/src/providers/reports_provider.dart';
import 'package:hostel_mobile/src/providers/rooms_provider.dart';
import 'package:hostel_mobile/src/providers/settings_provider.dart';
import 'package:hostel_mobile/src/providers/student_provider.dart';
import 'package:hostel_mobile/src/providers/students_provider.dart';
import 'package:hostel_mobile/src/providers/tickets_provider.dart';
import 'package:hostel_mobile/src/routing/app_router.dart';
import 'package:hostel_mobile/src/theme/app_theme.dart';
import 'package:hostel_mobile/src/theme/theme_provider.dart';

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
        Provider<ActivityFeedRepository>(
          create: (_) => ActivityFeedRepository(widget.apiClient),
        ),
        Provider<AuditLogRepository>(
          create: (_) => AuditLogRepository(widget.apiClient),
        ),
        Provider<StudentRepository>(
          create: (_) => StudentRepository(widget.apiClient),
        ),
        Provider<RoomRepository>(
          create: (_) => RoomRepository(widget.apiClient),
        ),
        Provider<FloorRepository>(
          create: (_) => FloorRepository(widget.apiClient),
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
        Provider<SettingsRepository>(
          create: (_) => SettingsRepository(widget.apiClient),
        ),
        ChangeNotifierProvider<AnnouncementsProvider>(
          create: (context) => AnnouncementsProvider(
            context.read<AnnouncementRepository>(),
          ),
        ),
        ChangeNotifierProvider<TicketsProvider>(
          create: (context) => TicketsProvider(
            context.read<TicketRepository>(),
          ),
        ),
        ChangeNotifierProvider<DormsProvider>(
          create: (context) => DormsProvider(
            context.read<DormRepository>(),
          ),
        ),
        ChangeNotifierProvider<DormAdminsProvider>(
          create: (context) => DormAdminsProvider(
            context.read<DormRepository>(),
          ),
        ),
        ChangeNotifierProvider<FloorsProvider>(
          create: (context) => FloorsProvider(
            context.read<FloorRepository>(),
          ),
        ),
        ChangeNotifierProvider<RoomsProvider>(
          create: (context) => RoomsProvider(
            context.read<RoomRepository>(),
          ),
        ),
        ChangeNotifierProvider<StudentsProvider>(
          create: (context) => StudentsProvider(
            context.read<StudentRepository>(),
          ),
        ),
        ChangeNotifierProvider<ReportsProvider>(
          create: (context) => ReportsProvider(
            context.read<ReportRepository>(),
          ),
        ),
        ChangeNotifierProvider<SettingsProvider>(
          create: (context) => SettingsProvider(
            context.read<SettingsRepository>(),
          ),
        ),
        ChangeNotifierProvider<AuditLogsProvider>(
          create: (context) => AuditLogsProvider(
            context.read<AuditLogRepository>(),
          ),
        ),
        ChangeNotifierProvider<ActivityFeedProvider>(
          create: (context) => ActivityFeedProvider(
            context.read<ActivityFeedRepository>(),
          ),
        ),
        ChangeNotifierProvider<StudentProvider>(
          create: (context) => StudentProvider(
            context.read<StudentRepository>(),
            context.read<AnnouncementRepository>(),
          ),
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

