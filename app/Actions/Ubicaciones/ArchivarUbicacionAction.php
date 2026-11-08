<?php

namespace App\Actions\Ubicaciones;

use App\Models\Elemento;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ArchivarUbicacionAction
{
    /**
     * Archiva una sala o laboratorio como histórica (activa = false),
     * pasando todos sus activos asociados a estado de "limbo" para reubicación.
     *
     * @return array{ubicacion: Ubicacion, activos_en_limbo: int}
     *
     * @throws ValidationException
     */
    public function execute(int|Ubicacion $ubicacion, ?Usuario $usuario = null): array
    {
        return DB::transaction(function () use ($ubicacion) {
            $ubicacionId = $ubicacion instanceof Ubicacion ? $ubicacion->id : $ubicacion;
            /** @var Ubicacion $ubi */
            $ubi = Ubicacion::where('id', $ubicacionId)->lockForUpdate()->firstOrFail();

            if (! $ubi->activa) {
                throw ValidationException::withMessages([
                    'ubicacion_id' => "La sala \"{$ubi->nombre}\" ya se encuentra archivada e inactiva.",
                ]);
            }

            // Contar activos que entran al limbo
            $activosCount = Elemento::whereHas('ultimoMovimiento', function ($q) use ($ubi) {
                $q->where('ubicacion_id', $ubi->id)
                    ->whereNotIn('tipo_movimiento', ['baja']);
            })->whereDoesntHave('tipo', fn ($t) => $t->where('nombre', 'basura tecnologica'))->count();

            // Archivar como histórica
            $ubi->update([
                'activa' => false,
            ]);

            return [
                'ubicacion' => $ubi,
                'activos_en_limbo' => $activosCount,
            ];
        });
    }
}
