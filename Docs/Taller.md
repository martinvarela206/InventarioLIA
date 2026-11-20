# 1. Banco de Preguntas Evaluativas (Revisado y Calibrado)

> **Criterios aplicados:**
> - Opciones de longitud y redacción parejas (la correcta no se delata por ser más larga ni más técnica).
> - Letras de respuesta correcta variadas de forma aleatoria (B, D, A, C, B).
> - Sin tecnicismos de sintaxis de Laravel; enfocado en arquitectura, base de datos y modelo de dominio.
> - Se descartó la obsolescencia y se incorporó el ciclo de vida de los tickets/incidentes.

---

### Pregunta 1: Arquitectura de Persistencia e Integridad (Trazabilidad)
**Enunciado:**  
En el sistema de inventario, cuando un equipo cambia de laboratorio o pasa a mantenimiento, ¿por qué la arquitectura registra una nueva fila en `Movimientos` en lugar de simplemente actualizar los campos de ubicación y estado en la tabla `elementos`?

- **A)** Para acelerar las consultas de lectura en el servidor y evitar bloqueos de concurrencia en la base de datos.
- **B) (Correcta)** Para mantener un registro histórico inalterable de auditoría que permita saber quién, cuándo y por qué trasladó el equipo.
- **C)** Porque el motor SQL relacional impide modificar claves foráneas una vez que el registro fue insertado en la tabla.
- **D)** Para permitir que dos usuarios administradores modifiquen la ficha técnica del equipo en paralelo sin generar colisiones.

---

### Pregunta 2: Modelado Relacional (Especificaciones Técnicas Heterogéneas)
**Enunciado:**  
El laboratorio administra activos muy diversos (servidores, monitores, switches, memorias RAM). Para almacenar sus características técnicas particulares sin crear decenas de columnas vacías en la tabla principal, el sistema implementó un catálogo maestro y tablas intermedias (`atributos`, `tipo_atributo`, `elemento_atributo_valor`). ¿Cómo se denomina y justifica este patrón relacional?

- **A)** Es una desnormalización controlada para optimizar operaciones matemáticas masivas de hardware.
- **B)** Es una partición horizontal de base de datos diseñada para balancear el tráfico entre servidores.
- **C)** Es una arquitectura de réplicas de solo lectura implementada para garantizar alta disponibilidad.
- **D) (Correcta)** Es el patrón Entidad-Atributo-Valor (EAV), que permite definir esquemas técnicos flexibles mediante tablas pivot en SQL puro.

---

### Pregunta 3: Diseño de Arquitectura (Monolito Integrado vs. SPA Desacoplada)
**Enunciado:**  
Al definir la arquitectura del sistema, se optó por un monolito integrado con vistas del lado del servidor (Blade) en lugar de una aplicación web desacoplada (SPA en React o Vue con backend API REST independiente). ¿Cuál fue la justificación de ingeniería de esta decisión?

- **A) (Correcta)** Simplificar la infraestructura evitando desplegar contenedores adicionales de Node.js y reducir la sobrecarga de mantenimiento del sistema.
- **B)** Garantizar que la aplicación pueda compilarse directamente como código de máquina en el sistema operativo anfitrión.
- **C)** Permitir que el sistema funcione en redes locales sin necesidad de contar con un servidor de base de datos SQL.
- **D)** Resolver problemas de compatibilidad con navegadores antiguos que no soportan el protocolo HTTPS estándar.

---

### Pregunta 4: Modelo de Dominio y Gestión (Doble Identificador de Activos)
**Enunciado:**  
Cada equipo informático en el sistema cuenta con dos identificadores posibles: el `Nº LIA` y el `Nº UNSJ`. ¿A qué necesidad concreta de la operatoria del laboratorio responde esta decisión de diseño?

- **A)** A una exigencia del protocolo de red para asociar la dirección IP estática con la dirección MAC física del equipo.
- **B)** A una técnica de seguridad criptográfica utilizada para cifrar la identidad de los técnicos que manipulan los equipos.
- **C) (Correcta)** A respetar la metodología histórica de trabajo del LIA (planillas y papel) conviviendo con la numeración patrimonial de la universidad.
- **D)** A un mecanismo transaccional de la base de datos para prevenir inconsistencias durante caídas imprevistas del suministro eléctrico.

---

### Pregunta 5: Infraestructura y Despliegue (Entornos Contenerizados)
**Enunciado:**  
¿Cuál es la razón principal por la que la base de datos y la aplicación se despliegan mediante contenedores Docker en lugar de instalarse directamente sobre el sistema operativo del servidor físico?

- **A)** Automatizar la compra y renovación de certificados de seguridad y nombres de dominio en los servidores centrales.
- **B) (Correcta)** Proveer una capa de aislamiento y seguridad para proteger el servidor host, garantizando además la reproducibilidad del entorno.
- **C)** Aumentar la velocidad del procesador físico del servidor mediante la sobreasignación de ciclos de reloj de hardware.
- **D)** Reemplazar de forma definitiva los mecanismos tradicionales de respaldo físico y copias de seguridad de los datos.

---

### Pregunta 6: Gestión Operativa (Ciclo de Vida de Tickets y Mantenimiento)
**Enunciado:**  
Dentro del flujo de trabajo del LIA, cuando un usuario detecta una falla en una máquina y abre un ticket de soporte, ¿cómo se vincula este evento con el módulo de inventario?

- **A)** El sistema bloquea automáticamente la cuenta del usuario que reportó el incidente hasta que un técnico cierre la incidencia.
- **B)** El activo se elimina de la base de datos principal y se archiva temporalmente en una tabla de registros descartados.
- **C)** El ticket notifica de forma anónima al decanato de la facultad sin registrar el equipo ni el puesto afectado.
- **D) (Correcta)** El ticket se enlaza al equipo afectado y genera un movimiento de trazabilidad, documentando los cambios de estado y las intervenciones técnicas.

---

# 2. Cómo planificar tu taller de forma redonda

Con este enfoque, tu taller de 60 minutos queda estructurado de manera natural:

- **Bloque 1 (15 min) - Presentación de Ingeniería:** Explicás el problema del LIA (planillas desactualizadas, pérdida de equipos), el modelo relacional (por qué EAV con tablas pivot y no un millón de columnas nulas), y por qué Docker para aislar el host.
- **Bloque 2 (30 min) - Dinámica Práctica (Tu idea de los tickets):** Les das acceso local. Que se registren, busquen un equipo y abran un ticket de falla o respondan uno. Mientras ellos creen que están haciendo "testing/QA", en realidad están aprendiendo el flujo real del sistema que van a tener que usar.
- **Bloque 3 (15 min) - Cierre y Cuestionario Evaluativo:** Pasan las preguntas (en papel o en un Form rápido). Al haber usado la app y escuchado la introducción, van a responder en 5 minutos y el profesor tiene la evidencia formal que exige para aprobar la actividad.