import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/providers/settings_provider.dart';
import 'package:hostel_mobile/src/ui/widgets/error_state.dart';
import 'package:hostel_mobile/src/ui/widgets/loading_state.dart';
import 'package:hostel_mobile/src/ui/widgets/section_header.dart';

class UniversityAdminSettingsScreen extends StatefulWidget {
  const UniversityAdminSettingsScreen({super.key});

  @override
  State<UniversityAdminSettingsScreen> createState() =>
      _UniversityAdminSettingsScreenState();
}

class _UniversityAdminSettingsScreenState extends State<UniversityAdminSettingsScreen> {
  final _allowlistController = TextEditingController();
  final _retentionController = TextEditingController();
  final Map<String, bool> _flags = {};

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _allowlistController.dispose();
    _retentionController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<SettingsProvider>();

    if (provider.isLoading && provider.settings == null) {
      return const LoadingState(message: 'Loading settings...');
    }

    if (provider.errorMessage != null && provider.settings == null) {
      return ErrorState(
        message: provider.errorMessage!,
        onRetry: () async {
          await provider.load();
          if (!context.mounted) return;
          _syncFromProvider(provider);
        },
      );
    }

    return Scaffold(
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: ListView(
          children: [
            const SectionHeader(title: 'Settings'),
            const SizedBox(height: 12),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Feature flags', style: Theme.of(context).textTheme.titleMedium),
                    const SizedBox(height: 8),
                    ..._flags.entries.map(
                      (entry) => SwitchListTile(
                        title: Text(entry.key),
                        value: entry.value,
                        contentPadding: EdgeInsets.zero,
                        onChanged: (value) => setState(() => _flags[entry.key] = value),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Admin IP allowlist', style: Theme.of(context).textTheme.titleMedium),
                    const SizedBox(height: 8),
                    TextField(
                      controller: _allowlistController,
                      minLines: 3,
                      maxLines: 6,
                      decoration: const InputDecoration(
                        labelText: 'One IP per line',
                        border: OutlineInputBorder(),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text('Activity retention days',
                        style: Theme.of(context).textTheme.titleMedium),
                    const SizedBox(height: 8),
                    TextField(
                      controller: _retentionController,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(
                        labelText: 'Retention days',
                        border: OutlineInputBorder(),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: provider.isSaving ? null : _submit,
                child: provider.isSaving
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Text('Save settings'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _load() async {
    final provider = context.read<SettingsProvider>();
    await provider.load();
    if (!mounted) return;
    _syncFromProvider(provider);
  }

  void _syncFromProvider(SettingsProvider provider) {
    final settings = provider.settings;
    if (settings == null) return;

    _flags
      ..clear()
      ..addAll(settings.featureFlags);
    _allowlistController.text = settings.adminIpAllowlist.join('\n');
    _retentionController.text = settings.activityRetentionDays.toString();
    if (mounted) {
      setState(() {});
    }
  }

  Future<void> _submit() async {
    final provider = context.read<SettingsProvider>();
    final retention = int.tryParse(_retentionController.text.trim()) ?? 90;
    final allowlist = _allowlistController.text
        .split('\n')
        .map((line) => line.trim())
        .where((line) => line.isNotEmpty)
        .toList();

    final success = await provider.update(
      featureFlags: _flags,
      adminIpAllowlist: allowlist,
      activityRetentionDays: retention,
    );

    if (!mounted) return;
    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Settings updated.')),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(provider.errorMessage ?? 'Unable to save settings.')),
      );
    }
  }
}
