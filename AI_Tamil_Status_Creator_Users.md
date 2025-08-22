# AI Tamil Status Creator - Software Requirements Specification (SRS)

## Table of Contents
1. [Introduction](#introduction)
2. [System Overview](#system-overview)
3. [System Architecture](#system-architecture)
4. [Admin Panel Requirements](#admin-panel-requirements)
5. [Mobile Application Requirements](#mobile-application-requirements)
6. [Technical Specifications](#technical-specifications)
7. [Integration Requirements](#integration-requirements)
8. [User Experience Requirements](#user-experience-requirements)
9. [Security Requirements](#security-requirements)
10. [Performance Requirements](#performance-requirements)

## Detailed Workflow Documents
- **[Admin Panel Workflow](./Admin_Panel_Workflow.md)** - Comprehensive admin panel workflows and operations
- **[Mobile App Workflow](./Mobile_App_Workflow.md)** - Complete mobile application user journeys and features

---

## 1. Introduction

### 1.1 Project Overview
The AI Tamil Status Creator is a comprehensive Flutter + Laravel application designed for creating and sharing Tamil status images. The system minimizes LLM API costs through prebuilt templates, bulk AI generation, and efficient image captioning models while providing both mobile and web-based administrative interfaces.

### 1.2 Purpose and Scope
This SRS document defines the functional and non-functional requirements for both the admin panel (Laravel-based) and mobile application (Flutter-based) components of the AI Tamil Status Creator system.

### 1.3 Authentication System Update
**Important Note**: This system has migrated from OTP-based authentication to email/password authentication for improved user experience and reduced dependency on SMS services. The current authentication system uses:
- **Email/Password Authentication** as the primary method
- **Google OAuth** for social login
- **Email Verification** for account security
- **Biometric Authentication** (optional) for enhanced mobile security

Previous OTP/SMS integration (MSG91) has been removed in favor of more reliable email-based verification.

### 1.4 Target Users
The AI Tamil Status Creator app is designed for Tamil-speaking users who want to create and share status images for social media platforms like WhatsApp, Instagram, and Facebook. Here are the key user groups this system supports:

#### Primary Target Users

**1. Individual Social Media Users**

- People who want to share Tamil quotes and messages on WhatsApp status
- Instagram users posting Tamil content
- Facebook users sharing Tamil motivational/emotional posts
- Categories: Love quotes, Motivational messages, Sad expressions, etc.

**2. Small Business Owners**

- **Sweet Shop Owners**: Can create Tamil promotional messages for festivals, special offers
- **Jewelry Shop Owners**: Create Tamil wedding/festival promotional content
- **Local Retailers**: Share daily offers, new arrivals in Tamil
- **Restaurant Owners**: Menu specials, festival greetings in Tamil

**3. Content Creators & Influencers**

- Tamil content creators needing regular status updates
- Social media influencers targeting Tamil audience
- Bloggers and writers sharing Tamil quotes

**4. Politicians & Public Figures**

- **Politicians**: Create Tamil messages for campaigns, public announcements
- **Community Leaders**: Share Tamil motivational/inspirational content
- **Local Leaders**: Festival greetings, public service messages

**5. Educational & Religious Organizations**

- **Teachers**: Tamil educational quotes and messages
- **Religious Leaders**: Spiritual messages in Tamil
- **Cultural Organizations**: Tamil heritage and cultural content

#### App Features Supporting These Users

**For Business Owners:**

- **Branding Support**: Add logo, tagline to status images
- **Professional Templates**: Business-appropriate themes
- **Easy Sharing**: Direct share to WhatsApp Business, Instagram Business

**For Politicians:**

- **Bulk Generation**: Admin can pre-generate campaign messages
- **Quick Customization**: Change text, colors, alignment for different messages
- **Wide Reach**: Share across multiple social platforms

**Cost-Effective Model:**

- **Free Tier**: Access to prebuilt templates (perfect for small businesses)
- **Premium Tier**: Custom AI generation for unique content (suitable for frequent users like politicians/influencers)

#### Summary

The app essentially serves anyone in the Tamil-speaking community who wants to create professional-looking status images without design skills, making it particularly valuable for small businesses and public figures who need regular social media content.

---

## 2. System Overview

### 2.1 Technology Stack
- **Frontend**: Flutter mobile application (cross-platform)
- **Backend**: Laravel 11 API with PHP 8.2+
- **Database**: SQLite (development) / MySQL (production) with utf8mb4 for Tamil text support
- **Cache/Queue**: File-based cache and database queues (no Redis dependency)
- **Storage**: Local file storage for development, S3/MinIO for production
- **AI Integration**: OpenRouter LLM + BLIP/CLIP/OFA for image captioning
- **Authentication**: Email/Password + Google OAuth (SMS/OTP services removed)
- **Notifications**: Email-based notifications (SMTP) instead of SMS

### 2.2 System Components
- **Admin Panel**: Laravel Blade-based web interface with TailwindCSS
- **Mobile Application**: Flutter app with Provider/Riverpod state management
- **API Layer**: RESTful API endpoints for mobile-backend communication
- **AI Services**: Integrated AI services for content generation and image processing
- **File Management**: Comprehensive asset management system

### 2.3 Development Environment
- **Production Server**: https://status.dreamcoderz.com (cPanel hosting)
- **Local Development**: Laravel serves on http://localhost:8000
- **Mobile Development**: Flutter runs locally with hot reload
- **Database**: SQLite for local development, MySQL for production

---

## 3. System Architecture

### 3.1 High-Level Architecture
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│  Flutter App    │    │   Laravel API   │    │   AI Services   │
│  (Mobile)       │◄──►│   (Backend)     │◄──►│ OpenRouter +    │
│                 │    │                 │    │ HuggingFace     │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                                │
                       ┌─────────────────┐
                       │  Admin Panel    │
                       │  (Laravel Web)  │
                       └─────────────────┘
                                │
                       ┌─────────────────┐
                       │    Database     │
                       │ SQLite/MySQL    │
                       └─────────────────┘
```

### 3.2 Data Flow
1. **User Authentication**: Mobile app ↔ Laravel API (JWT tokens)
2. **Content Creation**: Flutter → Laravel → AI Services → Database
3. **Template Management**: Admin Panel → Database → API → Mobile App
4. **File Storage**: Admin Panel → Local/S3 Storage → API → Mobile App

### 3.3 Database Schema Overview
- **Core Tables**: users, themes, templates, user_creations, subscriptions
- **Admin Tables**: admins, activity_logs, settings
- **Content Tables**: fonts, ai_generation_logs
- **Support**: utf8mb4 charset for Tamil text across all tables

---

## 4. Admin Panel Requirements

> **📋 For detailed admin workflows, see: [Admin Panel Workflow](./Admin_Panel_Workflow.md)**

### 4.1 Authentication & Access Control

#### 4.1.1 Admin Authentication System
- **Login/Logout**: Secure admin authentication with session management
- **Role-Based Access**: Super admin, content admin, support admin roles
- **Password Security**: Strong password requirements and password reset functionality
- **Session Management**: Automatic logout after inactivity, remember me option

#### 4.1.2 Activity Logging & Audit
- **Audit Trail**: Complete logging of all admin actions
- **Activity Dashboard**: View recent activities and changes
- **User Action Tracking**: Monitor admin user activities
- **Data Change Logs**: Track modifications to templates, themes, and settings

### 4.2 Dashboard & Analytics

#### 4.2.1 Main Dashboard
- **User Statistics**: Total users, active users, new registrations
- **Usage Metrics**: Template downloads, AI generations, user creations
- **Revenue Analytics**: Subscription revenue, payment tracking
- **System Health**: API response times, error rates, storage usage

#### 4.2.2 Advanced Analytics
- **User Engagement**: Usage patterns, retention rates, feature adoption
- **Content Performance**: Most popular templates, theme preferences
- **AI Cost Tracking**: LLM API usage and cost optimization metrics
- **Geographic Analytics**: User distribution and regional preferences

### 4.3 Content Management System

#### 4.3.1 Template Management
- **CRUD Operations**: Create, read, update, delete templates
- **Bulk Operations**: Activate, deactivate, feature, delete multiple templates
- **Template Editor**: Visual editor with real-time preview
- **Categorization**: Organize templates by themes and tags
- **Status Control**: Active/inactive, premium/free, featured flags

#### 4.3.2 Template Editor System (Enhanced)
- **Visual Interface**: Drag-and-drop template creation
- **Background Selection**: Choose from uploaded asset library
- **Text Styling Options**:
  - Font family selection from uploaded fonts
  - Font size control (8-72px range)
  - Color picker for text color
  - Text alignment (left, center, right)
  - Padding and spacing controls
- **Real-time Preview**: Live preview of changes
- **Save & Export**: Save as template with metadata

#### 4.3.3 Theme Management
- **Category Management**: Create and organize content themes
- **Theme Customization**: Configure theme-specific settings
- **Template Association**: Link templates to appropriate themes
- **Theme Analytics**: Usage statistics per theme

### 4.4 File Manager & Asset System

#### 4.4.1 Comprehensive File Manager
- **Gallery View**: Visual grid layout with thumbnails
- **List View**: Detailed file information in table format
- **Category Organization**: Theme-based and asset-type-based folders
- **Search & Filter**: Advanced search by name, type, category, date
- **File Operations**: Rename, move, copy, delete operations

#### 4.4.2 Bulk Upload System
- **Multi-file Upload**: Drag-and-drop multiple files simultaneously
- **Asset Categories**:
  - Background images (by theme)
  - Logos and branding elements
  - Stickers and decorative elements
  - Overlay graphics and frames
- **File Validation**: Format, size, and quality checks
- **Progress Tracking**: Upload progress with error handling

#### 4.4.3 Asset Management (1000+ Files Support)
- **Scalable Storage**: Efficient handling of large file libraries
- **Pagination**: Lazy loading for performance
- **Thumbnail Generation**: Automatic thumbnail creation
- **File Metadata**: Size, dimensions, upload date, usage statistics
- **Storage Analytics**: Storage usage and optimization recommendations

### 4.5 Quote Content Management

#### 4.5.1 Text Content System
- **Category-wise Organization**: Organize quotes by themes
- **Bulk Quote Import**: CSV/Excel import functionality
- **Multi-language Support**: Tamil and English quote management
- **Quote Editor**: Rich text editor with formatting options
- **Quote Analytics**: Usage tracking and popularity metrics

#### 4.5.2 Content Workflow
- **Draft System**: Save and review quotes before publishing
- **Approval Workflow**: Review and approve user-submitted content
- **Version Control**: Track changes and maintain quote history
- **Content Moderation**: Review and moderate user-generated content

### 4.6 User Management

#### 4.6.1 User Administration
- **User Profiles**: Complete user information management
- **Subscription Control**: Manage premium subscriptions and billing
- **Usage Monitoring**: Track user activity and API usage
- **Support Integration**: Handle user feedback and support tickets

#### 4.6.2 Subscription Management
- **Plan Configuration**: Set up and modify subscription plans
- **Payment Tracking**: Monitor payment status and history
- **Usage Quotas**: Configure AI generation limits
- **Renewal Management**: Handle subscription renewals and upgrades

### 4.7 AI Management & Optimization

#### 4.7.1 Bulk AI Generation
- **Batch Processing**: Generate multiple templates simultaneously
- **Theme-based Generation**: Create content for specific themes
- **Prompt Management**: Configure and optimize AI prompts
- **Quality Control**: Review and approve AI-generated content

#### 4.7.2 Cost Management
- **API Usage Tracking**: Monitor OpenRouter and HuggingFace usage
- **Cost Analytics**: Track costs per user, per generation, per theme
- **Budget Controls**: Set spending limits and alerts
- **Optimization Tools**: Identify cost-saving opportunities

---

## 5. Mobile Application Requirements

> **📱 For detailed mobile app workflows, see: [Mobile App Workflow](./Mobile_App_Workflow.md)**

### 5.1 Authentication & Onboarding

#### 5.1.1 User Authentication
**Current Authentication Methods** (OTP system removed):
- **Email/Password Login**: Primary authentication method with strong password requirements
- **Google OAuth**: Streamlined social login integration
- **Email Verification**: Secure account verification process (replaces SMS OTP)
- **User Registration**: Comprehensive profile setup with email confirmation
- **Security Features**: Biometric authentication (fingerprint/face) for enhanced mobile security

#### 5.1.2 Onboarding Experience
- **App Introduction**: Feature overview and benefits
- **Permission Requests**: Camera, storage, notification permissions
- **Language Selection**: Tamil/English interface
- **Theme Preferences**: Light/dark mode selection

### 5.2 Core Navigation & User Interface

#### 5.2.1 Main Navigation
- **Bottom Navigation**: Home, Templates, Editor, My Work, Profile
- **Drawer Navigation**: Additional features and settings
- **Search Functionality**: Global search across templates and content
- **Quick Actions**: Floating action button for common tasks

#### 5.2.2 Home Screen Features
- **Welcome Section**: Personalized greeting and daily stats
- **Featured Templates**: Curated template showcase
- **Category Browser**: Quick access to template categories
- **Recent Creations**: User's recent work display
- **Quick Actions**: AI generate, browse templates

### 5.3 Template System

#### 5.3.1 Template Browsing
- **Category Navigation**: Browse templates by themes
- **Search & Filter**: Advanced search with multiple filters
- **Template Preview**: Full-screen template preview
- **Favorites System**: Save and organize favorite templates
- **Social Features**: Share templates with friends

#### 5.3.2 Template Details
- **Full Preview**: High-resolution template display
- **Customization Options**: Available styling modifications
- **Usage Statistics**: Download and usage metrics
- **Related Templates**: Suggested similar templates
- **Action Buttons**: Use, favorite, share, report

### 5.4 Content Creation & Editing

#### 5.4.1 AI-Powered Generation
- **Custom Prompts**: User input for personalized content
- **Theme-based Generation**: Generate content for specific categories
- **Language Options**: Tamil and English generation
- **Style Controls**: Mood, tone, and length parameters
- **Generation History**: Track and reuse previous generations

#### 5.4.2 Visual Editor
- **Template Customization**: Modify existing templates
- **Text Editing**: Font, size, color, alignment controls
- **Image Editing**: Crop, rotate, filter, brightness adjustments
- **Layer Management**: Text and image layer controls
- **Real-time Preview**: Live editing with instant preview

#### 5.4.3 Export & Sharing
- **Multiple Formats**: PNG, JPG export options
- **Quality Settings**: Resolution and compression controls
- **Social Integration**: Direct share to WhatsApp, Instagram, Facebook
- **Save to Gallery**: Local device storage
- **Cloud Backup**: Optional cloud storage of creations

### 5.5 User Profile & Management

#### 5.5.1 Profile Features
- **Personal Information**: Name, phone, email management
- **Creation Gallery**: Organized view of user's work
- **Favorites Collection**: Saved templates and content
- **Usage Statistics**: AI generations used, creations made
- **Achievement System**: Badges and milestones

#### 5.5.2 Settings & Preferences
- **App Settings**: Theme, language, notification preferences
- **Privacy Controls**: Data sharing and visibility settings
- **Storage Management**: Local cache and storage options
- **Account Management**: Password change, account deletion
- **Help & Support**: FAQ, contact support, feedback

### 5.6 Premium Features & Subscription

#### 5.6.1 Premium Content Access
- **Exclusive Templates**: Premium-only template collection
- **Advanced AI Features**: Enhanced generation capabilities
- **Priority Processing**: Faster AI generation times
- **Unlimited Generations**: No daily limits for premium users
- **Export Quality**: Higher resolution exports

#### 5.6.2 Subscription Management
- **Plan Selection**: Free and premium plan comparison
- **Payment Integration**: Secure payment processing
- **Subscription Status**: Current plan and usage display
- **Renewal Management**: Auto-renewal settings
- **Billing History**: Payment history and receipts

---

## 6. Technical Specifications

### 6.1 API Architecture
- **RESTful Design**: Standard HTTP methods and status codes
- **Authentication**: JWT tokens with automatic refresh
- **Rate Limiting**: User-based API call limits
- **Versioning**: API versioning strategy (/api/v1)
- **Documentation**: Swagger/OpenAPI documentation

### 6.2 Database Requirements
- **Character Set**: utf8mb4_unicode_ci for Tamil text support
- **Indexing**: Optimized indexes for performance
- **Relationships**: Proper foreign key constraints
- **Migrations**: Version-controlled database changes
- **Backup Strategy**: Automated backup and recovery

### 6.3 File Storage System
- **Local Development**: File-based storage
- **Production**: S3/MinIO object storage
- **CDN Integration**: Content delivery optimization
- **File Validation**: Type, size, and security checks
- **Cleanup Jobs**: Automated old file cleanup

---

## 7. Integration Requirements

### 7.1 AI Service Integration
- **OpenRouter API**: LLM text generation
- **HuggingFace API**: Image captioning and analysis
- **Model Configuration**: Switchable AI models
- **Error Handling**: Fallback and retry mechanisms
- **Cost Monitoring**: Usage tracking and optimization

### 7.2 Payment Gateway Integration
- **Razorpay Integration**: Indian payment processing
- **Subscription Handling**: Recurring payment management
- **Webhook Processing**: Payment status updates
- **Refund Management**: Automated refund processing
- **Currency Support**: Multiple currency options

### 7.3 Third-party Services
**Updated Service Integration** (SMS services removed):
- **Google OAuth**: Social authentication integration
- **Firebase Analytics**: User behavior tracking and insights
- **Push Notifications**: User engagement and app updates
- **Social Media APIs**: Direct sharing to WhatsApp, Instagram, Facebook
- **Email Service**: SMTP-based notifications and account verification
- **Payment Gateway**: Razorpay for subscription management

---

## 8. User Experience Requirements

### 8.1 Responsive Design
- **Mobile-first**: Optimized for mobile devices
- **Tablet Support**: Adaptive layouts for larger screens
- **Admin Panel**: Responsive web interface
- **Cross-platform**: Consistent experience across platforms
- **Accessibility**: WCAG 2.1 AA compliance

### 8.2 Performance Standards
- **Load Times**: <3 seconds for app launch
- **API Response**: <500ms for most endpoints
- **Image Loading**: Progressive loading with placeholders
- **Offline Support**: Basic functionality without internet
- **Memory Usage**: Optimized for low-end devices

### 8.3 Localization
- **Tamil Language**: Full Tamil interface support
- **Font Support**: Proper Tamil font rendering
- **RTL Support**: Right-to-left text handling
- **Cultural Adaptation**: Region-specific content
- **Date/Time Format**: Localized formatting

---

## 9. Security Requirements

### 9.1 Data Protection
- **Encryption**: Data encryption at rest and in transit
- **Secure Storage**: Encrypted local storage for sensitive data
- **Token Security**: Secure JWT implementation
- **Input Validation**: Server-side validation for all inputs
- **SQL Injection Prevention**: Parameterized queries

### 9.2 Authentication Security
**Enhanced Security Model** (post-OTP removal):
- **Password Requirements**: Strong password policies with complexity validation
- **Email Verification**: Secure email-based account verification (replaces OTP)
- **Session Management**: JWT-based secure session handling
- **Brute Force Protection**: Progressive login attempt limiting
- **Account Lockout**: Automatic protection with email recovery
- **Multi-Factor Authentication**: Biometric options on mobile devices

### 9.3 API Security
- **Rate Limiting**: Per-user API limits
- **CORS Configuration**: Proper cross-origin settings
- **HTTPS Enforcement**: SSL/TLS for all communications
- **API Key Management**: Secure key storage and rotation
- **Audit Logging**: Comprehensive security event logging

---

## 10. Performance Requirements

### 10.1 Scalability
- **User Capacity**: Support for 100,000+ concurrent users
- **Database Performance**: Optimized queries and indexing
- **File Storage**: Scalable asset management
- **API Throughput**: High-performance API endpoints
- **Cache Strategy**: Intelligent caching mechanisms

### 10.2 Reliability
- **Uptime Target**: 99.9% availability
- **Error Handling**: Graceful error recovery
- **Backup Systems**: Automated backup and restore
- **Monitoring**: Comprehensive system monitoring
- **Alerting**: Real-time issue notifications

### 10.3 Mobile Performance
- **Battery Optimization**: Efficient power usage
- **Network Efficiency**: Minimal data usage
- **Storage Management**: Efficient local storage
- **Memory Optimization**: Low memory footprint
- **Startup Time**: Fast application launch