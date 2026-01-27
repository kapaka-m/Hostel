class RoomModel {
  final int id;
  final int dormId;
  final int? floorId;
  final String roomNumber;
  final int capacity;
  final String? status;
  final int occupancy;

  RoomModel({
    required this.id,
    required this.dormId,
    this.floorId,
    required this.roomNumber,
    required this.capacity,
    this.status,
    required this.occupancy,
  });

  factory RoomModel.fromJson(Map<String, dynamic> json) {
    return RoomModel(
      id: json['id'] as int,
      dormId: json['dorm_id'] as int,
      floorId: json['floor_id'] as int?,
      roomNumber: json['room_number'] as String? ?? '',
      capacity: json['capacity'] as int? ?? 0,
      status: json['status'] as String?,
      occupancy: json['occupancy'] as int? ?? 0,
    );
  }
}
