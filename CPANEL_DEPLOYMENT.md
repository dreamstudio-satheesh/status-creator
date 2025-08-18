# cPanel Deployment Guide - Tamil Status Creator

Complete guide for deploying the Laravel backend to cPanel hosting at `status.dreamcoderz.com` using git hooks and automated deployment.

## Table of Contents
- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Local Setup](#local-setup)
- [Server Setup](#server-setup)
- [Git Hooks Configuration](#git-hooks-configuration)
- [Deployment Process](#deployment-process)
- [Troubleshooting](#troubleshooting)

## Overview

This guide sets up automated deployment from the `cpanel` branch to your cPanel hosting using git hooks. When you push to the remote server, the application automatically deploys to `/home/statusdreamcoder/live`.

### Deployment Architecture
```
Local Machine (cpanel branch)
      ↓ git push server cpanel
cPanel Server (/home/statusdreamcoder/)
├── repo.git/          # Bare repository
│   └── hooks/post-receive
├── live/              # Live application
│   ├── backend/       # Laravel backend
│   └── public_html/   # Web accessible files
└── backups/           # Automatic backups
```

## Prerequisites

### cPanel Requirements
- PHP 8.2 or higher
- MySQL database
- SSH access to cPanel server
- Git installed on server
- Composer installed on server

### Local Requirements  
- Git configured with SSH keys
- Access to `cpanel` branch (this branch)

## Local Setup

### 1. Branch Overview
The `cpanel` branch is optimized for cPanel hosting with these changes:
- Removed Docker configurations
- Configured for MySQL database (not containerized)
- Set up for local file storage (no MinIO/S3)
- Database-based sessions, cache, and queues
- Production-ready environment settings

### 2. Environment Configuration
Copy and configure the environment file:
```bash
cd backend
cp .env.example .env
```

Update these critical settings in `.env`:
```env
# Application Settings
APP_NAME="Tamil Status Creator"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://status.dreamcoderz.com

# Database (get these from cPanel)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=statusdr_tamilstatus
DB_USERNAME=statusdr_dbuser
DB_PASSWORD=your_database_password

# Email (cPanel email settings)
MAIL_HOST=mail.dreamcoderz.com
MAIL_USERNAME=noreply@status.dreamcoderz.com
MAIL_PASSWORD=your_email_password

# AI Services
OPENROUTER_API_KEY=your_openrouter_api_key
HUGGINGFACE_API_KEY=your_huggingface_api_key
MSG91_API_KEY=your_msg91_api_key
```

## Server Setup

### 1. Create Directory Structure
SSH into your cPanel server and create the necessary directories:
```bash
mkdir -p /home/statusdreamcoder/{repo.git,live,backups}
cd /home/statusdreamcoder/repo.git
git init --bare
```

### 2. Database Setup in cPanel
1. Log into cPanel
2. Go to **MySQL Databases**
3. Create database: `statusdr_tamilstatus`
4. Create user: `statusdr_dbuser` with strong password
5. Add user to database with **All Privileges**
6. Note the database connection details for your `.env` file

### 3. Set Up Git Remote
On your local machine, add the server remote:
```bash
git remote add server user@status.dreamcoderz.com:/home/statusdreamcoder/repo.git
```

Replace `user` with your cPanel username.

## Git Hooks Configuration

### 1. Create Post-Receive Hook
Create the deployment hook on the server:
```bash
nano /home/statusdreamcoder/repo.git/hooks/post-receive
```

Add this content:
```bash
#!/bin/bash

# Git Post-Receive Hook for cPanel Deployment
# Automatically deploys Tamil Status Creator when pushing to cpanel branch

REPO_DIR="/home/statusdreamcoder/repo.git"
LIVE_DIR="/home/statusdreamcoder/live"
BACKUP_DIR="/home/statusdreamcoder/backups"
BRANCH="cpanel"

echo "🚀 Starting deployment process..."

# Check if the push is for the cpanel branch
while read oldrev newrev refname; do
    branch=$(git rev-parse --symbolic --abbrev-ref $refname)
    if [ "$branch" = "$BRANCH" ]; then
        echo "📥 Received push to $BRANCH branch"
        
        # Create backup of current live version
        if [ -d "$LIVE_DIR" ]; then
            echo "💾 Creating backup..."
            tar -czf "$BACKUP_DIR/backup-$(date +%Y%m%d-%H%M%S).tar.gz" -C "$LIVE_DIR" .
            
            # Keep only last 5 backups
            cd "$BACKUP_DIR"
            ls -t backup-*.tar.gz | tail -n +6 | xargs -r rm --
        fi
        
        # Deploy new version
        echo "📂 Checking out files to live directory..."
        git --git-dir=$REPO_DIR --work-tree=$LIVE_DIR checkout -f $BRANCH
        
        # Enter live directory and run deployment
        cd "$LIVE_DIR"
        
        # Check if backend directory exists
        if [ -d "backend" ]; then
            cd backend
            
            echo "🔧 Running deployment script..."
            if [ -f "deploy.sh" ]; then
                chmod +x deploy.sh
                ./deploy.sh
            else
                echo "⚠️  deploy.sh not found, running manual deployment..."
                
                # Manual deployment steps
                composer install --optimize-autoloader --no-dev --no-interaction
                php artisan config:cache
                php artisan route:cache
                php artisan view:cache
                php artisan migrate --force --no-interaction
                php artisan storage:link
                
                # Set permissions
                find . -type f -exec chmod 644 {} \\;
                find . -type d -exec chmod 755 {} \\;
                chmod -R 775 storage bootstrap/cache
            fi
            
            echo "✅ Deployment completed successfully!"
            echo "🌐 Live at: https://status.dreamcoderz.com"
        else
            echo "❌ Backend directory not found!"
            exit 1
        fi
    fi
done
```

Make the hook executable:
```bash
chmod +x /home/statusdreamcoder/repo.git/hooks/post-receive
```

### 2. Configure cPanel Document Root
In cPanel File Manager or via SSH, you may need to:
1. Point your subdomain/domain to `/home/statusdreamcoder/live/backend/public`
2. Or create a symbolic link from `public_html` to the Laravel public directory

## Deployment Process

### 1. Deploy Your Application
From your local machine:
```bash
# Make sure you're on the cpanel branch
git checkout cpanel

# Add and commit your changes
git add .
git commit -m "Deploy to cPanel"

# Push to server (this triggers automatic deployment)
git push server cpanel
```

### 2. First-Time Setup
After the first deployment, SSH to the server and run:
```bash
cd /home/statusdreamcoder/live/backend

# Generate application key (if not done automatically)
php artisan key:generate

# Run database seeders for initial data
php artisan db:seed

# Create admin user
php artisan make:admin
```

## File Structure on Server

After successful deployment:
```
/home/statusdreamcoder/
├── repo.git/              # Bare git repository
│   └── hooks/
│       └── post-receive   # Deployment automation
├── live/                  # Live application
│   ├── backend/           # Laravel application
│   │   ├── app/
│   │   ├── public/        # Web-accessible directory
│   │   ├── storage/       # File storage
│   │   ├── .env           # Production environment
│   │   └── deploy.sh      # Deployment script
│   ├── mobileapp/         # Flutter app (if included)
│   └── README.md
└── backups/               # Automatic backups
    ├── backup-20250118-143022.tar.gz
    └── ...
```

## API Endpoints

Once deployed, your API will be available at:
- **Base URL**: `https://status.dreamcoderz.com/api/v1/`
- **Health Check**: `https://status.dreamcoderz.com/api/v1/health`
- **Admin Panel**: `https://status.dreamcoderz.com/admin`
- **API Documentation**: `https://status.dreamcoderz.com/api/documentation`

## Troubleshooting

### Common Issues

#### 1. Permission Errors
```bash
# Fix Laravel permissions
cd /home/statusdreamcoder/live/backend
chmod -R 775 storage bootstrap/cache
chown -R user:user storage bootstrap/cache  # Replace 'user' with your cPanel username
```

#### 2. Database Connection Issues
- Verify database credentials in `.env`
- Ensure database user has proper privileges
- Check database host (usually `localhost` for cPanel)

#### 3. Composer Errors
```bash
# If composer is not found globally
cd /home/statusdreamcoder/live/backend
php ~/composer.phar install --optimize-autoloader --no-dev
```

#### 4. File Storage Issues
```bash
# Recreate storage symbolic link
cd /home/statusdreamcoder/live/backend
php artisan storage:link
```

#### 5. Git Push Issues
```bash
# If you get permission denied
ssh-add ~/.ssh/id_rsa  # Add your SSH key
git remote -v  # Verify remote URL is correct
```

### Deployment Logs
Check deployment logs on the server:
```bash
# Git hook output is logged during push
# Check server logs in cPanel or via SSH
tail -f /var/log/httpd/error_log  # Apache error log
tail -f /home/statusdreamcoder/live/backend/storage/logs/laravel.log
```

### Rollback Process
If deployment fails, restore from backup:
```bash
cd /home/statusdreamcoder
tar -xzf backups/backup-YYYYMMDD-HHMMSS.tar.gz -C live/
```

## Production Optimization

### 1. Performance
- Enable OPcache in cPanel PHP settings
- Set up cron jobs for Laravel scheduler if needed:
  ```bash
  * * * * * cd /home/statusdreamcoder/live/backend && php artisan schedule:run >> /dev/null 2>&1
  ```

### 2. Security
- Keep `.env` file secure and outside web root
- Set up regular database backups in cPanel
- Monitor error logs regularly
- Use strong passwords for all services

### 3. Monitoring
- Set up uptime monitoring for your API endpoints
- Monitor disk usage on cPanel
- Set up email alerts for application errors

## Branch Management

### Working with Both Environments
```bash
# Switch to Docker environment (master branch)
git checkout master

# Switch to cPanel environment
git checkout cpanel

# Merge changes from master to cpanel
git checkout cpanel
git merge master

# Deploy changes
git push server cpanel
```

### Keeping Branches in Sync
1. Develop new features in `master` branch (with Docker)
2. Test locally with Docker environment
3. Merge or cherry-pick changes to `cpanel` branch
4. Deploy to cPanel for testing
5. Use `master` branch for future production Docker deployment

## Support

### Getting Help
- Laravel Documentation: https://laravel.com/docs
- cPanel Documentation: Check your hosting provider's knowledge base
- Server logs: `/home/statusdreamcoder/live/backend/storage/logs/laravel.log`

### Updating the Application
1. Make changes in appropriate branch
2. Commit changes locally
3. Push to server: `git push server cpanel`
4. Monitor deployment output for any errors

---

**Last Updated**: January 2025
**Branch**: cpanel  
**Target**: status.dreamcoderz.com