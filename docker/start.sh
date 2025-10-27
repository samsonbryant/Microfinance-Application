#!/bin/sh

set -e

echo "Starting Microfinance Application..."

# Navigate to application directory
cd /var/www/html

# Set correct permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache

# Ensure database directory exists and has correct permissions
mkdir -p /var/www/html/database
touch /var/www/html/database/database.sqlite
chown www-data:www-data /var/www/html/database/database.sqlite
chmod 664 /var/www/html/database/database.sqlite

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force --no-interaction

# Seed database if empty
echo "Checking if database needs seeding..."
php artisan db:seed --force --no-interaction || true

# Link storage
echo "Linking storage..."
php artisan storage:link || true

# Clear and cache config
echo "Optimizing application..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
# Skip route caching for now to avoid conflicts during deployment
# php artisan route:cache
php artisan view:cache

# Create symbolic link for storage if not exists
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link
fi

echo "Application ready!"

# Start Supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

