#!/usr/bin/env bash

# Salir inmediatamente si algún comando falla
set -e

# Asegurar que el script se ejecute desde el directorio raíz del proyecto
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_ROOT"

# Colores para salida por consola
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # Sin color

echo -e "${BLUE}==============================================${NC}"
echo -e "${BLUE}  🚀 Inicializando Proyecto Inventario LIA    ${NC}"
echo -e "${BLUE}==============================================${NC}"

# 1. Instalar dependencias PHP
echo -e "\n${YELLOW}[1/6] Instalando dependencias de PHP con Composer...${NC}"
composer install

# 2. Configurar archivo de entorno (.env)
echo -e "\n${YELLOW}[2/6] Configurando archivo .env...${NC}"
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${GREEN}✓ Archivo .env creado a partir de .env.example${NC}"
else
    echo -e "${YELLOW}ℹ El archivo .env ya existe. Se mantiene la configuración actual.${NC}"
fi

# 3. Generar la clave de la aplicación
echo -e "\n${YELLOW}[3/6] Generando clave de la aplicación (APP_KEY)...${NC}"
php artisan key:generate

# 4. Crear archivo SQLite si no existe
echo -e "\n${YELLOW}[4/6] Preparando la base de datos SQLite...${NC}"
mkdir -p database
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    echo -e "${GREEN}✓ Base de datos SQLite creada en database/database.sqlite${NC}"
else
    echo -e "${YELLOW}ℹ database/database.sqlite ya existe.${NC}"
fi

# Instalar dependencias npm si están disponibles
if command -v npm &> /dev/null && [ -f package.json ]; then
    echo -e "\n${YELLOW}[Extra] Instalando dependencias frontend (npm)...${NC}"
    npm install
    if npm run | grep -q "build"; then
        echo -e "${YELLOW}[Extra] Compilando assets frontend (npm run build)...${NC}"
        npm run build
    fi
fi

# 5. Ejecutar migraciones y seeders
echo -e "\n${YELLOW}[5/6] Ejecutando migraciones y seeders...${NC}"
php artisan migrate --seed --force

# 6. Iniciar el servidor local
echo -e "\n${GREEN}==============================================${NC}"
echo -e "${GREEN}  ✓ Inicialización completada exitosamente    ${NC}"
echo -e "${GREEN}==============================================${NC}"

if [[ "$*" == *"--no-serve"* ]]; then
    echo -e "\n${BLUE}Modo --no-serve detectado. No se iniciará el servidor automáticamente.${NC}"
    echo -e "Puedes iniciarlo manualmente con: ${YELLOW}php artisan serve${NC} o ${YELLOW}composer run dev${NC}\n"
else
    echo -e "\n${YELLOW}[6/6] Iniciando el servidor de desarrollo...${NC}"
    echo -e "${BLUE}Presiona Ctrl+C para detener el servidor.${NC}\n"
    php artisan serve
fi
