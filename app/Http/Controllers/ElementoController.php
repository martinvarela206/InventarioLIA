<?php

namespace App\Http\Controllers;

use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use App\Http\Requests\StoreElementoRequest;
use App\Http\Requests\UpdateElementoRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Atributo;
use App\Models\ElementoAtributoValor;

use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ElementoController extends Controller
{
    /**
     * Construye la consulta filtrada de inventario completo reutilizable.
     */
    private function construirConsultaInventario(Request $request): Builder
    {
        $query = Elemento::with(['tipo', 'ultimoMovimiento.ubicacion', 'valoresAtributos.atributo']);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->buscarIdentificador($search);
        }

        if ($request->filled('tipo')) {
            $query->whereHas('tipo', function ($q) use ($request) {
                $q->where('nombre', $request->tipo)->orWhere('id', $request->tipo);
            });
        }

        if ($request->filled('ubicacion')) {
            if ($request->ubicacion === 'prestamo_externo') {
                $query->whereHas('ultimoMovimiento', function ($q) {
                    $q->whereNotNull('fecha_prestamo')->whereNull('fecha_devolucion');
                });
            } else {
                $query->whereHas('ultimoMovimiento', function ($q) use ($request) {
                    $q->where('ubicacion_id', $request->ubicacion);
                });
            }
        }

        if ($request->filled('situacion')) {
            if ($request->situacion === 'operativo') {
                $query->disponibles();
            } elseif ($request->situacion === 'prestado') {
                $query->prestadosActivos();
            } elseif ($request->situacion === 'baja') {
                $query->obsoletos();
            } elseif ($request->situacion === 'reparacion') {
                $query->enReparacion();
            }
        }

        if ($request->filled('anio')) {
            $query->where('anio_compra', $request->anio);
        }

        if ($request->filled('gb')) {
            $gb = $request->input('gb');
            $query->where(function ($q) use ($gb) {
                $q->where('descripcion', 'like', "%{$gb}%")
                  ->orWhereHas('valoresAtributos', function ($vaq) use ($gb) {
                      $vaq->where('valor', 'like', "%{$gb}%");
                  });
            });
        }

        if ($request->filled('garantia')) {
            $hoy = Carbon::today()->toDateString();
            if ($request->garantia === 'vigente') {
                $query->whereNotNull('fecha_vencimiento_garantia')->where('fecha_vencimiento_garantia', '>=', $hoy);
            } elseif ($request->garantia === 'vencida') {
                $query->whereNotNull('fecha_vencimiento_garantia')->where('fecha_vencimiento_garantia', '<', $hoy);
            } elseif ($request->garantia === 'sin_datos') {
                $query->whereNull('fecha_vencimiento_garantia');
            }
        }

        return $query;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = $this->construirConsultaInventario($request);

        $elementos = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $tipos = Tipo::orderBy('nombre')->get();
        $ubicaciones = Ubicacion::orderBy('nombre')->get();
        $anios = Elemento::whereNotNull('anio_compra')->distinct()->orderBy('anio_compra', 'desc')->pluck('anio_compra');

        return view('elementos.index', compact('elementos', 'tipos', 'ubicaciones', 'anios'));
    }

    /**
     * Exportación a CSV del inventario filtrado.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $query = $this->construirConsultaInventario($request);
        $items = $query->orderBy('id', 'asc')->get();

        $columnas = ['Tipo', 'Nro LIA', 'Nro UNSJ', 'Marca', 'Modelo', 'Nro Serie', 'Ubicación', 'Puesto', 'Situación', 'Solicitante / Préstamo', 'Garantía', 'Año Compra', 'Observaciones'];

        $filas = $items->map(function ($el) {
            $u = $el->ultimoMovimiento;
            $situacion = 'En Sala';
            if ($u?->esPrestamo() && !$u?->fecha_devolucion) {
                $situacion = 'Prestado (' . $u->diasPrestado() . ' d)';
            } elseif ($u?->tipo_movimiento === 'baja' || $el->tipo?->nombre === 'basura tecnologica') {
                $situacion = 'Basura Tecnológica / De Baja';
            } elseif ($u?->esTicketAbierto()) {
                $situacion = 'En Reparación';
            }

            $garantia = 'Sin datos';
            if ($el->fecha_vencimiento_garantia) {
                $venc = Carbon::parse($el->fecha_vencimiento_garantia);
                $garantia = $venc->isPast() ? 'Vencida (' . $venc->format('d/m/Y') . ')' : 'Vigente (' . $venc->format('d/m/Y') . ')';
            }

            return [
                $el->tipo?->nombre ?? 'N/A',
                $el->nro_lia ?? '-',
                $el->nro_unsj ?? '-',
                $el->marca ?? '-',
                $el->modelo ?? '-',
                $el->nro_serie ?? '-',
                $u?->ubicacion?->nombre ?? ($u?->esPrestamo() ? 'Préstamo Externo' : 'Sin asignar'),
                $u?->puesto ?: ($el->puesto ?? '-'),
                $situacion,
                $u?->solicitante ?? '-',
                $garantia,
                $el->anio_compra ? (string)$el->anio_compra : '-',
                $el->observaciones ?: ($el->descripcion_texto ?? '-'),
            ];
        });

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Inventario_Completo_' . date('Ymd_His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($filas, $columnas) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, $columnas, ';');
            foreach ($filas as $fila) {
                fputcsv($handle, $fila, ';');
            }
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Vista de impresión / PDF del inventario completo filtrado.
     */
    public function imprimir(Request $request): View
    {
        $query = $this->construirConsultaInventario($request);
        $items = $query->orderBy('id', 'asc')->get();

        $columnas = ['Tipo', 'Nro LIA', 'Nro UNSJ', 'Marca', 'Modelo', 'Ubicación', 'Puesto', 'Situación', 'Solicitante / Préstamo', 'Garantía / Obs'];

        $filas = $items->map(function ($el) {
            $u = $el->ultimoMovimiento;
            $situacion = 'En Sala';
            if ($u?->esPrestamo() && !$u?->fecha_devolucion) {
                $situacion = 'Prestado (' . $u->diasPrestado() . ' d)';
            } elseif ($u?->tipo_movimiento === 'baja' || $el->tipo?->nombre === 'basura tecnologica') {
                $situacion = 'Basura Tecnológica / De Baja';
            } elseif ($u?->esTicketAbierto()) {
                $situacion = 'En Reparación';
            }

            return [
                $el->tipo?->nombre ?? 'N/A',
                $el->nro_lia ?? '-',
                $el->nro_unsj ?? '-',
                $el->marca ?? '-',
                $el->modelo ?? '-',
                $u?->ubicacion?->nombre ?? ($u?->esPrestamo() ? 'Préstamo Externo' : 'Sin asignar'),
                $u?->puesto ?: ($el->puesto ?? '-'),
                $situacion,
                $u?->solicitante ?? '-',
                $el->observaciones ?: ($el->descripcion_texto ?? '-'),
            ];
        });

        return view('reportes.show', [
            'titulo' => 'Informe de Inventario Completo',
            'subtitulo' => 'Reporte consolidado generado a partir de los filtros activos del catálogo',
            'columnas' => $columnas,
            'filas' => $filas,
            'tipoReporte' => 'inventario-completo',
            'request' => $request,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        if (!auth()->user()?->canManageElementos()) {
            abort(403, 'Acceso restringido a administradores de inventario (item_admin).');
        }

        $tipos = Tipo::with('atributos')->orderBy('nombre')->get();
        $ubicaciones = Ubicacion::orderBy('nombre')->get();

        return view('elementos.create', compact('tipos', 'ubicaciones'));
    }

    /**
     * Store a newly created resource in storage and guarantee initial movement with ACID atomicity.
     */
    public function store(StoreElementoRequest $request): RedirectResponse
    {
        if (!auth()->user()?->canManageElementos()) {
            abort(403, 'Acceso restringido a administradores de inventario (item_admin).');
        }

        $validated = $request->validated();

        $elemento = DB::transaction(function () use ($request, $validated) {
            $cleanLia = Elemento::normalizarIdentificador($validated['nro_lia'] ?? null);
            $cleanUnsj = Elemento::normalizarIdentificador($validated['nro_unsj'] ?? null);

            $descripcion = Elemento::parsearDescripcion(
                $request->input('descripcion_keys', []),
                $request->input('descripcion_values', [])
            );

            $elemento = Elemento::create([
                'nro_lia' => $cleanLia,
                'nro_unsj' => $cleanUnsj,
                'tipo_id' => $validated['tipo_id'],
                'marca' => $validated['marca'] ?? null,
                'modelo' => $validated['modelo'] ?? null,
                'nro_serie' => $validated['nro_serie'] ?? null,
                'anio_compra' => $validated['anio_compra'] ?? null,
                'puesto' => $validated['puesto'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'descripcion' => $descripcion,
                'cantidad' => $validated['cantidad'],
                'fecha_adquisicion' => $validated['fecha_adquisicion'] ?? now()->toDateString(),
                'fecha_vencimiento_garantia' => $validated['fecha_vencimiento_garantia'] ?? null,
            ]);

            // Guardar atributos específicos dinámicos en la tabla pivot
            if ($request->has('atributos') && is_array($request->atributos)) {
                foreach ($request->atributos as $attrId => $valor) {
                    $valLimpio = trim((string)$valor);
                    if ($valLimpio !== '') {
                        ElementoAtributoValor::create([
                            'elemento_id' => $elemento->id,
                            'atributo_id' => $attrId,
                            'valor' => $valLimpio,
                        ]);
                    }
                }
            }

            // GARANTIZAR MOVIMIENTO INICIAL AUTOMÁTICO
            $defaultUbicacion = Ubicacion::where('nombre', 'Armario de HW')->first() ?? Ubicacion::first();
            $ubicacionId = !empty($validated['ubicacion_id']) ? $validated['ubicacion_id'] : $defaultUbicacion->id;

            Movimiento::create([
                'elemento_id' => $elemento->id,
                'tipo_movimiento' => 'interno',
                'ubicacion_id' => $ubicacionId,
                'puesto' => $validated['puesto'] ?? null,
                'user_id' => auth()->id() ?? Usuario::first()->id,
                'fecha' => now(),
                'comentario' => 'Alta inicial del elemento en el inventario',
            ]);

            return $elemento;
        });

        return redirect()->route('elementos.show', $elemento->id)
            ->with('success', 'Elemento dado de alta exitosamente con su movimiento inicial registrado.');
    }

    /**
     * Display the specified resource in Dual-Pane inspector.
     */
    public function show(Elemento $elemento): View
    {
        $elemento->load([
            'tipo.atributos',
            'valoresAtributos.atributo',
            'ultimoMovimiento.ubicacion',
            'movimientos.ubicacion',
            'movimientos.usuario',
        ]);

        return view('elementos.show', compact('elemento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Elemento $elemento): View
    {
        if (!auth()->user()?->canManageElementos()) {
            abort(403, 'Acceso restringido a administradores de inventario (item_admin).');
        }

        $elemento->load('valoresAtributos');
        $tipos = Tipo::with('atributos')->orderBy('nombre')->get();
        return view('elementos.edit', compact('elemento', 'tipos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateElementoRequest $request, Elemento $elemento): RedirectResponse
    {
        if (!auth()->user()?->canManageElementos()) {
            abort(403, 'Acceso restringido a administradores de inventario (item_admin).');
        }

        $validated = $request->validated();

        $cleanLia = Elemento::normalizarIdentificador($validated['nro_lia'] ?? null);
        $cleanUnsj = Elemento::normalizarIdentificador($validated['nro_unsj'] ?? null);

        $descripcion = Elemento::parsearDescripcion(
            $request->input('descripcion_keys', []),
            $request->input('descripcion_values', [])
        );

        $elemento->update([
            'nro_lia' => $cleanLia,
            'nro_unsj' => $cleanUnsj,
            'tipo_id' => $validated['tipo_id'],
            'marca' => $validated['marca'] ?? null,
            'modelo' => $validated['modelo'] ?? null,
            'nro_serie' => $validated['nro_serie'] ?? null,
            'anio_compra' => $validated['anio_compra'] ?? null,
            'puesto' => $validated['puesto'] ?? null,
            'observaciones' => $validated['observaciones'] ?? null,
            'descripcion' => $descripcion,
            'cantidad' => $validated['cantidad'],
            'fecha_adquisicion' => $validated['fecha_adquisicion'] ?? null,
            'fecha_vencimiento_garantia' => $validated['fecha_vencimiento_garantia'] ?? null,
        ]);

        // Sincronizar atributos técnicos dinámicos
        if ($request->has('atributos') && is_array($request->atributos)) {
            foreach ($request->atributos as $attrId => $valor) {
                $valLimpio = trim((string)$valor);
                if ($valLimpio !== '') {
                    ElementoAtributoValor::updateOrCreate(
                        ['elemento_id' => $elemento->id, 'atributo_id' => $attrId],
                        ['valor' => $valLimpio]
                    );
                } else {
                    ElementoAtributoValor::where('elemento_id', $elemento->id)
                        ->where('atributo_id', $attrId)
                        ->delete();
                }
            }
        }

        return redirect()->route('elementos.show', $elemento->id)
            ->with('success', 'Ficha del elemento actualizada con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Elemento $elemento): RedirectResponse
    {
        if (!auth()->user()?->canManageElementos()) {
            abort(403, 'Acceso restringido a administradores de inventario (item_admin).');
        }

        $elemento->delete();

        return redirect()->route('elementos.index')
            ->with('success', 'Elemento eliminado del inventario.');
    }
}
