import 'package:flutter/material.dart';

import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/repositories/floor_repository.dart';
import 'package:hostel_mobile/src/models/floor_model.dart';

class FloorsProvider extends ChangeNotifier {
  final FloorRepository _repository;

  FloorsProvider(this._repository);

  bool isLoading = false;
  bool isSubmitting = false;
  String? errorMessage;
  List<FloorModel> floors = [];

  Future<void> load() async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      floors = await _repository.fetchFloors();
    } catch (error) {
      errorMessage = _resolveError(error);
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> saveFloor(Map<String, dynamic> payload, {int? floorId}) async {
    isSubmitting = true;
    errorMessage = null;
    notifyListeners();

    try {
      if (floorId == null) {
        await _repository.createFloor(payload);
      } else {
        await _repository.updateFloor(floorId, payload);
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

  Future<bool> deleteFloor(int floorId) async {
    isSubmitting = true;
    errorMessage = null;
    notifyListeners();

    try {
      await _repository.deleteFloor(floorId);
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
    return 'Unable to load floors.';
  }
}
