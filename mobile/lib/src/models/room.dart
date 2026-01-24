class Room {
  Room({
    required this.id,
    required this.dormId,
    required this.floorId,
    required this.roomNumber,
    required this.capacity,
    required this.status,
    required this.occupancy,
  });

  final int id;
  final int dormId;
  final int floorId;
  final String roomNumber;
  final int capacity;
  final String status;
  final int occupancy;

  factory Room.fromJson(Map<String, dynamic> json) {
    return Room(
      id: json['id'] as int,
      dormId: json['dorm_id'] as int,
      floorId: json['floor_id'] as int,
      roomNumber: json['room_number'] as String? ?? '',
      capacity: json['capacity'] as int? ?? 0,
      status: json['status'] as String? ?? 'AVAILABLE',
      occupancy: json['occupancy'] as int? ?? 0,
    );
  }
}
