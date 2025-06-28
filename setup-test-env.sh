#!/bin/bash

# Create test database
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS birth_certificate_system_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations for test database
for migration in database/migrations/*.sql; do
    mysql -u root -p birth_certificate_system_test < "$migration"
done

# Install PHP extensions
sudo apt-get update
sudo apt-get install -y \
    php8.1-xml \
    php8.1-mbstring \
    php8.1-zip \
    php8.1-pdo \
    php8.1-mysql \
    php8.1-json \
    php8.1-tokenizer \
    php8.1-fileinfo

# Install Composer dependencies
composer install

# Run tests
./vendor/bin/phpunit