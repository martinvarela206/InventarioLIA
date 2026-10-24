<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Ubicacion;
use App\Models\Estado;
use App\Models\Tipo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $hoy = Carbon::today()->toDateString();
        $proximo30Dias = Carbon::today()->addDays(30)->toDateString();

        // 1. KPI Cards Principales
        $totalElementos = Elemento::count();
        $totalOperativos = Elemento::disponibles()->count();
        $totalPrestados = Elemento::prestadosActivos()->count();
        $totalEnReparacion = Elemento::whereHas('ultimoMovimiento.estado', function ($q) {
            $q->whereIn('nombre', ['en reparacion', 'verificar funcionamiento']);
        })->count();
        $totalObsoletos = Elemento::obsoletos()->count();

        // 2. Widget de Préstamos Activos con cálculo de días y acción de devolución
        $prestamosActivos = Movimiento::prestadosActivos()
            ->with(['elemento.tipo', 'usuario'])
            ->orderBy('fecha_prestamo', 'asc')
            ->get();

        // 3. Distribución de Equipos por Salas y Ocupación
        $distribucionSalas = Ubicacion::all()->map(function ($ubicacion) {
            $count = Elemento::enUbicacion($ubicacion->id)->count();
            return [
                'id' => $ubicacion->id,
                'nombre' => $ubicacion->nombre,
                'descripcion' => $ubicacion->descripcion,
                'total_activos' => $count,
            ];
        })->filter(function ($u) {
            return $u['total_activos'] > 0;
        })->sortByDesc('total_activos')->values();

        // 4. Alertas de Equipos Críticos
        $alertasGarantiaVencida = Elemento::whereNotNull('fecha_vencimiento_garantia')
            ->where('fecha_vencimiento_garantia', '<', $hoy)
            ->with(['tipo', 'ultimoMovimiento.ubicacion'])
            ->limit(10)
            ->get();

        $alertasVerificar = Elemento::whereHas('ultimoMovimiento.estado', function ($q) {
            $q->where('nombre', 'verificar funcionamiento');
        })->with(['tipo', 'ultimoMovimiento.ubicacion'])->limit(10)->get();

        return view('dashboard.index', compact(
            'totalElementos',
            'totalOperativos',
            'totalPrestados',
            'totalEnReparacion',
            'totalObsoletos',
            'prestamosActivos',
            'distribucionSalas',
            'alertasGarantiaVencida',
            'alertasVerificar'
        ));
    }
}
