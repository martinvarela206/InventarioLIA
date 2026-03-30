# ETAPA 1: Compilación de Assets (Node)
FROM node:18-bullseye AS build-stage
WORKDIR /app
COPY . .
RUN npm install && npm run build

# ETAPA 2: Servidor de Aplicación (PHP + Apache)
FROM php:8.2-apache

# Instalamos extensiones necesarias para Laravel y SQLite
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev libsqlite3-dev \
    zip unzip git curl \
    && docker-php-ext-install pdo_sqlite mbstring exif pcntl bcmath gd

# Copiamos Composer desde la imagen oficial
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

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