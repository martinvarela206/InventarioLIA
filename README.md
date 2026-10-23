# Inventario LIA (LIA Kairos)

Sistema de Gestión de Inventario para el **Laboratorio de Informática Aplicada (LIA)** — Facultad de Ciencias Exactas, Físicas y Naturales (UNSJ).

Este proyecto es una aplicación web desarrollada en **Laravel 12** para administrar el ciclo de vida completo de equipos, componentes, periféricos y herramientas del laboratorio. Permite realizar un seguimiento preciso de su ubicación física, estado operativo, historial de intervenciones técnicas, préstamos y obsolescencia.

---

## 🚀 Características Principales

* **Dashboard Interactivo:** Vista principal con métricas clave, distribución de hardware por laboratorio y alertas de obsolescencia.
* **Gestión Integral de Activos:** CRUD completo de elementos (CPUs, Monitores, Switches, Periféricos) con especificaciones técnicas y doble identificación patrimonial (`nro_lia` y `nro_unsj`).
* **Historial Inmutable de Movimientos:** Trazabilidad completa de traslados, mantenimientos, préstamos y bajas vinculado a cada usuario responsable.
* **Filtros Dinámicos de Hardware:** Búsqueda instantánea y filtrado persistente por tipo, estado, laboratorio y capacidades de hardware (RAM en GB).
* **Gestión de Préstamos y Bajas:** Control de préstamos externos a cátedras o docentes con registro de motivo, fecha y devolución.
* **Reportes y Exportación CSV:** Generación de informes tabulares y exportación de inventarios en formato digital y CSV con un solo clic.
* **Roles y Permisos:** Niveles de acceso diferenciados (Administrador, Técnico, Revisor, Registrado).

---

## 🛠️ Stack Tecnológico

* **Backend Framework:** Laravel 12 (PHP 8.2.35+)
* **Frontend:** Blade Templates, Tailwind CSS, Vite
* **Base de Datos:**
  * **Desarrollo:** SQLite (`database/database.sqlite`) para máxima velocidad y cero configuración.
  * **Producción:** MariaDB 11.4 LTS oficial bajo contenedor Docker.
* **Orquestación en Producción:** Docker Compose (Arquitectura contenida de 2 servicios: `app` + `db`).
* **Testing:** PHPUnit 11

---

## ⚙️ Instalación y Configuración para Desarrollo Local

Para ejecutar el proyecto de forma nativa en tu máquina local sin Docker ni servicios externos de base de datos:

### 1. Clonar el repositorio
```bash
git clone <url-del-repo>
cd InventarioLIA
```

### 2. Instalar dependencias
```bash
# Dependencias PHP
composer install

# Dependencias Frontend (Tailwind / Vite)
npm install
```

### 3. Configurar entorno (`.env`)
```bash
cp .env.example .env
php artisan key:generate
```
Verifica que en `.env` la base de datos esté configurada en SQLite:
```dotenv
DB_CONNECTION=sqlite
```

### 4. Base de datos y datos de prueba
```bash
touch database/database.sqlite
php artisan migrate --seed
```

### 5. Iniciar entorno de desarrollo
El proyecto incluye un comando concurrente que levanta el servidor Laravel, los workers y Vite:
```bash
composer run dev
```
Accede en tu navegador a: `http://localhost:8000`

---

## 🐳 Despliegue en Producción (Docker)

El proyecto cuenta con un despliegue estricto de **2 contenedores** orquestados mediante Docker Compose:

1. Configurar variables de producción en `.env`:
   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=http://tu-servidor:8080
   DB_CONNECTION=mariadb
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=inventario_lia
   DB_USERNAME=inventario_user
   DB_PASSWORD=tu_password_segura
   ```
2. Construir e iniciar los contenedores:
   ```bash
   docker compose up -d --build
   ```
3. Ejecutar migraciones en el contenedor:
   ```bash
   docker compose exec app php artisan migrate --force
   docker compose exec app php artisan storage:link
   docker compose exec app php artisan optimize
   ```

---

## 📖 Manual de Uso del Sistema

### 1. Acceso y Autenticación
1. Ingresa a la URL del sistema (`http://localhost:8000` en local o puerto `8080` en servidor).
2. Introduce tu **CUIL** y **Contraseña**.
3. Al autenticarte accederás al panel de control principal.

### 2. Consulta y Búsqueda de Inventario
* **Búsqueda por texto:** En la barra superior, escribe el número LIA (ej. `PC-05`), código patrimonial UNSJ, marca o descripción.
* **Filtros por Capacidad:** Haz clic sobre los botones de pastilla (`4GB`, `8GB`, `16GB`) para filtrar equipos por memoria RAM.
* **Filtros por Ubicación:** Filtra por laboratorio (`Lab A`, `Lab B`, `Depósito`, etc.).

### 3. Carga de un Nuevo Elemento
1. Presiona el botón **"+ Nuevo Elemento"**.
2. Selecciona la categoría en el menú desplegable (CPU, Monitor, Teclado, Switch, etc.).
3. Ingresa los identificadores: `Nº LIA` y/o `Nº UNSJ`.
4. Completa la descripción técnica (marca, modelo, capacidad, procesador y año de adquisición).
5. Selecciona la ubicación inicial (por defecto `LIA - Depósito`).
6. Presiona **"Guardar Elemento"**. El sistema confirmará el alta y generará de forma automática el primer movimiento con estado `"nuevo"` asociado a tu usuario.

### 4. Registro de Movimientos, Préstamos y Reparaciones
1. Localiza el activo en el listado y haz clic sobre él para abrir su ficha técnica.
2. Presiona el botón **"Registrar Movimiento"**.
3. Selecciona el nuevo estado y destino:
   * **Traslado interno:** Selecciona el nuevo laboratorio o puesto de destino.
   * **Préstamo externo:** Selecciona estado `"prestado"` e indica en las observaciones el solicitante (docente/cátedra) y la fecha acordada de devolución.
   * **Avería / Taller:** Selecciona `"en mantenimiento"` detallando la falla detectada.
4. Presiona **"Guardar Movimiento"**. La acción quedará registrada permanentemente en la línea de tiempo del historial.

### 5. Exportación de Reportes a CSV
1. Ingresa a la sección **Reportes**.
2. Aplica los filtros deseados (rango de fechas, ubicación o estado).
3. Presiona el botón **"Exportar CSV"** para descargar el archivo estructurado listo para Excel o LibreOffice.

---

## 🏗️ Arquitectura y Modelo de Negocio

El sistema se estructura en torno a los siguientes pilares:

```
    ┌──────────────┐         1:N          ┌────────────────┐
    │   Elemento   │─────────────────────>│   Movimiento   │
    │  (nro_lia PK)│                      │  (Historial)   │
    └──────┬───────┘                      └───────┬────────┘
           │                                      │
       pertenece                              asociado a
           │                                      │
           ▼                                      ▼
    ┌──────────────┐                      ┌────────────────┐
    │  Categoria   │                      │  Ubicación /   │
    │ (is_container│                      │    Usuario     │
    └──────────────┘                      └────────────────┘
```

* **`Elemento` (`app/Models/Elemento.php`):** Representa el activo físico. Utiliza `nro_lia` como clave primaria y admite identificación complementaria con `nro_unsj`. El atributo `descripcion` almacena metadatos técnicos en JSON, accesibles mediante el accesor `$elemento->descripcion_texto`.
* **`Movimiento` (`app/Models/Movimiento.php`):** Registro inmutable de cada cambio de estado, traslado o préstamo. La última ubicación del elemento se resuelve dinámicamente mediante `latestOfMany()`.
* **Estados del Ciclo de Vida:**
  * `nuevo`: Alta inicial registrada.
  * `en uso`: Asignado operativamente a un puesto o laboratorio.
  * `prestado`: Cedido temporalmente a una cátedra o docente.
  * `en mantenimiento`: Fuera de servicio temporal por reparación.
  * `guardado`: Disponible en depósito para asignación.
  * `dado de baja` / `donado`: Retirado definitivamente del inventario patrimonial.

---

## 🧪 Protocolo de QA y Pruebas

### Pruebas Automatizadas
Ejecuta la suite de pruebas unitarias y funcionales con:
```bash
php artisan test
```

### Batería de Pruebas Manuales de Aceptación (QA)

| ID | Escenario de Prueba | Pasos de Verificación | Resultado Esperado |
| :-: | :--- | :--- | :--- |
| **QA-01** | Alta exitosa con identificador | Cargar equipo con `LIA-PC-99` y `UNSJ-45001`. Guardar. | Elemento creado correctamente con movimiento inicial `"nuevo"`. |
| **QA-02** | Prevención de duplicación | Intentar registrar otro equipo con el mismo `LIA-PC-99`. | El sistema rechaza la operación con validación de clave única. |
| **QA-03** | Trazabilidad de traslados | Mover un equipo de `Lab A` a `Lab B`, y luego a `Depósito`. | La ficha refleja `Depósito` como actual y lista los 3 eventos cronológicos. |
| **QA-04** | Registro de préstamo | Asignar estado `"prestado"` con comentario `"Cátedra Algoritmos"`. | Badge de estado en ámbar y observación visible en el historial. |
| **QA-05** | Integridad de Exportación CSV | Generar reporte con filtros y presionar "Exportar CSV". | Descarga de archivo `.csv` íntegro con cabeceras y codificación UTF-8. |

---

## 🛠️ Diagnóstico y Solución de Problemas (Troubleshooting)

* **Error: "Cuenta pendiente de aprobación":**
  * *Causa:* Usuario registrado pero no activado por un Administrador.
  * *Solución:* Solicitar al Administrador del LIA la activación y asignación de rol desde el panel `/usuarios`.
* **No se reflejan cambios de estilos en frontend:**
  * *Solución:* Limpiar cachés del navegador con `Ctrl + F5` o ejecutar `php artisan optimize:clear`.
* **Problema de permisos en almacenamiento Docker:**
  * *Solución:* Asegurar la propiedad de carpetas para el usuario de Apache:
    ```bash
    docker compose exec app chown -R www-data:www-data storage bootstrap/cache
    ```

---

Desarrollado para el **Laboratorio de Informática Aplicada (LIA)** — FCEFN — UNSJ.
