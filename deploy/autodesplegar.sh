#!/usr/bin/env bash
#
# Revisa si la rama remota avanzo y, si es asi, despliega.
# Pensado para correr desde cron cada pocos minutos.
#
# En el servidor vive en /usr/local/bin/autodesplegar-centros-acopio.sh,
# FUERA del repositorio: si viviera dentro, el propio git pull chocaria
# con el archivo al versionarlo. Esta copia queda aqui para historial.
#
# Instalacion (ver deploy/LEEME.md):
#   sudo cp deploy/autodesplegar.sh /usr/local/bin/autodesplegar-centros-acopio.sh
#   sudo chmod +x /usr/local/bin/autodesplegar-centros-acopio.sh
#   crontab -e   ->   */2 * * * * /usr/local/bin/autodesplegar-centros-acopio.sh >/dev/null 2>&1

set -euo pipefail

RUTA_APP="/var/www/centros_acopio"
RAMA="master"
REGISTRO="/var/log/centros_acopio-autodeploy.log"

# Si un despliegue anterior sigue corriendo, este turno se salta.
# Sin esto, dos pushes seguidos lanzan dos composer install a la vez
# y en 1 GB de RAM eso termina en el asesino de procesos del kernel.
exec 9>/tmp/centros_acopio-autodeploy.lock
flock -n 9 || exit 0

cd "$RUTA_APP"

git fetch --quiet origin "$RAMA"

LOCAL=$(git rev-parse HEAD)
REMOTO=$(git rev-parse "origin/$RAMA")

if [[ "$LOCAL" == "$REMOTO" ]]; then
    exit 0
fi

{
    echo
    echo "===== $(date -Is) : ${LOCAL:0:7} -> ${REMOTO:0:7} ====="
    # Se invoca con bash a proposito, no como ./deploy/desplegar.sh: si el
    # commit que esta por llegar es justamente el que arregla el bit de
    # ejecucion, la version en disco todavia no lo tiene.
    if bash ./deploy/desplegar.sh "$RAMA"; then
        echo "===== $(date -Is) : desplegado ====="
    else
        echo "===== $(date -Is) : FALLO, la version anterior sigue en linea ====="
    fi
} >> "$REGISTRO" 2>&1

# El registro no debe crecer sin limite: el disco lleno tumba MySQL.
#
# El recorte pasa por /tmp y vuelve con cat, no con mv: el usuario que
# despliega es dueño del archivo de registro pero no de /var/log, asi
# que no puede crear archivos nuevos ahi.
RECORTE=/tmp/centros_acopio-autodeploy.recorte
tail -n 2000 "$REGISTRO" > "$RECORTE"
cat "$RECORTE" > "$REGISTRO"
rm -f "$RECORTE"
