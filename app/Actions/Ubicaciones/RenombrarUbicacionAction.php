<?php

namespace App\Actions\Ubicaciones;

use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RenombrarUbicacionAction
{
    /**
     * Actualiza el nombre y descripción de una sala o espacio físico existente in-place,
     * preservando su identificador (ID), estado de activación y relaciones históricas.
     *
     * @return array{ubicacion: Ubicacion, ubicacion_antigua: Ubicacion, nueva_ubicacion: Ubicacion, renombro: bool, activos_en_limbo: int}
     *
     * @throws ValidationException
     */
    public function execute(int|Ubicacion $ubicacion, string $nuevoNombre, ?string $descripcion = null, ?Usuario $usuario = null): array
    {
        $nuevoNombre = trim($nuevoNombre);
        if ($nuevoNombre === '') {
            throw ValidationException::withMessages([
                'nombre' => 'El nombre del espacio o laboratorio es obligatorio.',
            ]);
        }

        return DB::transaction(function () use ($ubicacion, $nuevoNombre, $descripcion) {
            $ubicacionId = $ubicacion instanceof Ubicacion ? $ubicacion->id : $ubicacion;
            /** @var Ubicacion $ubi */
            $ubi = Ubicacion::where('id', $ubicacionId)->lockForUpdate()->firstOrFail();

            // Validar unicidad del nuevo nombre excluyendo la ubicación actual
            if (Ubicacion::where('nombre', $nuevoNombre)->where('id', '!=', $ubi->id)->exists()) {
                throw ValidationException::withMessages([
                    'nombre' => "Ya existe una sala o laboratorio registrado con el nombre \"{$nuevoNombre}\".",
                ]);
            }

            $renombro = $ubi->nombre !== $nuevoNombre;

            $ubi->update([
                'nombre' => $nuevoNombre,
                'descripcion' => $descripcion !== null ? (trim($descripcion) !== '' ? trim($descripcion) : null) : $ubi->descripcion,
            ]);

            return [
                'ubicacion' => $ubi,
                'ubicacion_antigua' => $ubi,
                'nueva_ubicacion' => $ubi,
                'renombro' => $renombro,
                'activos_en_limbo' => 0,
            ];
        });
    }
}
