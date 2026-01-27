import 'package:hostel_mobile/src/domain/models/dorm_model.dart';
import 'package:hostel_mobile/src/domain/models/floor_model.dart';
import 'package:hostel_mobile/src/domain/models/room_model.dart';
import 'package:hostel_mobile/src/domain/models/student_model.dart';

class StudentRoomAssignment {
  final DormModel? dorm;
  final FloorModel? floor;
  final RoomModel? room;
  final List<StudentModel> occupants;

  StudentRoomAssignment({
    this.dorm,
    this.floor,
    this.room,
    required this.occupants,
  });

  factory StudentRoomAssignment.fromJson(Map<String, dynamic> json) {
    final rawOccupants = json['occupants'] as Iterable<dynamic>?;
    return StudentRoomAssignment(
      dorm: json['dorm'] is Map<String, dynamic>
          ? DormModel.fromJson(Map<String, dynamic>.from(json['dorm'] as Map))
          : null,
      floor: json['floor'] is Map<String, dynamic>
          ? FloorModel.fromJson(Map<String, dynamic>.from(json['floor'] as Map))
          : null,
      room: json['room'] is Map<String, dynamic>
          ? RoomModel.fromJson(Map<String, dynamic>.from(json['room'] as Map))
          : null,
      occupants: rawOccupants != null
          ? rawOccupants
              .whereType<Map<String, dynamic>>()
              .map(StudentModel.fromJson)
              .toList()
          : const [],
    );
  }
}
