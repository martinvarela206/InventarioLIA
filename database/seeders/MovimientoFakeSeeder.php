<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Ubicacion;
use App\Models\Usuario;
use App\Models\Tipo;
use Carbon\Carbon;

class MovimientoFakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command?->info("Generando movimientos y datos simulados realistas para probar Nexo LIA...");

        $adminUser = Usuario::where('email', 'admin@lia.unsj.edu.ar')->first() ?? Usuario::first();
        $tecUser = Usuario::where('email', 'tecnico@lia.unsj.edu.ar')->first() ?? $adminUser;
        $revUser = Usuario::where('email', 'revisor@lia.unsj.edu.ar')->first() ?? $adminUser;

        // Ubicaciones
        $labFS = Ubicacion::where('nombre', 'Laboratorio Federico Sanchez')->first() ?? Ubicacion::first();
        $labPM = Ubicacion::where('nombre', 'Laboratorio Paola Moyano')->first() ?? Ubicacion::first();
        $labRedes = Ubicacion::where('nombre', 'Laboratorio Redes')->first() ?? Ubicacion::first();
        $tallerHW = Ubicacion::where('nombre', 'Armario de HW')->first() ?? Ubicacion::first();
        $rackRedes = Ubicacion::where('nombre', 'Rack Redes')->first() ?? Ubicacion::first();

        // 1. ENRIQUECIMIENTO DE SPECS Y GARANTÍAS EN COMPUTADORAS Y NOTEBOOKS
        $pcs = Elemento::whereHas('tipo', function ($q) {
            $q->whereIn('nombre', ['computadora', 'notebook', 'servidor']);
        })->get();

        $cpuOptions = [
            'Intel Core i5-10400 2.9GHz',
            'Intel Core i7-10700 3.8GHz',
            'AMD Ryzen 5 5600G 3.9GHz',
            'Intel Core i3-10105 3.7GHz',
            'AMD Ryzen 7 5700U',
            'Intel Core i5-1135G7',
        ];
        $ramOptions = ['8GB DDR4', '16GB DDR4', '16GB DDR4 (2x8GB)', '32GB DDR4', '4GB DDR3'];
        $discoOptions = ['SSD 240GB Kingston', 'SSD 480GB Crucial', 'SSD 512GB NVMe M.2', 'SSD 1TB Western Digital Blue', 'HDD 1TB Seagate Barracuda'];

        foreach ($pcs as $idx => $pc) {
            $specs = is_array($pc->descripcion) ? $pc->descripcion : [];
            
            // Si le falta RAM o CPU, agregarlo para probar el filtro de GB
            if (!isset($specs['RAM']) && !isset($specs['Memoria RAM'])) {
                $specs['RAM'] = $ramOptions[$idx % count($ramOptions)];
            }
            if (!isset($specs['CPU']) && !isset($specs['Procesador'])) {
                $specs['CPU'] = $cpuOptions[$idx % count($cpuOptions)];
            }
            if (!isset($specs['Disco']) && !isset($specs['Almacenamiento'])) {
                $specs['Disco'] = $discoOptions[$idx % count($discoOptions)];
            }

            // Asignar fechas de adquisición y garantía si están vacías
            $anio = $pc->anio_compra ?: (2020 + ($idx % 5));
            $pc->update([
                'descripcion' => $specs,
                'anio_compra' => $anio,
                'fecha_adquisicion' => Carbon::createFromDate($anio, 3, 15),
                'fecha_vencimiento_garantia' => ($idx % 3 === 0) 
                    ? Carbon::now()->addMonths(6 + ($idx % 12)) // Vigente
                    : Carbon::now()->subMonths(3 + ($idx % 24)), // Vencida
            ]);

            // Sincronizar en la tabla pivot elemento_atributo_valor
            foreach ($specs as $key => $val) {
                $attr = \App\Models\Atributo::firstOrCreateNormalizado($key);
                \App\Models\ElementoAtributoValor::updateOrCreate(
                    ['elemento_id' => $pc->id, 'atributo_id' => $attr->id],
                    ['valor' => (string)$val]
                );
            }
        }

        // 2. PRÉSTAMOS EXTERNOS ACTIVOS (Para alimentar el Dashboard y el check-in)
        $candidatosPrestamo = Elemento::whereHas('tipo', function ($q) {
            $q->whereIn('nombre', ['notebook', 'proyector', 'herramienta', 'disco']);
        })->limit(6)->get();

        $solicitantesMock = [
            ['nombre' => 'Dr. Carlos Gómez (Cátedra Sistemas Operativos)', 'dias' => 4],
            ['nombre' => 'Dra. Laura Morales (Cátedra Base de Datos)', 'dias' => 38], // En mora
            ['nombre' => 'Ing. Roberto Fernández (Cátedra Redes y Comunicaciones)', 'dias' => 15],
            ['nombre' => 'Lic. Mariana Torres (Proyecto I+D Inteligencia Artificial)', 'dias' => 2],
            ['nombre' => 'Prof. Esteban Castro (Cátedra Algoritmos I)', 'dias' => 45], // En mora
            ['nombre' => 'Dra. Andrea Giménez (Dirección de Posgrado FCEFN)', 'dias' => 8],
        ];

        foreach ($candidatosPrestamo as $i => $elemento) {
            if (!isset($solicitantesMock[$i])) break;
            $sol = $solicitantesMock[$i];
            $fechaSalida = Carbon::now()->subDays($sol['dias']);

            Movimiento::create([
                'elemento_id' => $elemento->id,
                'tipo_movimiento' => 'externo_prestamo',
                'ubicacion_id' => null,
                'puesto' => null,
                'user_id' => $tecUser->id,
                'fecha' => $fechaSalida,
                'solicitante' => $sol['nombre'],
                'fecha_prestamo' => $fechaSalida->toDateString(),
                'fecha_devolucion' => null,
                'comentario' => "Préstamo formal autorizado para actividades de cátedra/investigación. Solicitante: {$sol['nombre']}",
            ]);
        }

        // 3. PRÉSTAMOS EXTERNOS HISTÓRICOS DEVUELTOS
        $candidatosDevueltos = Elemento::whereHas('tipo', function ($q) {
            $q->whereIn('nombre', ['notebook', 'proyector', 'placa de red']);
        })->skip(6)->limit(4)->get();

        foreach ($candidatosDevueltos as $j => $elemDev) {
            $salida = Carbon::now()->subMonths(3)->subDays($j * 5);
            $retorno = (clone $salida)->addDays(10 + $j * 3);

            // Registro de préstamo
            $prestamo = Movimiento::create([
                'elemento_id' => $elemDev->id,
                'tipo_movimiento' => 'externo_prestamo',
                'ubicacion_id' => null,
                'puesto' => null,
                'user_id' => $tecUser->id,
                'fecha' => $salida,
                'solicitante' => "Dr. Investigador Invitado #{$j}",
                'fecha_prestamo' => $salida->toDateString(),
                'fecha_devolucion' => $retorno->toDateString(),
                'comentario' => "Préstamo concluido. Salida: {$salida->toDateString()}",
            ]);

            // Reingreso a sala
            Movimiento::create([
                'elemento_id' => $elemDev->id,
                'tipo_movimiento' => 'interno',
                'ubicacion_id' => $tallerHW->id,
                'puesto' => null,
                'user_id' => $tecUser->id,
                'fecha' => $retorno,
                'comentario' => "Reingreso conforme de préstamo externo. Retornado en perfecto estado operativo.",
            ]);
        }

        // 4. DISTRIBUCIÓN EN SALAS Y PUESTOS (Laboratorios FS y PM)
        $pcsLabFS = Elemento::whereHas('tipo', function ($q) {
            $q->where('nombre', 'computadora');
        })->limit(15)->get();

        foreach ($pcsLabFS as $k => $pcFS) {
            $nroPuesto = sprintf('Puesto %02d', $k + 1);
            $pcFS->update(['puesto' => $nroPuesto]);

            Movimiento::create([
                'elemento_id' => $pcFS->id,
                'tipo_movimiento' => 'interno',
                'ubicacion_id' => $labFS->id,
                'puesto' => $nroPuesto,
                'user_id' => $tecUser->id,
                'fecha' => Carbon::now()->subMonths(2)->addDays($k),
                'comentario' => "Asignación e instalación física en Laboratorio Federico Sánchez ({$nroPuesto})",
            ]);
        }

        $pcsLabPM = Elemento::whereHas('tipo', function ($q) {
            $q->where('nombre', 'computadora');
        })->skip(15)->limit(12)->get();

        foreach ($pcsLabPM as $m => $pcPM) {
            $nroPuestoPM = sprintf('Puesto %02d', $m + 1);
            $pcPM->update(['puesto' => $nroPuestoPM]);

            Movimiento::create([
                'elemento_id' => $pcPM->id,
                'tipo_movimiento' => 'interno',
                'ubicacion_id' => $labPM->id,
                'puesto' => $nroPuestoPM,
                'user_id' => $tecUser->id,
                'fecha' => Carbon::now()->subMonths(1)->addDays($m),
                'comentario' => "Reubicación de puesto en Laboratorio Paola Moyano ({$nroPuestoPM})",
            ]);
        }

        // 5. MANTENIMIENTO TÉCNICO Y REPARACIONES
        $candidatosReparacion = Elemento::whereHas('tipo', function ($q) {
            $q->whereIn('nombre', ['computadora', 'monitor', 'impresora', 'ups']);
        })->skip(30)->limit(5)->get();

        $fallas = [
            'Falla en fuente de poder ATX. Zumbido eléctrico en capacitor y reinicios espontáneos bajo carga.',
            'Disco rígido con sectores defectuosos (SMART warning). En espera de clonación a SSD.',
            'Líneas verticales en panel LCD. Falla de placa T-Con o flex de video.',
            'Ventilador de cooler CPU trabado por acumulación de pelusa. Sobrecalentamiento a 90°C.',
            'Baterías de UPS agotadas (no retiene carga ante cortes). Reemplazo programado.',
        ];

        foreach ($candidatosReparacion as $fIdx => $elemRep) {
            Movimiento::create([
                'elemento_id' => $elemRep->id,
                'tipo_movimiento' => 'interno',
                'ubicacion_id' => $tallerHW->id,
                'puesto' => 'Banco de Trabajo HW',
                'user_id' => $tecUser->id,
                'fecha' => Carbon::now()->subDays(6 - $fIdx),
                'comentario' => "Intervención técnica: " . $fallas[$fIdx],
            ]);
        }

        // 6. CASOS DE AUDITORÍA CON ENMIENDAS / CORRECCIONES ENLAZADAS (correccion_de_id)
        $elemEnmienda = Elemento::where('nro_lia', '!=', null)->first();
        if ($elemEnmienda) {
            // Movimiento original con error
            $movError = Movimiento::create([
                'elemento_id' => $elemEnmienda->id,
                'tipo_movimiento' => 'interno',
                'ubicacion_id' => $tallerHW->id,
                'puesto' => 'Puesto Equivocado',
                'user_id' => $tecUser->id,
                'fecha' => Carbon::now()->subDays(20),
                'comentario' => 'Movimiento registrado con error de sala y puesto de destino.',
            ]);

            // Movimiento de enmienda oficial auditado
            Movimiento::create([
                'elemento_id' => $elemEnmienda->id,
                'tipo_movimiento' => 'interno',
                'ubicacion_id' => $labFS->id,
                'puesto' => 'Puesto 07',
                'user_id' => $adminUser->id,
                'fecha' => Carbon::now()->subDays(18),
                'correccion_de_id' => $movError->id,
                'comentario' => "ENMIENDA / CORRECCIÓN de Movimiento #{$movError->id}: Se corrigió la sala y puesto real. El equipo se encuentra efectivamente funcionando en Lab Federico Sánchez Puesto 07.",
            ]);
        }

        $this->command?->info("✓ Movimientos simulados generados: préstamos activos con mora, devoluciones, puestos de laboratorio, reparaciones técnicas y enmiendas auditadas.");
    }
}
