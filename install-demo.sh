#!/usr/bin/env bash
set -e

echo "===================================================="
echo "    Installing Laravel Project & Security Package   "
echo "===================================================="

CD_DIR="/home/virus/Downloads"
PACKAGE_DIR="/home/virus/Downloads/Package"
PROJECT_DIR="/home/virus/Downloads/demo-app"

cd "$CD_DIR"

if [ ! -d "$PROJECT_DIR" ]; then
    echo "[1/5] Creating new Laravel project in demo-app..."
    composer create-project laravel/laravel demo-app --prefer-dist --no-scripts
else
    echo "[1/5] Directory demo-app already exists."
fi

cd "$PROJECT_DIR"

echo "[2/5] Configuring MySQL database connection in .env..."
if [ -f .env ]; then
    sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
    sed -i 's/# DB_HOST=.*/DB_HOST=127.0.0.1/' .env
    sed -i 's/# DB_PORT=.*/DB_PORT=3306/' .env
    sed -i 's/# DB_DATABASE=.*/DB_DATABASE=demo_app/' .env
    sed -i 's/# DB_USERNAME=.*/DB_USERNAME=root/' .env
    sed -i 's/# DB_PASSWORD=.*/DB_PASSWORD=/' .env
fi

echo "[3/5] Linking local sagor/laravel-security package..."
composer config minimum-stability dev
composer config prefer-stable true
composer config repositories.laravel-security path "$PACKAGE_DIR"
composer require sagor/laravel-security:@dev --no-interaction

echo "[4/5] Running Security Firewall installer..."
php artisan security:install --no-interaction

echo "[5/5] Database Migrations Status..."
echo "To run migrations on your MySQL database (after creating 'demo_app' database in MySQL):"
echo "  php artisan migrate"

echo "===================================================="
echo "  Installation Complete with MySQL configuration!"
echo "  Run: cd /home/virus/Downloads/demo-app && php artisan serve"
echo "===================================================="
