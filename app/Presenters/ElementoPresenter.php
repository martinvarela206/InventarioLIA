<?php

namespace App\Presenters;

use App\Models\Elemento;
use Carbon\Carbon;

class ElementoPresenter
{
    protected Elemento $elemento;

    public function __construct(Elemento $elemento)
    {
        $this->elemento = $elemento;
    }

    /**
     * Formateo visual consistente: "LIA: 02-05959 | UNSJ: 02-05969"
     */
    public function identificadorCompleto(): string
    {
        $lia = $this->elemento->nro_lia ?: '-';
        $unsj = $this->elemento->nro_unsj ?: '-';
        return "LIA: {$lia} | UNSJ: {$unsj}";
    }

    /**
     * Badge HTML con colores semánticos según el estado del último movimiento
     */
    public function statusBadge(): string
    {
        $ultimo = $this->elemento->ultimoMovimiento;
        if (!$ultimo) {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">Sin historial</span>';
        }

        if ($ultimo->tipo_movimiento === 'baja' || $this->elemento->tipo?->nombre === 'basura tecnologica') {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-300">Basura Tecnológica</span>';
        }

        if ($ultimo->esTicketAbierto()) {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300">En Reparación</span>';
        }

        if ($ultimo->esPrestamo() && !$ultimo->fecha_devolucion) {
            $dias = $ultimo->diasPrestado();
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-300">Prestado (' . $dias . ' d)</span>';
        }

        return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">En Sala / Operativo</span>';
    }

    /**
     * Indicador de garantía vigente o vencida con cálculo de tiempo relativo
     */
    public function garantiaBadge(): string
    {
        if (!$this->elemento->fecha_vencimiento_garantia) {
            return '<span class="text-xs text-gray-500 italic">Sin datos de garantía</span>';
        }

        $vencimiento = Carbon::parse($this->elemento->fecha_vencimiento_garantia);
        $hoy = Carbon::today();

        if ($vencimiento->isPast()) {
            return '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Vencida hace ' . $vencimiento->diffForHumans($hoy, ['parts' => 1, 'syntax' => Carbon::DIFF_ABSOLUTE]) . '</span>';
        }

        return '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Vigente (vence en ' . $hoy->diffForHumans($vencimiento, ['parts' => 1, 'syntax' => Carbon::DIFF_ABSOLUTE]) . ')</span>';
    }

    /**
     * Ubicación física formateada con sala y puesto (ej. "Laboratorio Federico Sanchez - Puesto 04")
     */
    public function puestoUbicacion(): string
    {
        $ultimo = $this->elemento->ultimoMovimiento;
        $sala = $ultimo?->ubicacion?->nombre ?? 'Sin asignar';
        $puesto = $ultimo?->puesto ?: $this->elemento->puesto;

        if ($puesto) {
            return "{$sala} - {$puesto}";
        }

        return $sala;
    }

    /**
     * Renderizado visual en HTML con badges compactos de especificaciones técnicas.
     */
    public function especificacionesHtml(): string
    {
        return $this->elemento->especificacionesSintetizadas(true);
    }

    /**
     * Renderizado en texto plano delimitado por pipes para reportes y exportación.
     */
    public function especificacionesTexto(): string
    {
        return $this->elemento->especificacionesSintetizadas(false);
    }
}
