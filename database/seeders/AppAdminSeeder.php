<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Carrera;
use App\Models\EspacioLia;
use App\Models\PersonalLia;
use App\Models\ActividadLia;
use App\Models\Usuario;

class AppAdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Carreras de la FCEFN - UNSJ
        $carreras = [
            ['nombre' => 'Licenciatura en Ciencias de la Computación', 'codigo' => 'LCC', 'activa' => true],
            ['nombre' => 'Licenciatura en Sistemas de Información', 'codigo' => 'LSI', 'activa' => true],
            ['nombre' => 'Tecnicatura Universitaria en Programación Web', 'codigo' => 'TUPW', 'activa' => true],
            ['nombre' => 'Profesorado en Ciencias de la Computación', 'codigo' => 'PCC', 'activa' => true],
            ['nombre' => 'Licenciatura en Geofísica', 'codigo' => 'LG', 'activa' => true],
            ['nombre' => 'Licenciatura en Astronomía', 'codigo' => 'LA', 'activa' => true],
            ['nombre' => 'Licenciatura en Biología', 'codigo' => 'LB', 'activa' => true],
            ['nombre' => 'Licenciatura en Ciencias Geológicas', 'codigo' => 'LCG', 'activa' => true],
        ];

        foreach ($carreras as $c) {
            Carrera::firstOrCreate(['nombre' => $c['nombre']], $c);
        }

        // 2. Espacios del LIA
        $espacios = [
            [
                'titulo' => 'Laboratorio Federico Sánchez',
                'descripcion' => 'Espacio de docencia y práctica estudiantil con 30 puestos informáticos equipados, proyector interactivo y red de alta velocidad.',
                'imagen_path' => '/assets/notebook-wide.png',
                'orden' => 1,
                'activo' => true,
            ],
            [
                'titulo' => 'Laboratorio Paola Moyano',
                'descripcion' => 'Laboratorio de investigación aplicada, inteligencia artificial y proyectos de tesis de grado y posgrado.',
                'imagen_path' => '/assets/notebook-wide.png',
                'orden' => 2,
                'activo' => true,
            ],
            [
                'titulo' => 'Taller de Mantenimiento y Hardware',
                'descripcion' => 'Área técnica equipada para diagnóstico, reparación, cambio de pasta térmica, ensamblado y banco de pruebas de componentes.',
                'imagen_path' => null,
                'orden' => 3,
                'activo' => true,
            ],
            [
                'titulo' => 'Rack de Redes y Servidores',
                'descripcion' => 'Infraestructura central de telecomunicaciones, servidores ProLiant, routers perimetrales y switches administrables del departamento.',
                'imagen_path' => null,
                'orden' => 4,
                'activo' => true,
            ],
        ];

        foreach ($espacios as $esp) {
            EspacioLia::firstOrCreate(['titulo' => $esp['titulo']], $esp);
        }

        // 3. Personal Clave del LIA
        $personal = [
            [
                'nombre' => 'Dra. María Elena Fernández',
                'cargo' => 'Directora del LIA',
                'comentario' => 'Supervisión general del laboratorio, vinculación institucional con decanato y cátedras, y gestión de convenios académicos.',
                'email' => 'direccion.lia@unsj.edu.ar',
                'foto_path' => '/assets/flavia.png',
                'orden' => 1,
                'activo' => true,
            ],
            [
                'nombre' => 'Lic. Carlos A. Gómez',
                'cargo' => 'Coordinador de Infraestructura y Redes',
                'comentario' => 'A cargo de la conectividad por fibra, administración de servidores, políticas de backup y disponibilidad de salas.',
                'email' => 'cgomez@exactas.unsj.edu.ar',
                'foto_path' => '/assets/manuel.png',
                'orden' => 2,
                'activo' => true,
            ],
            [
                'nombre' => 'Ing. Roberto D. Vega',
                'cargo' => 'Encargado de Seguridad ante Emergencias',
                'comentario' => 'Responsable del plan de evacuación, mantenimiento de extintores, seguridad eléctrica e higiene en el recinto del LIA.',
                'email' => 'seguridad.lia@unsj.edu.ar',
                'foto_path' => null,
                'orden' => 3,
                'activo' => true,
            ],
        ];

        foreach ($personal as $p) {
            PersonalLia::firstOrCreate(['nombre' => $p['nombre']], $p);
        }

        // 4. Actividades del LIA (Posteos estilo feed / Instagram)
        $admin = Usuario::first();
        $actividades = [
            [
                'titulo' => 'Jornada de Mantenimiento Preventivo y Limpieza de Equipos',
                'contenido' => 'Este viernes realizamos el mantenimiento integral de los puestos del Laboratorio Federico Sánchez. Agradecemos a los ayudantes de cátedra y becarios por su dedicación para dejar los equipos 100% operativos.',
                'imagen_path' => '/assets/notebook-wide.png',
                'fecha_publicacion' => now()->subDays(2),
                'activo' => true,
                'user_id' => $admin?->id,
            ],
            [
                'titulo' => 'Nueva Conectividad Wi-Fi 6 en Salas del LIA',
                'contenido' => 'Quedó operativa la nueva infraestructura de puntos de acceso de alta densidad. Ahora estudiantes y docentes pueden conectarse con mayor velocidad y estabilidad durante las clases prácticas.',
                'imagen_path' => null,
                'fecha_publicacion' => now()->subDays(6),
                'activo' => true,
                'user_id' => $admin?->id,
            ],
        ];

        foreach ($actividades as $act) {
            ActividadLia::firstOrCreate(['titulo' => $act['titulo']], $act);
        }

        // 5. Avisos de Seguridad y Buenas Prácticas Informáticas
        $avisos = [
            [
                'titulo' => 'Protección de Credenciales Institucionales',
                'descripcion' => 'Nunca compartas tu usuario ni contraseña de Nexo LIA. Utilizá contraseñas robustas y cerrá tu sesión al retirarte de un puesto común del laboratorio.',
                'severidad' => 'warning',
                'icono' => 'key',
                'orden' => 1,
                'activo' => true,
            ],
            [
                'titulo' => 'Bloqueo de Puesto al Ausentarse',
                'descripcion' => 'Presioná Win + L o bloqueá la pantalla cada vez que te alejes de tu puesto de trabajo en las salas para evitar accesos no autorizados a tu sesión.',
                'severidad' => 'tip',
                'icono' => 'lock',
                'orden' => 2,
                'activo' => true,
            ],
            [
                'titulo' => 'Cuidado con Dispositivos USB Externos',
                'descripcion' => 'Evitá conectar memorias USB de procedencia desconocida en terminales de red del LIA. Escaneá siempre con el antivirus antes de transferir archivos.',
                'severidad' => 'danger',
                'icono' => 'usb',
                'orden' => 3,
                'activo' => true,
            ],
            [
                'titulo' => 'Reporte Temprano de Incidentes y Anomalías',
                'descripcion' => 'Si detectás fallas físicas, cables en mal estado, anomalías de software o sospecha de intrusión, abrí un ticket de incidencia de inmediato para el equipo técnico.',
                'severidad' => 'info',
                'icono' => 'shield',
                'orden' => 4,
                'activo' => true,
            ],
        ];

        foreach ($avisos as $av) {
            \App\Models\AvisoSeguridad::firstOrCreate(['titulo' => $av['titulo']], $av);
        }
    }
}
