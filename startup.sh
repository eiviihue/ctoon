# Copy nginx configuration
cp /home/site/wwwroot/nginx.conf /etc/nginx/sites-enabled/default

# Copy PHP configuration if exists
if [ -f "/home/php.ini" ]; then
    cp /home/php.ini /usr/local/etc/php/conf.d/php.ini
fi

# Enable Laravel maintenance mode
php /home/site/wwwroot/artisan down --refresh=15 --secret="1630542a-246b-4b66-afa1-dd72a4c43515"

# Run migrations
php /home/site/wwwroot/artisan migrate --force

# Clear and cache everything
php /home/site/wwwroot/artisan optimize
php /home/site/wwwroot/artisan view:cache

# Instead of restarting nginx directly, touch the restart marker
touch /tmp/app_initialized
mkdir -p /home/LogFiles
echo "$(date): Initialization complete" >> /home/LogFiles/startup.log

# Disable maintenance mode
php /home/site/wwwroot/artisan up

