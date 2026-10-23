# ETAPA 1: Compilación de Assets (Node 20 LTS)
FROM node:20-bookworm-slim AS build-stage
WORKDIR /app
COPY . .
RUN npm install && npm run build

# ETAPA 2: Servidor de Aplicación (PHP 8.2 Parcheado >= 8.2.35 + Apache)
FROM php:8.2-apache

# Instalamos extensiones necesarias para Laravel, MariaDB/MySQL y SQLite, y aplicamos parches del SO
RUN apt-get update && apt-get upgrade -y && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev libsqlite3-dev \
    zip unzip git curl \
    && docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copiamos Composer desde la versión más reciente (v2)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Hardening y directivas de seguridad PHP (mitigación OOM CVE-2026-109618 y ocultamiento de versiones)
RUN echo "expose_php = Off" > /usr/local/etc/php/conf.d/security.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/security.ini \
    && echo "upload_max_filesize = 32M" >> /usr/local/etc/php/conf.d/security.ini \
    && echo "post_max_size = 32M" >> /usr/local/etc/php/conf.d/security.ini \
    && echo "max_execution_time = 60" >> /usr/local/etc/php/conf.d/security.ini \
    && echo "ServerTokens Prod" >> /etc/apache2/conf-available/security.conf \
    && echo "ServerSignature Off" >> /etc/apache2/conf-available/security.conf \
    && a2enconf security

WORKDIR /var/www/html
# Copiamos todo el código fuente
COPY . /var/www/html

# TRAEMOS LOS ARCHIVOS COMPILADOS (JS/CSS de Tailwind) desde la Etapa 1
COPY --from=build-stage /app/public/build /var/www/html/public/build

# Instalamos dependencias de PHP para producción
RUN composer install --no-dev --optimize-autoloader

# Configuración de Apache
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && a2enmod rewrite \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Base de datos SQLite y Permisos
RUN mkdir -p /var/www/html/database \
    && touch /var/www/html/database/database.sqlite \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

EXPOSE 80
CMD php artisan migrate --force && apache2-foreground