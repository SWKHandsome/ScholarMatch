#!/bin/sh
set -eu

cd /home/site/wwwroot

mkdir -p bootstrap/cache storage/app/private storage/app/public \
    storage/framework/cache/data storage/framework/sessions \
    storage/framework/views storage/logs

# Cache Azure's environment, never the developer's local .env or cached config.
# Database restoration/migrations are deliberate release steps, not restart tasks.
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

cp deploy/azure/nginx.conf /etc/nginx/sites-available/default
nginx -t
service nginx reload
