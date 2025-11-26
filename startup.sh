#!/bin/bash

# --- 1. System Dependencies ---
echo "Installing FFmpeg..."
apt-get update && apt-get install -y ffmpeg

# --- 2. Create Missing Directories (Fixes the 500 Errors) ---
echo "Creating storage directories..."
mkdir -p /home/site/wwwroot/storage/framework/sessions
mkdir -p /home/site/wwwroot/storage/framework/views
mkdir -p /home/site/wwwroot/storage/framework/cache
mkdir -p /home/site/wwwroot/storage/logs

# --- 3. SSL Certificate (Fixes Database SSL Error) ---
# We check if the file exists; if not, we download it.
if [ ! -f /home/site/wwwroot/ssl/DigiCertGlobalRootG2.crt.pem ]; then
    echo "Downloading Azure MySQL SSL Certificate..."
    mkdir -p /home/site/wwwroot/ssl
    wget -O /home/site/wwwroot/ssl/DigiCertGlobalRootG2.crt.pem https://dl.cacerts.digicert.com/DigiCertGlobalRootG2.crt.pem
fi

# --- 4. Permissions ---
# Ensure the server can write to storage
echo "Setting permissions..."
chmod -R 775 /home/site/wwwroot/storage
chown -R www-data:www-data /home/site/wwwroot/storage

# --- 5. Nginx Config ---
cp /home/site/wwwroot/nginx.conf /etc/nginx/sites-available/default
service nginx reload

# --- 6. Laravel Setup ---
cd /home/site/wwwroot

# Install PHP deps if missing (usually handled by GitHub Actions, but good backup)
if [ ! -d "vendor" ]; then
    composer install --optimize-autoloader --no-dev
fi

# Link storage
php artisan storage:link

# Run Migrations (Force is required in production)
echo "Running Migrations..."
php artisan migrate --force

# --- 7. Caching ---
echo "Clearing and Rebuilding Cache..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# --- 8. Start Queue Worker ---
echo "Starting Queue Worker..."
nohup php artisan queue:work --daemon --tries=3 > /dev/null 2>&1 &
