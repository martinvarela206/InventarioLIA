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

        // 0. Carga de alto rendimiento: obtener el último movimiento de cada activo en un único query indexado (<60ms)
        $ultimosMovimientos = Movimiento::whereIn('id', function ($q) {
            $q->select(DB::raw('MAX(id)'))->from('movimientos')->groupBy('elemento_id');
        })->with(['estado', 'ubicacion'])->get();

        // 1. KPI Cards Principales (procesamiento instantáneo en memoria)
        $totalElementos = Elemento::count();
        $totalOperativos = $ultimosMovimientos->filter(function ($m) {
            return $m->estado?->disponible && empty($m->fecha_prestamo);
        })->count();

        $totalPrestados = $ultimosMovimientos->filter(function ($m) {
            return !empty($m->fecha_prestamo) && empty($m->fecha_devolucion);
        })->count();

        $totalEnReparacion = $ultimosMovimientos->filter(function ($m) {
            return in_array($m->estado?->nombre, ['en reparacion', 'verificar funcionamiento']);
        })->count();

        $totalObsoletos = $ultimosMovimientos->filter(function ($m) {
            return in_array($m->estado?->nombre, ['dado de baja', 'donado', 'transferido', 'basura tecnologica']);
        })->count();

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
        })->filter(function ($u) {
            return $u['total_activos'] > 0;
        })->sortByDesc('total_activos')->values();

        // 4. Alertas de Equipos Críticos
        $alertasGarantiaVencida = Elemento::whereNotNull('fecha_vencimiento_garantia')
            ->where('fecha_vencimiento_garantia', '<', $hoy)
            ->with(['tipo', 'ultimoMovimiento.ubicacion'])
            ->limit(10)
            ->get();

        $idsVerificar = $ultimosMovimientos->filter(function ($m) {
            return $m->estado?->nombre === 'verificar funcionamiento';
        })->pluck('elemento_id');

        $alertasVerificar = Elemento::whereIn('id', $idsVerificar)
            ->with(['tipo', 'ultimoMovimiento.ubicacion'])
            ->limit(10)
            ->get();

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
