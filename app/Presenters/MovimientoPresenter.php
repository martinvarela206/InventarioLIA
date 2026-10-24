<?php

namespace App\Presenters;

use App\Models\Movimiento;

class MovimientoPresenter
{
    protected Movimiento $movimiento;

    public function __construct(Movimiento $movimiento)
    {
        $this->movimiento = $movimiento;
    }

    public function tipoBadge(): string
    {
        $badge = '';
        if ($this->movimiento->esCorreccion()) {
            $badge .= '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-purple-100 text-purple-900 border border-purple-300 mr-1.5">Enmienda de #' . $this->movimiento->correccion_de_id . '</span> ';
        }

        if ($this->movimiento->esPrestamo()) {
            return $badge . '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300">Préstamo Externo</span>';
        }
        return $badge . '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-300">Movimiento Interno</span>';
    }

    public function correccionBadge(): string
    {
        if ($this->movimiento->esCorreccion()) {
            return '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-purple-100 text-purple-900 border border-purple-300">Enmienda de #' . $this->movimiento->correccion_de_id . '</span>';
        }
        return '';
    }

    public function destinoTexto(): string
    {
        $ubicacion = $this->movimiento->ubicacion?->nombre ?? 'N/A';
        $puesto = $this->movimiento->puesto;
        if ($puesto) {
            return "{$ubicacion} ({$puesto})";
        }
        return $ubicacion;
    }

    public function solicitanteTexto(): string
    {
        if ($this->movimiento->esPrestamo()) {
            return $this->movimiento->solicitante ?: 'No especificado';
        }
        return '-';
    }

    public function estadoBadge(): string
    {
        if ($this->movimiento->esPrestamo()) {
            return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Préstamo</span>';
        }
        return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Interno</span>';
    }
}
