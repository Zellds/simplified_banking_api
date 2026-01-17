#!/bin/bash

set -e

echo "🔧 Exporting host UID and GID"
export HOST_UID=$(id -u)
export HOST_GID=$(id -g)

echo "Building containers"
docker compose build

echo "Starting containers"
docker compose up -d

echo "Installing dependencies"
docker compose exec app composer install

if [ ! -f ".env" ]; then
  echo ".env not found, creating from .env.example"
  docker compose exec app cp .env.example .env
else
  echo ".env already exists, skipping"
fi

echo "Generating app key"
docker compose exec app php artisan key:generate

echo "Running migrations"
docker compose exec app php artisan migrate --force || true

echo "Starting Laravel server"
docker compose exec -d app php artisan serve --host=0.0.0.0 --port=8000

echo "Application is up and running at http://localhost:8000"
