import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../shared/providers/theme_provider.dart';

class ProfileScreen extends ConsumerStatefulWidget {
  const ProfileScreen({Key? key}) : super(key: key);

  @override
  ConsumerState<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends ConsumerState<ProfileScreen> {
  final _user = UserProfile(
    name: 'User Name',
    email: 'user@example.com',
    phone: '+91 9876543210',
    isPremium: false,
    creationsCount: 25,
    downloadsCount: 1200,
    joinDate: DateTime(2024, 1, 15),
  );

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('Profile'),
        actions: [
          IconButton(
            icon: const Icon(Icons.settings),
            onPressed: () => context.pushNamed('settings'),
          ),
        ],
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            // Profile Header
            _buildProfileHeader(theme),
            
            const SizedBox(height: 16),
            
            // Stats Cards
            _buildStatsSection(theme),
            
            const SizedBox(height: 16),
            
            // Quick Actions
            _buildQuickActions(theme),
            
            const SizedBox(height: 16),
            
            // Menu Items
            _buildMenuItems(theme),
          ],
        ),
      ),
    );
  }

  Widget _buildProfileHeader(ThemeData theme) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            theme.colorScheme.primary,
            theme.colorScheme.primary.withOpacity(0.8),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Column(
        children: [
          // Profile Picture
          Stack(
            children: [
              CircleAvatar(
                radius: 50,
                backgroundColor: theme.colorScheme.onPrimary,
                child: Icon(
                  Icons.person,
                  size: 60,
                  color: theme.colorScheme.primary,
                ),
              ),
              Positioned(
                bottom: 0,
                right: 0,
                child: CircleAvatar(
                  radius: 16,
                  backgroundColor: theme.colorScheme.secondary,
                  child: IconButton(
                    icon: Icon(
                      Icons.edit,
                      size: 16,
                      color: theme.colorScheme.onSecondary,
                    ),
                    onPressed: _editProfilePicture,
                  ),
                ),
              ),
            ],
          ),
          
          const SizedBox(height: 16),
          
          // User Name
          Text(
            _user.name,
            style: theme.textTheme.headlineSmall?.copyWith(
              color: theme.colorScheme.onPrimary,
              fontWeight: FontWeight.bold,
            ),
          ),
          
          const SizedBox(height: 4),
          
          // User Email
          Text(
            _user.email,
            style: theme.textTheme.bodyMedium?.copyWith(
              color: theme.colorScheme.onPrimary.withOpacity(0.9),
            ),
          ),
          
          const SizedBox(height: 8),
          
          // Premium Badge or Upgrade Button
          if (_user.isPremium)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
              decoration: BoxDecoration(
                color: Colors.orange,
                borderRadius: BorderRadius.circular(20),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.star, color: Colors.white, size: 16),
                  const SizedBox(width: 4),
                  Text(
                    'Premium Member',
                    style: theme.textTheme.bodySmall?.copyWith(
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
            )
          else
            ElevatedButton.icon(
              onPressed: () => context.pushNamed('subscription'),
              icon: const Icon(Icons.upgrade),
              label: const Text('Upgrade to Premium'),
              style: ElevatedButton.styleFrom(
                backgroundColor: theme.colorScheme.onPrimary,
                foregroundColor: theme.colorScheme.primary,
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildStatsSection(ThemeData theme) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Row(
        children: [
          Expanded(
            child: _buildStatCard(
              theme,
              'Creations',
              _user.creationsCount.toString(),
              Icons.create,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: _buildStatCard(
              theme,
              'Downloads',
              _formatNumber(_user.downloadsCount),
              Icons.download,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: _buildStatCard(
              theme,
              'Member Since',
              _formatDate(_user.joinDate),
              Icons.calendar_today,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard(ThemeData theme, String title, String value, IconData icon) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Icon(
              icon,
              size: 24,
              color: theme.colorScheme.primary,
            ),
            const SizedBox(height: 8),
            Text(
              value,
              style: theme.textTheme.titleMedium?.copyWith(
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              title,
              style: theme.textTheme.bodySmall?.copyWith(
                color: theme.colorScheme.onSurface.withOpacity(0.7),
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuickActions(ThemeData theme) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Quick Actions',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: _buildActionCard(
                  theme,
                  'My Creations',
                  Icons.photo_library,
                  () => context.go('/creations'),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _buildActionCard(
                  theme,
                  'Create New',
                  Icons.add_circle,
                  () => context.go('/editor'),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildActionCard(ThemeData theme, String title, IconData icon, VoidCallback onTap) {
    return Card(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [
              Icon(
                icon,
                size: 32,
                color: theme.colorScheme.primary,
              ),
              const SizedBox(height: 8),
              Text(
                title,
                style: theme.textTheme.bodyMedium?.copyWith(
                  fontWeight: FontWeight.w500,
                ),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMenuItems(ThemeData theme) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Account',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 12),
          Card(
            child: Column(
              children: [
                _buildMenuItem(
                  theme,
                  'Edit Profile',
                  Icons.edit,
                  _editProfile,
                ),
                _buildMenuItem(
                  theme,
                  'Subscription',
                  Icons.star,
                  () => context.pushNamed('subscription'),
                  trailing: _user.isPremium 
                      ? const Chip(label: Text('Active'), backgroundColor: Colors.orange)
                      : null,
                ),
                _buildMenuItem(
                  theme,
                  'Settings',
                  Icons.settings,
                  () => context.pushNamed('settings'),
                ),
                _buildMenuItem(
                  theme,
                  'Help & Support',
                  Icons.help,
                  _showHelp,
                ),
                _buildMenuItem(
                  theme,
                  'About',
                  Icons.info,
                  _showAbout,
                ),
                _buildMenuItem(
                  theme,
                  'Sign Out',
                  Icons.logout,
                  _signOut,
                  isDestructive: true,
                ),
              ],
            ),
          ),
          
          // Theme Toggle
          const SizedBox(height: 16),
          Card(
            child: _buildThemeToggle(theme),
          ),
        ],
      ),
    );
  }

  Widget _buildMenuItem(
    ThemeData theme,
    String title,
    IconData icon,
    VoidCallback onTap, {
    Widget? trailing,
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
      trailing: trailing ?? const Icon(Icons.chevron_right),
      onTap: onTap,
    );
  }

  Widget _buildThemeToggle(ThemeData theme) {
    final themeMode = ref.watch(themeProvider);
    
    return ListTile(
      leading: Icon(
        themeMode == ThemeMode.dark ? Icons.dark_mode : Icons.light_mode,
        color: theme.colorScheme.primary,
      ),
      title: const Text('Dark Mode'),
      trailing: Switch(
        value: themeMode == ThemeMode.dark,
        onChanged: (value) {
          ref.read(themeProvider.notifier).setTheme(
            value ? ThemeMode.dark : ThemeMode.light,
          );
        },
      ),
    );
  }

  void _editProfilePicture() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Profile picture editor coming soon!')),
    );
  }

  void _editProfile() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Profile editor coming soon!')),
    );
  }

  void _showHelp() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Help & Support'),
        content: const Text(
          'For help and support, please contact us at:\n\n'
          'Email: support@tamilstatus.com\n'
          'Phone: +91 9876543210\n\n'
          'We typically respond within 24 hours.',
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

  void _showAbout() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('About Tamil Status Creator'),
        content: const Text(
          'Version 1.0.0\n\n'
          'Create beautiful Tamil status images with AI-powered quotes and stunning templates.\n\n'
          'Developed with ❤️ for Tamil community.',
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

  void _signOut() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Sign Out'),
        content: const Text('Are you sure you want to sign out?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              // TODO: Implement sign out logic
              context.go('/login');
            },
            child: const Text('Sign Out'),
          ),
        ],
      ),
    );
  }

  String _formatNumber(int number) {
    if (number >= 1000) {
      return '${(number / 1000).toStringAsFixed(1)}K';
    }
    return number.toString();
  }

  String _formatDate(DateTime date) {
    final now = DateTime.now();
    final difference = now.difference(date);
    
    if (difference.inDays < 30) {
      return '${difference.inDays}d ago';
    } else if (difference.inDays < 365) {
      return '${(difference.inDays / 30).round()}m ago';
    } else {
      return '${(difference.inDays / 365).round()}y ago';
    }
  }
}

class UserProfile {
  final String name;
  final String email;
  final String phone;
  final bool isPremium;
  final int creationsCount;
  final int downloadsCount;
  final DateTime joinDate;

  UserProfile({
    required this.name,
    required this.email,
    required this.phone,
    required this.isPremium,
    required this.creationsCount,
    required this.downloadsCount,
    required this.joinDate,
  });
}