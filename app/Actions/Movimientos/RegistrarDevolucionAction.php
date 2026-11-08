<?php

namespace App\Actions\Movimientos;

use App\Models\Movimiento;
use App\Models\Elemento;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class RegistrarDevolucionAction
{
    /**
     * Registra de forma transaccional con bloqueo pesimista la devolución de un préstamo.
     *
     * @throws ValidationException
     */
    public function execute(int|Movimiento $movimiento, array $datos, ?Usuario $usuario = null): Movimiento
    {
        $movimientoId = $movimiento instanceof Movimiento ? $movimiento->id : $movimiento;

        return DB::transaction(function () use ($movimientoId, $datos, $usuario) {
            /** @var Movimiento $prestamo */
            $prestamo = Movimiento::where('id', $movimientoId)->lockForUpdate()->firstOrFail();

            // 1. Validar que el movimiento sea efectivamente un préstamo externo
            if (!$prestamo->esPrestamo()) {
                throw ValidationException::withMessages([
                    'movimiento_id' => 'El movimiento especificado no corresponde a un préstamo externo y no puede devolverse.',
                ]);
            }

            // 2. Validar que no haya sido devuelto previamente (protección contra doble clic / condición de carrera)
            if ($prestamo->fecha_devolucion !== null) {
                $fechaStr = Carbon::parse($prestamo->fecha_devolucion)->format('d/m/Y');
                throw ValidationException::withMessages([
                    'movimiento_id' => "Este préstamo ya fue devuelto previamente el día {$fechaStr}.",
                ]);
            }

            // 3. Bloquear el Elemento para asegurar consistencia
            /** @var Elemento $el */
            $el = Elemento::where('id', $prestamo->elemento_id)->lockForUpdate()->firstOrFail();

            // 4. Validar ubicación de reingreso (debe existir y estar activa)
            $ubicacionId = $datos['ubicacion_id'] ?? null;
            if (!$ubicacionId) {
                throw ValidationException::withMessages([
                    'ubicacion_id' => 'La ubicación de reingreso es obligatoria.',
                ]);
            }

            $ubicacion = Ubicacion::where('id', $ubicacionId)->first();
            if (!$ubicacion || !$ubicacion->activa) {
                throw ValidationException::withMessages([
                    'ubicacion_id' => 'La ubicación seleccionada para el reingreso no está activa o se encuentra archivada.',
                ]);
            }

            // 5. Validar fecha de devolución
            $fechaDev = !empty($datos['fecha_devolucion'])
                ? Carbon::parse($datos['fecha_devolucion'])->toDateString()
                : now()->toDateString();

            if (Carbon::parse($fechaDev)->isFuture()) {
                throw ValidationException::withMessages([
                    'fecha_devolucion' => 'La fecha de devolución no puede ser una fecha futura.',
                ]);
            }

            if ($prestamo->fecha_prestamo && Carbon::parse($fechaDev)->lt(Carbon::parse($prestamo->fecha_prestamo))) {
                throw ValidationException::withMessages([
                    'fecha_devolucion' => 'La fecha de devolución no puede ser anterior a la fecha del préstamo.',
                ]);
            }

            // 6. Actualizar préstamo original (permitido por trigger/modelo)
            $prestamo->update([
                'fecha_devolucion' => $fechaDev,
            ]);

            $userId = $usuario?->id ?? auth()->id() ?? Usuario::first()?->id;

            // 7. Crear el nuevo movimiento interno de reingreso al laboratorio
            $comentario = 'Reingreso de préstamo externo.';
            if (!empty($datos['comentario'])) {
                $comentario .= ' ' . trim($datos['comentario']);
            }

            return Movimiento::create([
                'elemento_id' => $el->id,
                'tipo_movimiento' => 'interno',
                'ubicacion_id' => $ubicacion->id,
                'puesto' => $datos['puesto'] ?? null,
                'user_id' => $userId,
                'fecha' => now(),
                'comentario' => $comentario,
            ]);
        });
    }
}
