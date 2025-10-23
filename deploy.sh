#!/bin/bash

# Composer install
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Generate key if not already set
php artisan key:generate --force

# Clear cache
php artisan cache:clear
php artisan config:clear

# Storage link
php artisan storage:link

# Database migrations
php artisan migrate --force

# Optimize
php artisan optimize

# NPM Install & Build (if you have front-end assets)
if [ -f "package.json" ]; then
    npm install
    npm run build
fi