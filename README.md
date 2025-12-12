# Inventario LIA

Sistema de Gestión de Inventario para el Laboratorio de Informática Aplicada (LIA).

Este proyecto es una aplicación web desarrollada en **Laravel** para administrar el inventario de equipos, componentes y periféricos del laboratorio. Permite realizar un seguimiento de la ubicación, estado, préstamos y obsolescencia de los activos.

## 🚀 Características Principales

*   **Dashboard Interactivo**: Vista principal con estadísticas clave, gráficos de distribución y alertas de obsolescencia.
*   **Gestión de Elementos**: CRUD completo de elementos (CPUs, Monitores, Teclados, etc.) con detalles técnicos almacenados en JSON.
*   **Control de Movimientos**: Registro histórico de cambios de ubicación y estado (Ingresado, Prestado, Dado de baja, etc.).
*   **Revisión de Inventario**: Herramienta para auditar la ubicación actual de los equipos con filtros avanzados.
*   **Roles y Permisos**: Sistema de acceso basado en roles (Admin, Coordinador, Técnico, Revisor).
*   **Reportes**: Visualización de equipos prestados, obsoletos y actividad de usuarios.

## 🛠️ Stack Tecnológico

*   **Backend**: Laravel 12 (PHP 8.2+)
*   **Frontend**: Blade Templates, Tailwind CSS
*   **Base de Datos**: SQLite (Desarrollo) / MySQL (Producción)
*   **Gráficos**: Chart.js
*   **Autenticación**: Laravel Sanctum / Session based

## ⚙️ Instalación y Configuración

Sigue estos pasos para levantar el proyecto en tu entorno local:

1.  **Clonar el repositorio**:
    ```bash
    git clone <url-del-repo>
    cd InventarioLIALaravel
    ```

2.  **Instalar dependencias de PHP**:
    ```bash
    composer install
    ```

3.  **Instalar dependencias de Node.js**:
    ```bash
    npm install
    ```

4.  **Configurar entorno**:
    *   Copia el archivo de ejemplo: `cp .env.example .env`
    *   Genera la key de la aplicación: `php artisan key:generate`
    *   Configura tu base de datos en `.env` (por defecto usa SQLite).

5.  **Base de Datos**:
    *   Crea el archivo SQLite (si usas SQLite): `touch database/database.sqlite`
    *   Ejecuta las migraciones y seeders:
        ```bash
        php artisan migrate --seed
        ```
    *   *Nota*: El seeder crea usuarios de prueba y datos iniciales para el dashboard.

6.  **Ejecutar**:
    *   Inicia el servidor de desarrollo (backend + frontend):
        ```bash
        composer run dev
        ```
    *   Accede a `http://localhost:8000`

## 📂 Estructura del Proyecto

*   **`app/Models`**: Modelos Eloquent (`Elemento`, `Movimiento`, `Usuario`, `Ubicacion`, `Tipo`, `Estado`).
    *   *Nota*: `Elemento` usa un accessor `descripcion_texto` para formatear el campo JSON `descripcion`.
*   **`app/Http/Controllers`**:
    *   `DashboardController`: Lógica de la vista principal y estadísticas.
    *   `ElementoController`, `MovimientoController`: Gestión de recursos.
*   **`resources/views`**:
    *   `dashboard/`: Vista principal y parciales (tablas AJAX).
    *   `elementos/`, `movimientos/`, `revision/`: Vistas de gestión.
*   **`database/migrations`**: Definición del esquema de la base de datos.

## 💡 Conceptos Clave

### Estados del Inventario
Los elementos pasan por diferentes estados registrados en la tabla `movimientos`:
*   **Ingresado**: Alta inicial.
*   **Funcionando**: En uso normal.
*   **Prestado**: Asignado temporalmente a un usuario/ubicación.
*   **En reparación**: Fuera de servicio temporal.
*   **Dado de baja**: Retirado del inventario.

### Dashboard Reactivo
El dashboard incluye filtros AJAX (ej: año de obsolescencia) que actualizan tablas parciales sin recargar la página. La lógica se encuentra en `DashboardController` y `resources/views/dashboard/partials`.

## 🏗️ Arquitectura y Lógica de Negocio

### Modelo de Datos
El sistema se basa en tres pilares principales:
1.  **Elemento (`Elemento`)**: Representa un activo físico.
    *   **Atributos clave**: `nro_lia` (PK), `tipo_id`, `fecha_adquisicion`.
    *   **Descripción JSON**: El campo `descripcion` almacena especificaciones técnicas variables (RAM, Disco, Modelo) en formato JSON. Se accede a una versión legible mediante el accessor `$elemento->descripcion_texto`.
2.  **Movimiento (`Movimiento`)**: Historial de cambios.
    *   Registra cada cambio de ubicación, estado o usuario responsable.
    *   Permite reconstruir la trazabilidad completa de un activo.
3.  **Usuario (`Usuario`)**: Personal del laboratorio.
    *   Roles definidos para controlar el acceso a diferentes módulos (Dashboard, ABM, Reportes).

### Lógica de Vistas y Filtrado
Las vistas de listado (`revision`, `elementos`) implementan un sistema de filtrado persistente:
*   **Búsqueda**: Se realiza mediante parámetros GET (`?search=...`) que filtran por Nro LIA, descripción o ubicación.
*   **Filtros Combinables**: Los filtros de Tipo, Estado y Capacidad (GB) se pueden combinar.
    *   *Implementación*: Los controladores mantienen los parámetros de la URL al paginar o aplicar nuevos filtros (`request()->except(...)`).
*   **Componentes UI**:
    *   **Badges Interactivos**: En la vista de revisión, los filtros activos se muestran como badges que permiten su eliminación rápida.
    *   **Dropdowns/Inputs**: Integrados en la misma barra de herramientas para una experiencia de usuario fluida.

---
Desarrollado para el **Laboratorio de Informática Aplicada (LIA)**.
