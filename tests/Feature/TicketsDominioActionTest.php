<?php

namespace Tests\Feature;

use App\Actions\Tickets\AbrirTicketAction;
use App\Actions\Tickets\ResolverTicketAction;
use App\Enums\EstadoElemento;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TicketsDominioActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_apertura_ticket_exitosa_pasa_activo_a_en_reparacion(): void
    {
        $tipo = Tipo::first();
        $ubicacion = Ubicacion::where('activa', true)->first();
        $tec = Usuario::whereHas('roles', fn ($q) => $q->where('rol', 'tec'))->first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-TKT-01',
            'tipo_id' => $tipo->id,
            'modelo' => 'Monitor Dell',
        ]);

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $tec->id,
            'fecha' => now(),
        ]);

        $ticket = app(AbrirTicketAction::class)->execute($elemento, [
            'comentario' => 'Pantalla parpadea intermitentemente',
            'tipo_traslado' => 'insitu',
        ], $tec);

        $this->assertInstanceOf(Movimiento::class, $ticket);
        $this->assertEquals('reparacion', $ticket->tipo_movimiento);
        $this->assertEquals('abierto', $ticket->estado_ticket);

        $elemento->refresh();
        $this->assertEquals(EstadoElemento::EN_REPARACION, $elemento->estado);
    }

    public function test_apertura_ticket_sobre_activo_prestado_cierra_prestamo_formalmente(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn ($q) => $q->where('rol', 'tec'))->first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-TKT-PREST',
            'tipo_id' => $tipo->id,
            'modelo' => 'Notebook Lenovo',
        ]);

        $prestamo = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'externo_prestamo',
            'user_id' => $tec->id,
            'fecha' => now()->subDays(2),
            'solicitante' => 'Profesor Carlos',
            'fecha_prestamo' => now()->subDays(2)->toDateString(),
            'fecha_devolucion' => null,
        ]);

        $this->assertEquals(EstadoElemento::PRESTADO, $elemento->fresh()->estado);

        // Al abrir ticket por avería durante el préstamo:
        $ticket = app(AbrirTicketAction::class)->execute($elemento, [
            'comentario' => 'Teclado no responde tras derrame de cafe durante prestamo',
            'tipo_traslado' => 'taller',
        ], $tec);

        $prestamo->refresh();
        // El préstamo original debe quedar con fecha de devolución registrada, eliminando el préstamo fantasma
        $this->assertNotNull($prestamo->fecha_devolucion);
        $this->assertEquals(now()->toDateString(), $prestamo->fecha_devolucion->toDateString());

        $elemento->refresh();
        $this->assertEquals(EstadoElemento::EN_REPARACION, $elemento->estado);
    }

    public function test_rechaza_abrir_ticket_sobre_activo_en_baja_o_con_ticket_abierto(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn ($q) => $q->where('rol', 'tec'))->first();

        // 1. Activo en baja
        $elementoBaja = Elemento::create([
            'nro_lia' => 'TST-TKT-BAJA',
            'tipo_id' => $tipo->id,
            'modelo' => 'Chasis Quemado',
        ]);
        Movimiento::create([
            'elemento_id' => $elementoBaja->id,
            'tipo_movimiento' => 'baja',
            'user_id' => $tec->id,
            'fecha' => now(),
        ]);

        try {
            app(AbrirTicketAction::class)->execute($elementoBaja, [
                'comentario' => 'Intento reparar activo en baja',
            ], $tec);
            $this->fail('Debió fallar por activo en baja');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('elemento_id', $e->errors());
        }

        // 2. Activo ya con ticket abierto
        $elementoTkt = Elemento::create([
            'nro_lia' => 'TST-TKT-DUPL',
            'tipo_id' => $tipo->id,
            'modelo' => 'PC en taller',
        ]);
        Movimiento::create([
            'elemento_id' => $elementoTkt->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'user_id' => $tec->id,
            'fecha' => now(),
        ]);

        try {
            app(AbrirTicketAction::class)->execute($elementoTkt, [
                'comentario' => 'Segundo ticket simultaneo',
            ], $tec);
            $this->fail('Debió fallar por ticket abierto existente');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('elemento_id', $e->errors());
        }
    }

    public function test_resolucion_ticket_exitosa_desacopla_correccion_de_id_y_restaura_operativo(): void
    {
        $tipo = Tipo::first();
        $ubicacion = Ubicacion::where('activa', true)->first();
        $tec = Usuario::whereHas('roles', fn ($q) => $q->where('rol', 'tec'))->first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-RES-01',
            'tipo_id' => $tipo->id,
            'modelo' => 'Workstation HP',
        ]);

        $ticket = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $tec->id,
            'fecha' => now(),
            'comentario' => 'Fuente no enciende',
        ]);

        $resolucion = app(ResolverTicketAction::class)->execute($ticket, [
            'accion' => 'resolver',
            'solucion' => 'Reemplazo de fuente de alimentacion de 500W',
            'ubicacion_id' => $ubicacion->id,
            'puesto' => 'Puesto 05',
        ], $tec);

        $this->assertEquals('interno', $resolucion->tipo_movimiento);
        $this->assertEquals('resuelto', $resolucion->estado_ticket);
        $this->assertEquals($ticket->id, $resolucion->ticket_id);
        $this->assertNull($resolucion->correccion_de_id); // Invariante: no es enmienda

        $ticket->refresh();
        $this->assertEquals('resuelto', $ticket->estado_ticket);

        $elemento->refresh();
        $this->assertEquals(EstadoElemento::OPERATIVO, $elemento->estado);
    }

    public function test_resolucion_como_baja_patrimonial_pasa_activo_a_baja(): void
    {
        $tipo = Tipo::first();
        $ubicacion = Ubicacion::where('activa', true)->first();
        $tec = Usuario::whereHas('roles', fn ($q) => $q->where('rol', 'tec'))->first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-RES-BAJA',
            'tipo_id' => $tipo->id,
            'modelo' => 'Motherboard Danada',
        ]);

        $ticket = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $tec->id,
            'fecha' => now(),
            'comentario' => 'Cortocircuito severo en placa madre',
        ]);

        $resolucion = app(ResolverTicketAction::class)->execute($ticket, [
            'accion' => 'baja',
            'solucion' => 'Irreparable por quemadura de pistas internas y chipset',
        ], $tec);

        $this->assertEquals('baja', $resolucion->tipo_movimiento);
        $this->assertEquals('baja', $resolucion->estado_ticket);
        $this->assertEquals($ticket->id, $resolucion->ticket_id);

        $ticket->refresh();
        $this->assertEquals('baja', $ticket->estado_ticket);

        $elemento->refresh();
        $this->assertEquals(EstadoElemento::BAJA, $elemento->estado);
    }

    public function test_bloquea_doble_resolucion_y_evita_resurreccion_de_baja(): void
    {
        $tipo = Tipo::first();
        $ubicacion = Ubicacion::where('activa', true)->first();
        $tec = Usuario::whereHas('roles', fn ($q) => $q->where('rol', 'tec'))->first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-RES-NORESURREC',
            'tipo_id' => $tipo->id,
            'modelo' => 'Switch Roto',
        ]);

        $ticket = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'baja', // Ya cerrado como baja irreparable
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $tec->id,
            'fecha' => now(),
        ]);

        // Intento de "resolver" un ticket que fue dado de baja (resurrección de muerto)
        try {
            app(ResolverTicketAction::class)->execute($ticket, [
                'accion' => 'resolver',
                'solucion' => 'Intento reactivar el activo dado de baja',
            ], $tec);
            $this->fail('Debió bloquear resurrección de baja');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('ticket_id', $e->errors());
        }
    }
}
