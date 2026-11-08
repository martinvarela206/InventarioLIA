<?php

namespace App\Http\Controllers;

use App\Actions\Ubicaciones\ReubicarActivosLoteAction;
use App\Models\Elemento;
use App\Models\Ubicacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReubicacionController extends Controller
{
    /**
     * Muestra la interfaz de reubicación de activos en limbo.
     */
    public function show(Request $request): View
    {
        $origenId = $request->query('origen_id');
        $sugeridoId = $request->query('sugerido_id');

        $origen = $origenId ? Ubicacion::findOrFail($origenId) : null;
        $destinoSugerido = $sugeridoId ? Ubicacion::find($sugeridoId) : null;

        $query = Elemento::whereHas('ultimoMovimiento', function ($q) use ($origenId) {
            if ($origenId) {
                $q->where('ubicacion_id', $origenId);
            } else {
                $q->whereHas('ubicacion', fn ($u) => $u->where('activa', false));
            }
            $q->whereNotIn('tipo_movimiento', ['baja']);
        })->whereDoesntHave('tipo', fn ($t) => $t->where('nombre', 'basura tecnologica'))
            ->with(['tipo', 'ultimoMovimiento.ubicacion']);

        $activos = $query->get();

        $ubicacionesActivas = Ubicacion::activas()
            ->when($origenId, fn ($q) => $q->where('id', '!=', $origenId))
            ->orderBy('nombre')
            ->get();

        return view('ubicaciones.reubicar', compact('origen', 'destinoSugerido', 'activos', 'ubicacionesActivas'));
    }

    /**
     * Procesa la reubicación en lote o discriminada de activos en limbo.
     */
    public function reubicar(Request $request, ReubicarActivosLoteAction $reubicarAction): RedirectResponse
    {
        $request->validate([
            'items' => 'nullable|array',
            'elementos' => 'nullable|array',
            'ubicacion_comun_id' => 'nullable|exists:ubicaciones,id',
            'comentario' => 'nullable|string|max:255',
        ]);

        $items = $request->input('items', []);
        if (empty($items) && $request->filled('elementos')) {
            $items = $request->input('elementos');
        }

        try {
            $resultado = $reubicarAction->execute(
                $items,
                $request->filled('ubicacion_comun_id') ? (int) $request->input('ubicacion_comun_id') : null,
                auth()->user(),
                $request->input('comentario')
            );
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('ubicaciones.index')
            ->with('success', "Se han reubicado exitosamente {$resultado['total_reubicados']} activos.");
    }
}
