class DormModel {
  final int id;
  final int? universityId;
  final String? code;
  final String name;
  final String? address;
  final int? capacity;
  final String? status;
  final String? contactName;
  final String? contactEmail;
  final String? contactPhone;
  final String? notes;

  DormModel({
    required this.id,
    this.universityId,
    this.code,
    required this.name,
    this.address,
    this.capacity,
    this.status,
    this.contactName,
    this.contactEmail,
    this.contactPhone,
    this.notes,
  });

  factory DormModel.fromJson(Map<String, dynamic> json) {
    return DormModel(
      id: json['id'] as int,
      universityId: json['university_id'] as int?,
      code: json['code'] as String?,
      name: json['name'] as String? ?? '',
      address: json['address'] as String?,
      capacity: json['capacity'] as int?,
      status: json['status'] as String?,
      contactName: json['contact_name'] as String?,
      contactEmail: json['contact_email'] as String?,
      contactPhone: json['contact_phone'] as String?,
      notes: json['notes'] as String?,
    );
  }
}
