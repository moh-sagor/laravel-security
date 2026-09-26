#!/usr/bin/env bash
set -e

echo "===================================================="
echo "    Testing Product CRUD & Security Firewall        "
echo "===================================================="

CD_DIR="/home/virus/Downloads/demo-app"

cd "$CD_DIR"

echo "[1/3] Running Laravel Security Package & CRUD Test Suite..."
php artisan test tests/Feature/SecurityPackageTest.php

echo ""
echo "[2/3] Executing Live Security Firewall Self-Test (Populating Threats Log in MySQL)..."
php artisan security:test

echo ""
echo "[3/3] Generating Live Security Firewall Report..."
php artisan security:report

echo ""
echo "===================================================="
echo "✓ All tests executed successfully and threats logged to MySQL!"
echo "===================================================="
