#!/bin/bash
set -e

SERVER="root@syramik.newaves-systems.com"
REMOTE_PATH="/var/www/syramik.newaves-systems.com"
BRANCH="syramik"

echo "🚀 Deploying to $SERVER..."

# Pull latest changes first
echo "📥 Pulling latest changes..."
git pull --rebase origin $BRANCH

# Push local changes
echo "📤 Pushing to git..."
git push origin $BRANCH

# Deploy on server
echo "📥 Pulling on server..."
ssh $SERVER "
    cd $REMOTE_PATH
    git pull origin $BRANCH
    php artisan migrate --force
    php artisan cache:clear
    php artisan config:clear
    php artisan view:clear
    php artisan route:clear
"

echo "✅ Deployed successfully!"
