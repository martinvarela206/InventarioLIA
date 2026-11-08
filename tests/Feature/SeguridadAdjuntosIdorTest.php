<?php

namespace Tests\Feature;

use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\MovimientoAdjunto;
use App\Models\Rol;
use App\Models\TicketComentario;
use App\Models\TicketComentarioAdjunto;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeguridadAdjuntosIdorTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_registered_sin_permisos_recibe_403_al_intentar_acceder_a_adjunto_ajeno(): void
    {
        Storage::fake('local');
        $this->seed();

        $rolRegistered = Rol::where('rol', 'registered')->first();
        $rolTec = Rol::where('rol', 'tec')->first();

        // 1. Creador legítimo del ticket (técnico)
        $tecnico = Usuario::factory()->create(['activo' => true]);
        $tecnico->roles()->attach($rolTec->id);

        // 2. Atacante o usuario en espera sin rol operativo
        $usuarioRegistered = Usuario::factory()->create(['activo' => true]);
        $usuarioRegistered->roles()->attach($rolRegistered->id);

        $tipo = Tipo::first();
        $ubicacion = Ubicacion::first();

        $elemento = Elemento::create([
            'tipo_id' => $tipo->id,
            'ubicacion_id' => $ubicacion->id,
            'nro_lia' => 'LIA-IDOR-01',
            'marca' => 'Dell',
        ]);

        $ticket = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'user_id' => $tecnico->id,
            'fecha' => now(),
            'ubicacion_id' => $ubicacion->id,
            'comentario' => 'Ticket confidencial de soporte',
        ]);

        $filePath = 'tickets/adjunto_secreto.jpg';
        Storage::disk('local')->put($filePath, 'contenido de prueba');

        $adjunto = MovimientoAdjunto::create([
            'movimiento_id' => $ticket->id,
            'archivo_path' => $filePath,
            'nombre_original' => 'adjunto_secreto.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        // El usuario registered no autorizado intenta acceder vía ID secuencial -> 403 Forbidden
        $response = $this->actingAs($usuarioRegistered)->get(route('adjuntos.ticket', $adjunto->id));
        $response->assertStatus(403);

        // El técnico autorizado accede correctamente -> 200 OK
        $responseTec = $this->actingAs($tecnico)->get(route('adjuntos.ticket', $adjunto->id));
        $responseTec->assertStatus(200);
    }

    public function test_autor_del_ticket_puede_ver_su_propio_adjunto_aunque_no_tenga_rol_operativo(): void
    {
        Storage::fake('local');
        $this->seed();

        $rolRegistered = Rol::where('rol', 'registered')->first();

        $solicitante = Usuario::factory()->create(['activo' => true]);
        $solicitante->roles()->attach($rolRegistered->id);

        $tipo = Tipo::first();
        $ubicacion = Ubicacion::first();

        $elemento = Elemento::create([
            'tipo_id' => $tipo->id,
            'ubicacion_id' => $ubicacion->id,
            'nro_lia' => 'LIA-IDOR-02',
            'marca' => 'HP',
        ]);

        $ticket = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'user_id' => $solicitante->id,
            'fecha' => now(),
            'ubicacion_id' => $ubicacion->id,
            'comentario' => 'Ticket creado por solicitante',
        ]);

        $filePath = 'tickets/solicitud.jpg';
        Storage::disk('local')->put($filePath, 'foto daño');

        $adjunto = MovimientoAdjunto::create([
            'movimiento_id' => $ticket->id,
            'archivo_path' => $filePath,
            'nombre_original' => 'solicitud.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        // Creador del ticket accede con éxito
        $response = $this->actingAs($solicitante)->get(route('adjuntos.ticket', $adjunto->id));
        $response->assertStatus(200);
    }

    public function test_adjunto_de_comentario_bloquea_usuario_ajeno_con_403(): void
    {
        Storage::fake('local');
        $this->seed();

        $rolRegistered = Rol::where('rol', 'registered')->first();
        $rolTec = Rol::where('rol', 'tec')->first();

        $tecnico = Usuario::factory()->create(['activo' => true]);
        $tecnico->roles()->attach($rolTec->id);

        $ajeno = Usuario::factory()->create(['activo' => true]);
        $ajeno->roles()->attach($rolRegistered->id);

        $tipo = Tipo::first();
        $ubicacion = Ubicacion::first();

        $elemento = Elemento::create([
            'tipo_id' => $tipo->id,
            'ubicacion_id' => $ubicacion->id,
            'nro_lia' => 'LIA-IDOR-03',
            'marca' => 'Lenovo',
        ]);

        $ticket = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'user_id' => $tecnico->id,
            'fecha' => now(),
            'ubicacion_id' => $ubicacion->id,
        ]);

        $comentario = TicketComentario::create([
            'ticket_id' => $ticket->id,
            'user_id' => $tecnico->id,
            'mensaje' => 'Informe técnico interno confidencial',
            'tipo' => 'tecnico',
        ]);

        $filePath = 'comentarios/informe.pdf';
        Storage::disk('local')->put($filePath, 'pdf interno');

        $adjunto = TicketComentarioAdjunto::create([
            'ticket_comentario_id' => $comentario->id,
            'archivo_path' => $filePath,
            'nombre_original' => 'informe.pdf',
            'mime_type' => 'application/pdf',
        ]);

        // Usuario ajeno -> 403 Forbidden
        $responseAjeno = $this->actingAs($ajeno)->get(route('adjuntos.ticket-comentario', $adjunto->id));
        $responseAjeno->assertStatus(403);

        // Técnico del sistema -> 200 OK
        $responseTec = $this->actingAs($tecnico)->get(route('adjuntos.ticket-comentario', $adjunto->id));
        $responseTec->assertStatus(200);
    }
}
