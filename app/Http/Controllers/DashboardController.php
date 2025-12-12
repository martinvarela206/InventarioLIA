<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Elemento;
use App\Models\Usuario;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Estado;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasRole('revisor')) {
            abort(403, 'No tienes permiso para ver esta página.');
        }

        // 1. Elementos NO CPU por Ubicación (Disponible: ni dados de baja ni prestados)
        $elementosNoCpuPorUbicacion = Elemento::whereHas('tipo', function($q) {
                $q->where('nombre', '!=', 'cpu');
            })
            ->whereHas('ultimoMovimiento', function($q) {
                $q->whereHas('estado', function($q2) {
                    $q2->whereNotIn('nombre', ['dado de baja', 'prestado']);
                });
            })
            ->with(['ultimoMovimiento.ubicacion'])
            ->get()
            ->groupBy(function($elemento) {
                return $elemento->ultimoMovimiento->ubicacion->nombre ?? 'Sin ubicación';
            })
            ->map(function($items, $ubicacion) {
                return [
                    'ubicacion' => $ubicacion,
                    'cantidad' => $items->count()
                ];
            })
            ->values();

        // 2. CPUs por Ubicación (Disponible: ni dados de baja ni prestados)
        $cpusPorUbicacion = Elemento::whereHas('tipo', function($q) {
                $q->where('nombre', 'cpu');
            })
            ->whereHas('ultimoMovimiento', function($q) {
                $q->whereHas('estado', function($q2) {
                    $q2->whereNotIn('nombre', ['dado de baja', 'prestado']);
                });
            })
            ->with(['ultimoMovimiento.ubicacion'])
            ->get()
            ->groupBy(function($elemento) {
                return $elemento->ultimoMovimiento->ubicacion->nombre ?? 'Sin ubicación';
            })
            ->map(function($items, $ubicacion) {
                return [
                    'ubicacion' => $ubicacion,
                    'cantidad' => $items->count()
                ];
            })
            ->values();

        // 2. Elementos prestados
        $totalPrestados = Elemento::whereHas('ultimoMovimiento.estado', function($q) {
            $q->where('nombre', 'prestado');
        })->count();

        $elementosPrestados = Elemento::with(['ultimoMovimiento', 'tipo'])
            ->whereHas('ultimoMovimiento.estado', function($q) {
                $q->where('nombre', 'prestado');
            })
            ->get()
            ->map(function($elemento) {
                return [
                    'nro_lia' => $elemento->nro_lia,
                    'tipo' => $elemento->tipo->nombre ?? 'N/A',
                    'descripcion' => $elemento->descripcion_texto,
                    'comentario' => $elemento->ultimoMovimiento->comentario ?? 'Sin comentario',
                    'dias_prestado' => $elemento->ultimoMovimiento->fecha ? intval($elemento->ultimoMovimiento->fecha->diffInDays(now())) : 0
                ];
            });

        $totalElementos = Elemento::count();

        // 3. CPUs obsoletos
        $yearThreshold = $request->input('year', now()->year - 4);
        
        $cpusObsoletos = Elemento::whereHas('tipo', function($q) {
                $q->where('nombre', 'cpu');
            })
            ->whereNotNull('fecha_adquisicion')
            ->whereYear('fecha_adquisicion', '<=', $yearThreshold)
            ->with(['tipo', 'ultimoMovimiento.ubicacion'])
            ->get()
            ->map(function($elemento) {
                return [
                    'nro_lia' => $elemento->nro_lia,
                    'descripcion' => $elemento->descripcion_texto,
                    'fecha_adquisicion' => $elemento->fecha_adquisicion?->format('Y-m-d'),
                    'ubicacion' => $elemento->ultimoMovimiento->ubicacion->nombre ?? 'Sin ubicación',
                    'antiguedad' => $elemento->fecha_adquisicion ? now()->year - $elemento->fecha_adquisicion->year : 'N/A'
                ];
            });

        // 4. Actividad de usuarios
        $actividadUsuarios = Usuario::withCount('movimientos')
            ->has('movimientos')
            ->orderBy('movimientos_count', 'desc')
            ->get()
            ->map(function($user) {
                return [
                    'nombre' => $user->nombre,
                    'movimientos' => $user->movimientos_count
                ];
            });

        // Datos para gráficos
        $tipos = Tipo::all();
        $ubicaciones = Ubicacion::all();

        if ($request->ajax()) {
            return view('dashboard.partials.cpus_table_rows', compact('cpusObsoletos'))->render();
        }

        return view('dashboard.index', compact(
            'elementosNoCpuPorUbicacion',
            'cpusPorUbicacion',
            'totalPrestados',
            'elementosPrestados',
            'totalElementos',
            'cpusObsoletos',
            'yearThreshold',
            'actividadUsuarios',
            'tipos',
            'ubicaciones'
        ));
    }
}
