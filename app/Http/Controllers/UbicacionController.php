<?php

namespace App\Http\Controllers;

use App\Models\Ubicacion;
use App\Models\Movimiento;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class UbicacionController extends Controller
{
    /**
     * Muestra el catálogo de espacios físicos y salas del LIA con su ocupación actual.
     */
    public function index(): View
    {
        // 1. Obtener el último movimiento de cada activo para calcular la ocupación real
        $ultimosMovimientos = Movimiento::whereIn('id', function ($q) {
            $q->select(DB::raw('MAX(id)'))->from('movimientos')->groupBy('elemento_id');
        })->get();

        $conteosPorUbicacion = $ultimosMovimientos->groupBy('ubicacion_id');

        $ubicaciones = Ubicacion::all()->map(function ($ubicacion) use ($conteosPorUbicacion) {
            $count = isset($conteosPorUbicacion[$ubicacion->id]) ? $conteosPorUbicacion[$ubicacion->id]->count() : 0;
            $ubicacion->total_activos = $count;
            return $ubicacion;
        })->sortByDesc('total_activos')->values();

        return view('ubicaciones.index', compact('ubicaciones'));
    }

    /**
     * Formulario de creación de una nueva sala o espacio.
     */
    public function create(): View
    {
        return view('ubicaciones.create');
    }

    /**
     * Guarda una nueva sala o espacio físico en el sistema.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:ubicaciones,nombre',
            'descripcion' => 'nullable|string|max:255',
        ], [
            'nombre.required' => 'El nombre del espacio o sala es obligatorio.',
            'nombre.unique' => 'Ya existe un espacio o sala con este nombre.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 255 caracteres.',
        ]);

        $ubicacion = Ubicacion::create([
            'nombre' => trim($validated['nombre']),
            'descripcion' => !empty($validated['descripcion']) ? trim($validated['descripcion']) : null,
        ]);

        return redirect()->route('ubicaciones.index')
            ->with('success', "El espacio \"{$ubicacion->nombre}\" ha sido registrado exitosamente.");
    }

    /**
     * Formulario para editar una sala o espacio físico.
     */
    public function edit(Ubicacion $ubicacione): View
    {
        $ubicacion = $ubicacione;
        return view('ubicaciones.edit', compact('ubicacion'));
    }

    /**
     * Actualiza los datos de la sala o espacio físico.
     */
    public function update(Request $request, Ubicacion $ubicacione): RedirectResponse
    {
        $ubicacion = $ubicacione;

        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:ubicaciones,nombre,' . $ubicacion->id,
            'descripcion' => 'nullable|string|max:255',
        ], [
            'nombre.required' => 'El nombre del espacio o sala es obligatorio.',
            'nombre.unique' => 'Ya existe un espacio o sala con este nombre.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 255 caracteres.',
        ]);

        $nombreAnterior = $ubicacion->nombre;
        $ubicacion->update([
            'nombre' => trim($validated['nombre']),
            'descripcion' => !empty($validated['descripcion']) ? trim($validated['descripcion']) : null,
        ]);

        return redirect()->route('ubicaciones.index')
            ->with('success', "El espacio \"{$nombreAnterior}\" ha sido actualizado correctamente.");
    }

    /**
     * Elimina un espacio físico garantizando la integridad de datos patrimoniales.
     */
    public function destroy(Ubicacion $ubicacione): RedirectResponse
    {
        $ubicacion = $ubicacione;

        // Comprobar si tiene movimientos históricos o activos asignados
        $tieneMovimientos = $ubicacion->movimientos()->exists();

        if ($tieneMovimientos) {
            return redirect()->route('ubicaciones.index')
                ->with('error', "No es posible eliminar el espacio \"{$ubicacion->nombre}\" porque posee historial de movimientos patrimoniales o activos asignados. Puedes editar su nombre o reubicar los equipos previamente.");
        }

        $nombre = $ubicacion->nombre;
        $ubicacion->delete();

        return redirect()->route('ubicaciones.index')
            ->with('success', "El espacio \"{$nombre}\" ha sido eliminado exitosamente.");
    }
}
