<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Elemento;
use App\Models\Usuario;
use App\Models\Ubicacion;
use App\Enums\EstadoElemento;
use App\Actions\Movimientos\RegistrarPrestamoAction;
use App\Actions\Movimientos\RegistrarDevolucionAction;
use App\Http\Requests\StoreMovimientoRequest;
use App\Http\Requests\DevolucionMovimientoRequest;
use App\Http\Requests\StoreEnmiendaRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MovimientoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Movimiento::with(['elemento.tipo', 'usuario', 'ubicacion']);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('comentario', 'like', "%{$search}%")
                  ->orWhere('solicitante', 'like', "%{$search}%")
                  ->orWhere('puesto', 'like', "%{$search}%")
                  ->orWhereHas('elemento', function ($eq) use ($search) {
                      $eq->where('nro_lia', 'like', "%{$search}%")
                         ->orWhere('nro_unsj', 'like', "%{$search}%")
                         ->orWhere('marca', 'like', "%{$search}%")
                         ->orWhere('modelo', 'like', "%{$search}%");
                  })
                  ->orWhereHas('ubicacion', function ($uq) use ($search) {
                      $uq->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('tipo_movimiento')) {
            $query->where('tipo_movimiento', $request->tipo_movimiento);
        }

        if ($request->filled('ubicacion_id')) {
            $query->where('ubicacion_id', $request->ubicacion_id);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        $movimientos = $query->orderBy('fecha', 'desc')->paginate(20)->withQueryString();
        $ubicaciones = Ubicacion::all();

        return view('movimientos.index', compact('movimientos', 'ubicaciones'));
    }

    /**
     * Show the rapid mobile-first view for technicians.
     */
    public function rapido(Request $request): View
    {
        $ubicaciones = Ubicacion::orderBy('nombre')->get();
        $elementoPreseleccionado = null;

        if ($request->filled('elemento_id')) {
            $elementoPreseleccionado = Elemento::with(['tipo', 'ultimoMovimiento.ubicacion'])->find($request->elemento_id);
        } elseif ($request->filled('nro_lia')) {
            $elementoPreseleccionado = Elemento::with(['tipo', 'ultimoMovimiento.ubicacion'])
                ->where('nro_lia', $request->nro_lia)
                ->first();
        }

        $solicitantesFrecuentes = Movimiento::whereNotNull('solicitante')
            ->where('solicitante', '!=', '')
            ->distinct()
            ->orderBy('solicitante')
            ->limit(25)
            ->pluck('solicitante');

        return view('movimientos.rapido', compact('ubicaciones', 'elementoPreseleccionado', 'solicitantesFrecuentes'));
    }

    /**
     * API search endpoint for live autocomplete on quick movements.
     */
    public function apiBuscar(Request $request): JsonResponse
    {
        $term = trim((string)$request->get('q', ''));
        if (strlen($term) < 1) {
            return response()->json([]);
        }

        $elementos = Elemento::with(['tipo', 'ultimoMovimiento.ubicacion'])
            ->buscarIdentificador($term)
            ->limit(10)
            ->get()
            ->map(function ($el) {
                $ultimo = $el->ultimoMovimiento;
                return [
                    'id' => $el->id,
                    'nro_lia' => $el->nro_lia ?: 'S/N',
                    'nro_unsj' => $el->nro_unsj ?: 'S/N',
                    'tipo' => $el->tipo?->nombre ?? 'Activo',
                    'marca' => $el->marca,
                    'modelo' => $el->modelo,
                    'puesto' => $el->puesto,
                    'ubicacion_actual' => $ultimo?->ubicacion?->nombre ?? 'N/A',
                    'es_prestamo_activo' => $ultimo && $ultimo->esPrestamo() && !$ultimo->fecha_devolucion,
                    'prestamo_id' => ($ultimo && $ultimo->esPrestamo() && !$ultimo->fecha_devolucion) ? $ultimo->id : null,
                    'solicitante_actual' => $ultimo?->solicitante,
                    'dias_prestado' => $ultimo?->diasPrestado(),
                ];
            });

        return response()->json($elementos);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        return $this->rapido($request);
    }

    /**
     * Store a newly created movement in storage.
     */
    public function store(StoreMovimientoRequest $request, RegistrarPrestamoAction $registrarPrestamoAction): RedirectResponse
    {
        $validated = $request->validated();
        $tipoMovimiento = $validated['tipo_movimiento'];

        if ($tipoMovimiento === 'externo_prestamo') {
            try {
                $registrarPrestamoAction->execute(
                    $validated['elemento_id'],
                    $validated,
                    auth()->user()
                );
            } catch (\Illuminate\Validation\ValidationException $e) {
                return redirect()->back()->withErrors($e->errors())->withInput();
            }

            return redirect()->route('elementos.show', $validated['elemento_id'])
                ->with('success', 'Préstamo externo registrado con éxito.');
        }

        // Movimiento interno
        $elemento = Elemento::findOrFail($validated['elemento_id']);
        if ($elemento->estado === EstadoElemento::BAJA) {
            return redirect()->back()
                ->withErrors(['elemento_id' => 'No es posible trasladar un activo dado de baja o clasificado como basura tecnológica.'])
                ->withInput();
        }
        if ($elemento->estado === EstadoElemento::PRESTADO) {
            return redirect()->back()
                ->withErrors(['elemento_id' => 'El activo se encuentra prestado actualmente. Debe registrar la devolución antes de realizar un traslado interno.'])
                ->withInput();
        }

        $fecha = !empty($validated['fecha']) ? Carbon::parse($validated['fecha']) : now();

        Movimiento::create([
            'elemento_id' => $validated['elemento_id'],
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $validated['ubicacion_id'] ?? null,
            'puesto' => $validated['puesto'] ?? null,
            'user_id' => auth()->id() ?? throw new \Illuminate\Auth\AuthenticationException('Usuario no autenticado.'),
            'fecha' => $fecha,
            'comentario' => $validated['comentario'] ?? null,
        ]);

        return redirect()->route('elementos.show', $validated['elemento_id'])
            ->with('success', 'Movimiento registrado con éxito.');
    }

    /**
     * Check-in / Registro de devolución de préstamo (Transacción atómica con bloqueo pesimista).
     */
    public function devolucion(DevolucionMovimientoRequest $request, Movimiento $movimiento, RegistrarDevolucionAction $registrarDevolucionAction): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $registrarDevolucionAction->execute($movimiento, $validated, auth()->user());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('elementos.show', $movimiento->elemento_id)
            ->with('success', 'Devolución registrada exitosamente. El equipo ha reingresado a la sala.');
    }

    /**
     * Registrar una corrección oficial sobre un movimiento previo usando método de dominio.
     */
    public function storeCorreccion(StoreEnmiendaRequest $request, Movimiento $movimiento): RedirectResponse
    {
        $validated = $request->validated();
        $usuarioId = auth()->id() ?? throw new \Illuminate\Auth\AuthenticationException('Usuario no autenticado.');

        $nuevoMovimiento = $movimiento->crearEnmienda(
            $validated,
            $usuarioId,
            $validated['motivo_correccion']
        );

        return redirect()->route('elementos.show', $movimiento->elemento_id)
            ->with('success', "Movimiento #{$movimiento->id} enmendado con éxito mediante el nuevo movimiento #{$nuevoMovimiento->id}.");
    }
}
