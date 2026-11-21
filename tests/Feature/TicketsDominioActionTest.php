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

    public function test_apertura_ticket_sobre_activo_prestado_es_rechazada_con_excepcion(): void
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

        // Al intentar abrir ticket por avería durante el préstamo, debe ser rechazado
        $this->expectException(ValidationException::class);

        app(AbrirTicketAction::class)->execute($elemento, [
            'comentario' => 'Teclado no responde tras derrame de cafe durante prestamo',
            'tipo_traslado' => 'taller',
        ], $tec);
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

    public function test_revisor_puede_acceder_a_tickets_index_pero_no_solucionar_ticket(): void
    {
        $rev = Usuario::whereHas('roles', fn ($q) => $q->where('rol', 'rev'))
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('rol', ['subtec', 'tec', 'asset_admin', 'user_admin', 'app_admin']))
            ->first();

        // 1. Acceso exitoso a la bandeja de tickets para el rol revisor
        $response = $this->actingAs($rev)->get(route('tickets.index'));
        $response->assertStatus(200);

        // 2. Comentar no puede marcar solución técnica si es revisor puro
        $ticket = Movimiento::where('tipo_movimiento', 'reparacion')->where('estado_ticket', 'abierto')->first();
        if (! $ticket) {
            $tipo = Tipo::first();
            $el = Elemento::create(['nro_lia' => 'TST-REV-01', 'tipo_id' => $tipo->id]);
            $ticket = Movimiento::create([
                'elemento_id' => $el->id,
                'tipo_movimiento' => 'reparacion',
                'estado_ticket' => 'abierto',
                'titulo' => 'Ticket de prueba para revisor',
                'user_id' => $rev->id,
                'fecha' => now(),
            ]);
        }

        $comentarResp = $this->actingAs($rev)->post(route('tickets.comentar', $ticket->id), [
            'mensaje' => 'Aporte y aclaración sobre la falla',
            'es_solucion' => true,
        ]);

        $comentarResp->assertRedirect();
        $ticket->refresh();
        // El ticket debe pasar a 'en_proceso' pero NUNCA a 'solucionado' por un revisor
        $this->assertEquals('en_proceso', $ticket->estado_ticket);
    }

    public function test_api_buscar_excluye_activos_prestados_cuando_se_solicita_y_vista_bloquea_boton(): void
    {
        $tec = Usuario::whereHas('roles', fn ($q) => $q->where('rol', 'tec'))->first();
        $tipo = Tipo::first();
        $ubicacion = Ubicacion::where('activa', true)->first();

        $elementoPrestado = Elemento::create([
            'nro_lia' => 'TST-PRESTADO-SEARCH',
            'tipo_id' => $tipo->id,
            'modelo' => 'Laptop Prestada',
        ]);

        Movimiento::create([
            'elemento_id' => $elementoPrestado->id,
            'tipo_movimiento' => 'externo_prestamo',
            'fecha' => now(),
            'fecha_prestamo' => now(),
            'solicitante' => 'Profesor Juan Perez',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $tec->id,
        ]);

        // 1. Búsqueda normal incluye el elemento (para devolución rápida)
        $respNormal = $this->actingAs($tec)->getJson(route('movimientos.buscar-rapido', ['q' => 'TST-PRESTADO-SEARCH']));
        $respNormal->assertStatus(200);
        $respNormal->assertJsonFragment(['nro_lia' => 'TST-PRESTADO-SEARCH']);

        // 2. Búsqueda con excluir_prestados=1 NO incluye el elemento prestado
        $respFiltrado = $this->actingAs($tec)->getJson(route('movimientos.buscar-rapido', ['q' => 'TST-PRESTADO-SEARCH', 'excluir_prestados' => 1]));
        $respFiltrado->assertStatus(200);
        $respFiltrado->assertJsonMissing(['nro_lia' => 'TST-PRESTADO-SEARCH']);

        // 3. Vista show de elemento prestado muestra el botón bloqueado
        $respShow = $this->actingAs($tec)->get(route('elementos.show', $elementoPrestado->id));
        $respShow->assertStatus(200);
        $respShow->assertSee('No es posible iniciar un ticket sobre un activo en préstamo');
        $respShow->assertSee('cursor-not-allowed');
    }
}
