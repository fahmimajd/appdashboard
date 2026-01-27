#!/bin/bash

# Exit on error
set -e

# Run composer install if vendor not found
if [ ! -d "vendor" ]; then
    composer install --no-interaction --no-progress
fi

# Create .env if not exists
if [ ! -f ".env" ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Set permissions
chmod -R 777 storage bootstrap/cache

# Start PHP-FPM
php-fpm
