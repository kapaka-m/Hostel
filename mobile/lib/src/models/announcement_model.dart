import 'package:hostel_mobile/src/models/dorm_model.dart';
import 'package:hostel_mobile/src/models/user_model.dart';

class AnnouncementModel {
  final int id;
  final String title;
  final String body;
  final String? audience;
  final String? status;
  final String? currentStatus;
  final DateTime? publishAt;
  final DateTime? expireAt;
  final DateTime createdAt;
  final DateTime updatedAt;
  final DormModel? dorm;
  final UserModel? creator;

  AnnouncementModel({
    required this.id,
    required this.title,
    required this.body,
    this.audience,
    this.status,
    this.currentStatus,
    this.publishAt,
    this.expireAt,
    required this.createdAt,
    required this.updatedAt,
    this.dorm,
    this.creator,
  });

  factory AnnouncementModel.fromJson(Map<String, dynamic> json) {
    return AnnouncementModel(
      id: json['id'] as int,
      title: json['title'] as String? ?? '',
      body: json['body'] as String? ?? '',
      audience: json['audience'] as String?,
      status: json['status'] as String?,
      currentStatus: json['current_status'] as String?,
      publishAt: _toDate(json['publish_at']),
      expireAt: _toDate(json['expire_at']),
      createdAt: _toDate(json['created_at']) ?? DateTime.now(),
      updatedAt: _toDate(json['updated_at']) ?? DateTime.now(),
      dorm: json['dorm'] is Map<String, dynamic>
          ? DormModel.fromJson(Map<String, dynamic>.from(json['dorm'] as Map))
          : null,
      creator: json['creator'] is Map<String, dynamic>
          ? UserModel.fromJson(Map<String, dynamic>.from(json['creator'] as Map))
          : null,
    );
  }

  static DateTime? _toDate(dynamic value) {
    if (value is String && value.isNotEmpty) {
      return DateTime.tryParse(value);
    }
    return null;
  }
}

