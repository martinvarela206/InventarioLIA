<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'elemento_id' => 'required|exists:elementos,id',
            'tipo_movimiento' => 'required|in:interno,externo_prestamo,reparacion,baja',
            'estado_ticket' => 'nullable|string|max:30',
            'ticket_id' => 'nullable|exists:movimientos,id',
            'ubicacion_id' => 'nullable|exists:ubicaciones,id',
            'puesto' => 'nullable|string|max:50',
            'solicitante' => 'nullable|string|max:150',
            'fecha_prestamo' => 'nullable|date',
            'fecha' => 'nullable|date',
            'comentario' => 'nullable|string',
        ];
    }
}
