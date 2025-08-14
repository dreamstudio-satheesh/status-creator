# AI Tamil Status Creator API Documentation

## Overview

The AI Tamil Status Creator API is a comprehensive RESTful service that powers a mobile application for creating and sharing Tamil status images. The API provides authentication, user management, AI-powered quote generation, template management, and file upload capabilities.

## Base URL

```
Development: http://localhost:8000/api/v1
Production: https://api.tamilstatus.app/v1
```

## Authentication

The API uses Laravel Sanctum for authentication with Bearer tokens. Most endpoints require authentication.

### Authentication Flow

1. **OTP Authentication** (Primary)
   - Send OTP to mobile number
   - Verify OTP to receive access token
   - Use token in Authorization header

2. **Google OAuth** (Alternative)
   - Redirect to Google OAuth
   - Receive access token on callback

### Token Usage

```http
Authorization: Bearer YOUR_ACCESS_TOKEN
```

## Rate Limiting

- **Authentication endpoints**: 5 requests per minute per IP
- **OTP endpoints**: 3 requests per minute per mobile number
- **AI generation**: 5 requests per minute per user
- **General API**: 60 requests per minute per user

## Response Format

All API responses follow a consistent format:

### Success Response
```json
{
  "success": true,
  "data": { /* response data */ },
  "message": "Operation completed successfully"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "errors": { /* validation errors if any */ }
}
```

## Pagination

List endpoints support pagination with the following parameters:

- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 20, max: 100)

### Pagination Response
```json
{
  "success": true,
  "data": [ /* items */ ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 95,
    "has_more": true
  }
}
```

## API Endpoints

### 🔐 Authentication

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/auth/send-otp` | Send OTP to mobile | No |
| POST | `/auth/verify-otp` | Verify OTP and get token | No |
| POST | `/auth/resend-otp` | Resend OTP | No |
| GET | `/auth/google/redirect` | Google OAuth redirect | No |
| GET | `/auth/google/callback` | Google OAuth callback | No |
| POST | `/auth/logout` | Logout user | Yes |

### 👤 User Management

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/user/profile` | Get user profile | Yes |
| PUT | `/user/profile` | Update profile | Yes |
| GET | `/user/dashboard` | Get dashboard data | Yes |
| GET | `/user/usage-stats` | Get usage statistics | Yes |
| GET | `/user/subscription` | Get subscription info | Yes |
| GET | `/user/preferences` | Get user preferences | Yes |
| PUT | `/user/preferences` | Update preferences | Yes |
| DELETE | `/user/account` | Delete account | Yes |

### 🎨 Themes

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/public/themes` | Get all themes | No |
| GET | `/public/themes/{id}` | Get theme details | No |
| GET | `/public/themes/{id}/templates` | Get theme templates | No |

### 📋 Templates

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/public/templates` | Get all templates | No |
| GET | `/public/templates/featured` | Get featured templates | No |
| GET | `/public/templates/search` | Search templates | No |
| GET | `/public/templates/{id}` | Get template details | No |
| POST | `/templates/{id}/use` | Mark template as used | Yes |
| POST | `/templates/{id}/favorite` | Toggle favorite | Yes |
| POST | `/templates/{id}/rate` | Rate template | Yes |
| GET | `/templates/favorites` | Get user favorites | Yes |

### 🤖 AI Generation

| Method | Endpoint | Description | Auth Required | Quota |
|--------|----------|-------------|---------------|-------|
| POST | `/ai/generate-quote` | Generate Tamil quote | Yes | Yes |
| POST | `/ai/caption-image` | Analyze image | Yes | No |
| POST | `/ai/regenerate` | Regenerate quote | Yes | Yes |
| GET | `/ai/quota` | Get quota info | Yes | No |
| GET | `/ai/usage` | Get usage stats | Yes | No |
| GET | `/ai/models` | Get available models | Yes | No |

### 📁 File Upload

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/uploads/avatar` | Upload avatar | Yes |
| POST | `/uploads/image` | Upload image | Yes |
| DELETE | `/uploads/file` | Delete file | Yes |
| GET | `/uploads/limits` | Get upload limits | Yes |

### 💬 Support & Feedback

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/feedback/submit` | Submit feedback | Yes |
| GET | `/feedback` | Get user feedback | Yes |
| GET | `/feedback/{id}` | Get feedback details | Yes |
| POST | `/feedback/app-rating` | Rate app | Yes |
| GET | `/public/faq` | Get FAQ | No |
| GET | `/public/contact` | Get contact info | No |

## AI Generation

### Quota System

- **Free Users**: 10 generations per day
- **Premium Users**: 100 generations per day
- **Quota resets**: Daily at midnight UTC

### Supported Themes

- `love` - காதல் (Love)
- `motivation` - ஊக்கம் (Motivation)
- `life` - வாழ்க்கை (Life)
- `success` - வெற்றி (Success)
- `friendship` - நட்பு (Friendship)
- `family` - குடும்பம் (Family)
- `spiritual` - ஆன்மீகம் (Spiritual)
- `nature` - இயற்கை (Nature)
- `wisdom` - ஞானம் (Wisdom)
- `hope` - நம்பிக்கை (Hope)

### Quote Styles

- `inspirational` - Motivational and uplifting
- `emotional` - Deep and touching
- `philosophical` - Thoughtful and wise
- `traditional` - Classical Tamil culture
- `modern` - Contemporary style
- `poetic` - Literary and artistic

### Quote Lengths

- `short` - 10-15 words
- `medium` - 20-30 words
- `long` - 40-50 words

## File Upload

### Supported Formats

- **Images**: JPEG, PNG, GIF
- **Maximum size**: 10MB (Premium), 5MB (Free)
- **Avatar**: Auto-resized to 200x200px
- **Image optimization**: Automatic JPEG conversion with quality control

### Upload Limits

- **Free users**: 10 uploads per day
- **Premium users**: Unlimited uploads

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Rate Limited / Quota Exceeded |
| 500 | Internal Server Error |

## Common Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "mobile": ["The mobile field is required."],
    "otp": ["The otp must be 6 characters."]
  }
}
```

### Rate Limited (429)
```json
{
  "success": false,
  "message": "Too many requests. Please try again in 5 minutes."
}
```

### Quota Exceeded (429)
```json
{
  "success": false,
  "message": "Daily AI generation quota exceeded",
  "daily_ai_quota": 10,
  "daily_ai_used": 10,
  "is_premium": false,
  "suggestion": "Upgrade to premium for higher quota (100 vs 10)"
}
```

## Example Requests

### 1. Authentication Flow

```bash
# Send OTP
curl -X POST "http://localhost:8000/api/v1/auth/send-otp" \
  -H "Content-Type: application/json" \
  -d '{"mobile": "+919876543210"}'

# Verify OTP
curl -X POST "http://localhost:8000/api/v1/auth/verify-otp" \
  -H "Content-Type: application/json" \
  -d '{"mobile": "+919876543210", "otp": "123456"}'
```

### 2. Generate Tamil Quote

```bash
curl -X POST "http://localhost:8000/api/v1/ai/generate-quote" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "theme": "love",
    "style": "inspirational",
    "length": "medium",
    "context": "for social media"
  }'
```

### 3. Upload Avatar

```bash
curl -X POST "http://localhost:8000/api/v1/uploads/avatar" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "avatar=@/path/to/image.jpg"
```

## SDKs and Tools

### Postman Collection

Import the Postman collection from `docs/postman_collection.json` for easy API testing.

### Swagger Documentation

Interactive API documentation available at:
- Development: http://localhost:8000/api/documentation
- Production: https://api.tamilstatus.app/documentation

## Testing

### Health Check

```bash
curl -X GET "http://localhost:8000/api/health"
```

Expected response:
```json
{
  "status": "OK",
  "timestamp": "2024-08-14T10:30:00.000000Z",
  "version": "1.0.0"
}
```

## Rate Limiting Headers

API responses include rate limiting information:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1692014400
```

## Webhook Support

The API supports webhooks for:
- Payment notifications (Razorpay)
- Firebase push notifications

Webhook endpoints:
- `/webhooks/razorpay`
- `/webhooks/firebase`

## Support

For API support and questions:
- Email: support@tamilstatus.app
- Documentation issues: Create an issue in the repository
- Feature requests: Use the feedback API endpoint

## Changelog

### Version 1.0.0
- Initial API release
- Authentication system
- User management
- Theme and template APIs
- AI generation capabilities
- File upload system
- Support and feedback system

---

**Note**: This API is designed for the Tamil Status Creator mobile application. All Tamil text examples use proper Tamil Unicode characters.