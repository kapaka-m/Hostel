import 'package:flutter/material.dart';

import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/repositories/dorm_repository.dart';
import 'package:hostel_mobile/src/models/dorm_model.dart';

class DormsProvider extends ChangeNotifier {
  final DormRepository _repository;

  DormsProvider(this._repository);

  bool isLoading = false;
  bool isSubmitting = false;
  String? errorMessage;
  List<DormModel> dorms = [];

  Future<void> load() async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      dorms = await _repository.fetchDorms();
    } catch (error) {
      errorMessage = _resolveError(error);
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> saveDorm(Map<String, dynamic> payload, {int? dormId}) async {
    isSubmitting = true;
    errorMessage = null;
    notifyListeners();

    try {
      if (dormId == null) {
        await _repository.createDorm(payload);
      } else {
        await _repository.updateDorm(dormId, payload);
      }
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

  Future<bool> deleteDorm(int dormId) async {
    isSubmitting = true;
    errorMessage = null;
    notifyListeners();

    try {
      await _repository.deleteDorm(dormId);
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
    return 'Unable to load dorms.';
  }
}
