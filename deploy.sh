#!/bin/bash
set -e

# ===================================================
# Unified Deploy Script
# Usage: ./deploy.sh [site]
# Examples:
#   ./deploy.sh rogence       # Deploy to rogence
#   ./deploy.sh syramik       # Deploy to syramik
#   ./deploy.sh demo-sibakuh  # Deploy to demo-sibakuh
#   ./deploy.sh all           # Deploy all platforms
#   ./deploy.sh               # Auto-detect from branch
#
# Branch structure:
#   main         → master branch with all features (no direct deploy)
#   rogence      → rogence site
#   syramik      → syramik site
#   demo-sibakuh → demo-sibakuh site
# ===================================================

SERVER="root@161.35.211.31"

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

deploy_site() {
    local SITE="$1"
    local REMOTE_PATH="${SITES[$SITE]}"
    local DEPLOY_BRANCH="$SITE"

    echo ""
    echo "🚀 Deploying to $SITE ($REMOTE_PATH)..."

    # Push the site branch
    echo "📤 Pushing $DEPLOY_BRANCH branch..."
    git push origin "$DEPLOY_BRANCH"

    # Deploy on server
    echo "📥 Syncing server..."
    ssh $SERVER "
        cd $REMOTE_PATH
        git fetch origin $DEPLOY_BRANCH
        git reset --hard origin/$DEPLOY_BRANCH
        php artisan migrate --force
        php artisan db:seed --class=FeatureSeeder --force
        php artisan db:seed --class=SiteFeatureSeeder --force
        php artisan cache:clear
        php artisan config:clear
        php artisan view:clear
        php artisan route:clear
    "

    echo "✅ $SITE deployed!"
}

# Deploy all platforms
if [ "$1" = "all" ]; then
    echo "🌍 Deploying ALL platforms..."
    for SITE in "${!SITES[@]}"; do
        deploy_site "$SITE"
    done
    echo ""
    echo "✅ All platforms deployed!"
    exit 0
fi

# Determine which site to deploy
SITE="${1}"

if [ -z "$SITE" ]; then
    # Auto-detect from current branch
    BRANCH=$(git rev-parse --abbrev-ref HEAD)
    SITE="${BRANCH_TO_SITE[$BRANCH]}"

    if [ -z "$SITE" ]; then
        echo "❌ Branch '$BRANCH' has no deploy target."
        echo ""
        echo "Usage: ./deploy.sh [rogence|syramik|demo-sibakuh|all]"
        echo ""
        echo "  main branch is the master codebase - switch to a platform branch to deploy:"
        echo "    git checkout rogence && ./deploy.sh"
        echo "    ./deploy.sh rogence"
        echo "    ./deploy.sh all"
        exit 1
    fi
fi

REMOTE_PATH="${SITES[$SITE]}"

if [ -z "$REMOTE_PATH" ]; then
    echo "❌ Unknown site: $SITE"
    echo "Available sites: ${!SITES[*]}"
    exit 1
fi

deploy_site "$SITE"
