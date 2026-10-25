# ==============================================================================
# ETAPA 1: Compilación de Assets Frontend (Vite + Tailwind CSS con Node 20 LTS)
# ==============================================================================
FROM node:20-bookworm-slim AS build-stage

WORKDIR /app

# Aprovechar caché de Docker instalando dependencias primero
COPY package*.json ./
RUN npm ci --no-audit --prefer-offline || npm install

# Copiar el resto del código y compilar los bundles de producción
COPY . .
RUN npm run build

# ==============================================================================
# ETAPA 2: Servidor de Aplicación (PHP 8.2 Parcheado + Apache)
# ==============================================================================
FROM php:8.2-apache

# Instalación de librerías del sistema y clientes de base de datos
RUN apt-get update && apt-get upgrade -y && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libsqlite3-dev \
    default-mysql-client \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_sqlite \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer oficial v2
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Hardening y directivas de seguridad PHP / Apache
RUN echo "expose_php = Off" > /usr/local/etc/php/conf.d/security.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/security.ini \
    && echo "upload_max_filesize = 32M" >> /usr/local/etc/php/conf.d/security.ini \
    && echo "post_max_size = 32M" >> /usr/local/etc/php/conf.d/security.ini \
    && echo "max_execution_time = 60" >> /usr/local/etc/php/conf.d/security.ini \
    && echo "ServerTokens Prod" >> /etc/apache2/conf-available/security.conf \
    && echo "ServerSignature Off" >> /etc/apache2/conf-available/security.conf \
    && a2enconf security \
    && a2enmod rewrite headers

WORKDIR /var/www/html

# Copiar código fuente del proyecto
COPY . /var/www/html

# Copiar los assets compilados por Vite desde la Etapa 1
COPY --from=build-stage /app/public/build /var/www/html/public/build

# Instalación optimizada de dependencias PHP sin paquetes de desarrollo
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Configuración de Apache DocumentRoot hacia public/
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Preparación de directorios y permisos
RUN mkdir -p /var/www/html/storage/app/public \
             /var/www/html/storage/framework/cache/data \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Script de entrada para arranque automatizado (espera de MariaDB, migraciones, cachés)
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]