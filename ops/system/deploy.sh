#!/bin/bash

cd /var/www/staging/lasid

# Load NVM (required for CI / non-interactive shells)
export NVM_DIR="$HOME/.nvm"
source "$NVM_DIR/nvm.sh"

# Ensure correct Node version
nvm use 24

# Enable corepack (pnpm)
corepack enable

git pull origin main

composer install --no-dev

pnpm install
pnpm build

php artisan migrate

php artisan storage:link

php artisan config:cache
php artisan route:cache

supervisorctl restart grant_queue:*

echo "Production deployment complete"