#!/bin/bash
set -e

SERVER="root@rogence.newaves-systems.com"
REMOTE_PATH="/var/www/rogence.newaves-systems.com"

echo "🚀 Deploying to $SERVER..."

# Push local changes
echo "📤 Pushing to git..."
git push origin main

# Deploy on server
echo "📥 Pulling on server..."
ssh $SERVER "
    cd $REMOTE_PATH
    git pull origin main
    php artisan migrate --force
    php artisan cache:clear
    php artisan config:clear
    php artisan view:clear
    php artisan route:clear
"

echo "✅ Deployed successfully!"
