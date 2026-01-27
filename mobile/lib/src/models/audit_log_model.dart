class AuditActor {
  final int? id;
  final String? name;
  final String? email;
  final String? role;

  AuditActor({
    this.id,
    this.name,
    this.email,
    this.role,
  });

  factory AuditActor.fromJson(Map<String, dynamic> json) {
    return AuditActor(
      id: json['id'] as int?,
      name: json['name'] as String?,
      email: json['email'] as String?,
      role: json['role'] as String?,
    );
  }
}

class AuditLogModel {
  final int id;
  final AuditActor? actor;
  final int? universityId;
  final String? action;
  final String? entityType;
  final int? entityId;
  final dynamic before;
  final dynamic after;
  final String? ip;
  final String? userAgent;
  final String? correlationId;
  final DateTime? createdAt;

  AuditLogModel({
    required this.id,
    this.actor,
    this.universityId,
    this.action,
    this.entityType,
    this.entityId,
    this.before,
    this.after,
    this.ip,
    this.userAgent,
    this.correlationId,
    this.createdAt,
  });

  factory AuditLogModel.fromJson(Map<String, dynamic> json) {
    return AuditLogModel(
      id: json['id'] as int? ?? 0,
      actor: json['actor'] is Map<String, dynamic>
          ? AuditActor.fromJson(Map<String, dynamic>.from(json['actor'] as Map))
          : null,
      universityId: json['university_id'] as int?,
      action: json['action'] as String?,
      entityType: json['entity_type'] as String?,
      entityId: json['entity_id'] as int?,
      before: json['before'],
      after: json['after'],
      ip: json['ip'] as String?,
      userAgent: json['user_agent'] as String?,
      correlationId: json['correlation_id'] as String?,
      createdAt: _toDate(json['created_at']),
    );
  }

  static DateTime? _toDate(dynamic value) {
    if (value is String && value.isNotEmpty) {
      return DateTime.tryParse(value);
    }
    return null;
  }
}
