import 'package:flutter/material.dart';

import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/repositories/room_repository.dart';
import 'package:hostel_mobile/src/models/room_model.dart';
import 'package:hostel_mobile/src/models/student_model.dart';

class RoomsProvider extends ChangeNotifier {
  final RoomRepository _repository;

  RoomsProvider(this._repository);

  bool isLoading = false;
  bool isSubmitting = false;
  String? errorMessage;
  List<RoomModel> rooms = [];

  Future<void> load({int? floorId}) async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      rooms = await _repository.fetchRooms(floorId: floorId);
    } catch (error) {
      errorMessage = _resolveError(error);
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<RoomModel?> fetchRoom(int id) async {
    try {
      return await _repository.fetchRoom(id);
    } catch (error) {
      errorMessage = _resolveError(error);
      notifyListeners();
      return null;
    }
  }

  Future<bool> saveRoom(Map<String, dynamic> payload, {int? roomId}) async {
    isSubmitting = true;
    errorMessage = null;
    notifyListeners();

    try {
      if (roomId == null) {
        await _repository.createRoom(payload);
      } else {
        await _repository.updateRoom(roomId, payload);
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

  Future<bool> deleteRoom(int id) async {
    isSubmitting = true;
    errorMessage = null;
    notifyListeners();

    try {
      await _repository.deleteRoom(id);
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

  Future<bool> assignStudent(int roomId, int studentId) async {
    try {
      await _repository.assignStudent(roomId, studentId);
      return true;
    } catch (error) {
      errorMessage = _resolveError(error);
      notifyListeners();
      return false;
    }
  }

  Future<List<StudentModel>> fetchOccupants(int roomId) async {
    try {
      return await _repository.fetchOccupants(roomId);
    } catch (error) {
      errorMessage = _resolveError(error);
      notifyListeners();
      return [];
    }
  }

  String _resolveError(Object error) {
    if (error is ApiException) {
      return error.message;
    }
    return 'Unable to load rooms.';
  }
}
