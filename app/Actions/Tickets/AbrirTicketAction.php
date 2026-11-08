<?php

namespace App\Actions\Tickets;

use App\Enums\EstadoElemento;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\MovimientoAdjunto;
use App\Models\Usuario;
use App\Services\ImageSanitizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AbrirTicketAction
{
    /**
     * Abre un nuevo ticket de reparación técnica con transacción y bloqueo pesimista.
     *
     * @throws ValidationException
     */
    public function execute(int|Elemento $elemento, array $datos, ?Usuario $usuario = null, array $fotos = []): Movimiento
    {
        $elementoId = $elemento instanceof Elemento ? $elemento->id : $elemento;
        $user = $usuario ?? auth()->user();
        if (! $user) {
            throw new \InvalidArgumentException('Se requiere un usuario responsable identificado para registrar la operación patrimonial.');
        }

        return DB::transaction(function () use ($elementoId, $datos, $user, $fotos) {
            /** @var Elemento $el */
            $el = Elemento::where('id', $elementoId)->lockForUpdate()->firstOrFail();

            // Bloquear último movimiento para consistencia concurrente
            $ultimo = Movimiento::where('elemento_id', $el->id)
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $estado = $el->estado;

            if ($estado === EstadoElemento::BAJA) {
                throw ValidationException::withMessages([
                    'elemento_id' => 'No es posible abrir un ticket de avería sobre un activo dado de baja o clasificado como basura tecnológica.',
                ]);
            }

            if ($estado === EstadoElemento::EN_REPARACION) {
                throw ValidationException::withMessages([
                    'elemento_id' => 'El activo ya se encuentra en taller técnico con un ticket de avería activo pendiente de resolución.',
                ]);
            }

            // Si el activo estaba prestado, cerrar formalmente el préstamo para evitar préstamo fantasma (I5)
            if ($estado === EstadoElemento::PRESTADO) {
                $prestamoAbierto = Movimiento::where('elemento_id', $el->id)
                    ->whereNotNull('fecha_prestamo')
                    ->whereNull('fecha_devolucion')
                    ->lockForUpdate()
                    ->first();

                if ($prestamoAbierto) {
                    $prestamoAbierto->update([
                        'fecha_devolucion' => now()->toDateString(),
                    ]);
                }
            }

            $comentario = trim((string) ($datos['comentario'] ?? ''));
            if (strlen($comentario) < 5) {
                throw ValidationException::withMessages([
                    'comentario' => 'La descripción del problema técnico debe contener al menos 5 caracteres.',
                ]);
            }

            $solicitante = trim((string) ($datos['solicitante'] ?? ''));
            if ($solicitante === '') {
                $solicitante = $user->nombre ?: $user->email;
            }

            // Ubicación del ticket
            $esInsitu = ($datos['tipo_traslado'] ?? 'insitu') === 'insitu';
            $ubicacionId = $esInsitu
                ? $ultimo?->ubicacion_id
                : ($datos['ubicacion_id'] ?? $ultimo?->ubicacion_id);
            $puesto = $esInsitu
                ? $ultimo?->puesto
                : ($datos['puesto'] ?? (! empty($datos['ubicacion_id']) ? null : $ultimo?->puesto));

            $movimiento = Movimiento::create([
                'elemento_id' => $el->id,
                'tipo_movimiento' => 'reparacion',
                'estado_ticket' => 'abierto',
                'ubicacion_id' => $ubicacionId,
                'puesto' => $puesto,
                'user_id' => $user->id,
                'fecha' => now(),
                'solicitante' => $solicitante,
                'comentario' => $comentario,
            ]);

            if (! empty($datos['area_id'])) {
                $movimiento->areas()->attach($datos['area_id'], [
                    'user_id' => $user->id,
                    'created_at' => now(),
                ]);
            }

            foreach ($fotos as $foto) {
                if ($foto && $foto->isValid()) {
                    $path = ImageSanitizer::sanitizeAndStore($foto, 'tickets', 'local');
                    MovimientoAdjunto::create([
                        'movimiento_id' => $movimiento->id,
                        'archivo_path' => $path,
                        'nombre_original' => $foto->getClientOriginalName(),
                        'mime_type' => $foto->getMimeType(),
                    ]);
                }
            }

            return $movimiento;
        });
    }
}
