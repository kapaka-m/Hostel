class Floor {
  Floor({
    required this.id,
    required this.dormId,
    required this.number,
    required this.bathrooms,
    required this.kitchens,
    required this.showers,
  });

  final int id;
  final int dormId;
  final int number;
  final int bathrooms;
  final int kitchens;
  final int showers;

  factory Floor.fromJson(Map<String, dynamic> json) {
    return Floor(
      id: json['id'] as int,
      dormId: json['dorm_id'] as int,
      number: json['number'] as int,
      bathrooms: json['bathrooms'] as int? ?? 0,
      kitchens: json['kitchens'] as int? ?? 0,
      showers: json['showers'] as int? ?? 0,
    );
  }
}
