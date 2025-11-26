#!/bin/bash

# --- 1. System Dependencies ---
# Install FFmpeg (Required by your .env and README)
# NOTE: This runs on every restart. For faster boots, consider a custom Docker image later.
echo "Installing FFmpeg..."
apt-get update && apt-get install -y ffmpeg

# --- 2. Server Configuration ---
# Copy custom Nginx config (Ensure you created nginx.conf from the previous step)
cp /home/site/wwwroot/nginx.conf /etc/nginx/sites-available/default
service nginx reload

# --- 3. Laravel Setup ---
cd /home/site/wwwroot

# Install PHP dependencies (Only if vendor folder is missing)
if [ ! -d "vendor" ]; then
    echo "Vendor folder missing, running composer install..."
    composer install --optimize-autoloader --no-dev
fi

# Link storage (Crucial for file uploads)
php artisan storage:link

# Run Migrations
# We use --force because we are in production
php artisan migrate --force

# Run Seeders
# WARNING: Running seeders on every startup can duplicate data if not handled carefully in the seeder code.
# If your seeders are safe to run multiple times, keep these lines:
php artisan db:seed --class=AiAgentUserSeeder --force
php artisan db:seed --class=BallotQuestionSeeder --force

# Optimization & Caching
echo "Caching configuration..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# --- 4. Queue Worker (The "Brain") ---
# The README asks for `php artisan queue:work`.
# In Azure, we must run this in the background (nohup) so it doesn't block the web server.
echo "Starting Queue Worker..."
nohup php artisan queue:work --daemon --tries=3 > /dev/null 2>&1 &

# --- 5. Assets (NPM) ---
# NOTE: In Azure, we do NOT run `npm run dev`.
# You must have run `npm run build` locally or in your deployment pipeline
# so the `public/build` folder is already present.
