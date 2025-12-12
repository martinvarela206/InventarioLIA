<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\Elemento;
use App\Models\Usuario;
use App\Models\Ubicacion;
use App\Models\Estado;
use Illuminate\Http\Request;

class MovimientoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->hasRole('coordinador')) {
            abort(403, 'No tienes permiso para ver esta página.');
        }

        $query = Movimiento::with(['elemento.ultimoMovimiento', 'usuario', 'ubicacion', 'estado']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nro_lia', 'like', "%{$search}%")
                  ->orWhere('comentario', 'like', "%{$search}%")
                  ->orWhereHas('elemento', function ($q) use ($search) {
                      $q->where('descripcion', 'like', "%{$search}%")
                        ->orWhere('nro_unsj', 'like', "%{$search}%");
                  })
                  ->orWhereHas('usuario', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('estado', function ($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%");
                  })
                  ->orWhereHas('ubicacion', function ($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        // Estado filter
        if ($request->filled('estado')) {
            $query->whereHas('estado', function ($q) use ($request) {
                $q->where('nombre', $request->estado);
            });
        }

        if ($request->boolean('ultimo')) {
            $query->whereIn('id', function ($q) {
                $q->select('id')
                  ->from('movimientos as m_outer')
                  ->whereRaw('fecha = (select max(fecha) from movimientos as m_inner where m_inner.nro_lia = m_outer.nro_lia)')
                  ->whereRaw('id = (select max(id) from movimientos as m_inner2 where m_inner2.nro_lia = m_outer.nro_lia and m_inner2.fecha = m_outer.fecha)');
            });
        }

        $movimientos = $query->orderBy('fecha', 'desc')->paginate(10)->withQueryString();
        $estados = Estado::all();
        return view('movimientos.index', compact('movimientos', 'estados'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $elementos = Elemento::all();
        $usuarios = Usuario::all();
        $ubicaciones = Ubicacion::all();
        $estados = Estado::all();
        $nro_lia = $request->query('nro_lia');
        return view('movimientos.create', compact('elementos', 'usuarios', 'ubicaciones', 'estados', 'nro_lia'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nro_lia' => 'required|exists:elementos,nro_lia',
            'estado_id' => 'required|exists:estados,id',
            'ubicacion_id' => 'nullable|exists:ubicaciones,id',
            'fecha' => 'required|date',
            'comentario' => 'nullable|string',
        ]);

        $fecha = $request->fecha;
        if (!auth()->user()->can('manage-movements')) {
            $fecha = now();
        }

        // Use hidden ubicacion_id if the select was disabled
        $ubicacionId = $request->ubicacion_id ?: $request->ubicacion_id_hidden;

        Movimiento::create([
            'nro_lia' => $request->nro_lia,
            'user_id' => auth()->id(),
            'estado_id' => $request->estado_id,
            'ubicacion_id' => $ubicacionId,
            'fecha' => $fecha,
            'comentario' => $request->comentario,
        ]);

        return redirect()->route('movimientos.index')->with('success', 'Movimiento registrado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Not typically needed for movements, but can be implemented if required
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $movimiento = Movimiento::findOrFail($id);
        $elementos = Elemento::all();
        $usuarios = Usuario::all();
        $ubicaciones = Ubicacion::all();
        $estados = Estado::all();
        return view('movimientos.edit', compact('movimiento', 'elementos', 'usuarios', 'ubicaciones', 'estados'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nro_lia' => 'required|exists:elementos,nro_lia',
            'user_id' => 'required|exists:usuarios,id',
            'estado_id' => 'required|exists:estados,id',
            'fecha' => 'required|date',
        ]);

        $movimiento = Movimiento::findOrFail($id);
        
        // Use hidden ubicacion_id if the select was disabled
        $data = $request->all();
        if (!$request->ubicacion_id && $request->ubicacion_id_hidden) {
            $data['ubicacion_id'] = $request->ubicacion_id_hidden;
        }
        
        $movimiento->update($data);

        return redirect()->route('movimientos.index')->with('success', 'Movimiento actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $movimiento = Movimiento::findOrFail($id);
        $movimiento->delete();

        return redirect()->route('movimientos.index')->with('success', 'Movimiento eliminado exitosamente.');
    }
}
