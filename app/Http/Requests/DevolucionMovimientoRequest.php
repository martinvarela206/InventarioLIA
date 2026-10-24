<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DevolucionMovimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_devolucion' => 'nullable|date',
            'ubicacion_id' => 'required|exists:ubicaciones,id',
            'puesto' => 'nullable|string|max:50',
            'comentario' => 'nullable|string',
        ];
    }
}
