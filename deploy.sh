#!/bin/bash
set -e
cd "$(dirname "$0")"
PHP=/opt/alt/php82/usr/bin/php
git pull origin main
$PHP artisan migrate --force
$PHP artisan optimize:clear
echo "Deployed OK"
