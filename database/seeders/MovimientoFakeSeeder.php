<?php

namespace Database\Seeders;

use App\Models\AmbitoTicket;
use App\Models\Area;
use App\Models\Atributo;
use App\Models\Elemento;
use App\Models\ElementoAtributoValor;
use App\Models\Movimiento;
use App\Models\TicketComentario;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MovimientoFakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command?->info('Generando movimientos y datos simulados realistas para probar Nexo LIA...');

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
            if (! isset($specs['RAM']) && ! isset($specs['Memoria RAM'])) {
                $specs['RAM'] = $ramOptions[$idx % count($ramOptions)];
            }
            if (! isset($specs['CPU']) && ! isset($specs['Procesador'])) {
                $specs['CPU'] = $cpuOptions[$idx % count($cpuOptions)];
            }
            if (! isset($specs['Disco']) && ! isset($specs['Almacenamiento'])) {
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
                $attr = Atributo::firstOrCreateNormalizado($key);
                ElementoAtributoValor::updateOrCreate(
                    ['elemento_id' => $pc->id, 'atributo_id' => $attr->id],
                    ['valor' => (string) $val]
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
            if (! isset($solicitantesMock[$i])) {
                break;
            }
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
                'comentario' => 'Reingreso conforme de préstamo externo. Retornado en perfecto estado operativo.',
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
                'comentario' => 'Intervención técnica: '.$fallas[$fIdx],
            ]);
        }

        // 6. TICKETS DE INCIDENCIA REALES EN ACTIVOS PATRIMONIALES
        $ambitoLia = AmbitoTicket::where('nombre', 'like', '%LIA General%')->first()
            ?? AmbitoTicket::firstOrCreate(['nombre' => 'LIA General (Infraestructura / Servicios)'], ['orden' => 3, 'activo' => true]);
        $ambitoRedes = AmbitoTicket::where('nombre', 'like', '%Redes%')->first()
            ?? AmbitoTicket::firstOrCreate(['nombre' => 'Redes y Conectividad'], ['orden' => 4, 'activo' => true]);
        $ambitoMobiliario = AmbitoTicket::where('nombre', 'like', '%Mobiliario%')->first()
            ?? AmbitoTicket::firstOrCreate(['nombre' => 'Mobiliario y Espacios Físicos'], ['orden' => 5, 'activo' => true]);
        $ambitoLabA = AmbitoTicket::where('nombre', 'like', '%Laboratorio A%')->first()
            ?? AmbitoTicket::firstOrCreate(['nombre' => 'Laboratorio A'], ['orden' => 1, 'activo' => true]);

        $areaHardware = Area::where('nombre', 'Hardware')->first();
        $areaTI = Area::where('nombre', 'TI / Infraestructura')->first();
        $areaGeneral = Area::where('nombre', 'General')->first();

        $flaviaUser = Usuario::where('email', 'flavia@lia.com')->first() ?? $adminUser;
        $marceloUser = Usuario::where('email', 'marcelo@lia.com')->first() ?? $tecUser;

        // Candidatos de hardware para tickets con activo
        $pcFsTicket = $pcsLabFS->skip(2)->first();
        $pcPmTicket = $pcsLabPM->skip(4)->first();
        $elemProyector = Elemento::whereHas('tipo', function ($q) {
            $q->whereIn('nombre', ['proyector', 'monitor']);
        })->first();
        $elemNotebook = Elemento::whereHas('tipo', function ($q) {
            $q->where('nombre', 'notebook');
        })->first();

        // 6.1 Ticket Abierto: Computadora en Lab Federico Sánchez
        if ($pcFsTicket) {
            $tck1 = Movimiento::create([
                'elemento_id' => $pcFsTicket->id,
                'ambito_ticket_id' => null,
                'tipo_movimiento' => 'reparacion',
                'estado_ticket' => 'abierto',
                'ubicacion_id' => $labFS->id,
                'puesto' => $pcFsTicket->puesto ?? 'Puesto 03',
                'user_id' => $revUser->id,
                'fecha' => Carbon::now()->subDays(4),
                'solicitante' => 'Lic. Martín Varela (Cátedra Programación Web)',
                'comentario' => 'Pantalla azul recurrente (DRIVER_IRQL_NOT_LESS_OR_EQUAL) al compilar proyectos en C++ o ejecutar contenedores Docker. Posible falla de módulo RAM o degradación en SSD.',
            ]);

            TicketComentario::create([
                'ticket_id' => $tck1->id,
                'user_id' => $tecUser->id,
                'mensaje' => 'Inspección técnica inicial en sala: el equipo bootea pero crashea bajo test de carga con Prime95. Se programó aislamiento de módulo de memoria durante la tarde.',
                'tipo' => 'comentario',
                'created_at' => Carbon::now()->subDays(3),
            ]);
            if ($areaHardware) {
                $tck1->areas()->attach($areaHardware->id, ['user_id' => $tecUser->id]);
            }
        }

        // 6.2 Ticket en Solución Propuesta: Computadora en Lab Paola Moyano
        if ($pcPmTicket) {
            $tck2 = Movimiento::create([
                'elemento_id' => $pcPmTicket->id,
                'ambito_ticket_id' => null,
                'tipo_movimiento' => 'reparacion',
                'estado_ticket' => 'solucionado',
                'ubicacion_id' => $labPM->id,
                'puesto' => $pcPmTicket->puesto ?? 'Puesto 05',
                'user_id' => $revUser->id,
                'fecha' => Carbon::now()->subDays(6),
                'solicitante' => 'Dra. Laura Morales (Investigación IA)',
                'comentario' => 'Artefactos visuales, congelamiento y reinicios espontáneos al procesar modelos en GPU integrada.',
            ]);

            TicketComentario::create([
                'ticket_id' => $tck2->id,
                'user_id' => $marceloUser->id,
                'mensaje' => 'Equipo recibido en taller de HW. Se desmontó el disipador encontrando pasta térmica completamente cristalizada.',
                'tipo' => 'comentario',
                'created_at' => Carbon::now()->subDays(4),
            ]);
            TicketComentario::create([
                'ticket_id' => $tck2->id,
                'user_id' => $marceloUser->id,
                'mensaje' => 'Se renovó pasta térmica con compuesto cerámico de alto rendimiento y se colocó ventilador adicional de 120mm en el chasis. Temperaturas estabilizadas a 62°C bajo estrés. Se propone dejar en prueba 48 hs antes de retorno a sala.',
                'tipo' => 'propuesta_solucion',
                'created_at' => Carbon::now()->subDays(1),
            ]);
            if ($areaHardware) {
                $tck2->areas()->attach($areaHardware->id, ['user_id' => $marceloUser->id]);
            }
        }

        // 6.3 Ticket Resuelto: Proyector / Monitor en Lab Redes
        if ($elemProyector) {
            $tck3 = Movimiento::create([
                'elemento_id' => $elemProyector->id,
                'ambito_ticket_id' => null,
                'tipo_movimiento' => 'reparacion',
                'estado_ticket' => 'resuelto',
                'ubicacion_id' => $labRedes->id,
                'puesto' => 'Soporte de Techo Lab Redes',
                'user_id' => $revUser->id,
                'fecha' => Carbon::now()->subDays(12),
                'solicitante' => 'Ing. Roberto Fernández (Cátedra Redes y Comunicaciones)',
                'comentario' => 'Falla de encendido y testigo ámbar de sobrecalentamiento intermitente al cabo de 15 minutos de proyección.',
            ]);

            TicketComentario::create([
                'ticket_id' => $tck3->id,
                'user_id' => $tecUser->id,
                'mensaje' => 'Limpieza ultrasónica de filtro de aire y sustitución de lámpara agotada.',
                'tipo' => 'comentario',
                'created_at' => Carbon::now()->subDays(10),
            ]);
            TicketComentario::create([
                'ticket_id' => $tck3->id,
                'user_id' => $tecUser->id,
                'mensaje' => 'Incidencia resuelta. 4 horas continuas de prueba de proyección sin fallas de temperatura. Reincorporado al aula.',
                'tipo' => 'resolucion',
                'created_at' => Carbon::now()->subDays(8),
            ]);

            Movimiento::create([
                'elemento_id' => $elemProyector->id,
                'ticket_id' => $tck3->id,
                'tipo_movimiento' => 'interno',
                'ubicacion_id' => $labRedes->id,
                'puesto' => 'Soporte de Techo Lab Redes',
                'user_id' => $tecUser->id,
                'fecha' => Carbon::now()->subDays(8),
                'comentario' => "Reincorporación operativa tras cierre y resolución de Ticket #{$tck3->id}.",
            ]);
            if ($areaHardware) {
                $tck3->areas()->attach($areaHardware->id, ['user_id' => $tecUser->id]);
            }
        }

        // 6.4 Ticket Abierto: Notebook Institucional
        if ($elemNotebook) {
            $tck4 = Movimiento::create([
                'elemento_id' => $elemNotebook->id,
                'ambito_ticket_id' => null,
                'tipo_movimiento' => 'reparacion',
                'estado_ticket' => 'abierto',
                'ubicacion_id' => $tallerHW->id,
                'puesto' => 'Banco de Pruebas',
                'user_id' => $flaviaUser->id,
                'fecha' => Carbon::now()->subDays(2),
                'solicitante' => 'Dr. Carlos Gómez (Cátedra Sistemas Operativos)',
                'comentario' => 'Teclado integrado con varias teclas numéricas y barra espaciadora que se traban físicamente tras derrame accidental de líquido.',
            ]);
            if ($areaHardware) {
                $tck4->areas()->attach($areaHardware->id, ['user_id' => $flaviaUser->id]);
            }
        }

        // 7. TICKETS GENERALES DE INFRAESTRUCTURA, ESPACIOS Y CUESTIONES DEL LIA (SIN ACTIVO)
        // 7.1 LIA General: Aire Acondicionado / Climatización (Abierto)
        $tckGenAire = Movimiento::create([
            'elemento_id' => null,
            'ambito_ticket_id' => $ambitoLia->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'ubicacion_id' => null,
            'puesto' => 'Pasillo central frente a Rack Troncal',
            'user_id' => $flaviaUser->id,
            'fecha' => Carbon::now()->subDays(3),
            'solicitante' => 'Flavia Millan (Dirección LIA)',
            'comentario' => 'Falla en equipo de aire acondicionado central del LIA. El split principal gotea agua condensada sobre el piso técnico frente al rack de servidores y no logra refrigerar el ambiente adecuadamente.',
        ]);
        TicketComentario::create([
            'ticket_id' => $tckGenAire->id,
            'user_id' => $tecUser->id,
            'mensaje' => 'Se instaló batea de contención plástica provisoria para proteger el cableado y se solicitó service oficial de refrigeración de la FCEFN para revisión del compresor y desagüe.',
            'tipo' => 'comentario',
            'created_at' => Carbon::now()->subDays(2),
        ]);
        if ($areaGeneral) {
            $tckGenAire->areas()->attach($areaGeneral->id, ['user_id' => $tecUser->id]);
        }

        // 7.2 Redes y Conectividad: Cortes de Internet / Wi-Fi en Lab Redes (Solución Propuesta)
        $tckGenRedes = Movimiento::create([
            'elemento_id' => null,
            'ambito_ticket_id' => $ambitoRedes->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'solucionado',
            'ubicacion_id' => $labRedes->id,
            'puesto' => 'Laboratorio Redes - Puestos de alumnos y AP-02',
            'user_id' => $revUser->id,
            'fecha' => Carbon::now()->subDays(5),
            'solicitante' => 'Ing. Roberto Fernández (Laboratorio de Redes)',
            'comentario' => 'Cortes intermitentes de internet y conectividad Wi-Fi en el Laboratorio de Redes. Los alumnos experimentan pérdida de paquetes hacia el DNS institucional durante las clases prácticas.',
        ]);
        TicketComentario::create([
            'ticket_id' => $tckGenRedes->id,
            'user_id' => $tecUser->id,
            'mensaje' => 'Inspección de red: se detectó bucle de broadcast por un cable UTP en puente en la mesa 3 de prácticas. Se reconfiguró STP (Spanning Tree Protocol) y se activó BPDU Guard en la boca del switch.',
            'tipo' => 'comentario',
            'created_at' => Carbon::now()->subDays(3),
        ]);
        TicketComentario::create([
            'ticket_id' => $tckGenRedes->id,
            'user_id' => $tecUser->id,
            'mensaje' => 'Enlace estabilizado durante las últimas 24 horas sin caídas de paquetes. Se mantiene en monitoreo hasta finalizar la jornada académica.',
            'tipo' => 'propuesta_solucion',
            'created_at' => Carbon::now()->subDays(1),
        ]);
        if ($areaTI) {
            $tckGenRedes->areas()->attach($areaTI->id, ['user_id' => $tecUser->id]);
        }

        // 7.3 Mobiliario y Espacios Físicos: Sillas y Mesas en Lab Federico Sánchez (Abierto)
        $tckGenSillas = Movimiento::create([
            'elemento_id' => null,
            'ambito_ticket_id' => $ambitoMobiliario->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'ubicacion_id' => $labFS->id,
            'puesto' => 'Laboratorio Federico Sánchez - Hileras 2 y 4',
            'user_id' => $revUser->id,
            'fecha' => Carbon::now()->subDays(2),
            'solicitante' => 'Revisor de Auditoría LIA',
            'comentario' => 'Problemas con sillas y mobiliario en Laboratorio Federico Sánchez: se relevaron 5 sillas ergonómicas con el pistón hidráulico vencido (se descienden solas al sentarse) y una mesa de trabajo con tornillos flojos en su base metálica con riesgo de inclinación.',
        ]);
        TicketComentario::create([
            'ticket_id' => $tckGenSillas->id,
            'user_id' => $marceloUser->id,
            'mensaje' => 'Se verificaron las 5 sillas. Se solicitaron repuestos de pistones a Intendencia y se ajustaron los bulones de la mesa.',
            'tipo' => 'comentario',
            'created_at' => Carbon::now()->subDays(1),
        ]);
        if ($areaGeneral) {
            $tckGenSillas->areas()->attach($areaGeneral->id, ['user_id' => $marceloUser->id]);
        }

        // 7.4 Laboratorio A: Iluminación y Tubos LED (Resuelto)
        $tckGenIluminacion = Movimiento::create([
            'elemento_id' => null,
            'ambito_ticket_id' => $ambitoLabA->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'resuelto',
            'ubicacion_id' => null,
            'puesto' => 'Laboratorio A - Luminarias sector docente y pizarrón',
            'user_id' => $revUser->id,
            'fecha' => Carbon::now()->subDays(9),
            'solicitante' => 'Dra. Andrea Giménez (Docencia LIA)',
            'comentario' => 'Zumbido eléctrico de alta frecuencia y parpadeo continuo en las dos luminarias tubulares superiores del Laboratorio A.',
        ]);
        TicketComentario::create([
            'ticket_id' => $tckGenIluminacion->id,
            'user_id' => $tecUser->id,
            'mensaje' => 'Se desmontaron los balastos electromagnéticos antiguos que causaban el zumbido.',
            'tipo' => 'comentario',
            'created_at' => Carbon::now()->subDays(8),
        ]);
        TicketComentario::create([
            'ticket_id' => $tckGenIluminacion->id,
            'user_id' => $tecUser->id,
            'mensaje' => 'Se instalaron 2 regletas LED directas a 220V con temperatura de color 4000K neutra. Iluminación restaurada y verificada.',
            'tipo' => 'resolucion',
            'created_at' => Carbon::now()->subDays(7),
        ]);
        if ($areaGeneral) {
            $tckGenIluminacion->areas()->attach($areaGeneral->id, ['user_id' => $tecUser->id]);
        }

        // 8. CASOS DE AUDITORÍA CON ENMIENDAS / CORRECCIONES ENLAZADAS (correccion_de_id)
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

        $this->command?->info('✓ Movimientos simulados generados: préstamos activos con mora, devoluciones, puestos de laboratorio, reparaciones técnicas y enmiendas auditadas.');
    }
}
