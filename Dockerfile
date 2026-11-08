# DEFINICIÓN GLOBAL DE ARGUMENTOS DE COMPILACIÓN
ARG PHP_VERSION=8.2.35

# ETAPA 1: Compilación de Assets (Node 20 LTS)
FROM node:20-bookworm-slim AS build-stage
WORKDIR /app
COPY . .
RUN npm install && npm run build

# ETAPA 2: Servidor de Aplicación (PHP 8.2.35 Parcheado >= 8.2.35 / 8.3.36 / 8.4.27 / 8.5.12 + Apache)
FROM php:${PHP_VERSION}-apache
ARG PHP_VERSION

# Instalamos extensiones necesarias para Laravel, MariaDB/MySQL, SQLite, GD completo y ZipArchive
RUN apt-get update && apt-get upgrade -y && apt-get install -y \
    libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev libzip-dev \
    libonig-dev libxml2-dev libsqlite3-dev \
    zip unzip git curl mariadb-client \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip \
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

# Entrypoint y Permisos de ejecución
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
