import 'package:flutter_test/flutter_test.dart';

import 'package:hostel_mobile/src/domain/models/user_role.dart';
import 'package:hostel_mobile/src/routing/app_router.dart';

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
}
