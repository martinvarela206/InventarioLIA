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
        return view('components.movimiento-tipo-badge', ['movimiento' => $this->movimiento])->render();
    }

    public function correccionBadge(): string
    {
        if ($this->movimiento->esCorreccion()) {
            return '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-purple-100 text-purple-900 border border-purple-300">Enmienda de #' . (int)$this->movimiento->correccion_de_id . '</span>';
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
        return view('components.movimiento-estado-badge', ['movimiento' => $this->movimiento])->render();
    }
}
