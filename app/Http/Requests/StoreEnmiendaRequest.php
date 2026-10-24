<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnmiendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motivo_correccion' => 'required|string|min:4|max:255',
            'ubicacion_id' => 'nullable|exists:ubicaciones,id',
            'puesto' => 'nullable|string|max:50',
            'estado_id' => 'required|exists:estados,id',
            'solicitante' => 'nullable|string|max:150',
            'comentario' => 'nullable|string',
        ];
    }
}
