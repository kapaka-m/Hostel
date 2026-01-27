import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/auth/auth_provider.dart';
import 'package:hostel_mobile/src/models/announcement_model.dart';
import 'package:hostel_mobile/src/models/dorm_model.dart';
import 'package:hostel_mobile/src/providers/announcements_provider.dart';
import 'package:hostel_mobile/src/providers/dorms_provider.dart';
import 'package:hostel_mobile/src/ui/widgets/app_text_field.dart';

class AnnouncementFormScreen extends StatefulWidget {
  final AnnouncementModel? initial;
  final int? announcementId;

  const AnnouncementFormScreen({
    super.key,
    this.initial,
    this.announcementId,
  });

  @override
  State<AnnouncementFormScreen> createState() => _AnnouncementFormScreenState();
}

class _AnnouncementFormScreenState extends State<AnnouncementFormScreen> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _titleController;
  late final TextEditingController _bodyController;
  late final TextEditingController _publishAtController;
  late final TextEditingController _expireAtController;
  String _status = 'DRAFT';
  String _audience = 'UNIVERSITY';
  int? _dormId;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _titleController = TextEditingController(text: widget.initial?.title ?? '');
    _bodyController = TextEditingController(text: widget.initial?.body ?? '');
    _publishAtController = TextEditingController(
      text: widget.initial?.publishAt?.toIso8601String() ?? '',
    );
    _expireAtController = TextEditingController(
      text: widget.initial?.expireAt?.toIso8601String() ?? '',
    );
    _status = widget.initial?.status ?? 'DRAFT';
    _audience = widget.initial?.audience ?? 'UNIVERSITY';
    _dormId = widget.initial?.dorm?.id;

    final auth = context.read<AuthProvider>();
    if (auth.user?.isUniversityAdmin == true || auth.user?.isSuperAdmin == true) {
      context.read<DormsProvider>().load();
    }
    if (widget.initial == null && widget.announcementId != null) {
      _loadInitial(widget.announcementId!);
    }
  }

  @override
  void dispose() {
    _titleController.dispose();
    _bodyController.dispose();
    _publishAtController.dispose();
    _expireAtController.dispose();
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
                  isEditing ? 'Edit announcement' : 'New announcement',
                  style: Theme.of(context).textTheme.titleLarge,
                ),
              ],
            ),
            const SizedBox(height: 16),
            AppTextField(
              controller: _titleController,
              label: 'Title',
              validator: (value) => value == null || value.isEmpty ? 'Required' : null,
            ),
              const SizedBox(height: 12),
              AppTextField(
                controller: _bodyController,
                label: 'Body',
                maxLines: 5,
                validator: (value) => value == null || value.isEmpty ? 'Required' : null,
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                initialValue: _status,
                decoration: const InputDecoration(labelText: 'Status'),
                items: const [
                  DropdownMenuItem(value: 'DRAFT', child: Text('Draft')),
                  DropdownMenuItem(value: 'PUBLISHED', child: Text('Publish now')),
                  DropdownMenuItem(value: 'SCHEDULED', child: Text('Scheduled')),
                ],
                onChanged: (value) => setState(() => _status = value ?? 'DRAFT'),
              ),
              const SizedBox(height: 12),
              if (!isDormAdmin) ...[
                DropdownButtonFormField<String>(
                  initialValue: _audience,
                  decoration: const InputDecoration(labelText: 'Audience'),
                  items: const [
                    DropdownMenuItem(value: 'UNIVERSITY', child: Text('University')),
                    DropdownMenuItem(value: 'DORM', child: Text('Dorm')),
                  ],
                  onChanged: (value) => setState(() => _audience = value ?? 'UNIVERSITY'),
                ),
                const SizedBox(height: 12),
                if (_audience == 'DORM')
                  DropdownButtonFormField<int>(
                    initialValue: _dormId,
                    decoration: const InputDecoration(labelText: 'Dorm'),
                    items: dorms
                        .map((DormModel dorm) => DropdownMenuItem(
                              value: dorm.id,
                              child: Text(dorm.name),
                            ))
                        .toList(),
                    onChanged: (value) => setState(() => _dormId = value),
                    validator: (value) =>
                        _audience == 'DORM' && value == null ? 'Select a dorm' : null,
                  ),
              ],
              const SizedBox(height: 12),
              AppTextField(
                controller: _publishAtController,
                label: 'Publish at (optional)',
                hint: 'YYYY-MM-DD HH:MM',
              ),
              const SizedBox(height: 12),
              AppTextField(
                controller: _expireAtController,
                label: 'Expire at (optional)',
                hint: 'YYYY-MM-DD HH:MM',
              ),
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

    final auth = context.read<AuthProvider>();
    final isDormAdmin = auth.user?.isDormAdmin == true;

    final payload = <String, dynamic>{
      'title': _titleController.text.trim(),
      'body': _bodyController.text.trim(),
      'status': _status,
    };

    if (!isDormAdmin) {
      payload['audience'] = _audience;
      if (_audience == 'DORM' && _dormId != null) {
        payload['dorm_id'] = _dormId;
      }
    }

    if (_publishAtController.text.trim().isNotEmpty) {
      payload['publish_at'] = _publishAtController.text.trim();
    }
    if (_expireAtController.text.trim().isNotEmpty) {
      payload['expire_at'] = _expireAtController.text.trim();
    }

    final provider = context.read<AnnouncementsProvider>();
    final existing = widget.initial ?? _loadedInitial;
    final success = existing == null
        ? await provider.createAnnouncement(payload)
        : await provider.updateAnnouncement(existing.id, payload);

    if (!mounted) return;
    if (success) {
      Navigator.of(context).pop(true);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(provider.errorMessage ?? 'Unable to save announcement.')),
      );
    }
  }

  AnnouncementModel? _loadedInitial;

  Future<void> _loadInitial(int id) async {
    setState(() => _isLoading = true);
    final provider = context.read<AnnouncementsProvider>();
    final announcement = await provider.fetchDetail(id);
    if (!mounted) return;
    if (announcement != null) {
      _loadedInitial = announcement;
      _titleController.text = announcement.title;
      _bodyController.text = announcement.body;
      _publishAtController.text = announcement.publishAt?.toIso8601String() ?? '';
      _expireAtController.text = announcement.expireAt?.toIso8601String() ?? '';
      _status = announcement.status ?? 'DRAFT';
      _audience = announcement.audience ?? 'UNIVERSITY';
      _dormId = announcement.dorm?.id;
    }
    setState(() => _isLoading = false);
  }
}
