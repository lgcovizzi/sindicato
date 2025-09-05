#!/bin/sh

# Wait for database to be ready
echo "Waiting for database..."
while ! nc -z postgres 5432; do
  sleep 1
done
echo "Database is ready!"

# Wait for Redis to be ready
echo "Waiting for Redis..."
while ! nc -z redis 6379; do
  sleep 1
done
echo "Redis is ready!"

# Generate application key if not exists
if [ ! -f ".env" ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Clear and cache configurations
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run database migrations
php artisan migrate --force

# Seed database if in development
if [ "$APP_ENV" = "local" ]; then
    php artisan db:seed --force
fi

# Cache configurations for production
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Create storage link
php artisan storage:link

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf