<?php

namespace App\Http\Requests;

use App\Models\Elemento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateElementoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxAnio = (int) date('Y') + 1;

        return [
            'nro_lia' => 'nullable|string|max:50|required_without:nro_unsj',
            'nro_unsj' => 'nullable|string|max:50|required_without:nro_lia',
            'tipo_id' => 'required|exists:tipos,id',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'nro_serie' => 'nullable|string|max:100',
            'anio_compra' => "nullable|integer|min:1980|max:{$maxAnio}",
            'puesto' => 'nullable|string|max:50',
            'cantidad' => 'required|integer|min:1',
            'fecha_adquisicion' => 'nullable|date|before_or_equal:today',
            'fecha_vencimiento_garantia' => 'nullable|date|after_or_equal:fecha_adquisicion',
            'observaciones' => 'nullable|string',
            'descripcion_keys' => 'nullable|array',
            'descripcion_values' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'nro_lia.required_without' => 'Debe ingresar al menos un número identificador (Nro LIA o Nro UNSJ).',
            'nro_unsj.required_without' => 'Debe ingresar al menos un número identificador (Nro LIA o Nro UNSJ).',
            'fecha_vencimiento_garantia.after_or_equal' => 'La fecha de vencimiento de garantía debe ser posterior o igual a la de adquisición.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $cleanLia = Elemento::normalizarIdentificador($this->input('nro_lia'));
            $cleanUnsj = Elemento::normalizarIdentificador($this->input('nro_unsj'));
            $elementoId = $this->route('elemento') instanceof Elemento
                ? $this->route('elemento')->id
                : $this->route('elemento');

            if ($cleanLia === null && $cleanUnsj === null) {
                $validator->errors()->add('nro_lia', 'Debe ingresar al menos un identificador válido (Nro LIA o Nro UNSJ).');

                return;
            }

            if ($cleanLia !== null) {
                if (Elemento::where('nro_lia', $cleanLia)->where('id', '!=', $elementoId)->exists()) {
                    $validator->errors()->add('nro_lia', "Ya existe otro activo registrado con el Nro LIA '{$cleanLia}'.");
                }
            }

            if ($cleanUnsj !== null) {
                if (Elemento::where('nro_unsj', $cleanUnsj)->where('id', '!=', $elementoId)->exists()) {
                    $validator->errors()->add('nro_unsj', "Ya existe otro activo registrado con el Nro UNSJ '{$cleanUnsj}'.");
                }
            }
        });
    }
}
