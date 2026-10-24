<?php

namespace App\Http\Requests;

use App\Models\Elemento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreElementoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxAnio = (int)date('Y') + 1;

        return [
            'nro_lia' => 'nullable|string|max:50',
            'nro_unsj' => 'nullable|string|max:50',
            'tipo_id' => 'required|exists:tipos,id',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'nro_serie' => 'nullable|string|max:100',
            'anio_compra' => "nullable|integer|min:1980|max:{$maxAnio}",
            'puesto' => 'nullable|string|max:50',
            'ubicacion_id' => 'nullable|exists:ubicaciones,id',
            'estado_id' => 'nullable|exists:estados,id',
            'cantidad' => 'required|integer|min:1',
            'fecha_adquisicion' => 'nullable|date',
            'fecha_vencimiento_garantia' => 'nullable|date|after_or_equal:fecha_adquisicion',
            'observaciones' => 'nullable|string',
            'descripcion_keys' => 'nullable|array',
            'descripcion_values' => 'nullable|array',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $cleanLia = Elemento::normalizarIdentificador($this->input('nro_lia'));
            $cleanUnsj = Elemento::normalizarIdentificador($this->input('nro_unsj'));

            if ($cleanLia !== null && $cleanUnsj !== null) {
                $exists = Elemento::where('nro_lia', $cleanLia)
                    ->where('nro_unsj', $cleanUnsj)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('nro_lia', 'Ya existe un elemento registrado con la misma combinación de Nro LIA y Nro UNSJ.');
                }
            }
        });
    }
}
