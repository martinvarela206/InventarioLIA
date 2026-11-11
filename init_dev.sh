#!/usr/bin/env bash

# Detener la ejecución si ocurre cualquier error
set -e

echo "🚀 Iniciando configuración del entorno de desarrollo..."

# 1. Configuración de variables de entorno (.env)
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        echo "📄 Creando archivo .env desde .env.example..."
        cp .env.example .env
    else
        echo "⚠️  No se encontró .env ni .env.example. Abortando."
        exit 1
    fi
else
    echo "ℹ️  El archivo .env ya existe. Omitiendo copia."
fi

# 2. Base de datos SQLite (si el .env lo requiere y no existe)
if grep -q "^DB_CONNECTION=sqlite" .env && [ ! -f database/database.sqlite ]; then
    echo "🗄️  Creando archivo database/database.sqlite..."
    mkdir -p database
    touch database/database.sqlite
fi

# 3. Instalación de dependencias de PHP
if [ -f composer.json ]; then
    echo "📦 Instalando dependencias de Composer..."
    composer install --no-interaction --prefer-dist
fi

# 4. Generación de APP_KEY
echo "🔑 Generando APP_KEY..."
php artisan key:generate --force

# 5. Enlace simbólico de almacenamiento
echo "🔗 Creando storage symlink..."
php artisan storage:link || true

# 6. Migraciones y Seeds
echo "⚡ Ejecutando migraciones y seeders..."
php artisan migrate:fresh --seed --force

# 7. Dependencias y compilación de assets de frontend
if [ -f package.json ]; then
    echo "🎨 Instalando dependencias de Node..."
    npm install
    
    echo "🛠️  Compilando assets de frontend..."
    npm run build
fi

# 8. Limpieza y preparación de cachés locales
echo "🧹 Limpiando configuraciones cacheadas..."
php artisan optimize:clear

echo ""
echo "✅ Entorno listo para trabajar."
echo "👉 Puedes iniciar el servidor con: php artisan serve"