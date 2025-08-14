# AI Tamil Status Creator App

A cost-efficient Flutter + Laravel application for creating and sharing Tamil status images for WhatsApp, Instagram, and other social platforms.  
The system minimizes LLM API costs using **prebuilt templates**, **bulk AI generation by admin**, and a **small image captioning model**.

---

## Features

- **Authentication**
  - Mobile OTP login via MSG91
  - Google Sign-In
  - Laravel Sanctum token authentication

- **Templates & Themes**
  - Prebuilt templates categorized by themes (Love, Motivation, Sad, etc.)
  - Admin uploads background images and generates Tamil quotes via AI once
  - Free users pick and edit prebuilt templates without triggering LLM calls
  - Premium users can generate quotes via AI (daily quota limits apply)

- **AI Pipeline**
  - Small image captioning model (BLIP, CLIP, OFA) for low-cost description
  - Minimal token LLM prompt via OpenRouter for Tamil quote generation
  - Bulk pre-generation for cost savings

- **Editor**
  - Change fonts, colors, alignment, background image, and text
  - Add logo, tagline, and branding

- **Sharing**
  - One-click share to WhatsApp, Instagram, Facebook
  - Save to gallery

- **Admin Panel**
  - Built with TailwindCSS + Blade.js
  - CRUD for templates, themes, users, and subscriptions
  - Bulk AI generation tool
  - Usage analytics

---

## Tech Stack

- **Frontend:** Flutter 3.x
- **Backend:** Laravel 11 (API-first)
- **Database:** MySQL
- **Authentication:** Laravel Sanctum, MSG91 OTP, Google OAuth
- **AI:** BLIP/CLIP/OFA for captions, OpenRouter LLM for quotes
- **File Storage:** AWS S3 / DigitalOcean Spaces
- **Payments:** Razorpay / Stripe
- **Notifications:** Firebase Cloud Messaging

---

## Installation

1. **Clone repository**
   ```bash
   git clone git@github.com:dreamstudio-satheesh/status-creator.git
   cd status-creator
