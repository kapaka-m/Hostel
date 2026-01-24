import 'student.dart';

class CreateStudentResult {
  CreateStudentResult({required this.student, required this.generatedPassword});

  final Student student;
  final String generatedPassword;
}
