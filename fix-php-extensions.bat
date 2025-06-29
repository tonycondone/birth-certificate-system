@echo off
echo ========================================
echo PHP Extensions Fix Script
echo ========================================
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% == 0 (
    echo Running as administrator - OK
) else (
    echo WARNING: This script should be run as administrator
    echo Right-click and select "Run as administrator"
    pause
    exit /b 1
)

echo.
echo Searching for PHP installation...

REM Try to find PHP installation
set "PHP_INI="
set "PHP_DIR="

REM Check common XAMPP locations
if exist "C:\xampp\php\php.ini" (
    set "PHP_INI=C:\xampp\php\php.ini"
    set "PHP_DIR=C:\xampp\php"
    echo Found XAMPP installation at C:\xampp
) else if exist "C:\wamp64\bin\php\php8.1.0\php.ini" (
    set "PHP_INI=C:\wamp64\bin\php\php8.1.0\php.ini"
    set "PHP_DIR=C:\wamp64\bin\php\php8.1.0"
    echo Found WAMP installation at C:\wamp64
) else if exist "C:\wamp\bin\php\php8.1.0\php.ini" (
    set "PHP_INI=C:\wamp\bin\php\php8.1.0\php.ini"
    set "PHP_DIR=C:\wamp\bin\php\php8.1.0"
    echo Found WAMP installation at C:\wamp
) else (
    echo ERROR: Could not find PHP installation
    echo Please install XAMPP or WAMP first
    pause
    exit /b 1
)

echo PHP INI file: %PHP_INI%
echo PHP Directory: %PHP_DIR%
echo.

REM Create backup
echo Creating backup of php.ini...
copy "%PHP_INI%" "%PHP_INI%.backup.%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%"
if %errorLevel% neq 0 (
    echo ERROR: Could not create backup
    pause
    exit /b 1
)
echo Backup created successfully
echo.

REM Enable extensions using PowerShell
echo Enabling PHP extensions...
powershell -Command "& {
    $content = Get-Content '%PHP_INI%'
    $content = $content -replace ';extension=pdo', 'extension=pdo'
    $content = $content -replace ';extension=pdo_mysql', 'extension=pdo_mysql'
    $content = $content -replace ';extension=json', 'extension=json'
    $content = $content -replace ';extension=xml', 'extension=xml'
    $content = $content -replace ';extension=tokenizer', 'extension=tokenizer'
    $content = $content -replace ';extension=mbstring', 'extension=mbstring'
    $content = $content -replace ';extension=fileinfo', 'extension=fileinfo'
    Set-Content '%PHP_INI%' $content
}"

if %errorLevel% neq 0 (
    echo ERROR: Could not modify php.ini
    pause
    exit /b 1
)

echo Extensions enabled successfully
echo.

REM Verify extensions
echo Verifying extensions...
"%PHP_DIR%\php.exe" -m | findstr /i "pdo json xml tokenizer mbstring fileinfo"
if %errorLevel% neq 0 (
    echo WARNING: Some extensions may not be loaded
    echo Please restart your web server
) else (
    echo All required extensions are loaded
)

echo.
echo ========================================
echo Fix completed successfully!
echo ========================================
echo.
echo Next steps:
echo 1. Restart your web server (Apache/Nginx)
echo 2. Restart your PHP development server
echo 3. Test the application
echo.
echo If you're using XAMPP:
echo - Open XAMPP Control Panel
echo - Stop Apache
echo - Start Apache
echo.
echo If you're using WAMP:
echo - Right-click WAMP icon
echo - Restart All Services
echo.
pause 