import 'package:flutter/material.dart';

import 'package:hostel_mobile/src/api/api_client.dart';
import 'package:hostel_mobile/src/api/repositories/announcement_repository.dart';
import 'package:hostel_mobile/src/api/repositories/student_repository.dart';
import 'package:hostel_mobile/src/models/announcement_model.dart';
import 'package:hostel_mobile/src/models/student_room_assignment_model.dart';

class StudentProvider extends ChangeNotifier {
  final StudentRepository _studentRepository;
  final AnnouncementRepository _announcementRepository;

  StudentProvider(this._studentRepository, this._announcementRepository);

  bool isLoading = true;
  String? errorMessage;
  StudentRoomAssignment? assignment;
  List<AnnouncementModel> announcements = [];

  Future<void> load() async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      assignment = await _studentRepository.fetchMyRoom();
      final result = await _announcementRepository.fetchAnnouncements();
      announcements = result.items;
    } catch (error) {
      if (error is ApiException) {
        errorMessage = error.message;
      } else {
        errorMessage = 'Unable to load your session. Please try again.';
      }
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }
}

