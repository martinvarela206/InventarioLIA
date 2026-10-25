<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReporteConfigurado;

class ReporteConfiguradoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reportes = [
            [
                'slug' => 'inventario-completo',
                'titulo' => 'Listado completo del Inventario',
                'subtitulo' => 'Catálogo exhaustivo de todos los activos del LIA (operativos, prestados y bajas).',
                'descripcion' => 'Catálogo exhaustivo de todos los activos del LIA (operativos, prestados y bajas).',
                'icono' => 'cubes',
                'categoria' => 'inventario',
                'filtros_fijos' => [],
                'variables_requeridas' => [],
                'es_sistema' => true,
                'activo' => true,
                'orden' => 1,
            ],
            [
                'slug' => 'auditoria-sala',
                'titulo' => 'Inventario por ubicación',
                'subtitulo' => 'Auditoría in situ de equipos asignados a cada laboratorio o armario.',
                'descripcion' => 'Auditoría in situ de equipos asignados a cada laboratorio o armario.',
                'icono' => 'building',
                'categoria' => 'auditoria',
                'filtros_fijos' => [],
                'variables_requeridas' => ['ubicacion_id' => true],
                'es_sistema' => true,
                'activo' => true,
                'orden' => 2,
            ],
            [
                'slug' => 'ubicacion-trazabilidad',
                'titulo' => 'Ubicación e Historial de Movimientos',
                'subtitulo' => 'Escribe el Nro LIA para recibir sugerencias y consultar la ubicación actual resaltada y su historial.',
                'descripcion' => 'Escribe el Nro LIA para recibir sugerencias y consultar la ubicación actual resaltada y su historial.',
                'icono' => 'location',
                'categoria' => 'trazabilidad',
                'filtros_fijos' => [],
                'variables_requeridas' => ['nro_lia' => true],
                'es_sistema' => true,
                'activo' => true,
                'orden' => 3,
            ],
            [
                'slug' => 'historial-reparaciones',
                'titulo' => 'Historial de Tickets y Reparaciones',
                'subtitulo' => 'Consulta por Nro LIA o UNSJ el consolidado de intervenciones técnicas, bajas, sala previa y último préstamo.',
                'descripcion' => 'Consulta por Nro LIA o UNSJ el consolidado de intervenciones técnicas, bajas, sala previa y último préstamo.',
                'icono' => 'wrench',
                'categoria' => 'mantenimiento',
                'filtros_fijos' => [],
                'variables_requeridas' => ['nro_lia' => false],
                'es_sistema' => true,
                'activo' => true,
                'orden' => 4,
            ],
            [
                'slug' => 'prestados',
                'titulo' => 'Historial de Préstamos',
                'subtitulo' => 'Auditoría de equipos cedidos a cátedras o autoridades con cálculo de días.',
                'descripcion' => 'Auditoría de equipos cedidos a cátedras o autoridades con cálculo de días.',
                'icono' => 'arrows-right-left',
                'categoria' => 'prestamos',
                'filtros_fijos' => ['situacion' => 'prestado'],
                'variables_requeridas' => [],
                'es_sistema' => true,
                'activo' => true,
                'orden' => 5,
            ],
            [
                'slug' => 'donados-fechas',
                'titulo' => 'Activos Donados entre Fechas',
                'subtitulo' => 'Auditoría de bienes ingresados o transferidos por donación dentro de un rango temporal.',
                'descripcion' => 'Auditoría de bienes ingresados o transferidos por donación dentro de un rango temporal.',
                'icono' => 'gift',
                'categoria' => 'patrimonio',
                'filtros_fijos' => [],
                'variables_requeridas' => ['fechas' => true],
                'es_sistema' => true,
                'activo' => true,
                'orden' => 6,
            ],
            [
                'slug' => 'altas-fechas',
                'titulo' => 'Altas de Elementos con Fechas',
                'subtitulo' => 'Registro patrimonial de nuevas incorporaciones e ingresos adquiridos entre fechas.',
                'descripcion' => 'Registro patrimonial de nuevas incorporaciones e ingresos adquiridos entre fechas.',
                'icono' => 'calendar-plus',
                'categoria' => 'patrimonio',
                'filtros_fijos' => [],
                'variables_requeridas' => ['fechas' => true],
                'es_sistema' => true,
                'activo' => true,
                'orden' => 7,
            ],
            [
                'slug' => 'ingreso-estado-fechas',
                'titulo' => 'Estado por Fecha de Ingreso',
                'subtitulo' => 'Evalúa la situación y ubicación actual de los equipos incorporados en un período.',
                'descripcion' => 'Evalúa la situación y ubicación actual de los equipos incorporados en un período.',
                'icono' => 'clock',
                'categoria' => 'patrimonio',
                'filtros_fijos' => [],
                'variables_requeridas' => ['fechas' => true],
                'es_sistema' => true,
                'activo' => true,
                'orden' => 8,
            ],
            [
                'slug' => 'garantias-fechas',
                'titulo' => 'Estado de Garantías de Activos',
                'subtitulo' => 'Vencimientos y coberturas de garantía vigentes o caducadas en el parque informático.',
                'descripcion' => 'Vencimientos y coberturas de garantía vigentes o caducadas en el parque informático.',
                'icono' => 'shield',
                'categoria' => 'garantias',
                'filtros_fijos' => [],
                'variables_requeridas' => ['fechas' => true],
                'es_sistema' => true,
                'activo' => true,
                'orden' => 9,
            ],
            [
                'slug' => 'operativos',
                'titulo' => 'Activos Operativos en Salas',
                'subtitulo' => 'Equipos disponibles y en funcionamiento dentro de las instalaciones del LIA.',
                'descripcion' => 'Equipos disponibles y en funcionamiento dentro de las instalaciones del LIA.',
                'icono' => 'check-circle',
                'categoria' => 'inventario',
                'filtros_fijos' => ['situacion' => 'operativo'],
                'variables_requeridas' => [],
                'es_sistema' => true,
                'activo' => true,
                'orden' => 10,
            ],
            [
                'slug' => 'bajas-basura',
                'titulo' => 'Activos Dados de Baja y Basura Tecnológica',
                'subtitulo' => 'Equipos obsoletos, donados, transferidos o descartados patrimonialmente.',
                'descripcion' => 'Equipos obsoletos, donados, transferidos o descartados patrimonialmente.',
                'icono' => 'trash',
                'categoria' => 'bajas',
                'filtros_fijos' => ['situacion' => 'baja'],
                'variables_requeridas' => [],
                'es_sistema' => true,
                'activo' => true,
                'orden' => 11,
            ],
        ];

        foreach ($reportes as $datos) {
            ReporteConfigurado::updateOrCreate(
                ['slug' => $datos['slug']],
                $datos
            );
        }
    }
}
