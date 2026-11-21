<?php

namespace App\Http\Controllers;

use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $hoy = Carbon::today()->toDateString();

        // 0. Carga de alto rendimiento: obtener el último movimiento de cada activo en un único query indexado (<60ms)
        $ultimosMovimientos = Movimiento::whereIn('id', function ($q) {
            $q->select(DB::raw('MAX(id)'))->from('movimientos')->groupBy('elemento_id');
        })->with(['elemento.tipo', 'ubicacion'])->get();

        // 1. KPI Cards Principales
        $totalElementos = Elemento::count();

        $totalPrestados = $ultimosMovimientos->filter(function ($m) {
            return ! empty($m->fecha_prestamo) && empty($m->fecha_devolucion);
        })->count();

        $totalObsoletos = $ultimosMovimientos->filter(function ($m) {
            return $m->tipo_movimiento === 'baja' || $m->elemento?->tipo?->nombre === 'basura tecnologica';
        })->count();

        // Conteo integral de todos los tickets abiertos (activos e infraestructura) para el KPI
        $totalEnReparacion = Movimiento::ticketsAbiertos()->count();

        // Para la fórmula patrimonial de activos operativos, restar únicamente activos físicos con ticket abierto
        $activosEnReparacion = Movimiento::ticketsAbiertos()
            ->whereNotNull('elemento_id')
            ->distinct('elemento_id')
            ->count('elemento_id');

        $totalOperativos = max(0, $totalElementos - $totalPrestados - $totalObsoletos - $activosEnReparacion);

        // 2. Widget de Préstamos Activos con cálculo de días y acción de devolución
        $prestamosActivos = Movimiento::prestadosActivos()
            ->with(['elemento.tipo', 'usuario'])
            ->orderBy('fecha_prestamo', 'asc')
            ->get();

        // 3. Distribución de Equipos por Salas y Ocupación (agrupación en memoria en 0ms)
        $conteosPorUbicacion = $ultimosMovimientos->groupBy('ubicacion_id');
        $distribucionSalas = Ubicacion::all()->map(function ($ubicacion) use ($conteosPorUbicacion) {
            $count = isset($conteosPorUbicacion[$ubicacion->id]) ? $conteosPorUbicacion[$ubicacion->id]->count() : 0;

            return [
                'id' => $ubicacion->id,
                'nombre' => $ubicacion->nombre,
                'descripcion' => $ubicacion->descripcion,
                'total_activos' => $count,
            ];
        })->sortByDesc('total_activos')->values();

        // 4. Alertas de Equipos Críticos (Garantías Vencidas)
        $alertasGarantiaVencida = Elemento::whereNotNull('fecha_vencimiento_garantia')
            ->where('fecha_vencimiento_garantia', '<', $hoy)
            ->with(['tipo', 'ultimoMovimiento.ubicacion'])
            ->limit(10)
            ->get();

        $exColaboradoresElegibles = (auth()->check() && auth()->user()->hasRole('user_admin'))
            ? Usuario::elegiblesBorradoHuella()
            : collect();

        return view('dashboard.index', compact(
            'totalElementos',
            'totalOperativos',
            'totalPrestados',
            'totalObsoletos',
            'totalEnReparacion',
            'prestamosActivos',
            'distribucionSalas',
            'alertasGarantiaVencida',
            'exColaboradoresElegibles'
        ));
    }
}
