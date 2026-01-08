#!/usr/bin/env bash
set -e
TARGET_DIR="."
if [ -f "$TARGET_DIR/artisan" ]; then
  echo "Laravel already appears installed in $TARGET_DIR"
  exit 0
fi

echo "Running composer create-project in $TARGET_DIR..."
composer create-project --prefer-dist laravel/laravel "$TARGET_DIR"

cd "$TARGET_DIR"
if [ -f .env.example ]; then
  cp .env.example .env
fi
composer install
php artisan key:generate
php artisan migrate
npm install
npm run dev

echo "Done. Run: php artisan serve"