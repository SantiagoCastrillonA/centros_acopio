# Despliegue en Google Cloud (nivel gratuito)

Guía para dejar la aplicación corriendo en una VM `e2-micro` del nivel siempre gratuito
de Google Cloud. Ubuntu 24.04 LTS, Nginx, PHP 8.3 y MySQL 8, todo en la misma máquina.

Los planes gratuitos cambian: confirme las condiciones vigentes en la consola antes de
crear recursos, y configure una alerta de presupuesto en **Facturación → Presupuestos y alertas**.

---

## 1. Crear la máquina virtual

En la consola: **Compute Engine → Instancias de VM → Crear instancia**.

Las tres condiciones que definen si la VM entra en el nivel gratuito:

| Opción | Valor obligatorio |
|---|---|
| Región | `us-east1` (Carolina del Sur), `us-central1` (Iowa) o `us-west1` (Oregón) — **ninguna otra** |
| Tipo de máquina | `e2-micro` (serie E2, 2 vCPU compartidas, 1 GB) |
| Disco de arranque | Ubuntu 24.04 LTS, tipo **disco persistente estándar**, 30 GB |
| Firewall | Marcar *Permitir tráfico HTTP* y *Permitir tráfico HTTPS* |

De las tres regiones permitidas, `us-east1` es la de menor latencia desde Colombia.

No elija disco balanceado ni SSD: no entran en el nivel gratuito. Verifique también si su
cuenta cobra por la dirección IPv4 externa — es un cargo aparte del de la VM.

Conéctese con el botón **SSH** de la consola: abre una terminal en el navegador, sin
configurar llaves.

## 2. Subir el código a un repositorio remoto

El repositorio local todavía no tiene remoto. Desde su equipo:

```bash
git remote add origin https://github.com/USUARIO/centros_acopio.git
git push -u origin master
```

## 3. Preparar el servidor

Dentro de la VM:

```bash
sudo apt-get update && sudo apt-get install -y git
git clone https://github.com/USUARIO/centros_acopio.git /tmp/centros_acopio
sudo /tmp/centros_acopio/deploy/preparar-servidor.sh
```

El script instala Nginx, PHP 8.3, MySQL 8 y Composer, crea 2 GB de swap y ajusta PHP-FPM
para 1 GB de RAM. **La swap no es opcional**: sin ella el kernel mata a MySQL en cuanto
Composer consume memoria.

## 4. Asegurar MySQL y crear la base

```bash
sudo mysql_secure_installation
```

Luego, con una contraseña larga y distinta de la de root:

```bash
sudo mysql -e "CREATE DATABASE centros_acopio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'centros_acopio'@'localhost' IDENTIFIED BY 'PONGA_AQUI_UNA_CONTRASENA_LARGA';"
sudo mysql -e "GRANT ALL PRIVILEGES ON centros_acopio.* TO 'centros_acopio'@'localhost'; FLUSH PRIVILEGES;"
```

Aplicar la configuración para máquinas de poca memoria:

```bash
sudo cp /tmp/centros_acopio/deploy/mysql-vm-pequena.cnf /etc/mysql/mysql.conf.d/zz-vm-pequena.cnf
sudo systemctl restart mysql
```

## 5. Instalar la aplicación

```bash
sudo mv /tmp/centros_acopio /var/www/centros_acopio
sudo chown -R $USER:www-data /var/www/centros_acopio
cd /var/www/centros_acopio

cp deploy/env.produccion.ejemplo .env
nano .env          # completar DB_PASSWORD y APP_URL

composer install --no-dev --optimize-autoloader
php artisan key:generate
./deploy/desplegar.sh
```

## 6. Publicar con Nginx

```bash
sudo cp deploy/nginx-centros_acopio.conf /etc/nginx/sites-available/centros_acopio
sudo nano /etc/nginx/sites-available/centros_acopio     # reemplazar SU_DOMINIO_O_IP
sudo ln -s /etc/nginx/sites-available/centros_acopio /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

Abra `http://IP_EXTERNA/` en el navegador. Debe verse la portada con el estado vacío.

## 7. Crear el usuario del panel

```bash
cd /var/www/centros_acopio && php artisan make:filament-user
```

## 8. HTTPS

Mientras el sitio responda solo por IP no hay HTTPS posible: los certificados se emiten
sobre un dominio. Con un dominio apuntando a la IP externa:

```bash
sudo apt-get install -y certbot python3-certbot-nginx
sudo certbot --nginx -d su-dominio.org
```

Después, en `.env`, cambie `APP_URL` a `https://` y ponga `SESSION_SECURE_COOKIE=true`,
y vuelva a correr `./deploy/desplegar.sh`.

**Hasta que exista HTTPS, no publique la dirección del panel.** El formulario de
`/admin/login` viaja en texto plano: cualquiera en la misma red puede leer la contraseña
del coordinador. La vista pública sí puede usarse por HTTP sin problema, porque no pide
credenciales ni recibe datos.

---

## Despliegues siguientes

```bash
cd /var/www/centros_acopio && ./deploy/desplegar.sh
```

## Respaldos

En una herramienta de emergencia, perder el inventario es perder la operación. Un
respaldo diario, fuera de la máquina:

```bash
mysqldump -u centros_acopio -p centros_acopio | gzip > ~/centros_acopio-$(date +%F).sql.gz
```

Automatícelo con `cron` y copie el archivo a otro lugar. Un respaldo que vive en el mismo
disco que la base no es un respaldo.

## Si algo falla

| Síntoma | Dónde mirar |
|---|---|
| Error 502 | `sudo tail -50 /var/log/nginx/centros_acopio-error.log` y `systemctl status php8.3-fpm` |
| Error 500 | `tail -50 storage/logs/laravel.log` |
| La página no cambia tras desplegar | Falta `sudo systemctl reload php8.3-fpm` (opcache) |
| MySQL se cae solo | Falta la swap, o no se aplicó `zz-vm-pequena.cnf` |
