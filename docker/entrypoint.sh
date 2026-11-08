#!/usr/bin/env bash
set -Eeuo pipefail

echo "==> [Nexo LIA Entrypoint] Inicializando contenedor de aplicación..."

# Esperar disponibilidad de base de datos si DB_CONNECTION es mysql/mariadb
if [ "${DB_CONNECTION:-}" = "mysql" ] || [ "${DB_CONNECTION:-}" = "mariadb" ]; then
    echo "==> [Nexo LIA Entrypoint] Verificando conectividad con servidor de base de datos (${DB_HOST:-db}:${DB_PORT:-3306})..."
    MAX_RETRIES=30
    COUNT=0
    until mariadb-admin ping -h"${DB_HOST:-db}" -P"${DB_PORT:-3306}" -u"${DB_USERNAME:-inventario_user}" -p"${DB_PASSWORD:-secret_password}" --silent; do
        COUNT=$((COUNT + 1))
        if [ "$COUNT" -ge "$MAX_RETRIES" ]; then
            echo "ERROR CRÍTICO: [Nexo LIA Entrypoint] MariaDB no respondió tras ${MAX_RETRIES} intentos. Abortando inicio con Fail-Fast." >&2
            exit 1
        fi
        echo "--> Esperando a MariaDB (${COUNT}/${MAX_RETRIES})..."
        sleep 2
    done
    echo "==> [Nexo LIA Entrypoint] Conexión con base de datos establecida y autenticada."
fi

# Generar APP_KEY si no está definida en el entorno
if [ -z "${APP_KEY:-}" ]; then
    echo "==> [Nexo LIA Entrypoint] Generando APP_KEY para producción..."
    php artisan key:generate --force
fi

# Ejecutar migraciones obligatorias sin omitir fallos (Fail-Fast estricto: prohibido ignorar errores)
echo "==> [Nexo LIA Entrypoint] Ejecutando migraciones de base de datos..."
php artisan migrate --force

# Optimizar cachés de producción
echo "==> [Nexo LIA Entrypoint] Optimizando cachés de Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> [Nexo LIA Entrypoint] Inicialización completada exitosamente. Ejecutando proceso principal: $@"
exec "$@"
