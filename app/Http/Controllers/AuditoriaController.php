<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\AuditoriaDetalle;
use App\Models\Ubicacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    /**
     * Muestra la bandeja histórica de auditorías realizadas.
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

        $auditorias = $query->paginate(15)->withQueryString();
        $ubicaciones = Ubicacion::orderBy('nombre')->get();

        return view('auditorias.index', compact('auditorias', 'ubicaciones'));
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
}
