import 'package:flutter/material.dart';

import 'package:hostel_mobile/src/domain/models/dorm_model.dart';
import 'package:hostel_mobile/src/domain/repositories/dorm_repository.dart';

class UniversityAdminDormsProvider extends ChangeNotifier {
  final DormRepository _dormRepository;

  UniversityAdminDormsProvider(this._dormRepository);

  bool isLoading = true;
  bool isSubmitting = false;
  String? errorMessage;
  List<DormModel> dorms = [];

  Future<void> loadDorms() async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      dorms = await _dormRepository.fetchDorms();
    } catch (_) {
      errorMessage = 'Unable to load dormitories.';
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> submitDorm(Map<String, dynamic> payload, {int? dormId}) async {
    isSubmitting = true;
    notifyListeners();

    try {
      if (dormId == null) {
        await _dormRepository.createDorm(payload);
      } else {
        await _dormRepository.updateDorm(dormId, payload);
      }
      await loadDorms();
    } catch (_) {
      errorMessage = 'Unable to save dorm details.';
    } finally {
      isSubmitting = false;
      notifyListeners();
    }
  }
}
