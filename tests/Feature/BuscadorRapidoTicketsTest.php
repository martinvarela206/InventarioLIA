<?php

namespace Tests\Feature;

use App\Models\AmbitoTicket;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Rol;
use App\Models\Tipo;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuscadorRapidoTicketsTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $tecnico;

    protected Usuario $revisor;

    protected Usuario $registeredUser;

    protected AmbitoTicket $ambito;

    protected Elemento $elemento;

    protected function setUp(): void
    {
        parent::setUp();

        Tipo::firstOrCreate(['id' => 1], ['slug' => 'pc', 'nombre' => 'PC']);

        $rolTec = Rol::firstOrCreate(['rol' => 'tec']);
        $rolRev = Rol::firstOrCreate(['rol' => 'rev']);
        $rolRegistered = Rol::firstOrCreate(['rol' => 'registered']);

        $this->tecnico = Usuario::factory()->create(['activo' => true]);
        $this->tecnico->roles()->sync([$rolTec->id, $rolRegistered->id]);

        $this->revisor = Usuario::factory()->create(['activo' => true]);
        $this->revisor->roles()->sync([$rolRev->id, $rolRegistered->id]);

        $this->registeredUser = Usuario::factory()->create(['activo' => true]);
        $this->registeredUser->roles()->sync([$rolRegistered->id]);

        $this->ambito = AmbitoTicket::firstOrCreate(
            ['nombre' => 'Climatización LIA'],
            ['descripcion' => 'Aires acondicionados y ventilación', 'orden' => 1, 'activo' => true]
        );

        $this->elemento = Elemento::factory()->create([
            'nro_lia' => 'LIA-99887',
            'marca' => 'Lenovo',
            'modelo' => 'ThinkCentre M720',
        ]);
    }

    public function test_tecnico_puede_buscar_tickets_por_activo(): void
    {
        $ticket = Movimiento::create([
            'elemento_id' => $this->elemento->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'user_id' => $this->tecnico->id,
            'fecha' => now(),
            'solicitante' => 'Profesor Prueba',
            'comentario' => 'Falla de encendido en ThinkCentre LIA-99887',
        ]);

        $response = $this->actingAs($this->tecnico)->getJson(route('tickets.buscar-rapido', ['q' => '99887']));

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment([
            'id' => $ticket->id,
            'codigo' => '#'.str_pad((string) $ticket->id, 4, '0', STR_PAD_LEFT),
            'es_general' => false,
            'solicitante' => 'Profesor Prueba',
        ]);
    }

    public function test_revisor_puede_buscar_tickets_generales_por_ambito(): void
    {
        $ticketGeneral = Movimiento::create([
            'elemento_id' => null,
            'ambito_ticket_id' => $this->ambito->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'user_id' => $this->revisor->id,
            'fecha' => now(),
            'solicitante' => 'Auditor General',
            'comentario' => 'Split goteando en sala central',
        ]);

        $response = $this->actingAs($this->revisor)->getJson(route('tickets.buscar-rapido', ['q' => 'Climatización']));

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment([
            'id' => $ticketGeneral->id,
            'es_general' => true,
            'ambito_nombre' => 'Climatización LIA',
        ]);
    }

    public function test_busqueda_por_codigo_id_directo(): void
    {
        $ticket = Movimiento::create([
            'elemento_id' => $this->elemento->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'user_id' => $this->tecnico->id,
            'fecha' => now(),
            'solicitante' => 'Docente X',
            'comentario' => 'Crasheo con Docker',
        ]);

        $response = $this->actingAs($this->tecnico)->getJson(route('tickets.buscar-rapido', ['q' => '#'.$ticket->id]));

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $this->assertEquals($ticket->id, $response->json()[0]['id']);
    }

    public function test_limite_maximo_de_5_coincidencias(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            Movimiento::create([
                'elemento_id' => null,
                'ambito_ticket_id' => $this->ambito->id,
                'tipo_movimiento' => 'reparacion',
                'estado_ticket' => 'abierto',
                'user_id' => $this->tecnico->id,
                'fecha' => now(),
                'solicitante' => "Solicitante {$i}",
                'comentario' => "Problema masivo recurrente {$i}",
            ]);
        }

        $response = $this->actingAs($this->tecnico)->getJson(route('tickets.buscar-rapido', ['q' => 'Problema masivo']));

        $response->assertStatus(200);
        $response->assertJsonCount(5);
    }

    public function test_parametro_vacio_retorna_arreglo_vacio(): void
    {
        $response = $this->actingAs($this->tecnico)->getJson(route('tickets.buscar-rapido', ['q' => '']));

        $response->assertStatus(200);
        $response->assertExactJson([]);
    }

    public function test_usuario_registrado_sin_rol_operativo_es_bloqueado(): void
    {
        $response = $this->actingAs($this->registeredUser)->getJson(route('tickets.buscar-rapido', ['q' => 'test']));

        $response->assertRedirect(route('bienvenida'));
    }

    public function test_vista_tickets_index_renderiza_componente_con_spinner(): void
    {
        $response = $this->actingAs($this->tecnico)->get(route('tickets.index'));

        $response->assertStatus(200);
        $response->assertSee('bandeja-ticket-search-spinner', false);
        $response->assertSee('bandeja-ticket-search-dropdown', false);
        $response->assertSee('animate-spin', false);
    }
}
