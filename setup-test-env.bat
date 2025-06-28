@echo off
setlocal enabledelayedexpansion

REM Create test database
echo Creating test database...
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS birth_certificate_system_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

REM Run migrations for test database
echo Running migrations...
for %%f in (database\migrations\*.sql) do (
    echo Processing migration: %%f
    mysql -u root -p birth_certificate_system_test < "%%f"
)

REM Install PHP extensions
echo Checking PHP extensions...
php -m | findstr "pdo_mysql"
if errorlevel 1 (
    echo PDO MySQL extension not found. Please install it in your php.ini
)

php -m | findstr "json"
if errorlevel 1 (
    echo JSON extension not found. Please install it in your php.ini
)

php -m | findstr "xml"
if errorlevel 1 (
    echo XML extension not found. Please install it in your php.ini
)

REM Install Composer dependencies
echo Installing Composer dependencies...
composer install

REM Create test environment file
echo Creating test environment file...
copy .env.example .env.testing
echo DB_DATABASE=birth_certificate_system_test >> .env.testing
echo APP_ENV=testing >> .env.testing

REM Run tests
echo Running tests...
vendor\bin\phpunit

endlocal