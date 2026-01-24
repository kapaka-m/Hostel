import 'dorm.dart';
import 'floor.dart';
import 'room.dart';
import 'student.dart';

class MyRoomInfo {
  MyRoomInfo({this.dorm, this.floor, this.room, required this.occupants});

  final Dorm? dorm;
  final Floor? floor;
  final Room? room;
  final List<Student> occupants;

  factory MyRoomInfo.fromJson(Map<String, dynamic> json) {
    final occupantsJson = (json['occupants'] as List<dynamic>? ?? [])
        .map((item) => Student.fromJson(item as Map<String, dynamic>))
        .toList();

    final dormJson = json['dorm'] as Map<String, dynamic>?;
    final floorJson = json['floor'] as Map<String, dynamic>?;
    final roomJson = json['room'] as Map<String, dynamic>?;

    return MyRoomInfo(
      dorm: dormJson != null ? Dorm.fromJson(dormJson) : null,
      floor: floorJson != null ? Floor.fromJson(floorJson) : null,
      room: roomJson != null ? Room.fromJson(roomJson) : null,
      occupants: occupantsJson,
    );
  }
}
