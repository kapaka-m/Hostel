import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/auth/auth_provider.dart';
import 'package:hostel_mobile/src/models/dorm_model.dart';
import 'package:hostel_mobile/src/models/ticket_model.dart';
import 'package:hostel_mobile/src/providers/dorms_provider.dart';
import 'package:hostel_mobile/src/providers/tickets_provider.dart';
import 'package:hostel_mobile/src/ui/widgets/app_text_field.dart';

class TicketFormScreen extends StatefulWidget {
  final TicketModel? initial;
  final int? ticketId;

  const TicketFormScreen({
    super.key,
    this.initial,
    this.ticketId,
  });

  @override
  State<TicketFormScreen> createState() => _TicketFormScreenState();
}

class _TicketFormScreenState extends State<TicketFormScreen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _subjectController;
  late final TextEditingController _descriptionController;
  late final TextEditingController _categoryController;
  String? _priority;
  String? _status;
  int? _dormId;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _subjectController = TextEditingController(text: widget.initial?.subject ?? '');
    _descriptionController = TextEditingController(text: widget.initial?.description ?? '');
    _categoryController = TextEditingController(text: widget.initial?.category ?? '');
    _priority = widget.initial?.priority;
    _status = widget.initial?.status;
    _dormId = widget.initial?.dorm?.id;

    final auth = context.read<AuthProvider>();
    if (auth.user?.isUniversityAdmin == true || auth.user?.isSuperAdmin == true) {
      context.read<DormsProvider>().load();
    }
    if (widget.initial == null && widget.ticketId != null) {
      _loadInitial(widget.ticketId!);
    }
  }

  @override
  void dispose() {
    _subjectController.dispose();
    _descriptionController.dispose();
    _categoryController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final isDormAdmin = auth.user?.isDormAdmin == true;
    final dorms = context.watch<DormsProvider>().dorms;
    final isEditing = widget.initial != null || _loadedInitial != null;

    return Padding(
      padding: const EdgeInsets.all(16),
      child: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Form(
        key: _formKey,
        child: ListView(
          children: [
            Row(
              children: [
                IconButton(
                  onPressed: () => Navigator.of(context).pop(),
                  icon: const Icon(Icons.arrow_back),
                ),
                const SizedBox(width: 8),
                Text(
                  isEditing ? 'Edit ticket' : 'New ticket',
                  style: Theme.of(context).textTheme.titleLarge,
                ),
              ],
            ),
            const SizedBox(height: 16),
            AppTextField(
              controller: _subjectController,
              label: 'Subject',
                validator: (value) => value == null || value.isEmpty ? 'Required' : null,
              ),
              const SizedBox(height: 12),
              AppTextField(
                controller: _descriptionController,
                label: 'Description',
                maxLines: 5,
                validator: (value) => value == null || value.isEmpty ? 'Required' : null,
              ),
              const SizedBox(height: 12),
              AppTextField(
                controller: _categoryController,
                label: 'Category (optional)',
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String?>(
                initialValue: _priority,
                decoration: const InputDecoration(labelText: 'Priority'),
                items: const [
                  DropdownMenuItem<String?>(value: null, child: Text('Default (Medium)')),
                  DropdownMenuItem<String?>(value: 'LOW', child: Text('Low')),
                  DropdownMenuItem<String?>(value: 'MEDIUM', child: Text('Medium')),
                  DropdownMenuItem<String?>(value: 'HIGH', child: Text('High')),
                  DropdownMenuItem<String?>(value: 'URGENT', child: Text('Urgent')),
                ],
                onChanged: (value) => setState(() => _priority = value),
              ),
              const SizedBox(height: 12),
              if (isEditing)
                DropdownButtonFormField<String?>(
                  initialValue: _status,
                  decoration: const InputDecoration(labelText: 'Status'),
                  items: const [
                    DropdownMenuItem<String?>(value: 'OPEN', child: Text('Open')),
                    DropdownMenuItem<String?>(value: 'IN_PROGRESS', child: Text('In progress')),
                    DropdownMenuItem<String?>(value: 'RESOLVED', child: Text('Resolved')),
                    DropdownMenuItem<String?>(value: 'CLOSED', child: Text('Closed')),
                  ],
                  onChanged: (value) => setState(() => _status = value),
                ),
              if (!isDormAdmin) ...[
                const SizedBox(height: 12),
                DropdownButtonFormField<int?>(
                  initialValue: _dormId,
                  decoration: const InputDecoration(labelText: 'Dorm (optional)'),
                  items: [
                    const DropdownMenuItem<int?>(value: null, child: Text('All dorms')),
                    ...dorms.map((DormModel dorm) => DropdownMenuItem<int?>(
                          value: dorm.id,
                          child: Text(dorm.name),
                        )),
                  ],
                  onChanged: (value) => setState(() => _dormId = value),
                ),
              ],
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: _submit,
                  child: const Text('Save'),
                ),
              ),
            ],
          ),
        ),
    );
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    final provider = context.read<TicketsProvider>();
    final payload = <String, dynamic>{
      'subject': _subjectController.text.trim(),
      'description': _descriptionController.text.trim(),
      if (_categoryController.text.trim().isNotEmpty) 'category': _categoryController.text.trim(),
      if (_priority != null) 'priority': _priority,
    };

    if (_status != null && widget.initial != null) {
      payload['status'] = _status;
    }
    if (_dormId != null) {
      payload['dorm_id'] = _dormId;
    }

    final existing = widget.initial ?? _loadedInitial;
    final success = existing == null
        ? await provider.createTicket(payload)
        : await provider.updateTicket(existing.id, payload);

    if (!mounted) return;
    if (success) {
      Navigator.of(context).pop(true);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(provider.errorMessage ?? 'Unable to save ticket.')),
      );
    }
  }

  TicketModel? _loadedInitial;

  Future<void> _loadInitial(int id) async {
    setState(() => _isLoading = true);
    final provider = context.read<TicketsProvider>();
    final ticket = await provider.fetchDetail(id);
    if (!mounted) return;
    if (ticket != null) {
      _loadedInitial = ticket;
      _subjectController.text = ticket.subject;
      _descriptionController.text = ticket.description;
      _categoryController.text = ticket.category ?? '';
      _priority = ticket.priority;
      _status = ticket.status;
      _dormId = ticket.dorm?.id;
    }
    setState(() => _isLoading = false);
  }
}
