#!/usr/bin/env bash

# Detener la ejecución si ocurre cualquier error
set -Eeuo pipefail

echo "🚀 [Nexo LIA Docker Init] Iniciando configuración y reseteo de entorno Docker (Laravel + MariaDB)..."

# 1. Configuración de variables de entorno (.env)
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        echo "📄 Creando archivo .env desde .env.example..."
        cp .env.example .env
    else
        echo "⚠️  No se encontró .env ni .env.example. Creando .env base para Docker..."
        touch .env
    fi
fi

# Asegurar configuración para el stack de dos contenedores Docker (app + db)
echo "⚙️  Configurando variables de conexión a MariaDB (host 'db')..."
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env || echo "DB_CONNECTION=mysql" >> .env
sed -i 's/^DB_HOST=.*/DB_HOST=db/' .env || echo "DB_HOST=db" >> .env
sed -i 's/^DB_PORT=.*/DB_PORT=3306/' .env || echo "DB_PORT=3306" >> .env
sed -i 's/^DB_DATABASE=.*/DB_DATABASE=inventario_lia/' .env || echo "DB_DATABASE=inventario_lia" >> .env
sed -i 's/^DB_USERNAME=.*/DB_USERNAME=inventario_user/' .env || echo "DB_USERNAME=inventario_user" >> .env
sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=secret_password/' .env || echo "DB_PASSWORD=secret_password" >> .env

# 2. Esperar conectividad con el contenedor de base de datos (mariadb_prod en db:3306)
echo "⏳ Verificando conectividad con el servidor MariaDB (${DB_HOST:-db}:${DB_PORT:-3306})..."
MAX_RETRIES=30
COUNT=0
DB_USER="${DB_USERNAME:-inventario_user}"
DB_PASS="${DB_PASSWORD:-secret_password}"
DB_TARGET="${DB_HOST:-db}"
DB_P="${DB_PORT:-3306}"

until mariadb-admin ping -h"${DB_TARGET}" -P"${DB_P}" -u"${DB_USER}" -p"${DB_PASS}" --silent 2>/dev/null || php -r "try { new PDO('mysql:host=${DB_TARGET};port=${DB_P}', '${DB_USER}', '${DB_PASS}'); exit(0); } catch (Exception \$e) { exit(1); }"; do
    COUNT=$((COUNT + 1))
    if [ "$COUNT" -ge "$MAX_RETRIES" ]; then
        echo "❌ ERROR: MariaDB en '${DB_TARGET}:${DB_P}' no respondió tras ${MAX_RETRIES} intentos. Abortando." >&2
        exit 1
    fi
    echo "--> Esperando a MariaDB (${COUNT}/${MAX_RETRIES})..."
    sleep 2
done
echo "✅ Conexión con MariaDB establecida."

# 3. Instalación de dependencias Composer (si aplica)
if [ -f composer.json ] && [ ! -d vendor ]; then
    echo "📦 Instalando dependencias de Composer..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# 4. Generación de APP_KEY si no existe
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null || [ -z "${APP_KEY:-}" ]; then
    echo "🔑 Generando APP_KEY..."
    php artisan key:generate --force
fi

# 5. Enlace simbólico de almacenamiento
echo "🔗 Creando storage symlink..."
php artisan storage:link --force || true

# 6. Purga total y reconstrucción limpia de la base de datos con migraciones y seeders oficiales
echo "⚡ Purgando base de datos previa y ejecutando migrate:fresh con seeders oficiales (ODS incluido)..."
php artisan migrate:fresh --seed --force

# 7. Compilación de assets si Node/NPM está disponible
if command -v npm &> /dev/null && [ -f package.json ] && [ ! -d public/build ]; then
    echo "🎨 Compilando assets de frontend..."
    npm install
    npm run build
fi

# 8. Limpieza y preparación de cachés de Laravel
echo "🧹 Limpiando y optimizando cachés..."
php artisan optimize:clear

echo ""
echo "✅ [Nexo LIA Docker Init] Entorno Docker completamente inicializado y base de datos relacional lista."
echo "👉 La aplicación está operativa en el contenedor."
