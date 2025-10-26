# Copy nginx configuration
cp /home/site/wwwroot/nginx.conf /etc/nginx/sites-enabled/default

# Copy PHP configuration if exists
if [ -f "/home/php.ini" ]; then
    cp /home/php.ini /usr/local/etc/php/conf.d/php.ini
fi

# Clear and cache everything
php /home/site/wwwroot/artisan cache:clear
php /home/site/wwwroot/artisan route:cache
php /home/site/wwwroot/artisan config:cache
php /home/site/wwwroot/artisan view:cache

# Restart nginx
service nginx restart

