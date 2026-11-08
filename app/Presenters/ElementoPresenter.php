<?php

namespace App\Presenters;

use App\Models\Elemento;

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
     * Badge HTML con colores semánticos según el estado canónico del activo
     */
    public function statusBadge(): string
    {
        return view('components.elemento-status-badge', ['elemento' => $this->elemento])->render();
    }

    /**
     * Indicador de garantía vigente o vencida con cálculo de tiempo relativo
     */
    public function garantiaBadge(): string
    {
        return view('components.elemento-garantia-badge', ['elemento' => $this->elemento])->render();
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
