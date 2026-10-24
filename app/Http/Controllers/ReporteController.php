<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Ubicacion;
use App\Models\Estado;
use App\Models\Tipo;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ReporteController extends Controller
{
    /**
     * Hub principal de selección de reportes.
     */
    public function index(): View
    {
        $ubicaciones = Ubicacion::orderBy('nombre')->get();
        $estados = Estado::orderBy('nombre')->get();
        $tipos = Tipo::orderBy('nombre')->get();

        return view('reportes.index', compact('ubicaciones', 'estados', 'tipos'));
    }

    /**
     * Vista de reporte individual sin paginación, preparada para pantalla e impresión.
     */
    public function show(string $tipo, Request $request): View
    {
        $data = $this->obtenerDatosReporte($tipo, $request);

        return view('reportes.show', array_merge($data, [
            'tipoReporte' => $tipo,
            'request' => $request,
        ]));
    }

    /**
     * Exportación masiva a archivo CSV sin paginar.
     */
    public function exportCsv(string $tipo, Request $request): StreamedResponse
    {
        $data = $this->obtenerDatosReporte($tipo, $request);
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
            $columnas = $data['columnas'] ?? ['Tipo', 'Nro LIA', 'Nro UNSJ', 'Marca', 'Modelo', 'Ubicación', 'Puesto', 'Estado', 'Solicitante / Obs'];
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
        $query = Elemento::enUbicacion($ubicacion->id)->with(['tipo', 'ultimoMovimiento.estado']);

        if ($request->filled('tipo_id')) {
            $query->where('tipo_id', $request->tipo_id);
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
     * Construcción de dataset según el reporte solicitado.
     */
    private function obtenerDatosReporte(string $tipo, Request $request): array
    {
        $columnasStandard = [
            'Tipo', 'Nro LIA', 'Nro UNSJ', 'Marca', 'Modelo', 'Ubicación', 'Puesto', 'Estado', 'Solicitante', 'Garantía / Obs'
        ];

        switch ($tipo) {
            case 'inventario-completo':
                $query = Elemento::with(['tipo', 'ultimoMovimiento.ubicacion', 'ultimoMovimiento.estado']);
                if ($request->filled('tipo_id')) $query->where('tipo_id', $request->tipo_id);
                if ($request->filled('estado_id')) $query->whereHas('ultimoMovimiento', fn($q) => $q->where('estado_id', $request->estado_id));

                $items = $query->orderBy('id', 'asc')->get();
                $filas = $items->map(function ($el) {
                    $u = $el->ultimoMovimiento;
                    return [
                        $el->tipo?->nombre ?? 'N/A',
                        $el->nro_lia ?? '-',
                        $el->nro_unsj ?? '-',
                        $el->marca ?? '-',
                        $el->modelo ?? '-',
                        $u?->ubicacion?->nombre ?? 'Sin asignar',
                        $u?->puesto ?: ($el->puesto ?? '-'),
                        $u?->estado?->nombre ?? 'Desconocido',
                        $u?->solicitante ?? '-',
                        $el->observaciones ?: ($el->descripcion_texto ?? '-'),
                    ];
                });

                return [
                    'titulo' => 'Listado de Inventario Completo (~1.150 Activos)',
                    'subtitulo' => 'Catálogo exhaustivo de activos del Laboratorio de Informática Aplicada',
                    'columnas' => $columnasStandard,
                    'filas' => $filas,
                    'total' => $items->count(),
                ];

            case 'auditoria-sala':
                $ubicacionId = $request->ubicacion_id;
                $ubicacion = Ubicacion::find($ubicacionId) ?? Ubicacion::first();
                $items = Elemento::enUbicacion($ubicacion->id)->with(['tipo', 'ultimoMovimiento.estado'])->orderBy('puesto', 'asc')->get();

                $filas = $items->map(function ($el) use ($ubicacion) {
                    $u = $el->ultimoMovimiento;
                    return [
                        $el->tipo?->nombre ?? 'N/A',
                        $el->nro_lia ?? '-',
                        $el->nro_unsj ?? '-',
                        $el->marca ?? '-',
                        $el->modelo ?? '-',
                        $ubicacion->nombre,
                        $u?->puesto ?: ($el->puesto ?? '-'),
                        $u?->estado?->nombre ?? 'Desconocido',
                        '-',
                        $el->observaciones ?? '-',
                    ];
                });

                return [
                    'titulo' => "Auditoría de Ubicación: {$ubicacion->nombre}",
                    'subtitulo' => 'Verificación física y control patrimonial en sala/armario',
                    'columnas' => $columnasStandard,
                    'filas' => $filas,
                    'total' => $items->count(),
                ];

            case 'concepto-fechas':
                $estadoId = $request->estado_id;
                $desde = $request->fecha_desde ?: Carbon::now()->subYear()->toDateString();
                $hasta = $request->fecha_hasta ?: Carbon::now()->toDateString();

                $query = Movimiento::with(['elemento.tipo', 'ubicacion', 'estado', 'usuario'])
                    ->whereBetween('fecha', [$desde, $hasta]);

                if ($estadoId) {
                    $query->where('estado_id', $estadoId);
                } else {
                    // Si no especifica estado, por defecto mostrar prestados y bajas
                    $query->whereHas('estado', fn($q) => $q->whereIn('nombre', ['prestado', 'dado de baja', 'donado', 'transferido']));
                }

                $items = $query->orderBy('fecha', 'desc')->get();
                $filas = $items->map(function ($m) {
                    $el = $m->elemento;
                    return [
                        $el?->tipo?->nombre ?? 'N/A',
                        $el?->nro_lia ?? '-',
                        $el?->nro_unsj ?? '-',
                        $el?->marca ?? '-',
                        $el?->modelo ?? '-',
                        $m->ubicacion?->nombre ?? 'Préstamo externo',
                        $m->puesto ?? '-',
                        $m->estado?->nombre ?? 'N/A',
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
                    ->with(['tipo', 'ultimoMovimiento.ubicacion', 'ultimoMovimiento.estado'])
                    ->orderBy('fecha_adquisicion', 'desc')
                    ->get();

                $filas = $items->map(function ($el) {
                    $u = $el->ultimoMovimiento;
                    return [
                        $el->tipo?->nombre ?? 'N/A',
                        $el->nro_lia ?? '-',
                        $el->nro_unsj ?? '-',
                        $el->marca ?? '-',
                        $el->modelo ?? '-',
                        $u?->ubicacion?->nombre ?? '-',
                        $u?->puesto ?? '-',
                        $u?->estado?->nombre ?? '-',
                        "Alta: " . Carbon::parse($el->fecha_adquisicion)->format('d/m/Y'),
                        $el->observaciones ?? '-',
                    ];
                });

                return [
                    'titulo' => 'Informe de Altas de Elementos con Fechas',
                    'subtitulo' => "Ingresos y altas patrimoniales entre {$desde} y {$hasta}",
                    'columnas' => $columnasStandard,
                    'filas' => $filas,
                    'total' => $items->count(),
                ];

            case 'movimientos-fechas':
                $desde = $request->fecha_desde ?: Carbon::now()->subMonths(6)->toDateString();
                $hasta = $request->fecha_hasta ?: Carbon::now()->toDateString();

                $items = Movimiento::with(['elemento.tipo', 'ubicacion', 'estado', 'usuario'])
                    ->whereBetween('fecha', [$desde, $hasta])
                    ->orderBy('fecha', 'desc')
                    ->get();

                $columnasMov = ['Fecha', 'Tipo Movimiento', 'Elemento (LIA/UNSJ)', 'Dispositivo', 'Ubicación / Puesto', 'Estado', 'Responsable', 'Solicitante / Comentario'];
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
                        $m->estado?->nombre ?? '-',
                        $m->usuario?->nombre ?? 'Sistema',
                        $m->solicitante ? "Solicitante: {$m->solicitante}" : ($m->comentario ?? '-'),
                    ];
                });

                return [
                    'titulo' => 'Detalle Histórico de Movimientos entre Fechas',
                    'subtitulo' => "Bitácora cronológica de traslados y préstamos entre {$desde} y {$hasta}",
                    'columnas' => $columnasMov,
                    'filas' => $filas,
                    'total' => $items->count(),
                ];

            case 'historial-reparaciones':
                $elementoId = $request->elemento_id;
                $elemento = $elementoId ? Elemento::find($elementoId) : Elemento::first();

                $items = $elemento ? Movimiento::where('elemento_id', $elemento->id)
                    ->whereHas('estado', fn($q) => $q->whereIn('nombre', ['en reparacion', 'verificar funcionamiento', 'funcionando']))
                    ->with(['estado', 'ubicacion', 'usuario'])
                    ->orderBy('fecha', 'desc')->get() : collect();

                $columnasRep = ['Fecha', 'Estado Registrado', 'Ubicación', 'Técnico Responsable', 'Diagnóstico / Comentario'];
                $filas = $items->map(function ($m) {
                    return [
                        Carbon::parse($m->fecha)->format('d/m/Y H:i'),
                        $m->estado?->nombre ?? '-',
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

            case 'ubicacion-trazabilidad':
                $elementoId = $request->elemento_id;
                $elemento = $elementoId ? Elemento::find($elementoId) : Elemento::first();

                $items = $elemento ? Movimiento::where('elemento_id', $elemento->id)
                    ->with(['estado', 'ubicacion', 'usuario'])
                    ->orderBy('fecha', 'desc')->get() : collect();

                $columnasTraza = ['Fecha / Hora', 'Tipo Evento', 'Ubicación / Puesto', 'Estado', 'Solicitante / Préstamo', 'Notas'];
                $filas = $items->map(function ($m) {
                    return [
                        Carbon::parse($m->fecha)->format('d/m/Y H:i'),
                        $m->tipo_movimiento === 'externo_prestamo' ? 'Préstamo Externo' : 'Movimiento Interno',
                        ($m->ubicacion?->nombre ?? 'Préstamo') . ($m->puesto ? " ({$m->puesto})" : ''),
                        $m->estado?->nombre ?? '-',
                        $m->solicitante ?: '-',
                        $m->comentario ?: '-',
                    ];
                });

                $ident = $elemento ? "{$elemento->presenter()->identificadorCompleto()} ({$elemento->tipo?->nombre} {$elemento->marca} {$elemento->modelo})" : 'Ninguno';

                return [
                    'titulo' => "Trazabilidad Completa del Activo: {$ident}",
                    'subtitulo' => 'Línea de tiempo inmutable de todos los traslados y préstamos desde su alta',
                    'columnas' => $columnasTraza,
                    'filas' => $filas,
                    'total' => $items->count(),
                ];

            case 'parametrizable':
            default:
                $query = Elemento::with(['tipo', 'ultimoMovimiento.ubicacion', 'ultimoMovimiento.estado']);
                if ($request->filled('tipo_id')) $query->where('tipo_id', $request->tipo_id);
                if ($request->filled('ubicacion_id')) $query->whereHas('ultimoMovimiento', fn($q) => $q->where('ubicacion_id', $request->ubicacion_id));
                if ($request->filled('estado_id')) $query->whereHas('ultimoMovimiento', fn($q) => $q->where('estado_id', $request->estado_id));
                if ($request->filled('anio')) $query->where('anio_compra', $request->anio);

                $items = $query->orderBy('id', 'asc')->get();
                $filas = $items->map(function ($el) {
                    $u = $el->ultimoMovimiento;
                    return [
                        $el->tipo?->nombre ?? 'N/A',
                        $el->nro_lia ?? '-',
                        $el->nro_unsj ?? '-',
                        $el->marca ?? '-',
                        $el->modelo ?? '-',
                        $u?->ubicacion?->nombre ?? 'Sin asignar',
                        $u?->puesto ?: ($el->puesto ?? '-'),
                        $u?->estado?->nombre ?? 'Desconocido',
                        $u?->solicitante ?? '-',
                        $el->observaciones ?: ($el->descripcion_texto ?? '-'),
                    ];
                });

                return [
                    'titulo' => 'Listado Detallado Parametrizable Multi-Criterio',
                    'subtitulo' => 'Filtrado dinámico por fecha, ubicación, tipo y garantías',
                    'columnas' => $columnasStandard,
                    'filas' => $filas,
                    'total' => $items->count(),
                ];
        }
    }
}
