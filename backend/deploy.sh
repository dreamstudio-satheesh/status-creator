#!/bin/bash

# Tamil Status Creator - Deployment Script for SQLite
echo "🚀 Starting deployment with SQLite..."

# Ensure SQLite database exists
touch database/database.sqlite

# Run fresh migrations with seeding
php artisan migrate:fresh --seed --force

echo "✅ SQLite migration and seeding completed!"
