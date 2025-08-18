import 'package:flutter/material.dart';

class MainShell extends StatelessWidget {
  final Widget child;

  const MainShell({Key? key, required this.child}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: child,
      bottomNavigationBar: BottomNavigationBar(
        type: BottomNavigationBarType.fixed,
        currentIndex: _getSelectedIndex(context),
        onTap: (index) => _onItemTapped(context, index),
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.home),
            label: 'Home',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.photo_library),
            label: 'Templates',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.edit),
            label: 'Create',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.folder),
            label: 'My Work',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person),
            label: 'Profile',
          ),
        ],
      ),
    );
  }

  int _getSelectedIndex(BuildContext context) {
    final location = ModalRoute.of(context)?.settings.name ?? '';
    if (location.contains('/templates')) return 1;
    if (location.contains('/editor')) return 2;
    if (location.contains('/creations')) return 3;
    if (location.contains('/profile')) return 4;
    return 0; // Home
  }

  void _onItemTapped(BuildContext context, int index) {
    switch (index) {
      case 0:
        Navigator.pushReplacementNamed(context, '/');
        break;
      case 1:
        Navigator.pushReplacementNamed(context, '/templates');
        break;
      case 2:
        Navigator.pushReplacementNamed(context, '/editor');
        break;
      case 3:
        Navigator.pushReplacementNamed(context, '/creations');
        break;
      case 4:
        Navigator.pushReplacementNamed(context, '/profile');
        break;
    }
  }
}