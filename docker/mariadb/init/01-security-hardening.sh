#!/bin/bash
set -eo pipefail

echo "==> [MariaDB Security Init] Aplicando directivas de endurecimiento y principio de menor privilegio..."

APP_USER="${MYSQL_USER:-inventario_user}"
APP_DB="${MYSQL_DATABASE:-inventario_lia}"

mariadb -u root -p"${MYSQL_ROOT_PASSWORD}" <<EOSQL
-- 1. Revocar privilegios globales destructivos del usuario de la aplicación
REVOKE FILE, SUPER, PROCESS, SHUTDOWN ON *.* FROM '${APP_USER}'@'%';

-- 2. Conceder únicamente privilegios operacionales requeridos por Laravel sobre su base de datos
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, INDEX, REFERENCES, TRIGGER, CREATE TEMPORARY TABLES, LOCK TABLES 
ON \`${APP_DB}\`.* TO '${APP_USER}'@'%';

FLUSH PRIVILEGES;
EOSQL

echo "==> [MariaDB Security Init] Privilegios asegurados exitosamente para ${APP_USER} sobre ${APP_DB}."
