#!/bin/bash

echo "🚀 Quick Setup - Birth Certificate System"
echo "========================================"

echo "📦 Installing PHP dependencies (production only)..."
composer install --no-dev --optimize-autoloader

echo "📦 Installing Node.js dependencies..."
npm install

echo "🏗️ Building frontend assets..."
npm run build

echo "📁 Creating required directories..."
mkdir -p storage/logs
mkdir -p public/uploads/certificates
mkdir -p public/uploads/documents

echo "⚙️ Setting up environment..."
if [ ! -f .env ]; then
    cp env.example .env
fi

echo "🔐 Setting permissions..."
chmod -R 755 storage/
chmod -R 755 public/uploads/
chmod 644 .env

echo "✅ Quick setup complete!"
echo ""
echo "📝 Next steps:"
echo "1. Edit .env file with your database credentials"
echo "2. Set up your database (see SETUP_GUIDE.md)"
echo "3. Start server: php -S localhost:8000 -t public"
echo "4. Visit: http://localhost:8000" 