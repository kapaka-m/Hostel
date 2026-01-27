import 'package:hostel_mobile/src/models/user_role.dart';

class UserModel {
  final int id;
  final int? universityId;
  final String name;
  final String email;
  final String role;

  UserModel({
    required this.id,
    this.universityId,
    required this.name,
    required this.email,
    required this.role,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] as int,
      universityId: json['university_id'] as int?,
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
      role: json['role'] as String? ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'university_id': universityId,
      'name': name,
      'email': email,
      'role': role,
    };
  }

  UserRole? get userRole => UserRoleExtension.fromString(role);

  bool get isUniversityAdmin => userRole == UserRole.universityAdmin;

  bool get isDormAdmin => userRole == UserRole.dormAdmin;

  bool get isStudent => userRole == UserRole.student;

  bool get isSuperAdmin => userRole == UserRole.superAdmin;
}

