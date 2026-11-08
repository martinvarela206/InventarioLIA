<?php

namespace App\Actions\Ubicaciones;

use App\Models\Ubicacion;
use App\Models\Elemento;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RenombrarUbicacionAction
{
    /**
     * Renombra un espacio físico tratando el nuevo nombre como una nueva Ubicación activa
     * y archivando la original como histórica, pasando sus activos a estado de limbo.
     *
     * @return array{ubicacion_antigua: Ubicacion, nueva_ubicacion: Ubicacion, renombro: bool, activos_en_limbo: int}
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
            /** @var Ubicacion $ubiAntigua */
            $ubiAntigua = Ubicacion::where('id', $ubicacionId)->lockForUpdate()->firstOrFail();

            // Si el nombre es exactamente igual, solo actualizar descripción
            if ($ubiAntigua->nombre === $nuevoNombre) {
                $ubiAntigua->update([
                    'descripcion' => $descripcion !== null ? trim($descripcion) : $ubiAntigua->descripcion,
                ]);

                return [
                    'ubicacion_antigua' => $ubiAntigua,
                    'nueva_ubicacion' => $ubiAntigua,
                    'renombro' => false,
                    'activos_en_limbo' => 0,
                ];
            }

            // Validar unicidad del nuevo nombre
            if (Ubicacion::where('nombre', $nuevoNombre)->where('id', '!=', $ubiAntigua->id)->exists()) {
                throw ValidationException::withMessages([
                    'nombre' => "Ya existe una sala o laboratorio registrado con el nombre \"{$nuevoNombre}\".",
                ]);
            }

            // Contar activos actualmente asignados a la sala antigua
            $activosCount = Elemento::whereHas('ultimoMovimiento', function ($q) use ($ubiAntigua) {
                $q->where('ubicacion_id', $ubiAntigua->id)
                  ->whereNotIn('tipo_movimiento', ['baja']);
            })->whereDoesntHave('tipo', fn($t) => $t->where('nombre', 'basura tecnologica'))->count();

            // 1. Archivar la ubicación antigua como histórica
            $ubiAntigua->update([
                'activa' => false,
            ]);

            // 2. Crear la nueva entidad de Ubicación con el nuevo nombre
            $nuevaUbi = Ubicacion::create([
                'nombre' => $nuevoNombre,
                'descripcion' => $descripcion !== null ? trim($descripcion) : $ubiAntigua->descripcion,
                'activa' => true,
            ]);

            return [
                'ubicacion_antigua' => $ubiAntigua,
                'nueva_ubicacion' => $nuevaUbi,
                'renombro' => true,
                'activos_en_limbo' => $activosCount,
            ];
        });
    }
}
