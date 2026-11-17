<?php

namespace Tests\Feature;

use App\Models\AmbitoTicket;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\MovimientoAdjunto;
use App\Models\Rol;
use App\Models\TicketComentario;
use App\Models\TicketComentarioAdjunto;
use App\Models\Tipo;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExportacionTicketPdfTest extends TestCase
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

        Storage::fake('local');

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
            ['nombre' => 'Laboratorio A'],
            ['descripcion' => 'Espacio de computación A', 'orden' => 1, 'activo' => true]
        );

        $this->elemento = Elemento::factory()->create([
            'nro_lia' => 'LIA-12345',
        ]);
    }

    public function test_tecnico_puede_descargar_pdf_de_ticket_con_activo(): void
    {
        $ticket = Movimiento::create([
            'elemento_id' => $this->elemento->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'user_id' => $this->tecnico->id,
            'fecha' => now(),
            'solicitante' => 'Docente X',
            'comentario' => 'Falla en la tarjeta gráfica del equipo LIA-12345',
        ]);

        $response = $this->actingAs($this->tecnico)->get(route('tickets.pdf', $ticket->id));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('ticket-'.str_pad((string) $ticket->id, 5, '0', STR_PAD_LEFT).'-informe.pdf', (string) $response->headers->get('content-disposition'));
    }

    public function test_revisor_puede_descargar_pdf_de_ticket_general_de_infraestructura(): void
    {
        $ticket = Movimiento::create([
            'elemento_id' => null,
            'ambito_ticket_id' => $this->ambito->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'user_id' => $this->revisor->id,
            'fecha' => now(),
            'solicitante' => 'Docente Y',
            'comentario' => 'Gotera sobre el tablero eléctrico del Laboratorio A',
        ]);

        $response = $this->actingAs($this->revisor)->get(route('tickets.pdf', $ticket->id));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }

    public function test_pdf_se_genera_correctamente_con_adjuntos_fotograficos_embebidos(): void
    {
        // Guardar foto simulada en disco local
        $file = UploadedFile::fake()->image('evidencia.jpg', 200, 200);
        $path = $file->store('tickets', 'local');

        $ticket = Movimiento::create([
            'elemento_id' => null,
            'ambito_ticket_id' => $this->ambito->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'user_id' => $this->tecnico->id,
            'fecha' => now(),
            'solicitante' => 'Técnico Z',
            'comentario' => 'Evidencia fotográfica adjunta en ticket',
        ]);

        MovimientoAdjunto::create([
            'movimiento_id' => $ticket->id,
            'archivo_path' => $path,
            'nombre_original' => 'evidencia.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        $comentario = TicketComentario::create([
            'ticket_id' => $ticket->id,
            'user_id' => $this->tecnico->id,
            'mensaje' => 'Inspección técnica con foto complementaria',
            'tipo' => 'comentario',
        ]);

        $pathComentario = $file->store('ticket-comentarios', 'local');
        TicketComentarioAdjunto::create([
            'ticket_comentario_id' => $comentario->id,
            'archivo_path' => $pathComentario,
            'nombre_original' => 'foto_comentario.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        $response = $this->actingAs($this->tecnico)->get(route('tickets.pdf', $ticket->id));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }

    public function test_usuario_no_autenticado_es_redirigido_al_login(): void
    {
        $ticket = Movimiento::create([
            'elemento_id' => $this->elemento->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'user_id' => $this->tecnico->id,
            'fecha' => now(),
            'comentario' => 'Prueba sin login',
        ]);

        $response = $this->get(route('tickets.pdf', $ticket->id));

        $response->assertRedirect(route('login'));
    }

    public function test_usuario_registrado_sin_rol_operativo_es_bloqueado_de_exportacion_pdf(): void
    {
        $ticket = Movimiento::create([
            'elemento_id' => $this->elemento->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'user_id' => $this->tecnico->id,
            'fecha' => now(),
            'comentario' => 'Prueba con usuario básico',
        ]);

        $response = $this->actingAs($this->registeredUser)->get(route('tickets.pdf', $ticket->id));

        $response->assertRedirect(route('bienvenida'));
    }
}
