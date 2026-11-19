<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreTicketRequest extends FormRequest
{
    /**
     * Determina si el usuario autenticado tiene permisos para abrir tickets.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) ($user && $user->activo && $user->hasRole(['rev', 'subtec', 'tec', 'asset_admin', 'user_admin', 'app_admin', 'item_admin']));
    }

    /**
     * Prepara los datos antes de la validación formal.
     * Si no se envía un título explícito pero sí una descripción, se auto-extrae el título para retrocompatibilidad.
     */
    protected function prepareForValidation(): void
    {
        if (empty($this->titulo) && ! empty($this->comentario)) {
            $this->merge([
                'titulo' => Str::limit(trim(strip_tags((string) $this->comentario)), 60),
            ]);
        }
    }

    /**
     * Reglas de validación para la creación de tickets.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'modalidad' => ['nullable', 'string', 'in:activo,general'],
            'elemento_id' => ['nullable', 'required_without:ambito_ticket_id', 'exists:elementos,id'],
            'ambito_ticket_id' => ['nullable', 'required_without:elemento_id', 'exists:ambitos_tickets,id'],
            'area_id' => ['nullable', 'exists:areas,id'],
            'titulo' => ['required_without:comentario', 'nullable', 'string', 'min:3', 'max:255'],
            'comentario' => ['nullable', 'string'],
            'solicitante' => ['nullable', 'string', 'max:150'],
            'tipo_traslado' => ['nullable', 'in:insitu,taller'],
            'ubicacion_id' => ['nullable', 'exists:ubicaciones,id'],
            'puesto' => ['nullable', 'string', 'max:50'],
            'fotos' => ['nullable', 'array', 'max:10'],
            'fotos.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ];
    }

    /**
     * Mensajes de error personalizados para validación amigable e institucional.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'titulo.required' => 'El título del ticket es obligatorio.',
            'titulo.min' => 'El título debe contener al menos 3 caracteres.',
            'elemento_id.required_without' => 'Debe seleccionar un activo de inventario o un ámbito de infraestructura general.',
            'elemento_id.exists' => 'El activo patrimonial seleccionado no existe en el sistema.',
            'ambito_ticket_id.required_without' => 'Debe seleccionar un ámbito general si la incidencia no está asociada a un activo patrimonial.',
            'ambito_ticket_id.exists' => 'El ámbito seleccionado no es válido o ha sido dado de baja.',
            'fotos.max' => 'No puede adjuntar más de 10 fotografías de evidencia.',
            'fotos.*.max' => 'Cada imagen adjunta no debe superar los 10 MB de tamaño.',
            'fotos.*.image' => 'Los archivos adjuntos deben ser imágenes válidas (JPG, PNG o WebP).',
        ];
    }
}
