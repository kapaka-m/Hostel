import 'package:hostel_mobile/src/models/dorm_model.dart';
import 'package:hostel_mobile/src/models/ticket_comment_model.dart';
import 'package:hostel_mobile/src/models/user_model.dart';

class TicketModel {
  final int id;
  final String subject;
  final String description;
  final String? category;
  final String? priority;
  final String? status;
  final DateTime? resolvedAt;
  final DateTime createdAt;
  final DateTime updatedAt;
  final DormModel? dorm;
  final UserModel? creator;
  final UserModel? assignee;
  final List<TicketCommentModel> comments;

  TicketModel({
    required this.id,
    required this.subject,
    required this.description,
    this.category,
    this.priority,
    this.status,
    this.resolvedAt,
    required this.createdAt,
    required this.updatedAt,
    this.dorm,
    this.creator,
    this.assignee,
    required this.comments,
  });

  factory TicketModel.fromJson(Map<String, dynamic> json) {
    final rawComments = json['comments'];
    Iterable<dynamic>? commentsList;
    if (rawComments is Map && rawComments['data'] is Iterable) {
      commentsList = rawComments['data'] as Iterable<dynamic>;
    } else if (rawComments is Iterable) {
      commentsList = rawComments;
    }
    return TicketModel(
      id: json['id'] as int,
      subject: json['subject'] as String? ?? '',
      description: json['description'] as String? ?? '',
      category: json['category'] as String?,
      priority: json['priority'] as String?,
      status: json['status'] as String?,
      resolvedAt: _toDate(json['resolved_at']),
      createdAt: _toDate(json['created_at']) ?? DateTime.now(),
      updatedAt: _toDate(json['updated_at']) ?? DateTime.now(),
      dorm: json['dorm'] is Map<String, dynamic>
          ? DormModel.fromJson(Map<String, dynamic>.from(json['dorm'] as Map))
          : null,
      creator: json['creator'] is Map<String, dynamic>
          ? UserModel.fromJson(Map<String, dynamic>.from(json['creator'] as Map))
          : null,
      assignee: json['assignee'] is Map<String, dynamic>
          ? UserModel.fromJson(Map<String, dynamic>.from(json['assignee'] as Map))
          : null,
      comments: commentsList != null
          ? commentsList
              .whereType<Map<String, dynamic>>()
              .map(TicketCommentModel.fromJson)
              .toList()
          : const [],
    );
  }

  static DateTime? _toDate(dynamic value) {
    if (value is String && value.isNotEmpty) {
      return DateTime.tryParse(value);
    }
    return null;
  }
}

