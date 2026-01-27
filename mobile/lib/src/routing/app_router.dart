import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/features/auth/auth_provider.dart';
import 'package:hostel_mobile/src/domain/models/user_role.dart';
import 'package:hostel_mobile/src/domain/repositories/room_repository.dart';
import 'package:hostel_mobile/src/domain/repositories/ticket_repository.dart';
import 'package:hostel_mobile/src/features/dorm_admin/dorm_admin_provider.dart';
import 'package:hostel_mobile/src/ui/components/role_shell.dart';
import 'package:hostel_mobile/src/ui/screens/dorm_admin_room_detail_screen.dart';
import 'package:hostel_mobile/src/ui/screens/dorm_admin_rooms_screen.dart';
import 'package:hostel_mobile/src/ui/screens/dorm_admin_students_screen.dart';
import 'package:hostel_mobile/src/ui/screens/dorm_admin_tickets_screen.dart';
import 'package:hostel_mobile/src/ui/screens/login_screen.dart';
import 'package:hostel_mobile/src/ui/screens/student_home_screen.dart';
import 'package:hostel_mobile/src/ui/screens/student_tickets_stub_screen.dart';
import 'package:hostel_mobile/src/ui/screens/university_admin_dorm_admins_screen.dart';
import 'package:hostel_mobile/src/ui/screens/university_admin_dorms_screen.dart';
import 'package:hostel_mobile/src/ui/screens/university_admin_overview_screen.dart';
import 'package:hostel_mobile/src/ui/screens/university_admin_tickets_screen.dart';

GoRouter createAppRouter(AuthProvider authProvider) {
  const studentDestinations = [
    RoleDestination(path: '/student/home', label: 'Home', icon: Icons.home),
    RoleDestination(path: '/student/tickets', label: 'Tickets', icon: Icons.support_agent),
  ];

  const dormDestinations = [
    RoleDestination(path: '/dorm-admin/rooms', label: 'Rooms', icon: Icons.meeting_room),
    RoleDestination(path: '/dorm-admin/students', label: 'Students', icon: Icons.group),
    RoleDestination(path: '/dorm-admin/tickets', label: 'Tickets', icon: Icons.report),
  ];

  const universityDestinations = [
    RoleDestination(path: '/university-admin/overview', label: 'Overview', icon: Icons.insights),
    RoleDestination(path: '/university-admin/dorms', label: 'Dorms', icon: Icons.apartment),
    RoleDestination(path: '/university-admin/dorm-admins', label: 'Dorm Admins', icon: Icons.person_add),
    RoleDestination(path: '/university-admin/tickets', label: 'Tickets', icon: Icons.report_gmailerrorred),
  ];

  return GoRouter(
    initialLocation: '/login',
    refreshListenable: authProvider,
    redirect: (context, state) {
      final loggedIn = authProvider.isAuthenticated;
      if (!loggedIn && state.location != '/login') {
        return '/login';
      }

      if (loggedIn && state.location == '/login') {
        return roleHome(authProvider.user?.userRole);
      }

      if (loggedIn) {
        final role = authProvider.user?.userRole;
        final prefix = rolePrefix(role);
        if (prefix != null && !state.location.startsWith(prefix)) {
          return roleHome(role);
        }
      }

      return null;
    },
    routes: [
      GoRoute(
        path: '/login',
        builder: (context, state) => const LoginScreen(),
      ),
      ShellRoute(
        builder: (context, state, child) => RoleShell(
          title: 'Student Portal',
          destinations: studentDestinations,
          currentLocation: state.location,
          child: child,
        ),
        routes: [
          GoRoute(
            path: '/student/home',
            builder: (context, state) => const StudentHomeScreen(),
          ),
          GoRoute(
            path: '/student/tickets',
            builder: (context, state) => const StudentTicketsStubScreen(),
          ),
        ],
      ),
      ShellRoute(
        builder: (context, state, child) =>
            ChangeNotifierProvider<DormAdminProvider>(
          create: (_) => DormAdminProvider(
            context.read<RoomRepository>(),
            context.read<TicketRepository>(),
          )..loadAll(),
          child: RoleShell(
            title: 'Dorm Admin',
            destinations: dormDestinations,
            currentLocation: state.location,
            child: child,
          ),
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
            path: '/dorm-admin/students',
            builder: (context, state) => const DormAdminStudentsScreen(),
          ),
          GoRoute(
            path: '/dorm-admin/tickets',
            builder: (context, state) => const DormAdminTicketsScreen(),
          ),
        ],
      ),
      ShellRoute(
        builder: (context, state, child) => RoleShell(
          title: 'University Admin',
          destinations: universityDestinations,
          currentLocation: state.location,
          child: child,
        ),
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
            path: '/university-admin/tickets',
            builder: (context, state) => const UniversityAdminTicketsScreen(),
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
