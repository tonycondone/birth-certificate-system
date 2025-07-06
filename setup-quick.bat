@echo off
echo 🚀 Quick Setup - Birth Certificate System
echo ========================================

echo 🏗️ Building frontend assets...
npm run build

echo 📁 Creating required directories...
if not exist "storage\logs" mkdir storage\logs
if not exist "public\uploads\certificates" mkdir public\uploads\certificates
if not exist "public\uploads\documents" mkdir public\uploads\documents

echo ⚙️ Setting up environment...
if not exist ".env" copy env.example .env

echo ✅ Quick setup complete!
echo.
echo 📝 Next steps:
echo 1. Edit .env file with your database credentials
echo 2. Set up your database (see SETUP_GUIDE.md)
echo 3. Start server: php -S localhost:8000 -t public
echo 4. Visit: http://localhost:8000
echo.
pause 