import 'package:flutter/material.dart';

class UserCreationsScreen extends StatefulWidget {
  const UserCreationsScreen({Key? key}) : super(key: key);

  @override
  State<UserCreationsScreen> createState() => _UserCreationsScreenState();
}

class _UserCreationsScreenState extends State<UserCreationsScreen> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Creations'),
      ),
      body: const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.photo_library,
              size: 80,
              color: Colors.grey,
            ),
            SizedBox(height: 16),
            Text(
              'My Creations',
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
              ),
            ),
            SizedBox(height: 8),
            Text(
              'Your created status images will appear here',
              style: TextStyle(
                color: Colors.grey,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}