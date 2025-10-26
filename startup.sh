# Set up nginx configuration
mkdir -p /etc/nginx/conf.d
cp /home/site/wwwroot/nginx.conf /etc/nginx/conf.d/default.conf

# Copy PHP configuration if exists
if [ -f "/home/php.ini" ]; then
    cp /home/php.ini /usr/local/etc/php/conf.d/php.ini
fi

# Verify nginx configuration
nginx -t || echo "Nginx configuration test failed"

# Wait for nginx to be available
timeout=300
counter=0
while [ $counter -lt $timeout ]; do
    if pgrep -x "nginx" > /dev/null; then
        echo "Nginx is running"
        break
    fi
    echo "Waiting for nginx to start..."
    sleep 1
    counter=$((counter + 1))
done

# Enable Laravel maintenance mode
php /home/site/wwwroot/artisan down --refresh=15 --secret="1630542a-246b-4b66-afa1-dd72a4c43515"

# Run migrations
php /home/site/wwwroot/artisan migrate --force

# Clear and cache everything
php /home/site/wwwroot/artisan optimize
php /home/site/wwwroot/artisan view:cache

# Create initialization marker and log
mkdir -p /home/LogFiles
echo "$(date): PHP Version: $(php -v)" >> /home/LogFiles/startup.log
echo "$(date): Nginx Status: $(ps aux | grep nginx)" >> /home/LogFiles/startup.log
echo "$(date): Document Root: $NGINX_DOCUMENT_ROOT" >> /home/LogFiles/startup.log
echo "$(date): Initialization complete" >> /home/LogFiles/startup.log

# Disable maintenance mode
php /home/site/wwwroot/artisan up

