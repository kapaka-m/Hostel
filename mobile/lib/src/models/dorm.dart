class Dorm {
  Dorm({required this.id, required this.name, this.address});

  final int id;
  final String name;
  final String? address;

  factory Dorm.fromJson(Map<String, dynamic> json) {
    return Dorm(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      address: json['address'] as String?,
    );
  }
}
