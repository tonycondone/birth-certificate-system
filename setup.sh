#!/bin/bash

# Install PHP dependencies
composer install

# Create test database
mysql -u root -e "CREATE DATABASE IF NOT EXISTS birth_certificate_test;"

# Run migrations for test database
for file in database/migrations/*.sql; do
    mysql -u root birth_certificate_test < "$file"
done

# Generate autoloader
composer dump-autoload -o</kodu_content>