<?php

namespace App\Http\Controllers;

use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Tipo;
use App\Models\Estado;
use App\Models\Ubicacion;
use App\Models\Usuario;
use App\Http\Requests\StoreElementoRequest;
use App\Http\Requests\UpdateElementoRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Atributo;
use App\Models\ElementoAtributoValor;

class ElementoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
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
            $query->where(function ($q) use ($gb) {
                $q->where('descripcion', 'like', "%{$gb}%")
                  ->orWhereHas('valoresAtributos', function ($vaq) use ($gb) {
                      $vaq->where('valor', 'like', "%{$gb}%");
                  });
            });
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
    public function create(): View
    {
        $tipos = Tipo::with('atributos')->orderBy('nombre')->get();
        $ubicaciones = Ubicacion::orderBy('nombre')->get();
        $estados = Estado::orderBy('nombre')->get();

        return view('elementos.create', compact('tipos', 'ubicaciones', 'estados'));
    }

    /**
     * Store a newly created resource in storage and guarantee initial movement with ACID atomicity.
     */
    public function store(StoreElementoRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $elemento = DB::transaction(function () use ($request, $validated) {
            $cleanLia = Elemento::normalizarIdentificador($validated['nro_lia'] ?? null);
            $cleanUnsj = Elemento::normalizarIdentificador($validated['nro_unsj'] ?? null);

            $descripcion = Elemento::parsearDescripcion(
                $request->input('descripcion_keys', []),
                $request->input('descripcion_values', [])
            );

            $elemento = Elemento::create([
                'nro_lia' => $cleanLia,
                'nro_unsj' => $cleanUnsj,
                'tipo_id' => $validated['tipo_id'],
                'marca' => $validated['marca'] ?? null,
                'modelo' => $validated['modelo'] ?? null,
                'nro_serie' => $validated['nro_serie'] ?? null,
                'anio_compra' => $validated['anio_compra'] ?? null,
                'puesto' => $validated['puesto'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'descripcion' => $descripcion,
                'cantidad' => $validated['cantidad'],
                'fecha_adquisicion' => $validated['fecha_adquisicion'] ?? now()->toDateString(),
                'fecha_vencimiento_garantia' => $validated['fecha_vencimiento_garantia'] ?? null,
            ]);

            // Guardar atributos específicos dinámicos en la tabla pivot
            if ($request->has('atributos') && is_array($request->atributos)) {
                foreach ($request->atributos as $attrId => $valor) {
                    $valLimpio = trim((string)$valor);
                    if ($valLimpio !== '') {
                        ElementoAtributoValor::create([
                            'elemento_id' => $elemento->id,
                            'atributo_id' => $attrId,
                            'valor' => $valLimpio,
                        ]);
                    }
                }
            }

            // GARANTIZAR MOVIMIENTO INICIAL AUTOMÁTICO
            $defaultEstado = Estado::where('nombre', 'nuevo')->first() ?? Estado::first();
            $estadoId = !empty($validated['estado_id']) ? $validated['estado_id'] : $defaultEstado->id;

            $defaultUbicacion = Ubicacion::where('nombre', 'Armario de HW')->first() ?? Ubicacion::first();
            $ubicacionId = !empty($validated['ubicacion_id']) ? $validated['ubicacion_id'] : $defaultUbicacion->id;

            Movimiento::create([
                'elemento_id' => $elemento->id,
                'tipo_movimiento' => 'interno',
                'ubicacion_id' => $ubicacionId,
                'puesto' => $validated['puesto'] ?? null,
                'estado_id' => $estadoId,
                'user_id' => auth()->id() ?? Usuario::first()->id,
                'fecha' => now(),
                'comentario' => 'Alta inicial del elemento en el inventario',
            ]);

            return $elemento;
        });

        return redirect()->route('elementos.show', $elemento->id)
            ->with('success', 'Elemento dado de alta exitosamente con su movimiento inicial registrado.');
    }

    /**
     * Display the specified resource in Dual-Pane inspector.
     */
    public function show(Elemento $elemento): View
    {
        $elemento->load([
            'tipo.atributos',
            'valoresAtributos.atributo',
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
    public function edit(Elemento $elemento): View
    {
        $elemento->load('valoresAtributos');
        $tipos = Tipo::with('atributos')->orderBy('nombre')->get();
        return view('elementos.edit', compact('elemento', 'tipos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateElementoRequest $request, Elemento $elemento): RedirectResponse
    {
        $validated = $request->validated();

        $cleanLia = Elemento::normalizarIdentificador($validated['nro_lia'] ?? null);
        $cleanUnsj = Elemento::normalizarIdentificador($validated['nro_unsj'] ?? null);

        $descripcion = Elemento::parsearDescripcion(
            $request->input('descripcion_keys', []),
            $request->input('descripcion_values', [])
        );

        $elemento->update([
            'nro_lia' => $cleanLia,
            'nro_unsj' => $cleanUnsj,
            'tipo_id' => $validated['tipo_id'],
            'marca' => $validated['marca'] ?? null,
            'modelo' => $validated['modelo'] ?? null,
            'nro_serie' => $validated['nro_serie'] ?? null,
            'anio_compra' => $validated['anio_compra'] ?? null,
            'puesto' => $validated['puesto'] ?? null,
            'observaciones' => $validated['observaciones'] ?? null,
            'descripcion' => $descripcion,
            'cantidad' => $validated['cantidad'],
            'fecha_adquisicion' => $validated['fecha_adquisicion'] ?? null,
            'fecha_vencimiento_garantia' => $validated['fecha_vencimiento_garantia'] ?? null,
        ]);

        // Sincronizar atributos técnicos dinámicos
        if ($request->has('atributos') && is_array($request->atributos)) {
            foreach ($request->atributos as $attrId => $valor) {
                $valLimpio = trim((string)$valor);
                if ($valLimpio !== '') {
                    ElementoAtributoValor::updateOrCreate(
                        ['elemento_id' => $elemento->id, 'atributo_id' => $attrId],
                        ['valor' => $valLimpio]
                    );
                } else {
                    ElementoAtributoValor::where('elemento_id', $elemento->id)
                        ->where('atributo_id', $attrId)
                        ->delete();
                }
            }
        }

        return redirect()->route('elementos.show', $elemento->id)
            ->with('success', 'Ficha del elemento actualizada con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Elemento $elemento): RedirectResponse
    {
        $elemento->delete();

        return redirect()->route('elementos.index')
            ->with('success', 'Elemento eliminado del inventario.');
    }
}
