# Flutter + Android Studio Setup Guide
**Complete Setup for Windows 11 + WSL2 Development Environment**

## 📋 Overview

This guide sets up a Flutter development environment using:
- **Windows 11**: Android Studio, Android SDK, Emulators
- **WSL2 (Debian)**: Flutter development, code editing, building
- **Cross-platform access**: WSL can use Windows Android tools

---

## 🔍 Current Status Check

Run this in WSL to see current status:
```bash
flutter doctor -v
```

---

## 🚀 Part 1: Windows Setup (Android Studio)

### Step 1: Install Java JDK on Windows

1. **Download OpenJDK 17** (recommended for Android development):
   ```powershell
   # In PowerShell as Administrator
   winget install Microsoft.OpenJDK.17
   ```
   
   Or manually download from: https://adoptopenjdk.net/

2. **Set JAVA_HOME Environment Variable**:
   ```powershell
   # In PowerShell as Administrator
   [Environment]::SetEnvironmentVariable("JAVA_HOME", "C:\Program Files\Microsoft\jdk-17.0.11.9-hotspot", "Machine")
   [Environment]::SetEnvironmentVariable("PATH", "$env:PATH;C:\Program Files\Microsoft\jdk-17.0.11.9-hotspot\bin", "Machine")
   ```

3. **Verify Installation**:
   ```powershell
   java -version
   javac -version
   ```

### Step 2: Install Android Studio

1. **Download Android Studio**:
   - Visit: https://developer.android.com/studio
   - Download and run the installer

2. **Install Android Studio**:
   - Choose "Standard" installation
   - Allow it to download Android SDK, Android SDK Platform-Tools, etc.
   - Note the SDK path (usually `C:\Users\%USERNAME%\AppData\Local\Android\Sdk`)

3. **Configure Android Studio**:
   - Open Android Studio
   - Go to **File → Settings → Appearance & Behavior → System Settings → Android SDK**
   - Install additional SDK versions if needed (API 34 recommended)
   - Go to **SDK Tools** tab and ensure these are installed:
     - Android SDK Build-Tools
     - Android SDK Command-line Tools
     - Android SDK Platform-Tools
     - Android Emulator

### Step 3: Create Android Virtual Device (AVD)

1. **Open AVD Manager**:
   - In Android Studio: **Tools → AVD Manager**

2. **Create New AVD**:
   - Click "Create Virtual Device"
   - Choose **Phone → Pixel 7** (or similar)
   - Select **API Level 34** (Android 14)
   - Choose **x86_64** for better performance
   - Configure RAM: 4GB (if your system has 16GB+ RAM)

3. **Test Emulator**:
   - Start the emulator from AVD Manager
   - Ensure it boots successfully

### Step 4: Configure Windows Environment Variables

1. **Set Android Environment Variables**:
   ```powershell
   # In PowerShell as Administrator
   $androidHome = "$env:LOCALAPPDATA\Android\Sdk"
   [Environment]::SetEnvironmentVariable("ANDROID_HOME", $androidHome, "Machine")
   [Environment]::SetEnvironmentVariable("ANDROID_SDK_ROOT", $androidHome, "Machine")
   
   # Add Android tools to PATH
   $newPath = "$env:PATH;$androidHome\tools;$androidHome\tools\bin;$androidHome\platform-tools"
   [Environment]::SetEnvironmentVariable("PATH", $newPath, "Machine")
   ```

2. **Restart PowerShell** and verify:
   ```powershell
   adb version
   echo $env:ANDROID_HOME
   ```

---

## 🐧 Part 2: WSL Setup

### Step 5: Install Required Packages in WSL

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install essential development tools
sudo apt install -y \
    openjdk-17-jdk \
    git \
    curl \
    unzip \
    xz-utils \
    zip \
    libglu1-mesa \
    clang \
    cmake \
    ninja-build \
    pkg-config \
    libgtk-3-dev \
    chromium-browser

# Verify Java installation
java -version
```

### Step 6: Configure WSL Environment Variables

1. **Edit your shell profile**:
   ```bash
   nano ~/.bashrc
   ```

2. **Add these environment variables** (add to end of file):
   ```bash
   # Android SDK (Windows path accessible from WSL)
   export ANDROID_HOME="/mnt/c/Users/$USER/AppData/Local/Android/Sdk"
   export ANDROID_SDK_ROOT="$ANDROID_HOME"
   export PATH="$PATH:$ANDROID_HOME/tools:$ANDROID_HOME/tools/bin:$ANDROID_HOME/platform-tools"
   
   # Java
   export JAVA_HOME="/usr/lib/jvm/java-17-openjdk-amd64"
   export PATH="$PATH:$JAVA_HOME/bin"
   
   # Flutter (you already have this)
   export PATH="$PATH:/home/satheesh/flutter/bin"
   
   # Chrome for Flutter web development
   export CHROME_EXECUTABLE="/usr/bin/chromium-browser"
   ```

3. **Reload environment**:
   ```bash
   source ~/.bashrc
   ```

### Step 7: Configure Flutter in WSL

1. **Point Flutter to Windows Android SDK**:
   ```bash
   flutter config --android-sdk /mnt/c/Users/$USER/AppData/Local/Android/Sdk
   ```

2. **Accept Android licenses**:
   ```bash
   flutter doctor --android-licenses
   ```
   Type `y` to accept all licenses.

3. **Run Flutter Doctor**:
   ```bash
   flutter doctor -v
   ```

   You should see:
   - ✅ Flutter (Channel stable)
   - ✅ Android toolchain
   - ✅ Chrome - develop for the web
   - ✅ Connected device

---

## 🏗️ Part 3: Development Workflow Setup

### Step 8: Test Android Connection

1. **Start Android Emulator** (from Windows):
   - Open Android Studio
   - Start your AVD from AVD Manager

2. **Check device connection from WSL**:
   ```bash
   adb devices
   ```
   Should show your emulator listed.

3. **Test Flutter deployment**:
   ```bash
   # Create a test project
   flutter create test_app
   cd test_app
   
   # Run on emulator
   flutter run
   ```

### Step 9: Setup VS Code for Flutter Development

1. **Install VS Code Extensions**:
   - Flutter
   - Dart
   - Flutter Widget Snippets
   - Bracket Pair Colorizer

2. **Configure VS Code settings** (`.vscode/settings.json`):
   ```json
   {
     "dart.flutterSdkPath": "/home/satheesh/flutter",
     "dart.androidSdk": "/mnt/c/Users/YourUsername/AppData/Local/Android/Sdk",
     "flutter.sdk": "/home/satheesh/flutter"
   }
   ```

---

## 📱 Part 4: Building APKs

### Step 10: Build Debug APK

```bash
cd your_flutter_project

# Build debug APK
flutter build apk --debug

# APK location
ls build/app/outputs/flutter-apk/
```

### Step 11: Build Release APK

1. **Generate keystore** (one-time setup):
   ```bash
   keytool -genkey -v -keystore ~/android-release-key.keystore -keyalg RSA -keysize 2048 -validity 10000 -alias release
   ```

2. **Create key.properties**:
   ```bash
   # Create android/key.properties
   cat > android/key.properties << EOF
   storePassword=your_keystore_password
   keyPassword=your_key_password
   keyAlias=release
   storeFile=/home/satheesh/android-release-key.keystore
   EOF
   ```

3. **Configure build.gradle** (android/app/build.gradle):
   ```gradle
   // Add before android block
   def keystoreProperties = new Properties()
   def keystorePropertiesFile = rootProject.file('key.properties')
   if (keystorePropertiesFile.exists()) {
       keystoreProperties.load(new FileInputStream(keystorePropertiesFile))
   }

   android {
       // ... existing config
       
       signingConfigs {
           release {
               keyAlias keystoreProperties['keyAlias']
               keyPassword keystoreProperties['keyPassword']
               storeFile keystoreProperties['storeFile'] ? file(keystoreProperties['storeFile']) : null
               storePassword keystoreProperties['storePassword']
           }
       }
       
       buildTypes {
           release {
               signingConfig signingConfigs.release
           }
       }
   }
   ```

4. **Build release APK**:
   ```bash
   flutter build apk --release
   ```

---

## 🔧 Troubleshooting

### Common Issues and Solutions

**1. "Unable to locate Android SDK"**
```bash
# Check if path exists
ls /mnt/c/Users/$USER/AppData/Local/Android/Sdk

# Re-configure if needed
flutter config --android-sdk /mnt/c/Users/$USER/AppData/Local/Android/Sdk
```

**2. "Android license status unknown"**
```bash
flutter doctor --android-licenses
# Accept all licenses with 'y'
```

**3. "No devices found"**
```bash
# Check ADB connection
adb devices

# Kill and restart ADB server
adb kill-server
adb start-server
```

**4. "Permission denied" for keystore**
```bash
chmod 600 ~/android-release-key.keystore
```

**5. Emulator won't start from WSL**
- Start emulator from Windows Android Studio first
- Then WSL can connect to it via ADB

**6. "Chrome executable not found"**
```bash
# Install Chromium
sudo apt install chromium-browser

# Set Chrome path
export CHROME_EXECUTABLE="/usr/bin/chromium-browser"
```

---

## ✅ Verification Checklist

After completing setup, verify everything works:

```bash
# 1. Check Flutter status
flutter doctor -v

# 2. Check Android connection
adb devices

# 3. Create and run test project
flutter create verification_test
cd verification_test
flutter run

# 4. Build APK
flutter build apk --debug

# 5. Test web development
flutter run -d chrome
```

**Expected `flutter doctor` output:**
```
[✓] Flutter (Channel stable, 3.29.2)
[✓] Android toolchain - develop for Android devices
[✓] Chrome - develop for the web  
[✓] Linux toolchain - develop for Linux desktop
[✓] Connected device (2 available)
[✓] Network resources
```

---

## 🚀 Next Steps: Flutter Project Architecture

Once setup is complete, proceed with:

1. **Initialize Flutter project with latest version** ✅
2. **Setup folder structure** (features, core, shared)
3. **Configure environment variables**
4. **Setup API client with Dio and interceptors**
5. **Configure routing with GoRouter**
6. **Setup state management (Riverpod/Provider)**
7. **Add error handling and logging**
8. **Configure app themes and fonts**

---

## 📞 Support

If you encounter issues:
1. Check the troubleshooting section above
2. Run `flutter doctor -v` for detailed diagnostics
3. Ensure Windows Android Studio is working independently first
4. Verify WSL can access Windows files via `/mnt/c/`

**Environment Details:**
- Flutter: 3.29.2 (stable)
- Dart: 3.7.2
- Android Studio: Latest stable
- Java: OpenJDK 17
- WSL: Debian GNU/Linux 12