import 'package:hostel_mobile/src/models/user_model.dart';

class TicketCommentModel {
  final int id;
  final String body;
  final DateTime createdAt;
  final UserModel? user;

  TicketCommentModel({
    required this.id,
    required this.body,
    required this.createdAt,
    this.user,
  });

  factory TicketCommentModel.fromJson(Map<String, dynamic> json) {
    return TicketCommentModel(
      id: json['id'] as int,
      body: json['body'] as String? ?? '',
      createdAt: DateTime.tryParse(json['created_at'] as String? ?? '') ?? DateTime.now(),
      user: json['user'] is Map<String, dynamic>
          ? UserModel.fromJson(Map<String, dynamic>.from(json['user'] as Map))
          : null,
    );
  }
}

