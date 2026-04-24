#!/bin/bash
set -e

echo "🚀 Starting deployment..."

# Put application in maintenance mode
echo "🚧 Putting application in maintenance mode..."
php artisan down || true

# Pull the latest changes from the git repository
echo "📥 Pulling latest changes..."
git pull origin product

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Install Node dependencies
echo "🌐 Installing Node dependencies..."
npm install

# Build assets using Vite
echo "🏗️ Building assets..."
npm run build

# Clear and cache configuration, routes, and views
echo "⚡ Optimizing application..."
php artisan optimize

# Bring application out of maintenance mode
echo "🚀 Bringing application out of maintenance mode..."
php artisan up

echo "✅ Deployment finished successfully!"
