# Regla de Ramas y Sincronización Git

## 1. Estructura de Ramas
- **`main`**: Rama principal y activa de desarrollo continuo, nuevas funcionalidades (features) y correcciones (fixes). Reemplaza definitivamente a la antigua rama `develop`.
- **`produccion`**: Rama reservada exclusivamente para el despliegue del sistema en el servidor productivo. Contiene la configuración específica de arquitectura con dos contenedores Docker (app y MariaDB).

## 2. Protocolo de Sincronización (`main` -> `produccion`)
Cuando el usuario solicite sincronizar `main` hacia `produccion`:
1. **Paso de Cambios**: Todos los cambios de código de aplicación, vistas Blade, controladores, modelos, migraciones de base de datos, assets y documentación deben propagarse a `produccion`.
2. **Archivos Protegidos de Producción**: Queda estrictamente prohibido sobrescribir o desmantelar las configuraciones exclusivas de producción. Siempre deben mantenerse intactos en `produccion`:
   - `Dockerfile`
   - `docker-compose.yml`
   - `docker/` (ej. `docker/entrypoint.sh`)
   - `.dockerignore`
   - `.env.production.example`
   - `.env.example` (versión de producción)
   - `DEPLOY.md`
   - Cualquier archivo o script de orquestación de Docker / MariaDB de producción.
   - Configuraciones o validaciones de seguridad exclusivas de entorno productivo (ej. política estricta de contraseñas robustas, mientras que `main` conserva validaciones simples `min:4` para facilitar el testing ágil de usuarios).
3. **Resolución de Conflictos**: Ante cualquier divergencia durante el merge o rebase en los archivos protegidos de infraestructura o configuraciones exclusivas de producción, prevalece siempre la versión existente en `produccion`.

