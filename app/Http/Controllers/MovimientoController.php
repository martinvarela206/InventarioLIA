<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Elemento;
use App\Models\Usuario;
use App\Models\Ubicacion;
use App\Models\Estado;
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
        $query = Movimiento::with(['elemento.tipo', 'usuario', 'ubicacion', 'estado']);

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
                  })
                  ->orWhereHas('estado', function ($esq) use ($search) {
                      $esq->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('tipo_movimiento')) {
            $query->where('tipo_movimiento', $request->tipo_movimiento);
        }

        if ($request->filled('estado_id')) {
            $query->where('estado_id', $request->estado_id);
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
        $estados = Estado::all();
        $ubicaciones = Ubicacion::all();

        return view('movimientos.index', compact('movimientos', 'estados', 'ubicaciones'));
    }

    /**
     * Show the rapid mobile-first view for technicians.
     */
    public function rapido(Request $request): View
    {
        $ubicaciones = Ubicacion::orderBy('nombre')->get();
        $estados = Estado::orderBy('nombre')->get();
        $elementoPreseleccionado = null;

        if ($request->filled('elemento_id')) {
            $elementoPreseleccionado = Elemento::with(['tipo', 'ultimoMovimiento.estado', 'ultimoMovimiento.ubicacion'])->find($request->elemento_id);
        } elseif ($request->filled('nro_lia')) {
            $elementoPreseleccionado = Elemento::with(['tipo', 'ultimoMovimiento.estado', 'ultimoMovimiento.ubicacion'])
                ->where('nro_lia', $request->nro_lia)
                ->first();
        }

        return view('movimientos.rapido', compact('ubicaciones', 'estados', 'elementoPreseleccionado'));
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

        $elementos = Elemento::with(['tipo', 'ultimoMovimiento.estado', 'ultimoMovimiento.ubicacion'])
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
                    'estado_actual' => $ultimo?->estado?->nombre ?? 'N/A',
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
    public function store(StoreMovimientoRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $tipoMovimiento = $validated['tipo_movimiento'];
        $esPrestamo = $tipoMovimiento === 'externo_prestamo';

        $fecha = !empty($validated['fecha']) ? Carbon::parse($validated['fecha']) : now();

        Movimiento::create([
            'elemento_id' => $validated['elemento_id'],
            'tipo_movimiento' => $tipoMovimiento,
            'ubicacion_id' => $esPrestamo ? null : ($validated['ubicacion_id'] ?? null),
            'puesto' => $esPrestamo ? null : ($validated['puesto'] ?? null),
            'estado_id' => $validated['estado_id'],
            'user_id' => auth()->id() ?? Usuario::first()->id,
            'fecha' => $fecha,
            'solicitante' => $esPrestamo ? ($validated['solicitante'] ?? null) : null,
            'fecha_prestamo' => $esPrestamo ? (!empty($validated['fecha_prestamo']) ? Carbon::parse($validated['fecha_prestamo'])->toDateString() : now()->toDateString()) : null,
            'comentario' => $validated['comentario'] ?? null,
        ]);

        return redirect()->route('elementos.show', $validated['elemento_id'])
            ->with('success', 'Movimiento registrado con éxito.');
    }

    /**
     * Check-in / Registro de devolución de préstamo (Transacción atómica).
     */
    public function devolucion(DevolucionMovimientoRequest $request, Movimiento $movimiento): RedirectResponse
    {
        $validated = $request->validated();
        $fechaDev = !empty($validated['fecha_devolucion'])
            ? Carbon::parse($validated['fecha_devolucion'])->toDateString()
            : now()->toDateString();

        DB::transaction(function () use ($movimiento, $validated, $fechaDev, $request) {
            // 1. Marcar fecha de devolución en el préstamo original
            $movimiento->update([
                'fecha_devolucion' => $fechaDev,
            ]);

            // 2. Registrar el nuevo movimiento interno de reingreso al laboratorio
            Movimiento::create([
                'elemento_id' => $movimiento->elemento_id,
                'tipo_movimiento' => 'interno',
                'ubicacion_id' => $validated['ubicacion_id'],
                'puesto' => $validated['puesto'] ?? null,
                'estado_id' => $validated['estado_id'],
                'user_id' => auth()->id() ?? Usuario::first()->id,
                'fecha' => now(),
                'comentario' => 'Reingreso de préstamo externo. ' . ($validated['comentario'] ?? ''),
            ]);
        });

        return redirect()->route('elementos.show', $movimiento->elemento_id)
            ->with('success', 'Devolución registrada exitosamente. El equipo ha reingresado a la sala.');
    }

    /**
     * Registrar una corrección oficial sobre un movimiento previo usando método de dominio.
     */
    public function storeCorreccion(StoreEnmiendaRequest $request, Movimiento $movimiento): RedirectResponse
    {
        $validated = $request->validated();
        $usuarioId = auth()->id() ?? Usuario::first()->id;

        $nuevoMovimiento = $movimiento->crearEnmienda(
            $validated,
            $usuarioId,
            $validated['motivo_correccion']
        );

        return redirect()->route('elementos.show', $movimiento->elemento_id)
            ->with('success', "Movimiento #{$movimiento->id} enmendado con éxito mediante el nuevo movimiento #{$nuevoMovimiento->id}.");
    }
}
