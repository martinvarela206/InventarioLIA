# Guía de Despliegue en Producción — Sistema LIA

Esta rama (`produccion`) contiene la arquitectura final basada en **dos contenedores Docker** orquestados mediante Docker Compose:

1. **`app` (`laravel_prod`)**: Aplicación web Laravel con PHP 8.2.35 Parcheado (o versiones seguras >= 8.2.35 / 8.3.36 / 8.4.27 / 8.5.12 mitigando CVE-2026-109618/19), servidor web Apache, extensiones PHP de alto rendimiento (`pdo_mysql`, `mbstring`, `gd`, `zip`, `opcache`), assets compilados con Vite + Tailwind CSS (Node 20 LTS en etapa multi-stage) y script de inicialización inteligente (`entrypoint.sh`).
2. **`db` (`mariadb_prod`)**: Motor de base de datos relacional MariaDB 11.4 LTS, configurado con `utf8mb4_unicode_ci`, volumen persistente y healthcheck activo.

---

## 🏗️ Arquitectura de Contenedores

```
                     ┌──────────────────────────────────┐
                     │          Cliente / Red           │
                     └────────────────┬─────────────────┘
                                      │ Puerto 8000 (o $APP_PORT)
                                      ▼
    ┌─────────────────────────────────────────────────────────────┐
    │  Red Docker Privada (app_network / bridge)                  │
    │                                                             │
    │   ┌───────────────────────────┐                             │
    │   │  Contenedor 1: APP        │                             │
    │   │  - PHP 8.2 + Apache       │                             │
    │   │  - Vite Assets Compilados │                             │
    │   │  - Migraciones y Caché    │                             │
    │   └─────────────┬─────────────┘                             │
    │                 │ Conexión interna (db:3306)                │
    │                 ▼                                           │
    │   ┌───────────────────────────┐   Volumen Persistente       │
    │   │  Contenedor 2: MARIADB    │ ═══════════════════════     │
    │   │  - MariaDB 11.4 LTS       │     mariadb_data            │
    │   │  - Healthcheck activo     │                             │
    │   └───────────────────────────┘                             │
    │                                                             │
    │   Volumen Uploads (storage_uploads) ═══════════════════     │
    │   (Fotos de personal, espacios físicos, tickets)            │
    └─────────────────────────────────────────────────────────────┘
```

---

## 🚀 Despliegue Rápido Paso a Paso

### 1. Clonar el repositorio y situarse en la rama `produccion`
```bash
git clone <URL_REPOSITORIO> inventario-lia
cd inventario-lia
git checkout produccion
```

### 2. Configurar el archivo de entorno
Copia la plantilla de producción:
```bash
cp .env.production.example .env
```

*(Opcional)* Edita las contraseñas y parámetros en `.env`:
```env
APP_URL=http://tu-servidor.unsj.edu.ar:8000
APP_PORT=8000

DB_DATABASE=inventario_lia
DB_USERNAME=inventario_user
DB_PASSWORD=tu_password_seguro
DB_ROOT_PASSWORD=tu_root_password_seguro
```

### 3. Construir y levantar los contenedores
Ejecuta con privilegios de Docker:
```bash
docker compose up -d --build
```

---

## ⚙️ Automatizaciones del Contenedor `app` (`docker/entrypoint.sh`)

Al iniciar, el contenedor de la aplicación realiza de forma automática y desatendida:
1. **Espera de MariaDB**: Verifica periódicamente la disponibilidad del puerto y las credenciales de MariaDB antes de proceder.
2. **Generación de Clave**: Si `APP_KEY` no existe en `.env`, genera una automáticamente con `php artisan key:generate`.
3. **Migraciones**: Ejecuta `php artisan migrate --force` para sincronizar las 19 tablas y relaciones.
4. **Sembrado Inteligente**: Si la base de datos es nueva (sin roles cargados), corre `php artisan db:seed --force` con los catálogos oficiales del LIA, tipos, ubicaciones, reportes y usuario administrador.
5. **Almacenamiento**: Garantiza el symlink `public/storage` apuntando a `storage/app/public`.
6. **Optimización de Producción**: Cachea configuración, rutas y vistas (`config:cache`, `route:cache`, `view:cache`).
7. **Permisos**: Asegura propiedad de `www-data` sobre `storage` y `bootstrap/cache`.

---

## 🛠️ Comandos de Operación y Mantenimiento

### Ver el estado y healthcheck de los contenedores
```bash
docker compose ps
```

### Ver los logs en tiempo real
```bash
# Todos los contenedores
docker compose logs -f

# Solo la aplicación
docker compose logs -f app

# Solo MariaDB
docker compose logs -f db
```

### Acceder al shell del contenedor de Laravel
```bash
docker compose exec app bash
```

### Ejecutar comandos Artisan dentro del contenedor
```bash
docker compose exec app php artisan list
docker compose exec app php artisan reportes:reindex
```

### Respaldar la base de datos MariaDB (Backup SQL)
```bash
docker compose exec db mariadb-dump -u root -p$(grep DB_ROOT_PASSWORD .env | cut -d '=' -f2) inventario_lia > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Restaurar un respaldo SQL
```bash
docker compose exec -T db mariadb -u root -p$(grep DB_ROOT_PASSWORD .env | cut -d '=' -f2) inventario_lia < backup.sql
```

### Detener los contenedores preservando los datos
```bash
docker compose down
```

### Detener eliminando volúmenes (⚠️ ¡Borra datos de MariaDB y Uploads!)
```bash
docker compose down -v
```
