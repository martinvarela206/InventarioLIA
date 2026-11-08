<?php

namespace App\Http\Controllers;

use App\Actions\Ubicaciones\ArchivarUbicacionAction;
use App\Actions\Ubicaciones\RenombrarUbicacionAction;
use App\Models\Movimiento;
use App\Models\Ubicacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

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
            'descripcion' => ! empty($validated['descripcion']) ? trim($validated['descripcion']) : null,
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
     * Si el nombre cambia, archiva la ubicación previa y crea una nueva,
     * enviando al asistente de reubicación si posee activos en limbo.
     */
    public function update(Request $request, Ubicacion $ubicacione, RenombrarUbicacionAction $renombrarAction): RedirectResponse
    {
        $ubicacion = $ubicacione;

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ], [
            'nombre.required' => 'El nombre del espacio o sala es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 255 caracteres.',
        ]);

        try {
            $resultado = $renombrarAction->execute($ubicacion, $validated['nombre'], $validated['descripcion'] ?? null, auth()->user());
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        if ($resultado['renombro']) {
            if ($resultado['activos_en_limbo'] > 0) {
                return redirect()->route('ubicaciones.reubicar', [
                    'origen_id' => $resultado['ubicacion_antigua']->id,
                    'sugerido_id' => $resultado['nueva_ubicacion']->id,
                ])->with('warning', "Se ha creado la nueva sala \"{$resultado['nueva_ubicacion']->nombre}\" y archivado \"{$resultado['ubicacion_antigua']->nombre}\". Se detectaron {$resultado['activos_en_limbo']} activos en limbo que deben ser reubicados.");
            }

            return redirect()->route('ubicaciones.index')
                ->with('success', "Se ha creado la nueva sala \"{$resultado['nueva_ubicacion']->nombre}\" y archivado la histórica \"{$resultado['ubicacion_antigua']->nombre}\".");
        }

        return redirect()->route('ubicaciones.index')
            ->with('success', "La información del espacio \"{$ubicacion->nombre}\" ha sido actualizada.");
    }

    /**
     * Elimina o archiva un espacio físico garantizando la integridad de datos patrimoniales.
     * Si posee historial de movimientos o activos, se archiva lógicamente y se envían los activos al limbo.
     */
    public function destroy(Ubicacion $ubicacione, ArchivarUbicacionAction $archivarAction): RedirectResponse
    {
        $ubicacion = $ubicacione;

        $tieneMovimientos = $ubicacion->movimientos()->exists();

        if ($tieneMovimientos) {
            try {
                $resultado = $archivarAction->execute($ubicacion, auth()->user());
            } catch (ValidationException $e) {
                return redirect()->route('ubicaciones.index')->withErrors($e->errors());
            }

            if ($resultado['activos_en_limbo'] > 0) {
                return redirect()->route('ubicaciones.reubicar', [
                    'origen_id' => $resultado['ubicacion']->id,
                ])->with('warning', "La sala \"{$resultado['ubicacion']->nombre}\" ha sido archivada como histórica. Se detectaron {$resultado['activos_en_limbo']} activos en limbo que deben ser reubicados.");
            }

            return redirect()->route('ubicaciones.index')
                ->with('success', "La sala \"{$resultado['ubicacion']->nombre}\" ha sido archivada como histórica.");
        }

        $nombre = $ubicacion->nombre;
        $ubicacion->delete();

        return redirect()->route('ubicaciones.index')
            ->with('success', "El espacio \"{$nombre}\" ha sido eliminado exitosamente.");
    }
}
