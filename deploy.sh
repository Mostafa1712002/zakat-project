#!/bin/bash
set -e

# ===================================================
# Unified Deploy Script
# Usage: ./deploy.sh [site]
# Examples:
#   ./deploy.sh rogence       # Deploy to rogence
#   ./deploy.sh syramik       # Deploy to syramik
#   ./deploy.sh demo-sibakuh  # Deploy to demo-sibakuh
#   ./deploy.sh               # Auto-detect from branch
# ===================================================

SERVER="root@rogence.newaves-systems.com"

# Site → Remote path mapping
declare -A SITES=(
    ["rogence"]="/var/www/rogence.newaves-systems.com"
    ["syramik"]="/var/www/syramik.newaves-systems.com"
    ["demo-sibakuh"]="/var/www/demo-sibakuh.newaves-systems.com"
)

# Branch → Site mapping (for auto-detect)
declare -A BRANCH_TO_SITE=(
    ["rogence"]="rogence"
    ["syramik"]="syramik"
    ["demo-sibakuh"]="demo-sibakuh"
)

# Determine which site to deploy
SITE="${1}"

if [ -z "$SITE" ]; then
    # Auto-detect from current branch
    BRANCH=$(git rev-parse --abbrev-ref HEAD)
    SITE="${BRANCH_TO_SITE[$BRANCH]}"

    if [ -z "$SITE" ]; then
        echo "❌ Unknown branch '$BRANCH'. Specify site: ./deploy.sh [rogence|syramik|demo-sibakuh]"
        exit 1
    fi
fi

REMOTE_PATH="${SITES[$SITE]}"

if [ -z "$REMOTE_PATH" ]; then
    echo "❌ Unknown site: $SITE"
    echo "Available sites: ${!SITES[*]}"
    exit 1
fi

echo "🚀 Deploying to $SITE ($REMOTE_PATH)..."

# Push local changes
BRANCH=$(git rev-parse --abbrev-ref HEAD)
echo "📤 Pushing to git ($BRANCH)..."
git push origin "$BRANCH"

# Deploy on server
echo "📥 Pulling on server..."
ssh $SERVER "
    cd $REMOTE_PATH
    git pull origin $BRANCH
    php artisan migrate --force
    php artisan db:seed --class=FeatureSeeder --force
    php artisan cache:clear
    php artisan config:clear
    php artisan view:clear
    php artisan route:clear
"

echo "✅ Deployed $SITE successfully!"
