import 'package:flutter/material.dart';

enum StatusKind { ticket, announcement, room, generic }

class StatusBadge extends StatelessWidget {
  final String? label;
  final StatusKind kind;

  const StatusBadge({
    super.key,
    required this.label,
    this.kind = StatusKind.generic,
  });

  @override
  Widget build(BuildContext context) {
    final value = label?.trim();
    if (value == null || value.isEmpty) {
      return const SizedBox.shrink();
    }

    final scheme = Theme.of(context).colorScheme;
    final color = _resolveColor(value.toUpperCase(), scheme);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withAlpha(31),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color.withAlpha(102)),
      ),
      child: Text(
        value.replaceAll('_', ' '),
        style: Theme.of(context)
            .textTheme
            .labelMedium
            ?.copyWith(color: color, fontWeight: FontWeight.w600),
      ),
    );
  }

  Color _resolveColor(String value, ColorScheme scheme) {
    switch (kind) {
      case StatusKind.ticket:
        return _ticketColor(value, scheme);
      case StatusKind.announcement:
        return _announcementColor(value, scheme);
      case StatusKind.room:
        return _roomColor(value, scheme);
      case StatusKind.generic:
        return scheme.primary;
    }
  }

  Color _ticketColor(String value, ColorScheme scheme) {
    switch (value) {
      case 'OPEN':
        return scheme.primary;
      case 'IN_PROGRESS':
        return scheme.tertiary;
      case 'RESOLVED':
        return Colors.green.shade700;
      case 'CLOSED':
        return scheme.outline;
      default:
        return scheme.primary;
    }
  }

  Color _announcementColor(String value, ColorScheme scheme) {
    switch (value) {
      case 'DRAFT':
        return scheme.outline;
      case 'SCHEDULED':
        return scheme.tertiary;
      case 'PUBLISHED':
        return Colors.green.shade700;
      case 'EXPIRED':
        return Colors.red.shade700;
      default:
        return scheme.primary;
    }
  }

  Color _roomColor(String value, ColorScheme scheme) {
    switch (value) {
      case 'AVAILABLE':
        return Colors.green.shade700;
      case 'PARTIAL':
        return scheme.tertiary;
      case 'FULL':
        return Colors.red.shade700;
      default:
        return scheme.primary;
    }
  }
}
