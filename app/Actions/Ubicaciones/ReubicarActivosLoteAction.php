<?php

namespace App\Actions\Ubicaciones;

use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Ubicacion;
use App\Models\Usuario;
use App\Enums\EstadoElemento;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReubicarActivosLoteAction
{
    /**
     * Reubica un lote de activos (sea a un destino común o con destinos discriminados por activo),
     * aplicando bloqueo pesimista y sacando a los activos de estado de "limbo".
     *
     * @param array<int|array{elemento_id: int, ubicacion_id?: int, puesto?: string}> $items
     * @param int|null $ubicacionComunId ID de destino si todos van al mismo espacio
     * @param Usuario|null $usuario
     * @param string|null $comentario
     * @return array{total_reubicados: int, movimientos: array<Movimiento>}
     * @throws ValidationException
     */
    public function execute(array $items, ?int $ubicacionComunId = null, ?Usuario $usuario = null, ?string $comentario = null): array
    {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'Debe especificar al menos un activo para reubicar.',
            ]);
        }

        $actor = $usuario ?? auth()->user();
        if (!$actor) {
            throw new \InvalidArgumentException('Se requiere un usuario responsable identificado para registrar la operación patrimonial.');
        }
        $userId = $actor->id;

        return DB::transaction(function () use ($items, $ubicacionComunId, $userId, $comentario) {
            $destinosCache = [];
            $movimientosGenerados = [];

            foreach ($items as $item) {
                $elementoId = is_array($item) ? ($item['elemento_id'] ?? null) : (int)$item;
                $destinoId = is_array($item) ? ($item['ubicacion_id'] ?? $ubicacionComunId) : $ubicacionComunId;
                $puesto = is_array($item) ? ($item['puesto'] ?? null) : null;

                if (!$elementoId) {
                    continue;
                }

                if (!$destinoId) {
                    throw ValidationException::withMessages([
                        'ubicacion_id' => 'Cada activo debe poseer una sala destino válida asignada.',
                    ]);
                }

                // Cargar y validar la sala destino
                if (!isset($destinosCache[$destinoId])) {
                    $sala = Ubicacion::where('id', $destinoId)->first();
                    if (!$sala || !$sala->activa) {
                        throw ValidationException::withMessages([
                            'ubicacion_id' => "La sala destino seleccionada (ID: {$destinoId}) no existe o se encuentra archivada e inactiva.",
                        ]);
                    }
                    $destinosCache[$destinoId] = $sala;
                }
                $destino = $destinosCache[$destinoId];

                /** @var Elemento $el */
                $el = Elemento::where('id', $elementoId)->lockForUpdate()->firstOrFail();

                // Bloquear último movimiento para consistencia concurrente
                $ultimo = Movimiento::where('elemento_id', $el->id)
                    ->orderBy('id', 'desc')
                    ->lockForUpdate()
                    ->first();

                if ($el->estado === EstadoElemento::BAJA) {
                    $identificador = $el->nro_lia ?: $el->nro_unsj ?: "#{$el->id}";
                    throw ValidationException::withMessages([
                        'elemento_id' => "El activo {$identificador} se encuentra dado de baja patrimonial y no puede ser reubicado.",
                    ]);
                }

                $comentarioFinal = $comentario
                    ? trim($comentario)
                    : "Reubicación de activo (traslado fuera de limbo a {$destino->nombre})";

                $movimiento = Movimiento::create([
                    'elemento_id' => $el->id,
                    'tipo_movimiento' => 'interno',
                    'ubicacion_id' => $destino->id,
                    'puesto' => $puesto ?: null,
                    'user_id' => $userId,
                    'fecha' => now(),
                    'comentario' => $comentarioFinal,
                ]);

                $movimientosGenerados[] = $movimiento;
            }

            return [
                'total_reubicados' => count($movimientosGenerados),
                'movimientos' => $movimientosGenerados,
            ];
        });
    }
}
