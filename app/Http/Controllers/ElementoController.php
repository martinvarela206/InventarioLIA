<?php

namespace App\Http\Controllers;

use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Tipo;
use App\Models\Estado;
use App\Models\Ubicacion;
use Illuminate\Http\Request;

class ElementoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->hasRole('tecnico')) {
            abort(403, 'No tienes permiso para ver esta página.');
        }

        $query = Elemento::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nro_lia', 'like', "%{$search}%")
                  ->orWhere('nro_unsj', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhereHas('tipo', function ($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%");
                  })
                  ->orWhereHas('ultimoMovimiento.estado', function ($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%");
                  })
                  ->orWhereHas('ultimoMovimiento.ubicacion', function ($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('tipo')) {
            $query->whereHas('tipo', function ($q) use ($request) {
                $q->where('nombre', $request->tipo);
            });
        }

        if ($request->filled('gb')) {
            $gb = $request->input('gb');
            $query->where('descripcion', 'like', "%{$gb}GB%");
        }

        if ($request->filled('estado')) {
            $query->whereHas('ultimoMovimiento.estado', function ($q) use ($request) {
                $q->where('nombre', $request->estado);
            });
        }

        $elementos = $query->with('tipo')->paginate(10)->withQueryString();
        $estados = Estado::all();
        $tipos = Tipo::all();
        return view('elementos.index', compact('elementos', 'estados', 'tipos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tipos = Tipo::all();
        return view('elementos.create', compact('tipos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nro_lia' => 'required|unique:elementos,nro_lia|max:25',
            'tipo_id' => 'required|exists:tipos,id',
            'cantidad' => 'required|integer|min:1',
            'descripcion_keys' => 'nullable|array',
            'descripcion_keys.*' => 'nullable|string|max:100',
            'descripcion_values' => 'nullable|array',
            'descripcion_values.*' => 'nullable|string|max:255',
            'fecha_adquisicion' => 'nullable|date',
            'fecha_vencimiento_garantia' => 'nullable|date|after_or_equal:fecha_adquisicion',
        ]);

        $data = $request->except(['descripcion_keys', 'descripcion_values']);
        
        // Build descripcion array from keys and values
        if ($request->has('descripcion_keys') && $request->has('descripcion_values')) {
            $keys = $request->input('descripcion_keys', []);
            $values = $request->input('descripcion_values', []);
            $descripcion = [];
            
            foreach ($keys as $index => $key) {
                if (!empty($key) && !empty($values[$index])) {
                    $descripcion[$key] = $values[$index];
                }
            }
            
            $data['descripcion'] = !empty($descripcion) ? $descripcion : null;
        }

        try {
            \DB::beginTransaction();
            
            $elemento = Elemento::create($data);

            // Create initial movement
            $estadoIngresado = Estado::where('nombre', 'ingresado')->first();
            $ubicacionLIA = Ubicacion::where('nombre', 'LIA')->first();
            
            if (!$estadoIngresado) {
                throw new \Exception('Estado "ingresado" no encontrado en la base de datos.');
            }
            
            if (!$ubicacionLIA) {
                throw new \Exception('Ubicación "LIA" no encontrada en la base de datos.');
            }
            
            Movimiento::create([
                'nro_lia' => $elemento->nro_lia,
                'user_id' => auth()->id(),
                'estado_id' => $estadoIngresado->id,
                'ubicacion_id' => $ubicacionLIA->id,
                'fecha' => now(),
                'comentario' => 'Ingreso inicial',
            ]);
            
            \DB::commit();
            
            return redirect()->route('elementos.index')->with('success', 'Elemento creado exitosamente.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'Error al crear el elemento: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $elemento = Elemento::with('tipo')->findOrFail($id);
        // Eager-load movimientos with usuario and ubicacion to avoid N+1 queries
        $movimientos = $elemento->movimientos()->with(['usuario', 'ubicacion'])->get(); // relationship returns ordered movimientos
        // Get the latest movimiento with usuario and ubicacion (may be null)
        $ultimoMovimiento = Movimiento::where('nro_lia', $elemento->nro_lia)->with(['usuario', 'ubicacion'])->orderBy('fecha', 'desc')->first();

        return view('elementos.show', compact('elemento', 'movimientos', 'ultimoMovimiento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $elemento = Elemento::findOrFail($id);
        $tipos = Tipo::all();
        return view('elementos.edit', compact('elemento', 'tipos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'tipo_id' => 'required|exists:tipos,id',
            'cantidad' => 'required|integer|min:1',
            'descripcion_keys' => 'nullable|array',
            'descripcion_keys.*' => 'nullable|string|max:100',
            'descripcion_values' => 'nullable|array',
            'descripcion_values.*' => 'nullable|string|max:255',
        ]);

        $elemento = Elemento::findOrFail($id);
        
        $data = $request->except(['descripcion_keys', 'descripcion_values']);
        
        // Build descripcion array from keys and values
        if ($request->has('descripcion_keys') && $request->has('descripcion_values')) {
            $keys = $request->input('descripcion_keys', []);
            $values = $request->input('descripcion_values', []);
            $descripcion = [];
            
            foreach ($keys as $index => $key) {
                if (!empty($key) && !empty($values[$index])) {
                    $descripcion[$key] = $values[$index];
                }
            }
            
            $data['descripcion'] = !empty($descripcion) ? $descripcion : null;
        }
        
        $elemento->update($data);

        return redirect()->route('elementos.index')->with('success', 'Elemento actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $elemento = Elemento::findOrFail($id);
        $elemento->delete();

        return redirect()->route('elementos.index')->with('success', 'Elemento eliminado exitosamente.');
    }
}
