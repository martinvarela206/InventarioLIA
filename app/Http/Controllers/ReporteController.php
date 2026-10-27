<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Ubicacion;
use App\Models\Tipo;
use App\Models\Atributo;
use App\Models\ReporteConfigurado;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ReporteController extends Controller
{
    /**
     * Obtiene los nombres de columnas de atributos específicos de tipos de activos.
     */
    public static function obtenerAtributosDinamicos(): array
    {
        try {
            return Atributo::whereNotIn('nombre', ['Año', 'Año de Compra'])
                ->orderBy('nombre', 'asc')
                ->pluck('nombre')
                ->toArray();
        } catch (\Throwable $e) {
            return ['Capacidad', 'Disco', 'Potencia', 'Procesador', 'Puertos', 'Pulgadas', 'RAM', 'Resolución', 'Velocidad'];
        }
    }

    /**
     * Lista de columnas que deben permanecer ocultas por defecto en la carga inicial.
     */
    public static function obtenerColumnasOcultasDefecto(array $atributos): array
    {
        return array_values(array_unique(array_merge(
            $atributos,
            ['Solicitante', 'Solicitante / Préstamo', 'Garantía', 'Garantía / Obs', 'Donante / Solicitante']
        )));
    }

    /**
     * Hub principal de selección de reportes.
     */
    public function index(): View
    {
        if (ReporteConfigurado::count() === 0) {
            (new \Database\Seeders\ReporteConfiguradoSeeder())->run();
        }

        $ubicaciones = Ubicacion::orderBy('nombre')->get();
        $tipos = Tipo::orderBy('nombre')->get();
        $reportes = ReporteConfigurado::activos()->ordenados()->get();
        $reportesPersonalizados = $reportes->where('es_sistema', false);

        return view('reportes.index', compact('ubicaciones', 'tipos', 'reportes', 'reportesPersonalizados'));
    }

    /**
     * Filtra dinámicamente las columnas y filas según la lista de columnas ocultas solicitadas.
     */
    private function filtrarColumnasOcultas(array &$data, Request $request): void
    {
        if ($request->filled('columnas_ocultas')) {
            $ocultas = array_filter(array_map('trim', explode(',', $request->input('columnas_ocultas'))));
            if (!empty($ocultas) && !empty($data['columnas'])) {
                $indicesOcultos = [];
                foreach ($data['columnas'] as $idx => $nombre) {
                    if (in_array($nombre, $ocultas)) {
                        $indicesOcultos[] = $idx;
                    }
                }
                if (!empty($indicesOcultos)) {
                    $data['columnas'] = array_values(array_filter($data['columnas'], fn($c, $i) => !in_array($i, $indicesOcultos), ARRAY_FILTER_USE_BOTH));
                    $data['filas'] = collect($data['filas'])->map(function($fila) use ($indicesOcultos) {
                        return array_values(array_filter((array)$fila, fn($v, $i) => !in_array($i, $indicesOcultos), ARRAY_FILTER_USE_BOTH));
                    })->all();
                }
            }
        }
    }

    /**
     * Vista de reporte individual sin paginación, preparada para pantalla e impresión.
     */
    public function show(string $tipo, Request $request): View
    {
        $data = $this->obtenerDatosReporte($tipo, $request);
        $this->filtrarColumnasOcultas($data, $request);

        $columnasOcultasIniciales = $request->has('columnas_ocultas')
            ? array_filter(array_map('trim', explode(',', (string)$request->input('columnas_ocultas'))))
            : ($data['columnasOcultasDefecto'] ?? []);

        return view('reportes.show', array_merge($data, [
            'tipoReporte' => $tipo,
            'request' => $request,
            'columnasOcultasIniciales' => $columnasOcultasIniciales,
        ]));
    }

    /**
     * Exportación masiva a archivo CSV sin paginar.
     */
    public function exportCsv(string $tipo, Request $request): StreamedResponse
    {
        $data = $this->obtenerDatosReporte($tipo, $request);

        // Si la petición no especificó columnas_ocultas expresas, aplicar las columnas ocultas por defecto
        if (!$request->has('columnas_ocultas') && !empty($data['columnasOcultasDefecto'])) {
            $request->merge(['columnas_ocultas' => implode(',', $data['columnasOcultasDefecto'])]);
        }

        $this->filtrarColumnasOcultas($data, $request);

        $filas = $data['filas'] ?? [];
        $titulo = $data['titulo'] ?? "Reporte_{$tipo}";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . str_replace(' ', '_', $titulo) . '_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($filas, $data) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 para apertura correcta en Microsoft Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados
            $columnas = $data['columnas'] ?? ['Tipo', 'Nro LIA', 'Nro UNSJ', 'Marca', 'Modelo', 'Especificaciones / Hardware', 'Ubicación', 'Puesto', 'Situación', 'Solicitante / Obs'];
            fputcsv($handle, $columnas, ';');

            foreach ($filas as $fila) {
                fputcsv($handle, $fila, ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Modo Auditoría de Sala / Armario (Vista directa para resguardo físico en papel).
     */
    public function auditoriaSala(Ubicacion $ubicacion, Request $request): View
    {
        $query = Elemento::enUbicacion($ubicacion->id)->with(['tipo', 'ultimoMovimiento.ubicacion']);

        if ($request->filled('tipo_id')) {
            $query->where('tipo_id', $request->tipo_id);
        }

        if ($request->filled('puesto')) {
            $puesto = trim($request->input('puesto'));
            $query->where(function ($q) use ($puesto) {
                $q->whereHas('ultimoMovimiento', fn($mq) => $mq->where('puesto', $puesto))
                  ->orWhere('puesto', $puesto);
            });
        }

        $elementos = $query->orderBy('puesto', 'asc')->orderBy('id', 'asc')->get();

        return view('reportes.auditoria', [
            'ubicacion' => $ubicacion,
            'elementos' => $elementos,
            'fechaEmision' => Carbon::now()->format('d/m/Y H:i'),
            'usuarioEmisor' => auth()->user()?->nombre ?? 'Sistema LIA',
        ]);
    }

    /**
     * Construye una fila estándar de activo con soporte para columnas diferenciales de atributos técnicos.
     */
    private function construirFilaElemento(
        Elemento $el,
        array $atributos,
        ?string $situacionForzada = null,
        ?string $solicitanteForzado = null,
        ?string $ubicacionForzada = null,
        ?string $obsForzada = null
    ): array {
        $u = $el->ultimoMovimiento;
        $situacion = $situacionForzada;
        if ($situacion === null) {
            if ($u?->esPrestamo() && !$u?->fecha_devolucion) {
                $situacion = 'Prestado';
            } elseif ($u?->tipo_movimiento === 'baja' || $el->tipo?->nombre === 'basura tecnologica') {
                $situacion = 'Basura Tecnológica / De Baja';
            } elseif ($u?->esTicketAbierto()) {
                $situacion = 'En Reparación';
            } else {
                $situacion = 'En Sala';
            }
        }

        $ubicacionNombre = $ubicacionForzada ?: ($u?->ubicacion?->nombre ?? ($u?->esPrestamo() ? 'Préstamo externo' : 'Sin asignar'));
        $puesto = $u?->puesto ?: ($el->puesto ?? '-');
        $solicitante = $solicitanteForzado ?: ($u?->solicitante ?? '-');
        $obs = $obsForzada ?: ($el->observaciones ?: ($el->descripcion_texto ?? '-'));

        $fila = [
            $el->tipo?->nombre ?? 'N/A',
            $el->nro_lia ?? '-',
            $el->nro_unsj ?? '-',
            $el->marca ?? '-',
            $el->modelo ?? '-',
            $el->especificacionesSintetizadas(false),
            $ubicacionNombre,
            $puesto,
            $situacion,
        ];

        foreach ($atributos as $attr) {
            $fila[] = $el->getValorAtributo($attr);
        }

        $fila[] = $solicitante;
        $fila[] = $obs;

        return $fila;
    }

    /**
     * Construcción de dataset según el reporte solicitado.
     */
    private function obtenerDatosReporte(string $tipo, Request $request): array
    {
        $atributos = self::obtenerAtributosDinamicos();
        $columnasStandard = array_merge(
            ['Tipo', 'Nro LIA', 'Nro UNSJ', 'Marca', 'Modelo', 'Especificaciones / Hardware', 'Ubicación', 'Puesto', 'Situación'],
            $atributos,
            ['Solicitante', 'Garantía / Obs']
        );
        $columnasOcultasDefecto = self::obtenerColumnasOcultasDefecto($atributos);

        switch ($tipo) {
            case 'inventario-completo':
                $query = Elemento::with(['tipo', 'ultimoMovimiento.ubicacion', 'valoresAtributos.atributo']);
                if ($request->filled('tipo_id')) $query->where('tipo_id', $request->tipo_id);
                if ($request->filled('ubicacion_id')) $query->whereHas('ultimoMovimiento', fn($q) => $q->where('ubicacion_id', $request->ubicacion_id));
                if ($request->filled('puesto')) {
                    $puesto = trim($request->input('puesto'));
                    $query->where(function ($q) use ($puesto) {
                        $q->whereHas('ultimoMovimiento', fn($mq) => $mq->where('puesto', $puesto))
                          ->orWhere('puesto', $puesto);
                    });
                }

                $items = $query->orderBy('id', 'asc')->get();
                $filas = $items->map(function ($el) use ($atributos) {
                    return $this->construirFilaElemento($el, $atributos);
                });

                return [
                    'titulo' => 'Listado completo del Inventario',
                    'subtitulo' => 'Catálogo completo de todos los activos del LIA.',
                    'columnas' => $columnasStandard,
                    'filas' => $filas,
                    'total' => $items->count(),
                    'columnasOcultasDefecto' => $columnasOcultasDefecto,
                ];

            case 'operativos':
                $query = Elemento::disponibles()->with(['tipo', 'ultimoMovimiento.ubicacion', 'valoresAtributos.atributo']);
                if ($request->filled('tipo_id')) $query->where('tipo_id', $request->tipo_id);
                if ($request->filled('ubicacion_id')) $query->whereHas('ultimoMovimiento', fn($q) => $q->where('ubicacion_id', $request->ubicacion_id));
                if ($request->filled('puesto')) {
                    $puesto = trim($request->input('puesto'));
                    $query->where(function ($q) use ($puesto) {
                        $q->whereHas('ultimoMovimiento', fn($mq) => $mq->where('puesto', $puesto))
                          ->orWhere('puesto', $puesto);
                    });
                }

                $items = $query->orderBy('id', 'asc')->get();
                $filas = $items->map(function ($el) use ($atributos) {
                    return $this->construirFilaElemento($el, $atributos, 'Operativo en Sala');
                });

                return [
                    'titulo' => 'Activos Operativos en Salas',
                    'subtitulo' => 'Equipos disponibles y en funcionamiento dentro de las instalaciones del LIA',
                    'columnas' => $columnasStandard,
                    'filas' => $filas,
                    'total' => $items->count(),
                    'columnasOcultasDefecto' => $columnasOcultasDefecto,
                ];

            case 'prestados':
                $query = Elemento::prestadosActivos()->with(['tipo', 'ultimoMovimiento.ubicacion', 'ultimoMovimiento.usuario', 'valoresAtributos.atributo']);
                if ($request->filled('tipo_id')) $query->where('tipo_id', $request->tipo_id);

                $items = $query->orderBy('id', 'asc')->get();
                $filas = $items->map(function ($el) use ($atributos) {
                    $u = $el->ultimoMovimiento;
                    $fechaSalida = $u?->fecha_prestamo ? Carbon::parse($u->fecha_prestamo)->format('d/m/Y') : '-';
                    $dias = $u?->fecha_prestamo ? Carbon::parse($u->fecha_prestamo)->diffInDays(now()) . ' días' : '-';
                    $sol = $u?->solicitante ? "{$u->solicitante} (Salida: {$fechaSalida} - {$dias})" : '-';
                    $coment = $u?->comentario ?: ($el->observaciones ?: ($el->descripcion_texto ?? '-'));
                    return $this->construirFilaElemento($el, $atributos, 'Prestado', $sol, null, $coment);
                });

                return [
                    'titulo' => 'Historial de Préstamos',
                    'subtitulo' => 'Historial de elementos prestados.',
                    'columnas' => $columnasStandard,
                    'filas' => $filas,
                    'total' => $items->count(),
                    'columnasOcultasDefecto' => $columnasOcultasDefecto,
                ];

            case 'bajas-basura':
            case 'bajas':
                $query = Elemento::obsoletos()->with(['tipo', 'ultimoMovimiento.ubicacion', 'ultimoMovimiento.usuario', 'valoresAtributos.atributo']);

                if ($request->filled('tipo_id')) $query->where('tipo_id', $request->tipo_id);

                $items = $query->orderBy('id', 'asc')->get();
                $filas = $items->map(function ($el) use ($atributos) {
                    $u = $el->ultimoMovimiento;
                    $sol = $u?->usuario?->nombre ? "Registrado por: {$u->usuario->nombre}" : '-';
                    $coment = $u?->comentario ?: ($el->observaciones ?: ($el->descripcion_texto ?? '-'));
                    return $this->construirFilaElemento($el, $atributos, 'Basura Tecnológica', $sol, 'Depósito / Descarte', $coment);
                });

                return [
                    'titulo' => 'Activos Dados de Baja y Basura Tecnológica',
                    'subtitulo' => 'Equipos obsoletos, donados, transferidos o descartados patrimonialmente',
                    'columnas' => $columnasStandard,
                    'filas' => $filas,
                    'total' => $items->count(),
                    'columnasOcultasDefecto' => $columnasOcultasDefecto,
                ];

            case 'historial-reparaciones':
            case 'tickets':
                $nroLia = trim((string)$request->input('nro_lia', ''));
                $nroUnsj = trim((string)$request->input('nro_unsj', ''));
                $elementoId = $request->input('elemento_id');

                $elemento = null;
                if ($elementoId) {
                    $elemento = Elemento::with(['tipo', 'ultimoMovimiento.ubicacion'])->find($elementoId);
                } elseif ($nroLia !== '') {
                    $elemento = Elemento::with(['tipo', 'ultimoMovimiento.ubicacion'])->where('nro_lia', $nroLia)->first();
                } elseif ($nroUnsj !== '') {
                    $elemento = Elemento::with(['tipo', 'ultimoMovimiento.ubicacion'])->where('nro_unsj', $nroUnsj)->first();
                }

                if ($elemento) {
                    $movimientosReparacion = Movimiento::where('elemento_id', $elemento->id)
                        ->where('tipo_movimiento', 'reparacion')
                        ->with(['ubicacion', 'usuario'])
                        ->get();

                    $movimientosBaja = Movimiento::where('elemento_id', $elemento->id)
                        ->where('tipo_movimiento', 'baja')
                        ->with(['ubicacion', 'usuario'])
                        ->get();

                    $primerTicket = $movimientosReparacion->sortBy('id')->first();
                    $movPrevio = null;
                    if ($primerTicket) {
                        $movPrevio = Movimiento::where('elemento_id', $elemento->id)
                            ->where('id', '<', $primerTicket->id)
                            ->with(['ubicacion', 'usuario'])
                            ->orderBy('id', 'desc')
                            ->first();
                    }

                    $ultimoPrestamo = Movimiento::where('elemento_id', $elemento->id)
                        ->whereNotNull('fecha_prestamo')
                        ->with(['ubicacion', 'usuario'])
                        ->orderBy('id', 'desc')
                        ->first();

                    $consolidado = collect();
                    if ($movPrevio) {
                        $consolidado->push([
                            'fecha' => $movPrevio->fecha,
                            'tipo' => 'Ubicación Previa a Taller',
                            'estado' => 'Ubicación Operativa Histórica',
                            'ubicacion' => "{$movPrevio->ubicacion?->nombre} (" . ($movPrevio->puesto ?: 'S/P') . ")",
                            'responsable' => $movPrevio->usuario?->nombre ?? 'Sistema',
                            'detalle' => $movPrevio->comentario ?: 'Movimiento anterior al reporte de falla',
                        ]);
                    }

                    if ($ultimoPrestamo && (!$movPrevio || $ultimoPrestamo->id !== $movPrevio->id)) {
                        $consolidado->push([
                            'fecha' => $ultimoPrestamo->fecha,
                            'tipo' => 'Último Préstamo Registrado',
                            'estado' => $ultimoPrestamo->fecha_devolucion ? 'Devuelto' : 'Prestado Activo',
                            'ubicacion' => 'Préstamo a Terceros / Cátedra',
                            'responsable' => $ultimoPrestamo->solicitante ?: 'Docente externo',
                            'detalle' => "Salida: " . ($ultimoPrestamo->fecha_prestamo ? Carbon::parse($ultimoPrestamo->fecha_prestamo)->format('d/m/Y') : '-') . 
                                         ($ultimoPrestamo->fecha_devolucion ? " | Retorno: " . Carbon::parse($ultimoPrestamo->fecha_devolucion)->format('d/m/Y') : ''),
                        ]);
                    }

                    foreach ($movimientosReparacion as $mr) {
                        $consolidado->push([
                            'fecha' => $mr->fecha,
                            'tipo' => 'Ticket de Reparación #' . $mr->id,
                            'estado' => ucfirst($mr->estado_ticket ?: 'Abierto'),
                            'ubicacion' => "{$mr->ubicacion?->nombre} (" . ($mr->puesto ?: 'S/P') . ")",
                            'responsable' => "Reportado por: " . ($mr->solicitante ?: ($mr->usuario?->nombre ?? 'Sistema')),
                            'detalle' => $mr->comentario,
                        ]);
                    }

                    foreach ($movimientosBaja as $mb) {
                        $consolidado->push([
                            'fecha' => $mb->fecha,
                            'tipo' => 'Baja Patrimonial',
                            'estado' => 'Basura Tecnológica',
                            'ubicacion' => "{$mb->ubicacion?->nombre} (" . ($mb->puesto ?: 'Descarte') . ")",
                            'responsable' => $mb->usuario?->nombre ?? 'Técnico LIA',
                            'detalle' => $mb->comentario,
                        ]);
                    }

                    $filas = $consolidado->sortByDesc('fecha')->values()->map(function ($c) {
                        return [
                            $c['fecha'] ? Carbon::parse($c['fecha'])->format('d/m/Y H:i') : '-',
                            $c['tipo'],
                            $c['estado'],
                            $c['ubicacion'],
                            $c['responsable'],
                            $c['detalle'],
                        ];
                    })->all();

                    return [
                        'titulo' => "Historial Consolidado de Tickets: {$elemento->nro_lia}",
                        'subtitulo' => "Auditoría de reparaciones, bajas, sala previa y último préstamo de {$elemento->marca} {$elemento->modelo} (" . ucfirst($elemento->tipo?->nombre ?? 'Activo') . ")",
                        'columnas' => ['Fecha / Hora', 'Concepto / Operación', 'Estado', 'Ubicación / Sala', 'Responsable / Informante', 'Detalle Técnico / Solución'],
                        'filas' => $filas,
                        'total' => count($filas),
                    ];
                }

                $query = Movimiento::whereIn('tipo_movimiento', ['reparacion', 'baja'])
                    ->with(['elemento.tipo', 'ubicacion', 'usuario'])
                    ->orderBy('fecha', 'desc');

                $items = $query->get();
                $filas = $items->map(function ($m) {
                    $el = $m->elemento;
                    $estado = $m->tipo_movimiento === 'baja' ? 'Baja / Basura Tec.' : ($m->estado_ticket ? ucfirst($m->estado_ticket) : 'En Taller');
                    return [
                        $el?->tipo?->nombre ?? 'N/A',
                        $el?->nro_lia ?? '-',
                        $el?->nro_unsj ?? '-',
                        $el?->marca ?? '-',
                        $el?->modelo ?? '-',
                        $m->ubicacion?->nombre ?? 'Taller de Hardware',
                        $m->puesto ?? '-',
                        $estado,
                        $m->solicitante ? "Inf: {$m->solicitante}" : ($m->usuario?->nombre ?? '-'),
                        $m->comentario ?? '-',
                    ];
                });

                return [
                    'titulo' => 'Historial General de Reparaciones y Tickets de Mantenimiento',
                    'subtitulo' => 'Auditoría cronológica de ingresos a taller técnico, intervenciones y bajas patrimoniales',
                    'columnas' => $columnasStandard,
                    'filas' => $filas,
                    'total' => $items->count(),
                ];

            case 'auditoria-sala':
                $ubicacionId = $request->ubicacion_id;
                $ubicacion = Ubicacion::find($ubicacionId) ?? Ubicacion::first();
                $items = Elemento::enUbicacion($ubicacion->id)->with(['tipo', 'ultimoMovimiento.ubicacion', 'valoresAtributos.atributo'])->orderBy('puesto', 'asc')->get();

                $filas = $items->map(function ($el) use ($atributos, $ubicacion) {
                    return $this->construirFilaElemento($el, $atributos, 'En Sala', '-', $ubicacion->nombre, $el->observaciones ?? '-');
                });

                return [
                    'titulo' => "Inventario por ubicación: {$ubicacion->nombre}",
                    'subtitulo' => "Listado de activos en {$ubicacion->nombre}.",
                    'columnas' => $columnasStandard,
                    'filas' => $filas,
                    'total' => $items->count(),
                    'columnasOcultasDefecto' => $columnasOcultasDefecto,
                ];

            case 'concepto-fechas':
                $desde = $request->fecha_desde ?: Carbon::now()->subYear()->toDateString();
                $hasta = $request->fecha_hasta ?: Carbon::now()->toDateString();

                $query = Movimiento::with(['elemento.tipo', 'ubicacion', 'usuario'])
                    ->whereBetween('fecha', [$desde, $hasta]);

                $items = $query->orderBy('fecha', 'desc')->get();
                $filas = $items->map(function ($m) {
                    $el = $m->elemento;
                    $situacion = $m->esPrestamo() ? 'Préstamo externo' : 'Movimiento interno';
                    return [
                        $el?->tipo?->nombre ?? 'N/A',
                        $el?->nro_lia ?? '-',
                        $el?->nro_unsj ?? '-',
                        $el?->marca ?? '-',
                        $el?->modelo ?? '-',
                        $m->ubicacion?->nombre ?? 'Préstamo externo',
                        $m->puesto ?? '-',
                        $situacion,
                        $m->solicitante ? "{$m->solicitante} (Desde: " . Carbon::parse($m->fecha_prestamo)->format('d/m/Y') . ")" : '-',
                        $m->comentario ?? '-',
                    ];
                });

                return [
                    'titulo' => 'Detalle de Elementos según Concepto / Préstamo entre Fechas',
                    'subtitulo' => "Periodo comprendido entre {$desde} y {$hasta}",
                    'columnas' => $columnasStandard,
                    'filas' => $filas,
                    'total' => $items->count(),
                ];

            case 'altas-fechas':
                $desde = $request->fecha_desde ?: Carbon::now()->subYears(2)->toDateString();
                $hasta = $request->fecha_hasta ?: Carbon::now()->toDateString();

                $items = Elemento::whereNotNull('fecha_adquisicion')
                    ->whereBetween('fecha_adquisicion', [$desde, $hasta])
                    ->with(['tipo', 'ultimoMovimiento.ubicacion', 'valoresAtributos.atributo'])
                    ->orderBy('fecha_adquisicion', 'desc')
                    ->get();

                $filas = $items->map(function ($el) use ($atributos) {
                    $sol = "Alta: " . Carbon::parse($el->fecha_adquisicion)->format('d/m/Y');
                    return $this->construirFilaElemento($el, $atributos, 'Registrado', $sol, null, $el->observaciones ?? '-');
                });

                return [
                    'titulo' => 'Informe de Altas de Elementos con Fechas',
                    'subtitulo' => "Ingresos y altas patrimoniales entre {$desde} y {$hasta}",
                    'columnas' => $columnasStandard,
                    'filas' => $filas,
                    'total' => $items->count(),
                    'columnasOcultasDefecto' => $columnasOcultasDefecto,
                ];

            case 'movimientos-fechas':
                $desde = $request->fecha_desde ?: Carbon::now()->subMonths(6)->toDateString();
                $hasta = $request->fecha_hasta ?: Carbon::now()->toDateString();

                $items = Movimiento::with(['elemento.tipo', 'ubicacion', 'usuario'])
                    ->whereBetween('fecha', [$desde, $hasta])
                    ->orderBy('fecha', 'desc')
                    ->get();

                $columnasMov = ['Fecha', 'Tipo Movimiento', 'Elemento (LIA/UNSJ)', 'Dispositivo', 'Ubicación / Puesto', 'Situación', 'Responsable', 'Solicitante / Comentario'];
                $filas = $items->map(function ($m) {
                    $el = $m->elemento;
                    $ident = "LIA: " . ($el?->nro_lia ?? '-') . " | UNSJ: " . ($el?->nro_unsj ?? '-');
                    $disp = ($el?->tipo?->nombre ?? '') . " " . ($el?->marca ?? '') . " " . ($el?->modelo ?? '');
                    return [
                        Carbon::parse($m->fecha)->format('d/m/Y H:i'),
                        $m->tipo_movimiento === 'externo_prestamo' ? 'Préstamo Externo' : 'Traslado Interno',
                        $ident,
                        trim($disp),
                        ($m->ubicacion?->nombre ?? 'Préstamo') . ($m->puesto ? " ({$m->puesto})" : ''),
                        $m->esPrestamo() ? 'Prestado' : 'En Sala',
                        $m->usuario?->nombre ?? 'Sistema',
                        $m->solicitante ? "Solicitante: {$m->solicitante}" : ($m->comentario ?? '-'),
                    ];
                });

                return [
                    'titulo' => 'Auditoría de movimientos entre fechas',
                    'subtitulo' => "Movimientos realizados entre {$desde} y {$hasta}.",
                    'columnas' => $columnasMov,
                    'filas' => $filas,
                    'total' => $items->count(),
                ];

            case 'historial-reparaciones':
                $elemento = $this->resolverElementoParaReporte($request);

                $items = $elemento ? Movimiento::where('elemento_id', $elemento->id)
                    ->with(['ubicacion', 'usuario'])
                    ->orderBy('fecha', 'desc')->get() : collect();

                $columnasRep = ['Fecha', 'Tipo Movimiento', 'Ubicación', 'Técnico Responsable', 'Diagnóstico / Comentario'];
                $filas = $items->map(function ($m) {
                    return [
                        Carbon::parse($m->fecha)->format('d/m/Y H:i'),
                        $m->tipo_movimiento === 'externo_prestamo' ? 'Préstamo Externo' : 'Traslado Interno',
                        $m->ubicacion?->nombre ?? '-',
                        $m->usuario?->nombre ?? '-',
                        $m->comentario ?? '-',
                    ];
                });

                $ident = $elemento ? "{$elemento->presenter()->identificadorCompleto()} ({$elemento->tipo?->nombre} {$elemento->marca} {$elemento->modelo})" : 'Ninguno';

                return [
                    'titulo' => "Historial de Reparación y Mantenimiento: {$ident}",
                    'subtitulo' => 'Intervenciones técnicas, diagnósticos y cambios operativos del equipo',
                    'columnas' => $columnasRep,
                    'filas' => $filas,
                    'total' => $items->count(),
                ];

            case 'donados-fechas':
                $desde = $request->fecha_desde ?: Carbon::now()->subYears(3)->toDateString();
                $hasta = $request->fecha_hasta ?: Carbon::now()->toDateString();

                $movimientosDonados = Movimiento::with(['elemento.tipo', 'ubicacion', 'usuario'])
                    ->whereBetween('fecha', [$desde, $hasta])
                    ->where(function ($q) {
                        $q->where('comentario', 'like', '%dona%')
                          ->orWhere('solicitante', 'like', '%dona%');
                    })
                    ->get();

                $elementosDonados = Elemento::whereBetween('fecha_adquisicion', [$desde, $hasta])
                    ->where(function ($q) {
                        $q->where('observaciones', 'like', '%dona%')
                          ->orWhere('descripcion', 'like', '%dona%');
                    })
                    ->with(['tipo', 'ultimoMovimiento.ubicacion'])
                    ->get();

                $columnasDonados = ['Fecha', 'Tipo', 'Nro LIA', 'Nro UNSJ', 'Marca / Modelo', 'Ubicación / Destino', 'Situación', 'Donante / Solicitante', 'Observaciones / Detalle'];
                $filas = collect();

                foreach ($elementosDonados as $el) {
                    $u = $el->ultimoMovimiento;
                    $filas->push([
                        $el->fecha_adquisicion ? Carbon::parse($el->fecha_adquisicion)->format('d/m/Y') : '-',
                        $el->tipo?->nombre ?? 'Activo',
                        $el->nro_lia ?? '-',
                        $el->nro_unsj ?? '-',
                        trim(($el->marca ?? '') . ' ' . ($el->modelo ?? '')),
                        $u?->ubicacion?->nombre ?? ($el->puesto ?: 'Depósito'),
                        'Alta por Donación',
                        'Donación Recibida',
                        $el->observaciones ?: ($el->descripcion_texto ?? '-'),
                    ]);
                }

                foreach ($movimientosDonados as $m) {
                    $el = $m->elemento;
                    $filas->push([
                        Carbon::parse($m->fecha)->format('d/m/Y'),
                        $el?->tipo?->nombre ?? 'Activo',
                        $el?->nro_lia ?? '-',
                        $el?->nro_unsj ?? '-',
                        trim(($el?->marca ?? '') . ' ' . ($el?->modelo ?? '')),
                        $m->ubicacion?->nombre ?? 'Destino Externo',
                        'Transferencia / Donación',
                        $m->solicitante ?: 'Donación Institucional',
                        $m->comentario ?? '-',
                    ]);
                }

                return [
                    'titulo' => 'Reporte de Activos Donados entre Fechas',
                    'subtitulo' => "Activos y transferencias por donación registradas entre {$desde} y {$hasta}",
                    'columnas' => $columnasDonados,
                    'filas' => $filas,
                    'total' => $filas->count(),
                ];

            case 'ingreso-estado-fechas':
                $desde = $request->fecha_desde ?: Carbon::now()->subYears(2)->toDateString();
                $hasta = $request->fecha_hasta ?: Carbon::now()->toDateString();

                $items = Elemento::whereNotNull('fecha_adquisicion')
                    ->whereBetween('fecha_adquisicion', [$desde, $hasta])
                    ->with(['tipo', 'ultimoMovimiento.ubicacion'])
                    ->orderBy('fecha_adquisicion', 'desc')
                    ->get();

                $columnasIngreso = ['Fecha Ingreso', 'Nro LIA', 'Nro UNSJ', 'Tipo', 'Marca y Modelo', 'Ubicación Actual', 'Puesto', 'Estado / Situación', 'Garantía', 'Observaciones'];

                $filas = $items->map(function ($el) {
                    $u = $el->ultimoMovimiento;
                    $situacion = 'En Sala / Operativo';
                    if ($u?->esPrestamo() && !$u?->fecha_devolucion) {
                        $situacion = 'Prestado (' . $u->diasPrestado() . ' d)';
                    } elseif ($el->tipo?->nombre === 'basura tecnologica') {
                        $situacion = 'Basura Tecnológica';
                    }

                    $garantia = 'Sin datos';
                    if ($el->fecha_vencimiento_garantia) {
                        $venc = Carbon::parse($el->fecha_vencimiento_garantia);
                        $garantia = $venc->isPast() ? 'Vencida' : 'Vigente';
                    }

                    return [
                        Carbon::parse($el->fecha_adquisicion)->format('d/m/Y'),
                        $el->nro_lia ?? '-',
                        $el->nro_unsj ?? '-',
                        $el->tipo?->nombre ?? 'N/A',
                        trim(($el->marca ?? '') . ' ' . ($el->modelo ?? '')),
                        $u?->ubicacion?->nombre ?? ($u?->esPrestamo() ? 'Préstamo Externo' : 'Sin asignar'),
                        $u?->puesto ?: ($el->puesto ?? '-'),
                        $situacion,
                        $garantia,
                        $el->observaciones ?: ($el->descripcion_texto ?? '-'),
                    ];
                });

                return [
                    'titulo' => 'Estado de Activos según Fecha de Ingreso al LIA',
                    'subtitulo' => "Situación operativa de equipos incorporados entre {$desde} y {$hasta}",
                    'columnas' => $columnasIngreso,
                    'filas' => $filas,
                    'total' => $items->count(),
                ];

            case 'garantias-fechas':
                $desde = $request->fecha_desde ?: Carbon::now()->subMonths(6)->toDateString();
                $hasta = $request->fecha_hasta ?: Carbon::now()->addYear()->toDateString();

                $items = Elemento::whereNotNull('fecha_vencimiento_garantia')
                    ->whereBetween('fecha_vencimiento_garantia', [$desde, $hasta])
                    ->with(['tipo', 'ultimoMovimiento.ubicacion'])
                    ->orderBy('fecha_vencimiento_garantia', 'asc')
                    ->get();

                $columnasGarantias = ['Vencimiento Garantía', 'Nro LIA', 'Nro UNSJ', 'Tipo', 'Marca y Modelo', 'Ubicación / Puesto', 'Estado Garantía', 'Tiempo Relativo', 'Observaciones'];

                $hoy = Carbon::today();
                $filas = $items->map(function ($el) use ($hoy) {
                    $u = $el->ultimoMovimiento;
                    $venc = Carbon::parse($el->fecha_vencimiento_garantia);
                    $esVencida = $venc->isPast();
                    $estado = $esVencida ? 'Vencida' : 'Vigente';
                    $relativo = $esVencida
                        ? 'Venció hace ' . $venc->diffForHumans($hoy, ['parts' => 1, 'syntax' => Carbon::DIFF_ABSOLUTE])
                        : 'Vence en ' . $hoy->diffForHumans($venc, ['parts' => 1, 'syntax' => Carbon::DIFF_ABSOLUTE]);

                    return [
                        $venc->format('d/m/Y'),
                        $el->nro_lia ?? '-',
                        $el->nro_unsj ?? '-',
                        $el->tipo?->nombre ?? 'N/A',
                        trim(($el->marca ?? '') . ' ' . ($el->modelo ?? '')),
                        ($u?->ubicacion?->nombre ?? 'Sin asignar') . ($u?->puesto ? " ({$u->puesto})" : ''),
                        $estado,
                        $relativo,
                        $el->observaciones ?: ($el->descripcion_texto ?? '-'),
                    ];
                });

                return [
                    'titulo' => 'Reporte de Estado de Garantías de Activos',
                    'subtitulo' => "Equipos con vencimiento de garantía entre {$desde} y {$hasta}",
                    'columnas' => $columnasGarantias,
                    'filas' => $filas,
                    'total' => $items->count(),
                ];

            case 'ubicacion-trazabilidad':
                $elemento = $this->resolverElementoParaReporte($request);

                $items = $elemento ? Movimiento::where('elemento_id', $elemento->id)
                    ->with(['ubicacion', 'usuario'])
                    ->orderBy('fecha', 'desc')->get() : collect();

                $columnasTraza = ['Fecha / Hora', 'Tipo Evento', 'Ubicación / Puesto', 'Situación', 'Solicitante / Préstamo', 'Notas'];
                $filas = $items->map(function ($m, $idx) {
                    $actualTag = ($idx === 0) ? ' ★ Actual' : '';
                    return [
                        Carbon::parse($m->fecha)->format('d/m/Y H:i'),
                        ($m->tipo_movimiento === 'externo_prestamo' ? 'Préstamo Externo' : 'Movimiento Interno') . $actualTag,
                        ($m->ubicacion?->nombre ?? 'Préstamo') . ($m->puesto ? " ({$m->puesto})" : ''),
                        $m->esPrestamo() ? 'Prestado' : 'En Sala',
                        $m->solicitante ?: '-',
                        $m->comentario ?: '-',
                    ];
                });

                $ident = $elemento ? "{$elemento->presenter()->identificadorCompleto()} ({$elemento->tipo?->nombre} {$elemento->marca} {$elemento->modelo})" : 'Ninguno';

                return [
                    'titulo' => "Ubicación e Historial de Movimientos: {$ident}",
                    'subtitulo' => 'Ubicación exacta e historial de movimiento del activo.',
                    'columnas' => $columnasTraza,
                    'filas' => $filas,
                    'total' => $items->count(),
                    'elemento' => $elemento,
                    'ubicacionActual' => $elemento?->presenter()->puestoUbicacion(),
                    'situacionBadge' => $elemento?->presenter()->statusBadge(),
                ];

            case 'parametrizable':
            default:
                $reporteConfigurado = ReporteConfigurado::where('slug', $tipo)->first();
                if ($reporteConfigurado) {
                    return $this->ejecutarReporteConfigurado($reporteConfigurado, $request, $atributos, $columnasStandard, $columnasOcultasDefecto);
                }

                $query = Elemento::with(['tipo', 'ultimoMovimiento.ubicacion', 'valoresAtributos.atributo']);
                if ($request->filled('tipo_id')) $query->where('tipo_id', $request->tipo_id);
                if ($request->filled('ubicacion_id')) $query->whereHas('ultimoMovimiento', fn($q) => $q->where('ubicacion_id', $request->ubicacion_id));
                if ($request->filled('puesto')) {
                    $puesto = trim($request->input('puesto'));
                    $query->where(function ($q) use ($puesto) {
                        $q->whereHas('ultimoMovimiento', fn($mq) => $mq->where('puesto', $puesto))
                          ->orWhere('puesto', $puesto);
                    });
                }
                if ($request->filled('anio')) $query->where('anio_compra', $request->anio);

                $items = $query->orderBy('id', 'asc')->get();
                $filas = $items->map(function ($el) use ($atributos) {
                    return $this->construirFilaElemento($el, $atributos);
                });

                return [
                    'titulo' => 'Listado Detallado Parametrizable Multi-Criterio',
                    'subtitulo' => 'Filtrado dinámico por fecha, ubicación, tipo y garantías',
                    'columnas' => $columnasStandard,
                    'filas' => $filas,
                    'total' => $items->count(),
                    'columnasOcultasDefecto' => $columnasOcultasDefecto,
                ];
        }
    }

    /**
     * Ejecuta un reporte configurado aplicando de forma combinada sus filtros fijos y parámetros del request.
     */
    private function ejecutarReporteConfigurado(
        ReporteConfigurado $reporte,
        Request $request,
        array $atributos,
        array $columnasStandard,
        array $columnasOcultasDefecto
    ): array {
        $filtros = $reporte->filtros_fijos ?? [];

        // 1. Resolución robusta de Tipo: soporta tipo_id, tipo (nombre o slug) o filtro search coincidente
        $tipoId = $request->input('tipo_id', $filtros['tipo_id'] ?? ($filtros['tipo'] ?? null));
        if (!empty($tipoId) && !is_numeric($tipoId)) {
            $tipoModel = Tipo::where('nombre', $tipoId)->orWhere('slug', $tipoId)->first();
            $tipoId = $tipoModel?->id;
        }

        // 2. Resolución de Ubicación: sólo filtra si se especifica expresamente (no fuerza ubicacion_id = 1)
        $ubicacionId = $request->input('ubicacion_id', $filtros['ubicacion_id'] ?? ($filtros['ubicacion'] ?? null));
        if (!empty($ubicacionId) && !is_numeric($ubicacionId)) {
            $ubModel = Ubicacion::where('nombre', $ubicacionId)->orWhere('slug', $ubicacionId)->first();
            $ubicacionId = $ubModel?->id;
        }

        $puesto = $request->input('puesto', $filtros['puesto'] ?? null);
        $situacion = $request->input('situacion', $filtros['situacion'] ?? null);
        $anio = $request->input('anio', $filtros['anio'] ?? null);
        $search = $request->input('search', $filtros['search'] ?? null);
        $nroLia = $request->input('nro_lia', $filtros['nro_lia'] ?? null);
        $fechaDesde = $request->input('fecha_desde', $filtros['fecha_desde'] ?? null);
        $fechaHasta = $request->input('fecha_hasta', $filtros['fecha_hasta'] ?? null);

        // Si search o filtro fijo coincide con un tipo de activo y no hay tipoId explícito,
        // resolver como filtro específico por tipo_id para encontrar todos los activos del tipo
        if (!empty($search) && empty($tipoId)) {
            $terminoNormalizado = strtolower(trim($search));
            $tipoCoincidente = Tipo::where('nombre', $terminoNormalizado)
                ->orWhere('nombre', rtrim($terminoNormalizado, 's'))
                ->first();
            if ($tipoCoincidente) {
                $tipoId = $tipoCoincidente->id;
                // Si el filtro search configurado coincidía con el nombre de tipo, limpiar search
                if (isset($filtros['search']) && strtolower(trim($filtros['search'])) === $terminoNormalizado) {
                    $search = null;
                }
            }
        }

        $query = Elemento::with(['tipo', 'ultimoMovimiento.ubicacion', 'valoresAtributos.atributo']);

        if (!empty($tipoId)) {
            $query->where('tipo_id', $tipoId);
        }

        if (!empty($ubicacionId)) {
            $query->whereHas('ultimoMovimiento', fn($q) => $q->where('ubicacion_id', $ubicacionId));
        }

        if (!empty($puesto)) {
            $puesto = trim($puesto);
            $query->where(function ($q) use ($puesto) {
                $q->whereHas('ultimoMovimiento', fn($mq) => $mq->where('puesto', $puesto))
                  ->orWhere('puesto', $puesto);
            });
        }

        if (!empty($situacion)) {
            if ($situacion === 'operativo') {
                $query->disponibles();
            } elseif ($situacion === 'prestado') {
                $query->prestados();
            } elseif ($situacion === 'baja') {
                $query->bajas();
            } elseif ($situacion === 'en_reparacion') {
                $query->whereHas('ultimoMovimiento', fn($q) => $q->where('tipo_movimiento', 'reparacion')->whereNull('fecha_devolucion'));
            }
        }

        if (!empty($anio)) {
            $query->where('anio_compra', $anio);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nro_lia', 'like', "%{$search}%")
                  ->orWhere('nro_unsj', 'like', "%{$search}%")
                  ->orWhere('marca', 'like', "%{$search}%")
                  ->orWhere('modelo', 'like', "%{$search}%")
                  ->orWhere('observaciones', 'like', "%{$search}%")
                  ->orWhereHas('tipo', function ($qt) use ($search) {
                      $qt->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($nroLia)) {
            $query->where('nro_lia', 'like', "%{$nroLia}%");
        }

        if (!empty($fechaDesde) && !empty($fechaHasta)) {
            $query->whereBetween('fecha_adquisicion', [$fechaDesde, $fechaHasta]);
        } elseif (!empty($fechaDesde)) {
            $query->where('fecha_adquisicion', '>=', $fechaDesde);
        } elseif (!empty($fechaHasta)) {
            $query->where('fecha_adquisicion', '<=', $fechaHasta);
        }

        $items = $query->orderBy('id', 'asc')->get();
        $filas = $items->map(function ($el) use ($atributos) {
            return $this->construirFilaElemento($el, $atributos);
        });

        return [
            'titulo' => $reporte->titulo,
            'subtitulo' => $reporte->subtitulo ?: $reporte->descripcion,
            'columnas' => $columnasStandard,
            'filas' => $filas,
            'total' => $items->count(),
            'columnasOcultasDefecto' => $columnasOcultasDefecto,
        ];
    }

    /**
     * Resuelve el activo para los reportes específicos a partir del Nro LIA, Nro UNSJ o ID.
     */
    private function resolverElementoParaReporte(Request $request): ?Elemento
    {
        // 1. Si especificó ID numérico directo (retrocompatibilidad)
        if ($request->filled('elemento_id')) {
            return Elemento::find($request->elemento_id);
        }

        // 2. Si especificó N° LIA y/o N° UNSJ
        $nroLia = trim((string)$request->input('nro_lia', ''));
        $nroUnsj = trim((string)$request->input('nro_unsj', ''));

        if ($nroLia !== '' && $nroUnsj !== '') {
            $el = Elemento::where('nro_lia', $nroLia)->where('nro_unsj', $nroUnsj)->first();
            if ($el) return $el;
        }

        if ($nroLia !== '') {
            $coincidencias = Elemento::where('nro_lia', $nroLia)->get();
            if ($coincidencias->isNotEmpty()) {
                return $coincidencias->first();
            }
        }

        if ($nroUnsj !== '') {
            $el = Elemento::where('nro_unsj', $nroUnsj)->first();
            if ($el) return $el;
        }

        return Elemento::first();
    }
}
