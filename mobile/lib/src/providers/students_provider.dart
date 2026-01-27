import 'package:flutter/material.dart';

import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/repositories/student_repository.dart';
import 'package:hostel_mobile/src/models/student_model.dart';

class StudentsProvider extends ChangeNotifier {
  final StudentRepository _repository;

  StudentsProvider(this._repository);

  bool isLoading = false;
  bool isSubmitting = false;
  String? errorMessage;
  List<StudentModel> students = [];
  String? generatedPassword;

  Future<void> load() async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      students = await _repository.fetchStudents();
    } catch (error) {
      errorMessage = _resolveError(error);
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> createStudent(Map<String, dynamic> payload) async {
    isSubmitting = true;
    errorMessage = null;
    generatedPassword = null;
    notifyListeners();

    try {
      final result = await _repository.createStudent(payload);
      generatedPassword = result.generatedPassword;
      await load();
      return true;
    } catch (error) {
      errorMessage = _resolveError(error);
      return false;
    } finally {
      isSubmitting = false;
      notifyListeners();
    }
  }

  Future<bool> updateStudent(int studentId, Map<String, dynamic> payload) async {
    isSubmitting = true;
    errorMessage = null;
    notifyListeners();

    try {
      await _repository.updateStudent(studentId, payload);
      await load();
      return true;
    } catch (error) {
      errorMessage = _resolveError(error);
      return false;
    } finally {
      isSubmitting = false;
      notifyListeners();
    }
  }

  Future<bool> deleteStudent(int studentId) async {
    isSubmitting = true;
    errorMessage = null;
    notifyListeners();

    try {
      await _repository.deleteStudent(studentId);
      await load();
      return true;
    } catch (error) {
      errorMessage = _resolveError(error);
      return false;
    } finally {
      isSubmitting = false;
      notifyListeners();
    }
  }

  String _resolveError(Object error) {
    if (error is ApiException) {
      return error.message;
    }
    return 'Unable to load students.';
  }
}
