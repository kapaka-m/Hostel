class FloorModel {
  final int id;
  final int dormId;
  final int number;
  final int? bathrooms;
  final int? kitchens;
  final int? showers;

  FloorModel({
    required this.id,
    required this.dormId,
    required this.number,
    this.bathrooms,
    this.kitchens,
    this.showers,
  });

  factory FloorModel.fromJson(Map<String, dynamic> json) {
    return FloorModel(
      id: json['id'] as int,
      dormId: json['dorm_id'] as int,
      number: json['number'] as int,
      bathrooms: json['bathrooms'] as int?,
      kitchens: json['kitchens'] as int?,
      showers: json['showers'] as int?,
    );
  }
}
