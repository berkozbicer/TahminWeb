#!/usr/bin/env bash
# Production deploy helper script
# Usage: sudo ./deploy.sh /path/to/project

set -euo pipefail
PROJECT_PATH=${1:-/var/www/at-yarislari}
USER=${2:-www-data}

echo "Deploying to $PROJECT_PATH as $USER"
cd $PROJECT_PATH

echo "Pull latest changes"
if [ -d .git ]; then
  git pull --rebase
fi

echo "Install composer deps"
composer install --no-dev --optimize-autoloader --prefer-dist

echo "Set permissions"
chown -R $USER:$USER storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

echo "Run migrations"
php artisan migrate --force

echo "Cache config, routes and views"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Restart queue workers"
# If using supervisor
if command -v supervisorctl >/dev/null 2>&1; then
  supervisorctl reread || true
  supervisorctl update || true
  supervisorctl restart all || true
fi

# If using systemd services
if command -v systemctl >/dev/null 2>&1; then
  systemctl restart laravel-worker || true
  systemctl restart horizon || true
fi

echo "Deploy finished"
