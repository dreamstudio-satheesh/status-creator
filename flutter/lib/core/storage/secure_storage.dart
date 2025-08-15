import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'dart:convert';

class SecureStorage {
  static const _storage = FlutterSecureStorage(
    aOptions: AndroidOptions(
      encryptedSharedPreferences: true,
      sharedPreferencesName: 'tamil_status_secure_prefs',
    ),
    iOptions: IOSOptions(
      groupId: 'group.com.example.tamilstatus',
      accountName: 'tamil_status_keychain',
    ),
  );

  // Write string value
  Future<void> write(String key, String value) async {
    await _storage.write(key: key, value: value);
  }

  // Read string value
  Future<String?> read(String key) async {
    return await _storage.read(key: key);
  }

  // Write JSON object
  Future<void> writeJson(String key, Map<String, dynamic> value) async {
    final jsonString = json.encode(value);
    await _storage.write(key: key, value: jsonString);
  }

  // Read JSON object
  Future<Map<String, dynamic>?> readJson(String key) async {
    final jsonString = await _storage.read(key: key);
    if (jsonString == null) return null;
    
    try {
      return json.decode(jsonString) as Map<String, dynamic>;
    } catch (e) {
      // If JSON decode fails, return null
      return null;
    }
  }

  // Write list
  Future<void> writeList(String key, List<String> value) async {
    final jsonString = json.encode(value);
    await _storage.write(key: key, value: jsonString);
  }

  // Read list
  Future<List<String>?> readList(String key) async {
    final jsonString = await _storage.read(key: key);
    if (jsonString == null) return null;
    
    try {
      final List<dynamic> decoded = json.decode(jsonString);
      return decoded.cast<String>();
    } catch (e) {
      return null;
    }
  }

  // Write boolean value
  Future<void> writeBool(String key, bool value) async {
    await _storage.write(key: key, value: value.toString());
  }

  // Read boolean value
  Future<bool?> readBool(String key) async {
    final value = await _storage.read(key: key);
    if (value == null) return null;
    return value.toLowerCase() == 'true';
  }

  // Write integer value
  Future<void> writeInt(String key, int value) async {
    await _storage.write(key: key, value: value.toString());
  }

  // Read integer value
  Future<int?> readInt(String key) async {
    final value = await _storage.read(key: key);
    if (value == null) return null;
    return int.tryParse(value);
  }

  // Write double value
  Future<void> writeDouble(String key, double value) async {
    await _storage.write(key: key, value: value.toString());
  }

  // Read double value
  Future<double?> readDouble(String key) async {
    final value = await _storage.read(key: key);
    if (value == null) return null;
    return double.tryParse(value);
  }

  // Delete single key
  Future<void> delete(String key) async {
    await _storage.delete(key: key);
  }

  // Delete multiple keys
  Future<void> deleteKeys(List<String> keys) async {
    for (final key in keys) {
      await _storage.delete(key: key);
    }
  }

  // Check if key exists
  Future<bool> containsKey(String key) async {
    final value = await _storage.read(key: key);
    return value != null;
  }

  // Get all keys
  Future<Map<String, String>> readAll() async {
    return await _storage.readAll();
  }

  // Clear all data
  Future<void> deleteAll() async {
    await _storage.deleteAll();
  }

  // Batch operations
  Future<void> writeBatch(Map<String, String> data) async {
    for (final entry in data.entries) {
      await _storage.write(key: entry.key, value: entry.value);
    }
  }

  // Get all keys matching pattern
  Future<List<String>> getKeysWithPrefix(String prefix) async {
    final allKeys = (await _storage.readAll()).keys.toList();
    return allKeys.where((key) => key.startsWith(prefix)).toList();
  }

  // Clean up expired data (if you store expiration timestamps)
  Future<void> cleanExpiredData() async {
    final allData = await _storage.readAll();
    final currentTime = DateTime.now().millisecondsSinceEpoch;
    
    for (final entry in allData.entries) {
      if (entry.key.endsWith('_expires')) {
        final expirationTime = int.tryParse(entry.value);
        if (expirationTime != null && currentTime > expirationTime) {
          // Remove expired key and its associated data
          final dataKey = entry.key.replaceAll('_expires', '');
          await delete(entry.key);
          await delete(dataKey);
        }
      }
    }
  }

  // Store data with expiration
  Future<void> writeWithExpiration(String key, String value, Duration expiration) async {
    final expirationTime = DateTime.now().add(expiration).millisecondsSinceEpoch;
    await write(key, value);
    await write('${key}_expires', expirationTime.toString());
  }

  // Read data and check if expired
  Future<String?> readWithExpirationCheck(String key) async {
    final expirationString = await read('${key}_expires');
    if (expirationString != null) {
      final expirationTime = int.tryParse(expirationString);
      if (expirationTime != null) {
        final currentTime = DateTime.now().millisecondsSinceEpoch;
        if (currentTime > expirationTime) {
          // Data expired, clean it up
          await delete(key);
          await delete('${key}_expires');
          return null;
        }
      }
    }
    
    return await read(key);
  }
}