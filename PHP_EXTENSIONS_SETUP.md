# PHP Extensions Setup Guide

## Overview

The Digital Birth Certificate System requires several PHP extensions to function properly. This guide will help you install the missing extensions that are causing warnings in your PHP startup.

## Missing Extensions

Based on the warnings in your logs, the following extensions need to be installed:

- **pdo** - Database connectivity
- **json** - JSON data handling
- **xml** - XML processing
- **tokenizer** - PHP token processing

## Installation Methods

### Method 1: Windows (XAMPP/WAMP)

#### For XAMPP:
1. **Locate php.ini file:**
   ```
   C:\xampp\php\php.ini
   ```

2. **Uncomment the following lines** (remove the semicolon at the beginning):
   ```ini
   extension=pdo
   extension=pdo_mysql
   extension=json
   extension=xml
   extension=tokenizer
   ```

3. **Restart Apache/Nginx**

#### For WAMP:
1. **Right-click on WAMP icon** → PHP → PHP Extensions
2. **Enable the following extensions:**
   - pdo
   - pdo_mysql
   - json
   - xml
   - tokenizer

### Method 2: Windows (Standalone PHP)

1. **Download PHP extensions** from [PHP for Windows](https://windows.php.net/downloads/pecl/releases/)
2. **Extract DLL files** to your PHP extensions directory
3. **Edit php.ini** and add:
   ```ini
   extension=pdo
   extension=pdo_mysql
   extension=json
   extension=xml
   extension=tokenizer
   ```

### Method 3: Linux (Ubuntu/Debian)

```bash
# Update package list
sudo apt update

# Install PHP extensions
sudo apt install php8.1-pdo php8.1-pdo-mysql php8.1-json php8.1-xml php8.1-tokenizer

# Restart web server
sudo systemctl restart apache2
# or
sudo systemctl restart nginx
```

### Method 4: Linux (CentOS/RHEL)

```bash
# Install PHP extensions
sudo yum install php-pdo php-pdo-mysql php-json php-xml php-tokenizer

# Restart web server
sudo systemctl restart httpd
```

### Method 5: macOS (Homebrew)

```bash
# Install PHP with extensions
brew install php

# The extensions should be included by default
# If not, install individually:
brew install php@8.1
```

## Verification

After installation, verify the extensions are loaded:

```bash
# Check loaded extensions
php -m | grep -E "(pdo|json|xml|tokenizer)"

# Expected output:
# json
# pdo
# pdo_mysql
# tokenizer
# xml
```

## Testing

1. **Restart your web server**
2. **Run the application:**
   ```bash
   php -S localhost:8000 -t public
   ```
3. **Check for warnings** - they should be gone

## Troubleshooting

### Extension Still Not Loading

1. **Check php.ini location:**
   ```bash
   php --ini
   ```

2. **Verify extension directory:**
   ```bash
   php -i | grep extension_dir
   ```

3. **Check if extension files exist:**
   ```bash
   ls /path/to/php/extensions/ | grep -E "(pdo|json|xml|tokenizer)"
   ```

### Common Issues

#### Issue: "The specified module could not be found"
**Solution:** 
- Ensure the DLL files are in the correct extensions directory
- Check that the PHP version matches the extension version
- Verify file permissions

#### Issue: "Module is already loaded"
**Solution:**
- Remove duplicate entries in php.ini
- Check for multiple php.ini files

#### Issue: "Permission denied"
**Solution:**
- Run as administrator (Windows)
- Check file permissions (Linux/macOS)
- Ensure web server has read access

## Production Deployment

For production environments:

1. **Install extensions via package manager** (recommended)
2. **Verify all extensions are loaded**
3. **Test thoroughly** before deployment
4. **Monitor logs** for any remaining issues

## Alternative: Docker

If you're using Docker, add these extensions to your Dockerfile:

```dockerfile
# Install PHP extensions
RUN apt-get update && apt-get install -y \
    php8.1-pdo \
    php8.1-pdo-mysql \
    php8.1-json \
    php8.1-xml \
    php8.1-tokenizer
```

## Support

If you continue to have issues:

1. **Check PHP version compatibility**
2. **Verify web server configuration**
3. **Review system logs**
4. **Contact system administrator**

## Quick Fix Script

For Windows users, create a batch file to enable extensions:

```batch
@echo off
echo Enabling PHP extensions...

REM Backup original php.ini
copy "C:\xampp\php\php.ini" "C:\xampp\php\php.ini.backup"

REM Enable extensions
powershell -Command "(Get-Content 'C:\xampp\php\php.ini') -replace ';extension=pdo', 'extension=pdo' -replace ';extension=pdo_mysql', 'extension=pdo_mysql' -replace ';extension=json', 'extension=json' -replace ';extension=xml', 'extension=xml' -replace ';extension=tokenizer', 'extension=tokenizer' | Set-Content 'C:\xampp\php\php.ini'"

echo Extensions enabled. Please restart your web server.
pause
```

---

**Note:** Always backup your configuration files before making changes. 