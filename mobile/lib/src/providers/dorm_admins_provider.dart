import 'package:flutter/material.dart';

import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/repositories/dorm_repository.dart';

class DormAdminsProvider extends ChangeNotifier {
  final DormRepository _repository;

  DormAdminsProvider(this._repository);

  bool isSubmitting = false;
  String? errorMessage;

  Future<bool> createDormAdmin({
    required int dormId,
    required String name,
    required String email,
    required String password,
  }) async {
    isSubmitting = true;
    errorMessage = null;
    notifyListeners();

    try {
      await _repository.inviteDormAdmin(
        dormId: dormId,
        name: name,
        email: email,
        password: password,
      );
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
    return 'Unable to create dorm admin.';
  }
}
