<?php

namespace Tests\Feature;

use App\Actions\Tickets\AbrirTicketAction;
use App\Models\AmbitoTicket;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Rol;
use App\Models\Tipo;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TicketsGeneralesEsquemaTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $tecnico;

    protected Usuario $revisor;

    protected AmbitoTicket $ambito;

    protected function setUp(): void
    {
        parent::setUp();

        Tipo::firstOrCreate(['id' => 1], ['slug' => 'pc', 'nombre' => 'PC']);

        $rolTec = Rol::firstOrCreate(['rol' => 'tec']);
        $rolRev = Rol::firstOrCreate(['rol' => 'rev']);

        $this->tecnico = Usuario::factory()->create(['activo' => true]);
        $this->tecnico->roles()->sync([$rolTec->id]);

        $this->revisor = Usuario::factory()->create(['activo' => true]);
        $this->revisor->roles()->sync([$rolRev->id]);

        $this->ambito = AmbitoTicket::firstOrCreate(
            ['nombre' => 'Laboratorio A'],
            ['descripcion' => 'Espacio físico A', 'orden' => 1, 'activo' => true]
        );
    }

    public function test_catalogo_ambitos_tickets_se_consulta_y_filtra_por_activos(): void
    {
        $inactivo = AmbitoTicket::create([
            'nombre' => 'Ámbito Descontinuado',
            'descripcion' => 'Ya no disponible',
            'orden' => 99,
            'activo' => false,
        ]);

        $activos = AmbitoTicket::activos()->get();

        $this->assertTrue($activos->contains('id', $this->ambito->id));
        $this->assertFalse($activos->contains('id', $inactivo->id));
    }

    public function test_se_puede_abrir_un_ticket_general_sin_activo_patrimonial_via_action(): void
    {
        $action = app(AbrirTicketAction::class);

        $ticket = $action->execute(null, [
            'ambito_ticket_id' => $this->ambito->id,
            'comentario' => 'Falla en el tendido eléctrico del Laboratorio A',
            'solicitante' => 'Profesor de Redes',
        ], $this->tecnico);

        $this->assertInstanceOf(Movimiento::class, $ticket);
        $this->assertNull($ticket->elemento_id);
        $this->assertEquals($this->ambito->id, $ticket->ambito_ticket_id);
        $this->assertEquals('reparacion', $ticket->tipo_movimiento);
        $this->assertEquals('abierto', $ticket->estado_ticket);
        $this->assertTrue($ticket->esTicketGeneral());
    }

    public function test_ticket_con_activo_patrimonial_no_es_ticket_general(): void
    {
        $elemento = Elemento::factory()->create();
        $action = app(AbrirTicketAction::class);

        $ticket = $action->execute($elemento->id, [
            'comentario' => 'No enciende la fuente de poder del equipo',
            'solicitante' => 'Técnico de guardia',
        ], $this->tecnico);

        $this->assertNotNull($ticket->elemento_id);
        $this->assertEquals($elemento->id, $ticket->elemento_id);
        $this->assertFalse($ticket->esTicketGeneral());
    }

    public function test_abrir_ticket_general_requiere_descripcion_minima(): void
    {
        $this->expectException(ValidationException::class);

        $action = app(AbrirTicketAction::class);
        $action->execute(null, [
            'ambito_ticket_id' => $this->ambito->id,
            'comentario' => 'abc', // menor a 5 caracteres
        ], $this->tecnico);
    }

    public function test_abrir_ticket_general_requiere_ambito_ticket_id(): void
    {
        $this->expectException(ValidationException::class);

        $action = app(AbrirTicketAction::class);
        $action->execute(null, [
            'comentario' => 'Falla general sin ámbito especificado',
        ], $this->tecnico);
    }

    public function test_endpoint_store_crea_ticket_general_exitosamente_y_redirecciona(): void
    {
        $response = $this->actingAs($this->tecnico)->post(route('tickets.store'), [
            'modalidad' => 'general',
            'ambito_ticket_id' => $this->ambito->id,
            'comentario' => 'El aire acondicionado no enfría la sala',
        ]);

        $ticket = Movimiento::where('ambito_ticket_id', $this->ambito->id)->first();
        $this->assertNotNull($ticket);
        $this->assertNull($ticket->elemento_id);
        $response->assertRedirect(route('tickets.show', $ticket->id));
        $response->assertSessionHas('success');
    }

    public function test_endpoint_store_rechaza_solicitud_sin_activo_ni_ambito(): void
    {
        $response = $this->actingAs($this->tecnico)->post(route('tickets.store'), [
            'comentario' => 'Problema técnico sin definir destino',
        ]);

        $response->assertSessionHasErrors(['elemento_id', 'ambito_ticket_id']);
    }

    public function test_formulario_create_renderiza_selector_dual_y_ambitos_activos(): void
    {
        $inactivo = AmbitoTicket::create([
            'nombre' => 'Ámbito Oculto Inactivo',
            'activo' => false,
        ]);

        $response = $this->actingAs($this->tecnico)->get(route('tickets.create'));

        $response->assertStatus(200);
        $response->assertSee('Sobre Activo Patrimonial');
        $response->assertSee('Infraestructura / Espacio LIA');
        $response->assertSee('Laboratorio A');
        $response->assertDontSee('Ámbito Oculto Inactivo');
    }

    public function test_formulario_create_con_elemento_precarga_datos_de_hardware(): void
    {
        $elemento = Elemento::factory()->create([
            'nro_lia' => 'LIA-99999',
        ]);

        $response = $this->actingAs($this->tecnico)->get(route('tickets.create', ['elemento_id' => $elemento->id]));

        $response->assertStatus(200);
        $response->assertSee('LIA-99999');
    }

    public function test_bandeja_tickets_muestra_badge_infraestructura_y_nombre_de_ambito(): void
    {
        $ticket = Movimiento::create([
            'elemento_id' => null,
            'ambito_ticket_id' => $this->ambito->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'user_id' => $this->tecnico->id,
            'fecha' => now(),
            'solicitante' => 'Docente A',
            'comentario' => 'Problema en iluminación general',
        ]);

        $response = $this->actingAs($this->tecnico)->get(route('tickets.index'));

        $response->assertStatus(200);
        $response->assertSee('Infraestructura / Espacio');
        $response->assertSee('Laboratorio A');
        $response->assertSee('Problema en iluminación general');
    }

    public function test_detalle_ticket_show_muestra_ficha_de_infraestructura_sin_errores(): void
    {
        $ticket = Movimiento::create([
            'elemento_id' => null,
            'ambito_ticket_id' => $this->ambito->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'puesto' => 'Puesto 03',
            'user_id' => $this->tecnico->id,
            'fecha' => now(),
            'solicitante' => 'Docente B',
            'comentario' => 'Falta conector RJ45 en mesa',
        ]);

        $response = $this->actingAs($this->tecnico)->get(route('tickets.show', $ticket->id));

        $response->assertStatus(200);
        $response->assertSee('Ámbito / Espacio Reportado');
        $response->assertSee('Infraestructura LIA');
        $response->assertSee('Laboratorio A');
        $response->assertSee('Puesto 03');
    }

    public function test_tecnico_puede_resolver_ticket_general(): void
    {
        $ticket = Movimiento::create([
            'elemento_id' => null,
            'ambito_ticket_id' => $this->ambito->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'user_id' => $this->tecnico->id,
            'fecha' => now(),
            'solicitante' => 'Docente C',
            'comentario' => 'Llave térmica salta con frecuencia',
        ]);

        $response = $this->actingAs($this->tecnico)->post(route('tickets.resolver', $ticket->id), [
            'accion' => 'resolver',
            'solucion' => 'Se reemplazó la llave termomagnética por una de 25A y se equilibraron fases.',
        ]);

        $response->assertRedirect(route('tickets.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('resuelto', $ticket->fresh()->estado_ticket);

        $resolucion = Movimiento::where('ticket_id', $ticket->id)->first();
        $this->assertNotNull($resolucion);
        $this->assertNull($resolucion->elemento_id);
        $this->assertEquals('resuelto', $resolucion->estado_ticket);
        $this->assertStringContainsString('RESOLUCIÓN INCIDENCIA GENERAL', $resolucion->comentario);
    }

    public function test_revisor_no_puede_resolver_ticket_general(): void
    {
        $ticket = Movimiento::create([
            'elemento_id' => null,
            'ambito_ticket_id' => $this->ambito->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'user_id' => $this->tecnico->id,
            'fecha' => now(),
            'solicitante' => 'Docente D',
            'comentario' => 'Cerradura trabada',
        ]);

        $response = $this->actingAs($this->revisor)->post(route('tickets.resolver', $ticket->id), [
            'accion' => 'resolver',
            'solucion' => 'Intento ilegal de cierre por un revisor.',
        ]);

        $response->assertStatus(403);
        $this->assertEquals('abierto', $ticket->fresh()->estado_ticket);
    }
}
