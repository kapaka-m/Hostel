import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/auth/auth_provider.dart';
import 'package:hostel_mobile/src/models/ticket_comment_model.dart';
import 'package:hostel_mobile/src/models/ticket_model.dart';
import 'package:hostel_mobile/src/providers/tickets_provider.dart';
import 'package:hostel_mobile/src/ui/widgets/error_state.dart';
import 'package:hostel_mobile/src/ui/widgets/loading_state.dart';
import 'package:hostel_mobile/src/ui/widgets/info_row.dart';
import 'package:hostel_mobile/src/ui/widgets/status_badge.dart';

class TicketDetailScreen extends StatefulWidget {
  final int ticketId;
  final String basePath;

  const TicketDetailScreen({
    super.key,
    required this.ticketId,
    required this.basePath,
  });

  @override
  State<TicketDetailScreen> createState() => _TicketDetailScreenState();
}

class _TicketDetailScreenState extends State<TicketDetailScreen> {
  late Future<TicketModel?> _future;
  final _commentController = TextEditingController();
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    _future = context.read<TicketsProvider>().fetchDetail(widget.ticketId);
  }

  @override
  void dispose() {
    _commentController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final canManage = auth.user?.isDormAdmin == true ||
        auth.user?.isUniversityAdmin == true ||
        auth.user?.isSuperAdmin == true;

    return FutureBuilder<TicketModel?>(
      future: _future,
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return const LoadingState(message: 'Loading ticket...');
        }

        if (snapshot.hasError || snapshot.data == null) {
          return ErrorState(
            message: 'Unable to load ticket.',
            onRetry: () {
              setState(() {
                _future = context.read<TicketsProvider>().fetchDetail(widget.ticketId);
              });
            },
          );
        }

        final ticket = snapshot.data!;

        return Padding(
          padding: const EdgeInsets.all(16),
          child: ListView(
            children: [
              Row(
                children: [
                  IconButton(
                    onPressed: () => context.pop(),
                    icon: const Icon(Icons.arrow_back),
                  ),
                  const SizedBox(width: 8),
                  Text('Ticket', style: Theme.of(context).textTheme.titleLarge),
                  const Spacer(),
                  if (canManage)
                    IconButton(
                      icon: const Icon(Icons.edit),
                      onPressed: () => context.go('${widget.basePath}/${ticket.id}/edit'),
                    ),
                ],
              ),
              const SizedBox(height: 16),
              Text(ticket.subject, style: Theme.of(context).textTheme.headlineSmall),
              const SizedBox(height: 8),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  StatusBadge(label: ticket.status, kind: StatusKind.ticket),
                  StatusBadge(label: ticket.priority, kind: StatusKind.generic),
                ],
              ),
              const SizedBox(height: 12),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(12),
                  child: Column(
                    children: [
                      InfoRow(
                        label: 'Category',
                        value: ticket.category ?? 'General',
                      ),
                      const SizedBox(height: 8),
                      InfoRow(
                        label: 'Dorm',
                        value: ticket.dorm?.name ?? 'N/A',
                      ),
                      const SizedBox(height: 8),
                      InfoRow(
                        label: 'Assignee',
                        value: ticket.assignee?.name ?? 'Unassigned',
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Text(ticket.description, style: Theme.of(context).textTheme.bodyLarge),
              const SizedBox(height: 24),
              Text('Comments', style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 8),
              if (ticket.comments.isEmpty)
                const Text('No comments yet.')
              else
                ...ticket.comments.map(_CommentTile.new),
              const SizedBox(height: 16),
              TextField(
                controller: _commentController,
                decoration: const InputDecoration(
                  labelText: 'Add a comment',
                  border: OutlineInputBorder(),
                ),
                minLines: 2,
                maxLines: 4,
              ),
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _isSubmitting ? null : () => _submitComment(ticket.id),
                  child: _isSubmitting
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Text('Post comment'),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _submitComment(int ticketId) async {
    if (_commentController.text.trim().isEmpty) {
      return;
    }

    setState(() => _isSubmitting = true);
    final provider = context.read<TicketsProvider>();
    final comment = await provider.addComment(ticketId, _commentController.text.trim());
    if (!mounted) return;
    setState(() => _isSubmitting = false);

    if (comment != null) {
      _commentController.clear();
      setState(() {
        _future = provider.fetchDetail(ticketId);
      });
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(provider.errorMessage ?? 'Unable to post comment.')),
      );
    }
  }
}

class _CommentTile extends StatelessWidget {
  final TicketCommentModel comment;

  const _CommentTile(this.comment);

  @override
  Widget build(BuildContext context) {
    return ListTile(
      contentPadding: EdgeInsets.zero,
      title: Text(comment.user?.name ?? 'User'),
      subtitle: Text(comment.body),
      trailing: Text(
        comment.createdAt.toLocal().toString().split('.').first,
        style: Theme.of(context).textTheme.bodySmall,
      ),
    );
  }
}
