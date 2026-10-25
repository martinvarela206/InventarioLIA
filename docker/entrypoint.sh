#!/bin/sh
set -e

echo "=== [LIA PROD] Iniciando contenedor de aplicación Laravel ==="

# 1. Esperar a que la base de datos MariaDB esté accesible si la conexión es mysql/mariadb
if [ "$DB_CONNECTION" = "mysql" ] || [ "$DB_CONNECTION" = "mariadb" ]; then
    echo "=== [LIA PROD] Verificando conexión a MariaDB en ${DB_HOST}:${DB_PORT:-3306}... ==="
    max_retries=30
    counter=0
    until php -r '
        $host = getenv("DB_HOST") ?: "127.0.0.1";
        $port = getenv("DB_PORT") ?: "3306";
        $database = getenv("DB_DATABASE") ?: "inventario_lia";
        $user = getenv("DB_USERNAME") ?: "root";
        $pass = getenv("DB_PASSWORD") ?: "";
        try {
            $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database}", $user, $pass, [
                PDO::ATTR_TIMEOUT => 2,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            exit(0);
        } catch (Exception $e) {
            exit(1);
        }
    ' >/dev/null 2>&1; do
        counter=$((counter + 1))
        if [ $counter -gt $max_retries ]; then
            echo "=== [LIA PROD] AVISO: No se pudo verificar MariaDB tras $max_retries intentos. Continuando arranque... ==="
            break
        fi
        echo "=== [LIA PROD] MariaDB aún no responde (intento $counter/$max_retries). Esperando 2s... ==="
        sleep 2
    done

    if [ $counter -le $max_retries ]; then
        echo "=== [LIA PROD] Conexión a MariaDB confirmada exitosamente. ==="
    fi
fi

# 2. Generar Application Key si no está configurada
if [ -z "$APP_KEY" ]; then
    echo "=== [LIA PROD] APP_KEY no detectada. Generando clave de aplicación... ==="
    php artisan key:generate --force || true
fi

# 3. Ejecutar migraciones en modo producción
echo "=== [LIA PROD] Ejecutando migraciones de base de datos... ==="
php artisan migrate --force --no-interaction || true

# 4. Sembrado de datos automático si la base de datos está vacía o si SEED_ON_INIT=true
if [ "$SEED_ON_INIT" = "true" ] || [ "$SEED_ON_INIT" = "1" ]; then
    echo "=== [LIA PROD] Forzando ejecución de seeders (SEED_ON_INIT activado)... ==="
    php artisan db:seed --force --no-interaction || true
else
    roles_count=$(php -r '
        require __DIR__ . "/vendor/autoload.php";
        $app = require_once __DIR__ . "/bootstrap/app.php";
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        try {
            echo \App\Models\Rol::count();
        } catch (\Throwable $e) {
            echo 0;
        }
    ' 2>/dev/null || echo 0)

    if [ "$roles_count" -eq "0" ]; then
        echo "=== [LIA PROD] Base de datos vacía detectada. Sembrando catálogo oficial, roles y usuarios iniciales... ==="
        php artisan db:seed --force --no-interaction || true
    fi
fi

# 5. Enlace simbólico de almacenamiento público
echo "=== [LIA PROD] Verificando symlink de storage... ==="
php artisan storage:link || true

# 6. Optimizaciones de caché en producción
if [ "$APP_ENV" = "production" ] || [ "$APP_ENV" = "prod" ]; then
    echo "=== [LIA PROD] Optimizando cachés de configuración, rutas y vistas... ==="
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# 7. Asegurar permisos finales sobre storage y cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

echo "=== [LIA PROD] Preparación completada. Arrancando servidor Apache en primer plano... ==="
exec "$@"
