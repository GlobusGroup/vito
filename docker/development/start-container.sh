#!/bin/sh

# Development container startup script

# Ensure storage directories exist with proper permissions
mkdir -p /var/www/html/storage/{app,framework,logs}
mkdir -p /var/www/html/storage/framework/{cache,sessions,testing,views}
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/bootstrap/cache

# Create the compiled views directory to prevent view:clear errors
mkdir -p /var/www/html/storage/framework/views

# Set ownership for all Laravel directories to vito user
chown -R vito:vito /var/www/html/storage /var/www/html/bootstrap/cache

# Set permissions for Laravel directories
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create .env file if it doesn't exist
if [ ! -f /var/www/html/.env ]; then
    echo "Creating .env file from .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
    chown vito:vito /var/www/html/.env
fi

# Run composer autoload dump to ensure all classes are loaded
echo "Regenerating Composer autoloader..."
su -c "cd /var/www/html && composer dump-autoload" vito

# Generate application key if not set
if ! grep -q "^APP_KEY=.\+" /var/www/html/.env; then
    echo "Generating application key..."
    su -c "cd /var/www/html && php artisan key:generate" vito
fi

# Run database migrations (optional - comment out if not needed)
echo "Running database migrations..."
su -c "cd /var/www/html && php artisan migrate --force" vito

# Clear and cache configuration for development
echo "Clearing caches for development..."
su -c "cd /var/www/html && php artisan config:clear" vito
su -c "cd /var/www/html && php artisan cache:clear" vito
su -c "cd /var/www/html && php artisan view:clear" vito 2>/dev/null || true
su -c "cd /var/www/html && php artisan route:clear" vito

# Create storage link
echo "Creating storage link..."
su -c "cd /var/www/html && php artisan storage:link" vito

# Start Vite development server in the background as vito user
echo "Starting Vite development server..."
su -c "cd /var/www/html && npm run dev" vito &

# Start cron daemon for Laravel scheduler
echo "Starting cron daemon..."
crond -b -l 8

# Start supervisord to manage PHP-FPM and Caddy
echo "Starting supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf