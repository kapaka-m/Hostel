enum UserRole {
  universityAdmin,
  dormAdmin,
  student,
  superAdmin,
}

extension UserRoleExtension on UserRole {
  String get name {
    switch (this) {
      case UserRole.universityAdmin:
        return 'UNIVERSITY_ADMIN';
      case UserRole.dormAdmin:
        return 'DORM_ADMIN';
      case UserRole.student:
        return 'STUDENT';
      case UserRole.superAdmin:
        return 'SUPER_ADMIN';
    }
  }

  bool isStudent() => this == UserRole.student;

  bool isDormAdmin() => this == UserRole.dormAdmin;

  bool isUniversityAdmin() => this == UserRole.universityAdmin;

  bool isSuperAdmin() => this == UserRole.superAdmin;

  static UserRole? fromString(String? value) {
    switch (value) {
      case 'UNIVERSITY_ADMIN':
        return UserRole.universityAdmin;
      case 'DORM_ADMIN':
        return UserRole.dormAdmin;
      case 'STUDENT':
        return UserRole.student;
      case 'SUPER_ADMIN':
        return UserRole.superAdmin;
      default:
        return null;
    }
  }
}
