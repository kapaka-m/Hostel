import 'package:flutter/material.dart';

import 'package:hostel_mobile/src/domain/models/announcement_model.dart';
import 'package:hostel_mobile/src/domain/models/student_room_assignment_model.dart';
import 'package:hostel_mobile/src/domain/repositories/announcement_repository.dart';
import 'package:hostel_mobile/src/domain/repositories/student_repository.dart';

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
      announcements = await _announcementRepository.fetchAnnouncements();
    } catch (_) {
      errorMessage = 'Unable to load your session. Please try again.';
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }
}
