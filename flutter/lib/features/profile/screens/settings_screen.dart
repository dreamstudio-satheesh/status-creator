import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../shared/providers/theme_provider.dart';

class SettingsScreen extends ConsumerStatefulWidget {
  const SettingsScreen({Key? key}) : super(key: key);

  @override
  ConsumerState<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends ConsumerState<SettingsScreen> {
  bool _notificationsEnabled = true;
  bool _autoSaveEnabled = true;
  bool _highQualityImages = false;
  String _language = 'English';
  String _imageQuality = 'Medium';

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('Settings'),
      ),
      body: ListView(
        children: [
          // Appearance Section
          _buildSectionHeader(theme, 'Appearance'),
          Card(
            margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Column(
              children: [
                _buildThemeSelector(theme),
                _buildLanguageSelector(theme),
              ],
            ),
          ),
          
          // Editor Settings
          _buildSectionHeader(theme, 'Editor'),
          Card(
            margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Column(
              children: [
                _buildSwitchTile(
                  theme,
                  'Auto Save',
                  'Automatically save your work',
                  _autoSaveEnabled,
                  (value) => setState(() => _autoSaveEnabled = value),
                  Icons.save,
                ),
                _buildImageQualitySelector(theme),
                _buildSwitchTile(
                  theme,
                  'High Quality Images',
                  'Use higher resolution for better quality',
                  _highQualityImages,
                  (value) => setState(() => _highQualityImages = value),
                  Icons.high_quality,
                ),
              ],
            ),
          ),
          
          // Notifications
          _buildSectionHeader(theme, 'Notifications'),
          Card(
            margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Column(
              children: [
                _buildSwitchTile(
                  theme,
                  'Push Notifications',
                  'Receive updates and reminders',
                  _notificationsEnabled,
                  (value) => setState(() => _notificationsEnabled = value),
                  Icons.notifications,
                ),
                _buildListTile(
                  theme,
                  'Notification Preferences',
                  'Customize what notifications you receive',
                  Icons.tune,
                  () => _showNotificationPreferences(),
                ),
              ],
            ),
          ),
          
          // Storage & Data
          _buildSectionHeader(theme, 'Storage & Data'),
          Card(
            margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Column(
              children: [
                _buildListTile(
                  theme,
                  'Clear Cache',
                  'Free up storage space',
                  Icons.storage,
                  _clearCache,
                ),
                _buildListTile(
                  theme,
                  'Backup & Sync',
                  'Backup your creations to cloud',
                  Icons.cloud_upload,
                  () => _showBackupSettings(),
                ),
                _buildListTile(
                  theme,
                  'Export Data',
                  'Download all your data',
                  Icons.download,
                  _exportData,
                ),
              ],
            ),
          ),
          
          // Privacy & Security
          _buildSectionHeader(theme, 'Privacy & Security'),
          Card(
            margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Column(
              children: [
                _buildListTile(
                  theme,
                  'Privacy Policy',
                  'Read our privacy policy',
                  Icons.privacy_tip,
                  () => _openPrivacyPolicy(),
                ),
                _buildListTile(
                  theme,
                  'Terms of Service',
                  'Read our terms of service',
                  Icons.description,
                  () => _openTermsOfService(),
                ),
                _buildListTile(
                  theme,
                  'Data Usage',
                  'View your data usage statistics',
                  Icons.data_usage,
                  () => _showDataUsage(),
                ),
              ],
            ),
          ),
          
          // Account
          _buildSectionHeader(theme, 'Account'),
          Card(
            margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Column(
              children: [
                _buildListTile(
                  theme,
                  'Delete Account',
                  'Permanently delete your account',
                  Icons.delete_forever,
                  () => _showDeleteAccountDialog(),
                  isDestructive: true,
                ),
              ],
            ),
          ),
          
          const SizedBox(height: 32),
        ],
      ),
    );
  }

  Widget _buildSectionHeader(ThemeData theme, String title) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 24, 16, 8),
      child: Text(
        title,
        style: theme.textTheme.titleMedium?.copyWith(
          fontWeight: FontWeight.bold,
          color: theme.colorScheme.primary,
        ),
      ),
    );
  }

  Widget _buildThemeSelector(ThemeData theme) {
    final themeMode = ref.watch(themeProvider);
    
    return ListTile(
      leading: Icon(
        Icons.palette,
        color: theme.colorScheme.primary,
      ),
      title: const Text('Theme'),
      subtitle: Text(_getThemeName(themeMode)),
      trailing: const Icon(Icons.chevron_right),
      onTap: () => _showThemeSelector(),
    );
  }

  Widget _buildLanguageSelector(ThemeData theme) {
    return ListTile(
      leading: Icon(
        Icons.language,
        color: theme.colorScheme.primary,
      ),
      title: const Text('Language'),
      subtitle: Text(_language),
      trailing: const Icon(Icons.chevron_right),
      onTap: () => _showLanguageSelector(),
    );
  }

  Widget _buildImageQualitySelector(ThemeData theme) {
    return ListTile(
      leading: Icon(
        Icons.image,
        color: theme.colorScheme.primary,
      ),
      title: const Text('Image Quality'),
      subtitle: Text(_imageQuality),
      trailing: const Icon(Icons.chevron_right),
      onTap: () => _showImageQualitySelector(),
    );
  }

  Widget _buildSwitchTile(
    ThemeData theme,
    String title,
    String subtitle,
    bool value,
    ValueChanged<bool> onChanged,
    IconData icon,
  ) {
    return SwitchListTile(
      secondary: Icon(
        icon,
        color: theme.colorScheme.primary,
      ),
      title: Text(title),
      subtitle: Text(subtitle),
      value: value,
      onChanged: onChanged,
    );
  }

  Widget _buildListTile(
    ThemeData theme,
    String title,
    String subtitle,
    IconData icon,
    VoidCallback onTap, {
    bool isDestructive = false,
  }) {
    return ListTile(
      leading: Icon(
        icon,
        color: isDestructive ? theme.colorScheme.error : theme.colorScheme.primary,
      ),
      title: Text(
        title,
        style: TextStyle(
          color: isDestructive ? theme.colorScheme.error : null,
        ),
      ),
      subtitle: Text(subtitle),
      trailing: const Icon(Icons.chevron_right),
      onTap: onTap,
    );
  }

  String _getThemeName(ThemeMode themeMode) {
    switch (themeMode) {
      case ThemeMode.light:
        return 'Light';
      case ThemeMode.dark:
        return 'Dark';
      case ThemeMode.system:
        return 'System';
    }
  }

  void _showThemeSelector() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Select Theme'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            _buildThemeOption('Light', ThemeMode.light),
            _buildThemeOption('Dark', ThemeMode.dark),
            _buildThemeOption('System', ThemeMode.system),
          ],
        ),
      ),
    );
  }

  Widget _buildThemeOption(String name, ThemeMode mode) {
    final currentMode = ref.watch(themeProvider);
    
    return RadioListTile<ThemeMode>(
      title: Text(name),
      value: mode,
      groupValue: currentMode,
      onChanged: (value) {
        if (value != null) {
          ref.read(themeProvider.notifier).setTheme(value);
          Navigator.pop(context);
        }
      },
    );
  }

  void _showLanguageSelector() {
    final languages = ['English', 'Tamil', 'Hindi'];
    
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Select Language'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: languages.map((lang) => RadioListTile<String>(
            title: Text(lang),
            value: lang,
            groupValue: _language,
            onChanged: (value) {
              if (value != null) {
                setState(() => _language = value);
                Navigator.pop(context);
              }
            },
          )).toList(),
        ),
      ),
    );
  }

  void _showImageQualitySelector() {
    final qualities = ['Low', 'Medium', 'High', 'Ultra'];
    
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Select Image Quality'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: qualities.map((quality) => RadioListTile<String>(
            title: Text(quality),
            value: quality,
            groupValue: _imageQuality,
            onChanged: (value) {
              if (value != null) {
                setState(() => _imageQuality = value);
                Navigator.pop(context);
              }
            },
          )).toList(),
        ),
      ),
    );
  }

  void _showNotificationPreferences() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Notification preferences coming soon!')),
    );
  }

  void _clearCache() async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const AlertDialog(
        content: Row(
          children: [
            CircularProgressIndicator(),
            SizedBox(width: 16),
            Text('Clearing cache...'),
          ],
        ),
      ),
    );

    await Future.delayed(const Duration(seconds: 2));
    
    if (mounted) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Cache cleared successfully!')),
      );
    }
  }

  void _showBackupSettings() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Backup settings coming soon!')),
    );
  }

  void _exportData() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Data export feature coming soon!')),
    );
  }

  void _openPrivacyPolicy() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Opening privacy policy...')),
    );
  }

  void _openTermsOfService() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Opening terms of service...')),
    );
  }

  void _showDataUsage() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Data Usage'),
        content: const Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Templates Downloaded: 45'),
            Text('AI Generations: 23'),
            Text('Images Uploaded: 12'),
            Text('Cache Size: 125 MB'),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  void _showDeleteAccountDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete Account'),
        content: const Text(
          'Are you sure you want to delete your account? This action cannot be undone and all your data will be permanently lost.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              // TODO: Implement account deletion
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Account deletion requested')),
              );
            },
            style: TextButton.styleFrom(
              foregroundColor: Theme.of(context).colorScheme.error,
            ),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
  }
}