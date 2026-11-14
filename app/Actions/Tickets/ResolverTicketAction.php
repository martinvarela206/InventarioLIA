<?php

namespace App\Actions\Tickets;

use App\Enums\EstadoElemento;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResolverTicketAction
{
    /**
     * Resuelve formalmente un ticket técnico o lo declara baja patrimonial,
     * aplicando bloqueo pesimista y previniendo resurrección de bajas o dobles resoluciones.
     *
     * @throws ValidationException
     */
    public function execute(int|Movimiento $ticket, array $datos, ?Usuario $usuario = null): Movimiento
    {
        $ticketId = $ticket instanceof Movimiento ? $ticket->id : $ticket;
        $user = $usuario ?? auth()->user();
        if (! $user) {
            throw new \InvalidArgumentException('Se requiere un usuario responsable identificado para registrar la operación patrimonial.');
        }

        return DB::transaction(function () use ($ticketId, $datos, $user) {
            /** @var Movimiento $tkt */
            $tkt = Movimiento::where('id', $ticketId)->lockForUpdate()->firstOrFail();

            if ($tkt->tipo_movimiento !== 'reparacion') {
                throw ValidationException::withMessages([
                    'ticket_id' => 'El movimiento especificado no es un ticket de reparación técnica.',
                ]);
            }

            if ($tkt->estado_ticket === 'resuelto') {
                throw ValidationException::withMessages([
                    'ticket_id' => 'El ticket ya ha sido finalizado y resuelto previamente.',
                ]);
            }

            if ($tkt->estado_ticket === 'baja') {
                throw ValidationException::withMessages([
                    'ticket_id' => 'El activo asociado a este ticket ya fue declarado baja patrimonial irreparable y no puede ser reactivado.',
                ]);
            }

            $solucion = trim((string) ($datos['solucion'] ?? ''));
            if (strlen($solucion) < 5) {
                throw ValidationException::withMessages([
                    'solucion' => 'El detalle técnico de la solución debe contener al menos 5 caracteres.',
                ]);
            }

            // Rama A: Incidencia General de Infraestructura / Espacios (sin activo patrimonial)
            if ($tkt->esTicketGeneral()) {
                $comentario = "RESOLUCIÓN INCIDENCIA GENERAL (Ticket #{$tkt->id}): {$solucion}";

                $movimientoResolucion = Movimiento::create([
                    'elemento_id' => null,
                    'ambito_ticket_id' => $tkt->ambito_ticket_id,
                    'tipo_movimiento' => 'interno',
                    'estado_ticket' => 'resuelto',
                    'ubicacion_id' => $tkt->ubicacion_id,
                    'puesto' => $tkt->puesto,
                    'user_id' => $user->id,
                    'fecha' => now(),
                    'ticket_id' => $tkt->id,
                    'correccion_de_id' => null,
                    'comentario' => $comentario,
                ]);

                $tkt->update([
                    'estado_ticket' => 'resuelto',
                ]);

                return $movimientoResolucion;
            }

            // Rama B: Incidencia sobre Activo Patrimonial Específico
            /** @var Elemento $el */
            $el = Elemento::where('id', $tkt->elemento_id)->lockForUpdate()->firstOrFail();

            $accion = $datos['accion'] ?? 'resolver';
            if (! in_array($accion, ['resolver', 'baja'])) {
                throw ValidationException::withMessages([
                    'accion' => 'La acción de cierre debe ser resolver o baja.',
                ]);
            }

            if ($el->estado === EstadoElemento::BAJA && $accion === 'resolver') {
                throw ValidationException::withMessages([
                    'accion' => 'No es posible reactivar o resolver como operativo un activo clasificado como baja patrimonial.',
                ]);
            }

            $esBaja = ($accion === 'baja');
            $tipoMov = $esBaja ? 'baja' : 'interno';
            $estadoTicket = $esBaja ? 'baja' : 'resuelto';

            $ubicacionId = $datos['ubicacion_id'] ?? $tkt->ubicacion_id;
            if (! $esBaja && $ubicacionId) {
                $ubicacion = Ubicacion::find($ubicacionId);
                if ($ubicacion && ! $ubicacion->activa) {
                    throw ValidationException::withMessages([
                        'ubicacion_id' => 'No se puede reintegrar el activo a una sala que se encuentra inactiva o archivada.',
                    ]);
                }
            }

            $comentario = $esBaja
                ? "BAJA PATRIMONIAL (Irreparable - Ticket #{$tkt->id}): {$solucion}"
                : "RESOLUCIÓN TICKET #{$tkt->id}: {$solucion}";

            // Crear el nuevo movimiento de resolución (correccion_de_id es NULL: desacoplado de enmiendas)
            $movimientoResolucion = Movimiento::create([
                'elemento_id' => $el->id,
                'tipo_movimiento' => $tipoMov,
                'estado_ticket' => $estadoTicket,
                'ubicacion_id' => $ubicacionId,
                'puesto' => $datos['puesto'] ?? $tkt->puesto,
                'user_id' => $user->id,
                'fecha' => now(),
                'ticket_id' => $tkt->id,
                'correccion_de_id' => null, // Desacoplado de enmiendas
                'comentario' => $comentario,
            ]);

            // Actualizar estado del ticket origen
            $tkt->update([
                'estado_ticket' => $estadoTicket,
            ]);

            return $movimientoResolucion;
        });
    }
}
