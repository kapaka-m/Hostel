import 'package:flutter/material.dart';

import 'package:hostel_mobile/src/domain/models/room_model.dart';
import 'package:hostel_mobile/src/domain/models/student_model.dart';
import 'package:hostel_mobile/src/domain/models/ticket_model.dart';
import 'package:hostel_mobile/src/domain/repositories/room_repository.dart';
import 'package:hostel_mobile/src/domain/repositories/ticket_repository.dart';

class DormAdminProvider extends ChangeNotifier {
  final RoomRepository _roomRepository;
  final TicketRepository _ticketRepository;

  DormAdminProvider(this._roomRepository, this._ticketRepository);

  bool isLoading = true;
  String? errorMessage;
  List<RoomModel> rooms = [];
  List<StudentModel> students = [];
  List<TicketModel> tickets = [];

  Future<void> loadAll() async {
    isLoading = true;
    errorMessage = null;
    notifyListeners();

    try {
      rooms = await _roomRepository.fetchRooms();
      students = await _roomRepository.fetchStudents();
      tickets = await _ticketRepository.fetchTickets();
    } catch (_) {
      errorMessage = 'Unable to load dorm admin data.';
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> assignStudent(int roomId, int studentId) async {
    try {
      await _roomRepository.assignStudent(roomId, studentId);
      await loadAll();
    } catch (error) {
      rethrow;
    }
  }
}
