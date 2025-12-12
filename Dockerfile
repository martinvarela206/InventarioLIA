# Dockerfile para Laravel con Apache y SQLite
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libsqlite3-dev \
    zip \
    unzip \
    git \
    curl \
    gnupg \
    && docker-php-ext-install pdo_sqlite mbstring exif pcntl bcmath gd

# Instalar Node.js y NPM para compilar assets
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copia el contenido del proyecto Laravel
COPY . /var/www/html

# Instalar dependencias de PHP y Node, y compilar assets
RUN composer install --no-dev --optimize-autoloader \
    && npm install \
    && npm run build

# Cambia el DocumentRoot de Apache a /var/www/html/public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Habilita mod_rewrite para Laravel
RUN a2enmod rewrite

# Configura Apache para permitir URLs amigables
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Crear archivo SQLite si no existe y asegurar permisos
RUN mkdir -p /var/www/html/database \
    && touch /var/www/html/database/database.sqlite \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

EXPOSE 80

# Ejecuta migraciones antes de iniciar Apache
CMD php artisan migrate --force && php artisan db:seed --force && apache2-foreground

## EN server con docker hay que ejecutar:
        # docker build -t laravel-sqlite-apache .
        # docker run -p 8080:80 laravel-sqlite-apache

## En ssh del server, para ver los dockers corriendo:
        # docker ps
## Eliminar un docker:
##       docker stop laravel_app
##       docker rm laravel_app
## Tambien se puede usar rmi y el id del contenedor
## Para ver las imagenes es docker images
## Para ver el historial de comando en ssh es history

## Pasos:
## 1) Acceder por ssh al server
## 2) clonar el proyecto: `git clone http://github.com/inventario-lia-app`. Se clonara en una carpeta por ejemplo: liati@serverlia:~/inventario-lia-app$ 
## 3) En local ir por terminal a la carpeta del proyecto
## 4) Copiar el archivo .env al server usando scp: `scp .env liati@10.0.0.200:~/inventario-lia-app/.env`
## 5) Construir la imagen de Docker: `sudo docker build -t inventario-lia-app .`
## 6) Correr el contenedor: `sudo docker run -d --name inventario_app -p 8080:80 inventario-lia-app`

## Se añadio un docker-compose.yml para gestionar vite..
## Pasos actualizados:
## 1) Acceder por ssh al server
## 2) Actualizar el repositorio: `git pull origin main`
## 3) Detener el docker antiguo: 
    ##sudo docker stop laravel_app || true
    ##sudo docker rm laravel_app || true
## 4) Dar permisos para Symfony:
    ##sudo chown -R 33:33 storage bootstrap/cache database
    ##sudo chmod -R 775 storage bootstrap/cache database
## 5) Correr el nuevo docker-compose: `sudo docker compose up -d --build`