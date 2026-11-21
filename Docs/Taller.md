# 1. Banco de Preguntas Evaluativas (Revisado y Calibrado)

---

## En el sistema de inventario, cuando un equipo cambia de laboratorio o pasa a mantenimiento, ¿por qué la arquitectura registra una nueva fila en `Movimientos` en lugar de simplemente actualizar los campos de ubicación y estado en la tabla `elementos`?

- **A)** Para acelerar las consultas de lectura en el servidor y evitar bloqueos de concurrencia en la base de datos.
- **B) (Correcta)** Para mantener un registro histórico inalterable de auditoría que permita saber quién, cuándo y por qué trasladó el equipo.
- **C)** Porque el motor SQL relacional impide modificar claves foráneas una vez que el registro fue insertado en la tabla.
- **D)** Para permitir que dos usuarios administradores modifiquen la ficha técnica del equipo en paralelo sin generar colisiones.

---

## El laboratorio administra activos muy diversos (servidores, monitores, switches, memorias RAM). Para almacenar sus características técnicas particulares sin crear decenas de columnas vacías en la tabla principal, el sistema implementó un catálogo maestro y tablas intermedias (`atributos`, `tipo_atributo`, `elemento_atributo_valor`). ¿Cómo se denomina y justifica este patrón relacional?

- **A)** Es una desnormalización controlada para optimizar operaciones de la base de datos.
- **B)** Es una partición horizontal de base de datos diseñada para balancear el tráfico entre peticiones.
- **C)** Es una arquitectura de réplicas que se implemento para garantizar alta disponibilidad.
- **D) (Correcta)** Es el patrón Entidad-Atributo-Valor (EAV), que permite definir esquemas técnicos flexibles mediante tablas pivot en SQL puro.

---

## Cual es la justificación para haber utilizado un contenedor docker para Laravel y otro para MariaDB?

- **A) (Correcta)** Se buscaba simplificar la infraestructura, dado que es una aplicación de uso interno.
- **B)** No hacia falta ni responsividad ni reactividad en el Frontend.
- **C)** Es una infraestructura básica para aplicaciones web, que ya integra todo lo necesario.
- **D)** Inició como un proyecto de cursado para la materia Backend 1, donde se aprende a trabajar con Laravel.

---

## Los activos del LIA estan identificados por medio de un Nro LIA y un Nro UNSJ, se utilizan como identificadores en la base de datos? Por qué?

- **A)** Si, se utilizan como identificadores independientes, porque es un requisito interno del LIA.
- **B)** Si, forman una clave compuesta obligatoria para todos los activos, por decisión de diseño.
- **C) (Correcta)** No, porque el registro de los mismos tiene inconsistencias graves para la base de datos, como por ejemplo, ser nulos en algunos casos e incluso se repiten en otros.
- **D)** No, porque solo es necesario el Nro LIA, que es el identificador único sobre el cual se registran los activos.

---

## ¿Cuál es la razón principal por la que la base de datos y la aplicación se despliegan mediante contenedores Docker en lugar de instalarse directamente sobre el sistema operativo del servidor físico?

- **A)** Automatizar la copia de seguridad de la base de datos.
- **B)** Garantizando la reproducibilidad del entorno.
- **C)** Explorar herramientas de desarrollo web.
- **D) (Correcta)** Añadir una capa mas a la seguridad de la aplicación.