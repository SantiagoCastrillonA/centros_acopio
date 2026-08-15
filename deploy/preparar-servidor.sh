#!/usr/bin/env bash
#
# Prepara una VM e2-micro de Google Cloud con Ubuntu 24.04 LTS para
# correr esta aplicacion. Se ejecuta UNA sola vez, recien creada la VM.
#
#   chmod +x preparar-servidor.sh
#   sudo ./preparar-servidor.sh
#
# No instala Docker, ni Redis, ni colas: la Entrega 1 no los necesita.

set -euo pipefail

RUTA_APP="/var/www/terremoto"

if [[ $EUID -ne 0 ]]; then
    echo "Ejecutelo con sudo." >&2
    exit 1
fi

echo "==> Actualizando paquetes"
apt-get update -qq
apt-get upgrade -y -qq

echo "==> Instalando Nginx, PHP 8.3, MySQL 8 y utilidades"
# Ubuntu 24.04 ya trae PHP 8.3 y MySQL 8 en sus repositorios oficiales:
# no hace falta agregar repositorios de terceros.
apt-get install -y -qq \
    nginx \
    mysql-server \
    php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml \
    php8.3-curl php8.3-zip php8.3-intl php8.3-bcmath php8.3-gd \
    git unzip curl

echo "==> Creando swap de 2 GB"
# Con 1 GB de RAM, sin swap el kernel mata a MySQL en cuanto Composer
# o Filament consumen memoria.
if [[ ! -f /swapfile ]]; then
    fallocate -l 2G /swapfile
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    echo '/swapfile none swap sw 0 0' >> /etc/fstab
    sysctl -w vm.swappiness=10
    echo 'vm.swappiness=10' > /etc/sysctl.d/99-swappiness.conf
else
    echo "    ya existe /swapfile, se deja como esta"
fi

echo "==> Instalando Composer"
if ! command -v composer >/dev/null 2>&1; then
    curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
    php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm -f /tmp/composer-setup.php
fi

echo "==> Ajustando PHP-FPM para 1 GB de RAM"
POOL="/etc/php/8.3/fpm/pool.d/www.conf"
sed -i 's/^pm = .*/pm = ondemand/' "$POOL"
sed -i 's/^pm.max_children = .*/pm.max_children = 8/' "$POOL"
sed -i 's/^;\?pm.process_idle_timeout = .*/pm.process_idle_timeout = 10s/' "$POOL"
sed -i 's/^;\?pm.max_requests = .*/pm.max_requests = 500/' "$POOL"

INI="/etc/php/8.3/fpm/php.ini"
sed -i 's/^;\?opcache.enable=.*/opcache.enable=1/' "$INI"
sed -i 's/^;\?opcache.memory_consumption=.*/opcache.memory_consumption=96/' "$INI"
sed -i 's/^;\?opcache.validate_timestamps=.*/opcache.validate_timestamps=0/' "$INI"

echo "==> Preparando el directorio de la aplicacion"
mkdir -p "$RUTA_APP"
chown -R "$SUDO_USER":www-data "$RUTA_APP"

systemctl enable --now nginx php8.3-fpm mysql
systemctl restart php8.3-fpm

echo
echo "Listo. Siguientes pasos, en este orden:"
echo "  1. sudo mysql_secure_installation"
echo "  2. Crear la base y el usuario (ver deploy/LEEME.md)"
echo "  3. Copiar deploy/mysql-vm-pequena.cnf a /etc/mysql/mysql.conf.d/"
echo "  4. Clonar el repositorio en $RUTA_APP"
echo "  5. Copiar deploy/nginx-terremoto.conf a /etc/nginx/sites-available/"
