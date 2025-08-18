#!/bin/bash

# cPanel Deployment Script for Tamil Status Creator
# This script optimizes Laravel for cPanel shared hosting

echo "🚀 Starting cPanel deployment process..."

# Install/update composer dependencies for production
echo "📦 Installing production dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction

# Generate application key if not exists
if [ ! -f .env ]; then
    echo "⚠️  .env file not found. Please create it from .env.example"
    exit 1
fi

# Check if APP_KEY is set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generating application key..."
    php artisan key:generate --no-interaction
fi

# Clear and cache configuration for production
echo "🧹 Clearing and caching configurations..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Cache configuration for better performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations (with safety check)
echo "🗄️  Running database migrations..."
php artisan migrate --force --no-interaction

# Create storage symbolic link
echo "🔗 Creating storage symbolic link..."
php artisan storage:link

# Set proper permissions for cPanel
echo "🔒 Setting proper file permissions..."
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache

# Clear any remaining cache
php artisan optimize:clear

echo "✅ Deployment completed successfully!"
echo "🌐 Your application should now be live at: https://status.dreamcoderz.com"

# Optional: Run seeders for initial data (uncomment if needed)
# echo "🌱 Running database seeders..."
# php artisan db:seed --no-interaction