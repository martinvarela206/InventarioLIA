<?php

namespace App\Http\Controllers;

use App\Models\Tipo;
use App\Models\Atributo;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class TipoController extends Controller
{
    /**
     * Muestra el panel maestro de Tipos de Activos y sus Atributos configurados.
     */
    public function index(): View
    {
        $tipos = Tipo::withCount('elementos')
                    ->with('atributos')
                    ->orderBy('nombre')
                    ->paginate(20);

        $atributosGlobales = Atributo::orderBy('nombre')->get();

        return view('tipos.index', compact('tipos', 'atributosGlobales'));
    }

    /**
     * Formulario de creación de nuevo tipo de activo.
     */
    public function create(): View
    {
        $atributos = Atributo::orderBy('nombre')->get();
        return view('tipos.create', compact('atributos'));
    }

    /**
     * Guarda un nuevo tipo de activo y asocia sus atributos técnicos en la tabla pivot.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:tipos,nombre',
            'descripcion' => 'nullable|string|max:255',
            'atributos' => 'nullable|array',
            'atributos.*' => 'exists:atributos,id',
            'nuevo_atributo_nombre' => 'nullable|string|max:100',
            'nuevo_atributo_unidad' => 'nullable|string|max:20',
            'nuevos_atributos_nombre' => 'nullable|array',
            'nuevos_atributos_nombre.*' => 'nullable|string|max:100',
            'nuevos_atributos_unidad' => 'nullable|array',
            'nuevos_atributos_unidad.*' => 'nullable|string|max:30',
            'nuevos_atributos_tipo' => 'nullable|array',
            'nuevos_atributos_tipo.*' => 'nullable|string',
        ]);

        $tipo = Tipo::create([
            'nombre' => mb_strtolower(trim($validated['nombre']), 'UTF-8'),
            'descripcion' => $validated['descripcion'] ?? ucfirst($validated['nombre']),
        ]);

        $atributoIds = $request->input('atributos', []);

        // Procesar nuevos atributos dinámicos
        $this->sincronizarNuevosAtributos($request, $atributoIds);

        if (!empty($atributoIds)) {
            $syncData = [];
            foreach (array_unique($atributoIds) as $idx => $attrId) {
                $syncData[$attrId] = [
                    'orden' => $idx + 1,
                    'es_requerido' => false,
                ];
            }
            $tipo->atributos()->sync($syncData);
        }

        return redirect()->route('tipos.index')
            ->with('success', "Tipo de activo '{$tipo->nombre}' creado exitosamente con sus especificaciones.");
    }

    /**
     * Formulario para editar un tipo y sus atributos asignados.
     */
    public function edit(Tipo $tipo): View
    {
        $tipo->load('atributos');
        $atributos = Atributo::orderBy('nombre')->get();
        return view('tipos.edit', compact('tipo', 'atributos'));
    }

    /**
     * Actualiza el tipo y su pivot de atributos.
     */
    public function update(Request $request, Tipo $tipo): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:tipos,nombre,' . $tipo->id,
            'descripcion' => 'nullable|string|max:255',
            'atributos' => 'nullable|array',
            'atributos.*' => 'exists:atributos,id',
            'nuevo_atributo_nombre' => 'nullable|string|max:100',
            'nuevo_atributo_unidad' => 'nullable|string|max:20',
            'nuevos_atributos_nombre' => 'nullable|array',
            'nuevos_atributos_nombre.*' => 'nullable|string|max:100',
            'nuevos_atributos_unidad' => 'nullable|array',
            'nuevos_atributos_unidad.*' => 'nullable|string|max:30',
            'nuevos_atributos_tipo' => 'nullable|array',
            'nuevos_atributos_tipo.*' => 'nullable|string',
        ]);

        $tipo->update([
            'nombre' => mb_strtolower(trim($validated['nombre']), 'UTF-8'),
            'descripcion' => $validated['descripcion'] ?? $tipo->descripcion,
        ]);

        $atributoIds = $request->input('atributos', []);

        // Procesar nuevos atributos dinámicos
        $this->sincronizarNuevosAtributos($request, $atributoIds);

        $syncData = [];
        foreach (array_unique($atributoIds) as $idx => $attrId) {
            $syncData[$attrId] = [
                'orden' => $idx + 1,
                'es_requerido' => false,
            ];
        }
        $tipo->atributos()->sync($syncData);

        return redirect()->route('tipos.index')
            ->with('success', "Especificaciones del tipo '{$tipo->nombre}' actualizadas correctamente.");
    }

    /**
     * Procesa y persiste atributos nuevos creados al vuelo en el formulario.
     */
    private function sincronizarNuevosAtributos(Request $request, array &$atributoIds): void
    {
        // 1. Array de múltiples nuevos atributos dinámicos
        $nuevosNombres = (array) $request->input('nuevos_atributos_nombre', []);
        $nuevasUnidades = (array) $request->input('nuevos_atributos_unidad', []);
        $nuevosTipos = (array) $request->input('nuevos_atributos_tipo', []);

        foreach ($nuevosNombres as $i => $rawNombre) {
            $nom = trim((string)$rawNombre);
            if ($nom !== '') {
                $uni = isset($nuevasUnidades[$i]) ? trim((string)$nuevasUnidades[$i]) : null;
                $tipoDato = isset($nuevosTipos[$i]) && in_array($nuevosTipos[$i], ['texto', 'numero', 'fecha', 'booleano'])
                    ? $nuevosTipos[$i]
                    : 'texto';

                $nuevoAttr = Atributo::firstOrCreateNormalizado(
                    $nom,
                    $tipoDato,
                    $uni ?: null
                );
                $atributoIds[] = $nuevoAttr->id;
            }
        }

        // 2. Campo individual legacy (por retrocompatibilidad)
        if ($request->filled('nuevo_atributo_nombre')) {
            $nuevoAttr = Atributo::firstOrCreateNormalizado(
                $request->nuevo_atributo_nombre,
                'texto',
                $request->nuevo_atributo_unidad ?: null
            );
            $atributoIds[] = $nuevoAttr->id;
        }
    }

    /**
     * Elimina un tipo si no posee elementos asociados.
     */
    public function destroy(Tipo $tipo): RedirectResponse
    {
        if ($tipo->elementos()->exists()) {
            return back()->withErrors([
                'error' => "No se puede eliminar el tipo '{$tipo->nombre}' porque tiene {$tipo->elementos()->count()} activos asociados en el inventario."
            ]);
        }

        $nombre = $tipo->nombre;
        $tipo->delete();

        return redirect()->route('tipos.index')
            ->with('success', "Tipo '{$nombre}' eliminado con éxito.");
    }

    /**
     * Endpoint API para obtener los atributos dinámicos correspondientes a un tipo seleccionado.
     */
    public function apiAtributosPorTipo(Tipo $tipo): JsonResponse
    {
        $atributos = $tipo->atributos()
            ->orderBy('orden', 'asc')
            ->get()
            ->map(function ($attr) {
                return [
                    'id' => $attr->id,
                    'nombre' => $attr->nombre,
                    'tipo_dato' => $attr->tipo_dato,
                    'unidad_medida' => $attr->unidad_medida,
                    'es_requerido' => (bool)$attr->pivot->es_requerido,
                ];
            });

        return response()->json($atributos);
    }

    /**
     * Permite crear un atributo global directamente desde el catálogo.
     */
    public function storeAtributo(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:atributos,nombre',
            'tipo_dato' => 'required|in:texto,numero,selector,fecha',
            'unidad_medida' => 'nullable|string|max:20',
            'descripcion' => 'nullable|string|max:255',
        ]);

        Atributo::create([
            'nombre' => trim($request->nombre),
            'tipo_dato' => $request->tipo_dato,
            'unidad_medida' => $request->unidad_medida ? trim($request->unidad_medida) : null,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()->route('tipos.index')
            ->with('success', "Atributo técnico '{$request->nombre}' creado en el catálogo global.");
    }
}
