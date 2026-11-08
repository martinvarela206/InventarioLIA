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
            'tipo_movimiento' => 'required|in:interno,externo_prestamo',
            'ubicacion_id' => 'required_if:tipo_movimiento,interno|nullable|exists:ubicaciones,id',
            'puesto' => 'nullable|string|max:50',
            'solicitante' => 'required_if:tipo_movimiento,externo_prestamo|nullable|string|max:150',
            'fecha_prestamo' => 'nullable|date|before_or_equal:today',
            'fecha' => 'nullable|date|before_or_equal:now',
            'comentario' => 'nullable|string',
        ];
    }
}
