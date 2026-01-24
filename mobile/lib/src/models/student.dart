class Student {
  Student({
    required this.id,
    required this.userId,
    required this.dormId,
    required this.fullName,
    required this.studentNo,
    this.phone,
    this.email,
  });

  final int id;
  final int userId;
  final int dormId;
  final String fullName;
  final String studentNo;
  final String? phone;
  final String? email;

  factory Student.fromJson(Map<String, dynamic> json) {
    return Student(
      id: json['id'] as int,
      userId: json['user_id'] as int,
      dormId: json['dorm_id'] as int,
      fullName: json['full_name'] as String? ?? '',
      studentNo: json['student_no'] as String? ?? '',
      phone: json['phone'] as String?,
      email: json['email'] as String?,
    );
  }
}
