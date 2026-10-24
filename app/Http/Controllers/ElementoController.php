<?php

namespace App\Http\Controllers;

use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Tipo;
use App\Models\Estado;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ElementoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Elemento::with(['tipo', 'ultimoMovimiento.estado', 'ultimoMovimiento.ubicacion']);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->buscarIdentificador($search);
        }

        if ($request->filled('tipo')) {
            $query->whereHas('tipo', function ($q) use ($request) {
                $q->where('nombre', $request->tipo)->orWhere('id', $request->tipo);
            });
        }

        if ($request->filled('estado')) {
            $query->whereHas('ultimoMovimiento.estado', function ($q) use ($request) {
                $q->where('nombre', $request->estado)->orWhere('id', $request->estado);
            });
        }

        if ($request->filled('ubicacion')) {
            $query->whereHas('ultimoMovimiento', function ($q) use ($request) {
                $q->where('ubicacion_id', $request->ubicacion);
            });
        }

        if ($request->filled('gb')) {
            $gb = $request->input('gb');
            $query->where('descripcion', 'like', "%{$gb}GB%");
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

        $elementos = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $estados = Estado::all();
        $tipos = Tipo::all();
        $ubicaciones = Ubicacion::all();

        return view('elementos.index', compact('elementos', 'estados', 'tipos', 'ubicaciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tipos = Tipo::orderBy('nombre')->get();
        $ubicaciones = Ubicacion::orderBy('nombre')->get();
        $estados = Estado::orderBy('nombre')->get();

        return view('elementos.create', compact('tipos', 'ubicaciones', 'estados'));
    }

    /**
     * Store a newly created resource in storage and guarantee initial movement.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nro_lia' => 'nullable|string|max:50',
            'nro_unsj' => 'nullable|string|max:50',
            'tipo_id' => 'required|exists:tipos,id',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'nro_serie' => 'nullable|string|max:100',
            'anio_compra' => 'nullable|integer|min:1980|max:' . (date('Y') + 1),
            'puesto' => 'nullable|string|max:50',
            'ubicacion_id' => 'nullable|exists:ubicaciones,id',
            'estado_id' => 'nullable|exists:estados,id',
            'cantidad' => 'required|integer|min:1',
            'fecha_adquisicion' => 'nullable|date',
            'fecha_vencimiento_garantia' => 'nullable|date|after_or_equal:fecha_adquisicion',
            'observaciones' => 'nullable|string',
            'descripcion_keys' => 'nullable|array',
            'descripcion_values' => 'nullable|array',
        ]);

        $lia = trim((string)$request->nro_lia);
        $unsj = trim((string)$request->nro_unsj);
        $cleanLia = ($lia === '' || strtoupper($lia) === 'S/N' || $lia === '-') ? null : $lia;
        $cleanUnsj = ($unsj === '' || strtoupper($unsj) === 'S/N' || $unsj === '-') ? null : $unsj;

        // Comprobar colisión de tupla compuesta
        if ($cleanLia !== null && $cleanUnsj !== null) {
            $exists = Elemento::where('nro_lia', $cleanLia)->where('nro_unsj', $cleanUnsj)->exists();
            if ($exists) {
                return back()->withInput()->withErrors([
                    'nro_lia' => 'Ya existe un elemento registrado con la misma combinación de Nro LIA y Nro UNSJ.',
                ]);
            }
        }

        // Construir JSON de características técnicas
        $descripcion = [];
        if ($request->has('descripcion_keys') && $request->has('descripcion_values')) {
            $keys = $request->input('descripcion_keys', []);
            $values = $request->input('descripcion_values', []);
            foreach ($keys as $i => $k) {
                $k = trim((string)$k);
                $v = isset($values[$i]) ? trim((string)$values[$i]) : '';
                if ($k !== '' && $v !== '') {
                    $descripcion[$k] = $v;
                }
            }
        }

        $elemento = Elemento::create([
            'nro_lia' => $cleanLia,
            'nro_unsj' => $cleanUnsj,
            'tipo_id' => $request->tipo_id,
            'marca' => $request->marca,
            'modelo' => $request->modelo,
            'nro_serie' => $request->nro_serie,
            'anio_compra' => $request->anio_compra,
            'puesto' => $request->puesto,
            'observaciones' => $request->observaciones,
            'descripcion' => !empty($descripcion) ? $descripcion : null,
            'cantidad' => $request->cantidad,
            'fecha_adquisicion' => $request->fecha_adquisicion ?: now()->toDateString(),
            'fecha_vencimiento_garantia' => $request->fecha_vencimiento_garantia,
        ]);

        // GARANTIZAR MOVIMIENTO INICIAL AUTOMÁTICO
        $defaultEstado = Estado::where('nombre', 'nuevo')->first() ?? Estado::first();
        $estadoId = $request->estado_id ?: $defaultEstado->id;

        $defaultUbicacion = Ubicacion::where('nombre', 'Armario de HW')->first() ?? Ubicacion::first();
        $ubicacionId = $request->ubicacion_id ?: $defaultUbicacion->id;

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacionId,
            'puesto' => $request->puesto,
            'estado_id' => $estadoId,
            'user_id' => auth()->id() ?? Usuario::first()->id,
            'fecha' => now(),
            'comentario' => 'Alta inicial del elemento en el inventario',
        ]);

        return redirect()->route('elementos.show', $elemento->id)
            ->with('success', 'Elemento dado de alta exitosamente con su movimiento inicial registrado.');
    }

    /**
     * Display the specified resource in Dual-Pane inspector.
     */
    public function show(Elemento $elemento)
    {
        $elemento->load([
            'tipo',
            'ultimoMovimiento.ubicacion',
            'ultimoMovimiento.estado',
            'movimientos.ubicacion',
            'movimientos.estado',
            'movimientos.usuario',
        ]);

        return view('elementos.show', compact('elemento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Elemento $elemento)
    {
        $tipos = Tipo::orderBy('nombre')->get();
        return view('elementos.edit', compact('elemento', 'tipos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Elemento $elemento)
    {
        $request->validate([
            'nro_lia' => 'nullable|string|max:50',
            'nro_unsj' => 'nullable|string|max:50',
            'tipo_id' => 'required|exists:tipos,id',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'nro_serie' => 'nullable|string|max:100',
            'anio_compra' => 'nullable|integer',
            'puesto' => 'nullable|string|max:50',
            'cantidad' => 'required|integer|min:1',
            'fecha_adquisicion' => 'nullable|date',
            'fecha_vencimiento_garantia' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'descripcion_keys' => 'nullable|array',
            'descripcion_values' => 'nullable|array',
        ]);

        $lia = trim((string)$request->nro_lia);
        $unsj = trim((string)$request->nro_unsj);
        $cleanLia = ($lia === '' || strtoupper($lia) === 'S/N' || $lia === '-') ? null : $lia;
        $cleanUnsj = ($unsj === '' || strtoupper($unsj) === 'S/N' || $unsj === '-') ? null : $unsj;

        // Comprobar colisión de tupla compuesta ignorando el registro actual
        if ($cleanLia !== null && $cleanUnsj !== null) {
            $exists = Elemento::where('nro_lia', $cleanLia)
                ->where('nro_unsj', $cleanUnsj)
                ->where('id', '!=', $elemento->id)
                ->exists();
            if ($exists) {
                return back()->withInput()->withErrors([
                    'nro_lia' => 'Ya existe otro elemento registrado con la misma combinación de Nro LIA y Nro UNSJ.',
                ]);
            }
        }

        $descripcion = [];
        if ($request->has('descripcion_keys') && $request->has('descripcion_values')) {
            $keys = $request->input('descripcion_keys', []);
            $values = $request->input('descripcion_values', []);
            foreach ($keys as $i => $k) {
                $k = trim((string)$k);
                $v = isset($values[$i]) ? trim((string)$values[$i]) : '';
                if ($k !== '' && $v !== '') {
                    $descripcion[$k] = $v;
                }
            }
        }

        $elemento->update([
            'nro_lia' => $cleanLia,
            'nro_unsj' => $cleanUnsj,
            'tipo_id' => $request->tipo_id,
            'marca' => $request->marca,
            'modelo' => $request->modelo,
            'nro_serie' => $request->nro_serie,
            'anio_compra' => $request->anio_compra,
            'puesto' => $request->puesto,
            'observaciones' => $request->observaciones,
            'descripcion' => !empty($descripcion) ? $descripcion : null,
            'cantidad' => $request->cantidad,
            'fecha_adquisicion' => $request->fecha_adquisicion,
            'fecha_vencimiento_garantia' => $request->fecha_vencimiento_garantia,
        ]);

        return redirect()->route('elementos.show', $elemento->id)
            ->with('success', 'Ficha del elemento actualizada con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Elemento $elemento)
    {
        $elemento->delete();

        return redirect()->route('elementos.index')
            ->with('success', 'Elemento eliminado del inventario.');
    }
}
