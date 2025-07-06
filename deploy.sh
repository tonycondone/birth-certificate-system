#!/bin/bash

# Birth Certificate System Deployment Script

set -e  # Exit immediately if a command exits with a non-zero status

# Configuration
PROJECT_DIR="/var/www/birth-certificate-system"
BACKUP_DIR="/var/backups/birth-certificate-system"
ENV_FILE=".env.production"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

# Logging
exec > >(tee -a /var/log/birth-certificate-deployment.log)
exec 2>&1

echo "🚀 Starting Birth Certificate System Deployment"
echo "============================================"

# Pre-deployment Checks
echo "📋 Running Pre-Deployment Checks..."
if [ ! -f "$ENV_FILE" ]; then
    echo "❌ Error: Production environment file not found!"
    exit 1
fi

# Create Backup
echo "💾 Creating System Backup..."
mkdir -p "$BACKUP_DIR"
cp -R "$PROJECT_DIR" "$BACKUP_DIR/backup_$TIMESTAMP"

# Pull Latest Code
echo "📥 Pulling Latest Code..."
cd "$PROJECT_DIR"
git fetch origin
git checkout main
git pull origin main

# Install Dependencies
echo "📦 Installing Dependencies..."
composer install --no-dev --optimize-autoloader

# Database Migration
echo "🗃️ Running Database Migrations..."
php artisan migrate --force

# Clear Caches
echo "🧹 Clearing Application Caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Optimize Application
echo "🚀 Optimizing Application..."
php artisan optimize
php artisan config:cache
php artisan route:cache

# Security Hardening
echo "🔒 Applying Security Hardening..."
chmod -R 750 "$PROJECT_DIR"
chown -R www-data:www-data "$PROJECT_DIR"

# Run Tests
echo "🧪 Running Deployment Tests..."
php run_tests.php

# Restart Services
echo "🔄 Restarting Web Services..."
systemctl restart nginx
systemctl restart php8.1-fpm

# Post-Deployment Monitoring
echo "📡 Triggering Monitoring Alerts..."
curl -X POST https://monitoring.service/deployment-alert \
     -H "Authorization: Bearer $MONITORING_TOKEN" \
     -d "service=birth-certificate-system&status=success&timestamp=$TIMESTAMP"

echo "✅ Deployment Completed Successfully!"
echo "Timestamp: $TIMESTAMP" 