<?php

namespace App\Actions\Movimientos;

use App\Enums\EstadoElemento;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrarPrestamoAction
{
    /**
     * Registra de forma transaccional y con bloqueo pesimista un nuevo préstamo externo de un activo.
     *
     * @throws ValidationException
     */
    public function execute(int|Elemento $elemento, array $datos, ?Usuario $usuario = null): Movimiento
    {
        $elementoId = $elemento instanceof Elemento ? $elemento->id : $elemento;
        $actor = $usuario ?? auth()->user();
        if (! $actor) {
            throw new \InvalidArgumentException('Se requiere un usuario responsable identificado para registrar la operación patrimonial.');
        }

        return DB::transaction(function () use ($elementoId, $datos, $actor) {
            /** @var Elemento $el */
            $el = Elemento::where('id', $elementoId)->lockForUpdate()->firstOrFail();

            // Bloquear el último movimiento para evitar condiciones de carrera concurrentes
            $ultimo = Movimiento::where('elemento_id', $el->id)
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $estado = $el->estado;

            if ($estado === EstadoElemento::BAJA) {
                throw ValidationException::withMessages([
                    'elemento_id' => 'No es posible prestar un activo dado de baja o clasificado como basura tecnológica.',
                ]);
            }

            if ($estado === EstadoElemento::EN_REPARACION) {
                throw ValidationException::withMessages([
                    'elemento_id' => 'No es posible prestar un activo que se encuentra en taller técnico o con un ticket de avería abierto.',
                ]);
            }

            if ($estado === EstadoElemento::EN_LIMBO_REUBICACION) {
                throw ValidationException::withMessages([
                    'elemento_id' => 'No es posible prestar un activo que se encuentra en limbo pendiente de reubicación física.',
                ]);
            }

            if ($estado === EstadoElemento::PRESTADO) {
                $solicitanteActual = $ultimo?->solicitante ?? 'otro solicitante';
                throw ValidationException::withMessages([
                    'elemento_id' => "El activo ya posee un préstamo activo sin devolver a cargo de: {$solicitanteActual}.",
                ]);
            }

            $solicitante = trim((string) ($datos['solicitante'] ?? ''));
            if ($solicitante === '') {
                throw ValidationException::withMessages([
                    'solicitante' => 'El solicitante del préstamo es obligatorio.',
                ]);
            }

            $fechaPrestamo = ! empty($datos['fecha_prestamo'])
                ? Carbon::parse($datos['fecha_prestamo'])->toDateString()
                : now()->toDateString();

            if (Carbon::parse($fechaPrestamo)->isFuture()) {
                throw ValidationException::withMessages([
                    'fecha_prestamo' => 'La fecha de préstamo no puede ser una fecha futura.',
                ]);
            }

            $fechaMovimiento = ! empty($datos['fecha'])
                ? Carbon::parse($datos['fecha'])
                : now();

            if ($fechaMovimiento->isFuture()) {
                throw ValidationException::withMessages([
                    'fecha' => 'La fecha de registro del movimiento no puede ser futura.',
                ]);
            }

            $userId = $actor->id;

            return Movimiento::create([
                'elemento_id' => $el->id,
                'tipo_movimiento' => 'externo_prestamo',
                'ubicacion_id' => null,
                'puesto' => null,
                'user_id' => $userId,
                'fecha' => $fechaMovimiento,
                'solicitante' => $solicitante,
                'fecha_prestamo' => $fechaPrestamo,
                'fecha_devolucion' => null,
                'comentario' => $datos['comentario'] ?? null,
            ]);
        });
    }
}
