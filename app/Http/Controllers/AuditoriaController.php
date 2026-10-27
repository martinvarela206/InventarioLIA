<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\AuditoriaDetalle;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    /**
     * Muestra la pantalla interactiva de auditoría de sala con grilla de puestos y mesones.
     */
    public function create(Request $request): View
    {
        $ubicaciones = Ubicacion::orderBy('nombre')->get();
        $ubicacionId = $request->input('ubicacion_id', $ubicaciones->first()?->id);
        $ubicacionSeleccionada = Ubicacion::findOrFail($ubicacionId);

        $user = Auth::user();
        $auditoria = Auditoria::firstOrCreate(
            [
                'ubicacion_id' => $ubicacionSeleccionada->id,
                'estado' => 'en_progreso',
            ],
            [
                'user_id' => $user->id,
                'fecha' => now(),
            ]
        );

        // Activos asignados actualmente a esta sala
        $elementosEnSala = Elemento::whereHas('ultimoMovimiento', function ($q) use ($ubicacionSeleccionada) {
            $q->where('ubicacion_id', $ubicacionSeleccionada->id);
        })->with(['tipo', 'ultimoMovimiento.ubicacion'])->get();

        // Agrupar por puestos
        $puestos = [];
        $elementosGenerales = [];

        foreach ($elementosEnSala as $el) {
            $puestoNombre = $el->ultimoMovimiento?->puesto ?: ($el->puesto ?: null);
            if ($puestoNombre && str_starts_with(strtolower($puestoNombre), 'puesto')) {
                $puestos[$puestoNombre][] = $el;
            } else {
                $elementosGenerales[] = $el;
            }
        }

        // Si no hay puestos con nombre explícito pero hay mesones de laboratorio, asegurar al menos 10 puestos base
        if (empty($puestos) && str_contains(strtolower($ubicacionSeleccionada->nombre), 'laboratorio')) {
            for ($i = 1; $i <= 10; $i++) {
                $nom = sprintf('Puesto %02d', $i);
                $puestos[$nom] = [];
            }
        }
        ksort($puestos);

        $detallesPorPuesto = $auditoria->detalles->keyBy('puesto');
        $detallesPorElemento = $auditoria->detalles->whereNotNull('elemento_id')->keyBy('elemento_id');

        return view('auditorias.create', compact(
            'auditoria',
            'ubicaciones',
            'ubicacionSeleccionada',
            'puestos',
            'elementosGenerales',
            'detallesPorPuesto',
            'detallesPorElemento'
        ));
    }

    /**
     * Muestra la bandeja histórica de auditorías realizadas con filtros multicriterio.
     */
    public function index(Request $request): View
    {
        $query = Auditoria::with(['ubicacion', 'usuario'])
            ->orderBy('fecha', 'desc');

        if ($request->filled('ubicacion_id')) {
            $query->where('ubicacion_id', $request->ubicacion_id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('con_inconsistencias') && $request->con_inconsistencias == '1') {
            $query->where('total_inconsistencias', '>', 0);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        $auditorias = $query->paginate(15)->withQueryString();
        $ubicaciones = Ubicacion::orderBy('nombre')->get();
        $usuarios = Usuario::orderBy('nombre')->get();

        return view('auditorias.index', compact('auditorias', 'ubicaciones', 'usuarios'));
    }

    /**
     * Emisión y visualización del Informe Oficial de Auditoría de Sala.
     */
    public function show(int $id): View
    {
        $auditoria = Auditoria::with([
            'ubicacion',
            'usuario',
            'detalles.elemento.tipo',
            'detalles.ticket.usuario',
        ])->findOrFail($id);

        return view('auditorias.show', compact('auditoria'));
    }

    /**
     * Inicia una nueva sesión de auditoría o retoma una existente en progreso para la sala.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'ubicacion_id' => 'required|exists:ubicaciones,id',
            'observaciones' => 'nullable|string',
        ]);

        $user = Auth::user();

        // Buscar auditoría en progreso para la misma sala y usuario o crear una nueva
        $auditoria = Auditoria::firstOrCreate(
            [
                'ubicacion_id' => $validated['ubicacion_id'],
                'estado' => 'en_progreso',
            ],
            [
                'user_id' => $user->id,
                'fecha' => now(),
                'observaciones' => $validated['observaciones'] ?? null,
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'auditoria' => $auditoria->load('ubicacion', 'detalles'),
            ]);
        }

        return redirect()->route('auditorias.create', ['ubicacion_id' => $validated['ubicacion_id'], 'auditoria_id' => $auditoria->id]);
    }

    /**
     * Guardado incremental en segundo plano para cada puesto o elemento auditado.
     * Previene pérdidas de datos por recargas o microcortes móviles in situ.
     */
    public function guardarDetalle(Request $request, int $auditoriaId): JsonResponse
    {
        $validated = $request->validate([
            'puesto' => 'nullable|string|max:50',
            'elemento_id' => 'nullable|exists:elementos,id',
            'estado' => 'required|in:ok,inconsistencia',
            'ticket_id' => 'nullable|exists:movimientos,id',
            'tipo_discrepancia' => 'nullable|in:faltante,rotura,sobrante',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $auditoria = Auditoria::findOrFail($auditoriaId);

        $detalle = DB::transaction(function () use ($auditoria, $validated) {
            $criterios = ['auditoria_id' => $auditoria->id];
            if (!empty($validated['elemento_id'])) {
                $criterios['elemento_id'] = $validated['elemento_id'];
            } elseif (!empty($validated['puesto'])) {
                $criterios['puesto'] = $validated['puesto'];
            }

            $det = AuditoriaDetalle::updateOrCreate(
                $criterios,
                [
                    'puesto' => $validated['puesto'] ?? null,
                    'elemento_id' => $validated['elemento_id'] ?? null,
                    'estado' => $validated['estado'],
                    'ticket_id' => $validated['ticket_id'] ?? null,
                    'tipo_discrepancia' => $validated['tipo_discrepancia'] ?? null,
                    'observaciones' => $validated['observaciones'] ?? null,
                ]
            );

            $auditoria->recalcularTotales();

            return $det;
        });

        return response()->json([
            'success' => true,
            'detalle' => $detalle,
            'totales' => [
                'total_auditados' => $auditoria->total_auditados,
                'total_ok' => $auditoria->total_ok,
                'total_inconsistencias' => $auditoria->total_inconsistencias,
            ],
        ]);
    }

    /**
     * Finaliza la sesión de auditoría y bloquea modificaciones posteriores.
     */
    public function finalizar(Request $request, int $auditoriaId): JsonResponse|RedirectResponse
    {
        $auditoria = Auditoria::findOrFail($auditoriaId);
        $auditoria->estado = 'finalizada';
        $auditoria->recalcularTotales();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'auditoria' => $auditoria,
            ]);
        }

        return redirect()->route('auditorias.index')
            ->with('success', "Auditoría de {$auditoria->ubicacion->nombre} finalizada correctamente.");
    }

    /**
     * Abre un ticket de reparación o incidencia in situ derivado de una anomalía en auditoría.
     */
    public function crearTicketInconsistencia(Request $request, int $auditoriaId): JsonResponse
    {
        $validated = $request->validate([
            'puesto' => 'nullable|string|max:50',
            'elemento_id' => 'required|exists:elementos,id',
            'tipo_discrepancia' => 'required|in:faltante,rotura,sobrante',
            'descripcion' => 'required|string|min:5',
        ]);

        $auditoria = Auditoria::with('ubicacion')->findOrFail($auditoriaId);
        $user = Auth::user();
        $elemento = Elemento::findOrFail($validated['elemento_id']);

        $ticket = DB::transaction(function () use ($auditoria, $user, $elemento, $validated) {
            $motivo = strtoupper($validated['tipo_discrepancia']);
            $movimiento = Movimiento::create([
                'elemento_id' => $elemento->id,
                'tipo_movimiento' => 'reparacion',
                'estado_ticket' => 'abierto',
                'ubicacion_id' => $auditoria->ubicacion_id,
                'puesto' => $validated['puesto'] ?? null,
                'user_id' => $user->id,
                'fecha' => now(),
                'solicitante' => $user->nombre ?: 'Auditoría Física',
                'comentario' => "[AUDITORÍA INCONSISTENCIA - {$motivo} - {$auditoria->ubicacion->nombre}] {$validated['descripcion']}",
            ]);

            $criterios = ['auditoria_id' => $auditoria->id, 'elemento_id' => $elemento->id];
            AuditoriaDetalle::updateOrCreate(
                $criterios,
                [
                    'puesto' => $validated['puesto'] ?? null,
                    'estado' => 'inconsistencia',
                    'tipo_discrepancia' => $validated['tipo_discrepancia'],
                    'ticket_id' => $movimiento->id,
                    'observaciones' => $validated['descripcion'],
                ]
            );

            $auditoria->recalcularTotales();

            return $movimiento;
        });

        return response()->json([
            'success' => true,
            'ticket_id' => $ticket->id,
            'elemento_id' => $elemento->id,
            'puesto' => $validated['puesto'],
            'totales' => [
                'total_auditados' => $auditoria->total_auditados,
                'total_ok' => $auditoria->total_ok,
                'total_inconsistencias' => $auditoria->total_inconsistencias,
            ],
        ]);
    }
}
