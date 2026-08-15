#!/usr/bin/env bash
#
# Despliega la version actual de la rama en el servidor.
# Se ejecuta en la VM, dentro de /var/www/centros_acopio:
#
#   ./deploy/desplegar.sh
#
# No usa sudo: la aplicacion pertenece al usuario que despliega.

set -euo pipefail

RUTA_APP="/var/www/centros_acopio"
RAMA="${1:-master}"

cd "$RUTA_APP"

echo "==> Trayendo la rama $RAMA"
git fetch --all --prune
git checkout "$RAMA"
git pull --ff-only origin "$RAMA"

echo "==> Instalando dependencias de produccion"
# --no-dev deja fuera laravel-lang/common: los archivos de lang/es ya
# estan versionados, la libreria solo hace falta para regenerarlos.
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "==> Migrando la base de datos"
php artisan migrate --force

echo "==> Sembrando el catalogo de insumos"
# El seeder usa updateOrCreate: repetirlo no duplica nada.
php artisan db:seed --class=CatalogoItemsSeeder --force

echo "==> Regenerando caches"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Ajustando permisos de escritura"
# id -un y no $USER: cron no define esa variable, y con set -u el
# despliegue muere justo antes de recargar PHP-FPM.
sudo chown -R "$(id -un)":www-data storage bootstrap/cache
sudo chmod -R ug+rw storage bootstrap/cache

echo "==> Recargando PHP-FPM"
# Con opcache.validate_timestamps=0, sin esto el servidor sigue
# sirviendo el codigo viejo.
sudo systemctl reload php8.3-fpm

echo
echo "Desplegado. Verifique:"
echo "  curl -I http://localhost/"
