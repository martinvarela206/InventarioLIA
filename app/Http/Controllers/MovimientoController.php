<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Elemento;
use App\Models\Usuario;
use App\Models\Ubicacion;
use App\Models\Estado;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MovimientoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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
    public function rapido(Request $request)
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
    public function apiBuscar(Request $request)
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
    public function create(Request $request)
    {
        return $this->rapido($request);
    }

    /**
     * Store a newly created movement in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'elemento_id' => 'required|exists:elementos,id',
            'tipo_movimiento' => 'required|in:interno,externo_prestamo',
            'estado_id' => 'required|exists:estados,id',
            'ubicacion_id' => 'nullable|exists:ubicaciones,id',
            'puesto' => 'nullable|string|max:50',
            'solicitante' => 'nullable|string|max:150',
            'fecha_prestamo' => 'nullable|date',
            'fecha' => 'nullable|date',
            'comentario' => 'nullable|string',
        ]);

        $tipoMovimiento = $request->tipo_movimiento;
        $esPrestamo = $tipoMovimiento === 'externo_prestamo';

        $fecha = $request->fecha ? Carbon::parse($request->fecha) : now();

        $movimiento = Movimiento::create([
            'elemento_id' => $request->elemento_id,
            'tipo_movimiento' => $tipoMovimiento,
            'ubicacion_id' => $esPrestamo ? null : $request->ubicacion_id,
            'puesto' => $esPrestamo ? null : $request->puesto,
            'estado_id' => $request->estado_id,
            'user_id' => auth()->id() ?? Usuario::first()->id,
            'fecha' => $fecha,
            'solicitante' => $esPrestamo ? $request->solicitante : null,
            'fecha_prestamo' => $esPrestamo ? ($request->fecha_prestamo ? Carbon::parse($request->fecha_prestamo)->toDateString() : now()->toDateString()) : null,
            'comentario' => $request->comentario,
        ]);

        return redirect()->route('elementos.show', $request->elemento_id)
            ->with('success', 'Movimiento registrado con éxito.');
    }

    /**
     * Check-in / Registro de devolución de préstamo
     */
    public function devolucion(Request $request, Movimiento $movimiento)
    {
        $request->validate([
            'fecha_devolucion' => 'nullable|date',
            'ubicacion_id' => 'required|exists:ubicaciones,id',
            'puesto' => 'nullable|string|max:50',
            'estado_id' => 'required|exists:estados,id',
            'comentario' => 'nullable|string',
        ]);

        $fechaDev = $request->fecha_devolucion ? Carbon::parse($request->fecha_devolucion)->toDateString() : now()->toDateString();

        // 1. Marcar fecha de devolución en el préstamo original
        $movimiento->update([
            'fecha_devolucion' => $fechaDev,
        ]);

        // 2. Registrar el nuevo movimiento interno de reingreso al laboratorio
        Movimiento::create([
            'elemento_id' => $movimiento->elemento_id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $request->ubicacion_id,
            'puesto' => $request->puesto,
            'estado_id' => $request->estado_id,
            'user_id' => auth()->id() ?? Usuario::first()->id,
            'fecha' => now(),
            'comentario' => 'Reingreso de préstamo externo. ' . ($request->comentario ?: ''),
        ]);

        return redirect()->route('elementos.show', $movimiento->elemento_id)
            ->with('success', 'Devolución registrada exitosamente. El equipo ha reingresado a la sala.');
    }

    /**
     * Registrar una corrección oficial sobre un movimiento previo
     */
    public function storeCorreccion(Request $request, Movimiento $movimiento)
    {
        $validated = $request->validate([
            'motivo_correccion' => 'required|string|min:4|max:255',
            'ubicacion_id' => 'nullable|exists:ubicaciones,id',
            'puesto' => 'nullable|string|max:50',
            'estado_id' => 'required|exists:estados,id',
            'solicitante' => 'nullable|string|max:150',
            'comentario' => 'nullable|string',
        ]);

        $tipoMovimiento = $movimiento->tipo_movimiento;

        $nuevoMovimiento = Movimiento::create([
            'elemento_id' => $movimiento->elemento_id,
            'tipo_movimiento' => $tipoMovimiento,
            'ubicacion_id' => $validated['ubicacion_id'] ?? $movimiento->ubicacion_id,
            'puesto' => $validated['puesto'] ?? $movimiento->puesto,
            'estado_id' => $validated['estado_id'],
            'user_id' => auth()->id() ?? Usuario::first()->id,
            'fecha' => now(),
            'solicitante' => $validated['solicitante'] ?? $movimiento->solicitante,
            'fecha_prestamo' => $movimiento->fecha_prestamo,
            'fecha_devolucion' => $movimiento->fecha_devolucion,
            'correccion_de_id' => $movimiento->id,
            'comentario' => "ENMIENDA / CORRECCIÓN de Movimiento #{$movimiento->id}: " . $validated['motivo_correccion'] . ($request->filled('comentario') ? " | " . $request->comentario : ""),
        ]);

        return redirect()->route('elementos.show', $movimiento->elemento_id)
            ->with('success', "Movimiento #{$movimiento->id} enmendado con éxito mediante el nuevo movimiento #{$nuevoMovimiento->id}.");
    }
}
