import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/auth/auth_provider.dart';
import 'package:hostel_mobile/src/models/user_role.dart';
import 'package:hostel_mobile/src/providers/settings_provider.dart';
import 'package:hostel_mobile/src/ui/screens/announcement_detail_screen.dart';
import 'package:hostel_mobile/src/ui/screens/announcement_form_screen.dart';
import 'package:hostel_mobile/src/ui/screens/announcements_screen.dart';
import 'package:hostel_mobile/src/ui/screens/dorm_admin_floors_screen.dart';
import 'package:hostel_mobile/src/ui/screens/dorm_admin_room_detail_screen.dart';
import 'package:hostel_mobile/src/ui/screens/dorm_admin_rooms_screen.dart';
import 'package:hostel_mobile/src/ui/screens/dorm_admin_students_screen.dart';
import 'package:hostel_mobile/src/ui/screens/dorm_admin_tickets_screen.dart';
import 'package:hostel_mobile/src/ui/screens/login_screen.dart';
import 'package:hostel_mobile/src/ui/screens/student_home_screen.dart';
import 'package:hostel_mobile/src/ui/screens/student_profile_screen.dart';
import 'package:hostel_mobile/src/ui/screens/student_room_screen.dart';
import 'package:hostel_mobile/src/ui/screens/ticket_detail_screen.dart';
import 'package:hostel_mobile/src/ui/screens/ticket_form_screen.dart';
import 'package:hostel_mobile/src/ui/screens/university_admin_activity_feed_screen.dart';
import 'package:hostel_mobile/src/ui/screens/university_admin_audit_logs_screen.dart';
import 'package:hostel_mobile/src/ui/screens/university_admin_dorm_admins_screen.dart';
import 'package:hostel_mobile/src/ui/screens/university_admin_dorms_screen.dart';
import 'package:hostel_mobile/src/ui/screens/university_admin_overview_screen.dart';
import 'package:hostel_mobile/src/ui/screens/university_admin_settings_screen.dart';
import 'package:hostel_mobile/src/ui/screens/university_admin_tickets_screen.dart';
import 'package:hostel_mobile/src/ui/widgets/app_scaffold.dart';

GoRouter createAppRouter(AuthProvider authProvider) {
  return GoRouter(
    initialLocation: '/login',
    refreshListenable: authProvider,
    redirect: (context, state) => authRedirect(authProvider, state.location),
    routes: [
      GoRoute(
        path: '/login',
        builder: (context, state) => const LoginScreen(),
      ),
      ShellRoute(
        builder: (context, state, child) => AppScaffold(
          title: 'Student',
          destinations: const [
            AppNavDestination(path: '/student/home', label: 'Home', icon: Icons.home),
            AppNavDestination(path: '/student/room', label: 'My Room', icon: Icons.home_work),
            AppNavDestination(
              path: '/student/announcements',
              label: 'Announcements',
              icon: Icons.campaign,
            ),
            AppNavDestination(path: '/student/profile', label: 'Profile', icon: Icons.person),
          ],
          currentLocation: state.location,
          child: child,
        ),
        routes: [
          GoRoute(
            path: '/student/home',
            builder: (context, state) => const StudentHomeScreen(),
          ),
          GoRoute(
            path: '/student/room',
            builder: (context, state) => const StudentRoomScreen(),
          ),
          GoRoute(
            path: '/student/announcements',
            builder: (context, state) =>
                const AnnouncementsScreen(basePath: '/student/announcements'),
          ),
          GoRoute(
            path: '/student/announcements/:id',
            builder: (context, state) => AnnouncementDetailScreen(
              announcementId: int.parse(state.pathParameters['id'] ?? '0'),
              basePath: '/student/announcements',
            ),
          ),
          GoRoute(
            path: '/student/profile',
            builder: (context, state) => const StudentProfileScreen(),
          ),
        ],
      ),
      ShellRoute(
        builder: (context, state, child) => AppScaffold(
          title: 'Dorm Admin',
          destinations: const [
            AppNavDestination(path: '/dorm-admin/rooms', label: 'Rooms', icon: Icons.meeting_room),
            AppNavDestination(path: '/dorm-admin/floors', label: 'Floors', icon: Icons.layers),
            AppNavDestination(path: '/dorm-admin/students', label: 'Students', icon: Icons.group),
            AppNavDestination(
              path: '/dorm-admin/announcements',
              label: 'Announcements',
              icon: Icons.campaign,
            ),
            AppNavDestination(path: '/dorm-admin/tickets', label: 'Tickets', icon: Icons.report),
          ],
          currentLocation: state.location,
          child: child,
        ),
        routes: [
          GoRoute(
            path: '/dorm-admin/rooms',
            builder: (context, state) => const DormAdminRoomsScreen(),
          ),
          GoRoute(
            path: '/dorm-admin/rooms/:roomId',
            builder: (context, state) => DormAdminRoomDetailScreen(
              roomId: int.tryParse(state.pathParameters['roomId'] ?? '') ?? 0,
            ),
          ),
          GoRoute(
            path: '/dorm-admin/floors',
            builder: (context, state) => const DormAdminFloorsScreen(),
          ),
          GoRoute(
            path: '/dorm-admin/students',
            builder: (context, state) => const DormAdminStudentsScreen(),
          ),
          GoRoute(
            path: '/dorm-admin/announcements',
            builder: (context, state) =>
                const AnnouncementsScreen(basePath: '/dorm-admin/announcements'),
          ),
          GoRoute(
            path: '/dorm-admin/announcements/new',
            builder: (context, state) => const AnnouncementFormScreen(),
          ),
          GoRoute(
            path: '/dorm-admin/announcements/:id',
            builder: (context, state) => AnnouncementDetailScreen(
              announcementId: int.parse(state.pathParameters['id'] ?? '0'),
              basePath: '/dorm-admin/announcements',
            ),
          ),
          GoRoute(
            path: '/dorm-admin/announcements/:id/edit',
            builder: (context, state) => AnnouncementFormScreen(
              announcementId: int.parse(state.pathParameters['id'] ?? '0'),
            ),
          ),
          GoRoute(
            path: '/dorm-admin/tickets',
            builder: (context, state) => const DormAdminTicketsScreen(),
          ),
          GoRoute(
            path: '/dorm-admin/tickets/new',
            builder: (context, state) => const TicketFormScreen(),
          ),
          GoRoute(
            path: '/dorm-admin/tickets/:ticketId',
            builder: (context, state) => TicketDetailScreen(
              ticketId: int.parse(state.pathParameters['ticketId'] ?? '0'),
              basePath: '/dorm-admin/tickets',
            ),
          ),
          GoRoute(
            path: '/dorm-admin/tickets/:ticketId/edit',
            builder: (context, state) => TicketFormScreen(
              ticketId: int.parse(state.pathParameters['ticketId'] ?? '0'),
            ),
          ),
        ],
      ),
      ShellRoute(
        builder: (context, state, child) {
          final settingsProvider = context.watch<SettingsProvider>();
          final flags = settingsProvider.featureFlags;
          final destinations = <AppNavDestination>[
            const AppNavDestination(
              path: '/university-admin/overview',
              label: 'Overview',
              icon: Icons.insights,
            ),
            const AppNavDestination(
              path: '/university-admin/dorms',
              label: 'Dorms',
              icon: Icons.apartment,
            ),
            const AppNavDestination(
              path: '/university-admin/dorm-admins',
              label: 'Dorm Admins',
              icon: Icons.person_add,
            ),
            const AppNavDestination(
              path: '/university-admin/announcements',
              label: 'Announcements',
              icon: Icons.campaign,
            ),
            const AppNavDestination(
              path: '/university-admin/tickets',
              label: 'Tickets',
              icon: Icons.report_gmailerrorred,
            ),
            const AppNavDestination(
              path: '/university-admin/settings',
              label: 'Settings',
              icon: Icons.settings,
            ),
          ];

          if (flags['activity_feed'] == true) {
            destinations.add(const AppNavDestination(
              path: '/university-admin/activity-feed',
              label: 'Activity Feed',
              icon: Icons.timeline,
            ));
          }

          if (flags['audit_logs'] == true) {
            destinations.add(const AppNavDestination(
              path: '/university-admin/audit-logs',
              label: 'Audit Logs',
              icon: Icons.receipt_long,
            ));
          }

          if (settingsProvider.settings == null && !settingsProvider.isLoading) {
            Future.microtask(() => settingsProvider.load());
          }

          return AppScaffold(
            title: 'University Admin',
            destinations: destinations,
            currentLocation: state.location,
            child: child,
          );
        },
        routes: [
          GoRoute(
            path: '/university-admin/overview',
            builder: (context, state) => const UniversityAdminOverviewScreen(),
          ),
          GoRoute(
            path: '/university-admin/dorms',
            builder: (context, state) => const UniversityAdminDormsScreen(),
          ),
          GoRoute(
            path: '/university-admin/dorm-admins',
            builder: (context, state) => const UniversityAdminDormAdminsScreen(),
          ),
          GoRoute(
            path: '/university-admin/announcements',
            builder: (context, state) =>
                const AnnouncementsScreen(basePath: '/university-admin/announcements'),
          ),
          GoRoute(
            path: '/university-admin/announcements/new',
            builder: (context, state) => const AnnouncementFormScreen(),
          ),
          GoRoute(
            path: '/university-admin/announcements/:id',
            builder: (context, state) => AnnouncementDetailScreen(
              announcementId: int.parse(state.pathParameters['id'] ?? '0'),
              basePath: '/university-admin/announcements',
            ),
          ),
          GoRoute(
            path: '/university-admin/announcements/:id/edit',
            builder: (context, state) => AnnouncementFormScreen(
              announcementId: int.parse(state.pathParameters['id'] ?? '0'),
            ),
          ),
          GoRoute(
            path: '/university-admin/tickets',
            builder: (context, state) => const UniversityAdminTicketsScreen(),
          ),
          GoRoute(
            path: '/university-admin/tickets/new',
            builder: (context, state) => const TicketFormScreen(),
          ),
          GoRoute(
            path: '/university-admin/tickets/:ticketId',
            builder: (context, state) => TicketDetailScreen(
              ticketId: int.parse(state.pathParameters['ticketId'] ?? '0'),
              basePath: '/university-admin/tickets',
            ),
          ),
          GoRoute(
            path: '/university-admin/tickets/:ticketId/edit',
            builder: (context, state) => TicketFormScreen(
              ticketId: int.parse(state.pathParameters['ticketId'] ?? '0'),
            ),
          ),
          GoRoute(
            path: '/university-admin/settings',
            builder: (context, state) => const UniversityAdminSettingsScreen(),
          ),
          GoRoute(
            path: '/university-admin/activity-feed',
            builder: (context, state) => const UniversityAdminActivityFeedScreen(),
          ),
          GoRoute(
            path: '/university-admin/audit-logs',
            builder: (context, state) => const UniversityAdminAuditLogsScreen(),
          ),
        ],
      ),
    ],
  );
}

String roleHome(UserRole? role) {
  switch (role) {
    case UserRole.student:
      return '/student/home';
    case UserRole.dormAdmin:
      return '/dorm-admin/rooms';
    case UserRole.universityAdmin:
    case UserRole.superAdmin:
      return '/university-admin/overview';
    default:
      return '/login';
  }
}

String? rolePrefix(UserRole? role) {
  switch (role) {
    case UserRole.student:
      return '/student';
    case UserRole.dormAdmin:
      return '/dorm-admin';
    case UserRole.universityAdmin:
    case UserRole.superAdmin:
      return '/university-admin';
    default:
      return null;
  }
}

String? authRedirect(AuthProvider authProvider, String location) {
  final loggedIn = authProvider.isAuthenticated;
  if (!loggedIn && location != '/login') {
    return '/login';
  }

  if (loggedIn && location == '/login') {
    return roleHome(authProvider.user?.userRole);
  }

  if (loggedIn) {
    final role = authProvider.user?.userRole;
    final prefix = rolePrefix(role);
    if (prefix != null && !location.startsWith(prefix)) {
      return roleHome(role);
    }
  }

  return null;
}
