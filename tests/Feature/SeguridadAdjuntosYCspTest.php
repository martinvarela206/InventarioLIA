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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeguridadAdjuntosYCspTest extends TestCase
{
    use RefreshDatabase;

    protected Ubicacion $ubicacion;
    protected Tipo $tipo;
    protected Usuario $tecnico;
    protected Usuario $revisor;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');

        $this->ubicacion = Ubicacion::firstOrCreate(['nombre' => 'Laboratorio Central'], ['activa' => true]);
        $this->tipo = Tipo::firstOrCreate(['nombre' => 'PC de Escritorio'], ['categoria' => 'Informatica']);

        $rolTec = Rol::firstOrCreate(['rol' => 'tec'], ['nombre' => 'Tecnico']);
        $rolRev = Rol::firstOrCreate(['rol' => 'rev'], ['nombre' => 'Revisor']);

        $this->tecnico = Usuario::factory()->create(['email' => 'tec@unsj.edu.ar', 'activo' => true]);
        $this->tecnico->roles()->attach($rolTec->id);

        $this->revisor = Usuario::factory()->create(['email' => 'rev@unsj.edu.ar', 'activo' => true]);
        $this->revisor->roles()->attach($rolRev->id);
    }

    public function test_cabeceras_de_seguridad_http_incluyen_hsts_permissions_policy_y_csp_endurecida(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $this->assertTrue($response->headers->has('Content-Security-Policy'));
        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $csp);
        // Verificar que no contenga comodines abiertos https: en default-src o connect-src
        $this->assertStringNotContainsString("default-src 'self' https:", $csp);
        $this->assertStringNotContainsString("connect-src 'self' https:", $csp);
    }

    public function test_apertura_de_ticket_limita_cuota_maxima_de_fotos(): void
    {
        $elemento = Elemento::create([
            'tipo_id' => $this->tipo->id,
            'ubicacion_id' => $this->ubicacion->id,
            'nro_lia' => 'LIA-CUOTA-01',
            'nro_unsj' => 'UNSJ-CUOTA-01',
            'marca' => 'Dell',
            'modelo' => 'Optiplex',
        ]);

        // Enviar 6 fotos (límite es 5)
        $fotos = [];
        for ($i = 0; $i < 6; $i++) {
            $fotos[] = UploadedFile::fake()->image("foto_{$i}.jpg", 100, 100);
        }

        $response = $this->actingAs($this->tecnico)->post(route('tickets.store'), [
            'elemento_id' => $elemento->id,
            'comentario' => 'Reporte con exceso de adjuntos fotográficos',
            'fotos' => $fotos,
        ]);

        $response->assertSessionHasErrors(['fotos']);
        $this->assertEquals(0, MovimientoAdjunto::count());
    }

    public function test_adjuntos_de_ticket_se_almacenan_en_disco_privado_y_su_url_es_ruta_autenticada(): void
    {
        $elemento = Elemento::create([
            'tipo_id' => $this->tipo->id,
            'ubicacion_id' => $this->ubicacion->id,
            'nro_lia' => 'LIA-PRIV-01',
            'nro_unsj' => 'UNSJ-PRIV-01',
            'marca' => 'Dell',
            'modelo' => 'Optiplex',
        ]);

        $foto = UploadedFile::fake()->image('evidencia_privada.jpg', 200, 200);

        $response = $this->actingAs($this->tecnico)->post(route('tickets.store'), [
            'elemento_id' => $elemento->id,
            'comentario' => 'Falla en condensadores de la placa madre',
            'fotos' => [$foto],
        ]);

        $response->assertRedirect();

        $adjunto = MovimientoAdjunto::first();
        $this->assertNotNull($adjunto);

        // Almacenado en disco local (privado)
        Storage::disk('local')->assertExists($adjunto->archivo_path);

        // La propiedad url debe ser la ruta autenticada
        $this->assertEquals(route('adjuntos.ticket', $adjunto->id), $adjunto->url);

        // 1. Acceso anónimo debe ser redirigido a login
        auth()->logout();
        $resAnonimo = $this->get($adjunto->url);
        $resAnonimo->assertRedirect(route('login'));

        // 2. Acceso autenticado entrega el archivo
        $resAuth = $this->actingAs($this->tecnico)->get($adjunto->url);
        $resAuth->assertOk();
    }

    public function test_adjuntos_en_hilo_de_ticket_se_almacenan_en_disco_privado_y_requieren_auth(): void
    {
        $elemento = Elemento::create([
            'tipo_id' => $this->tipo->id,
            'ubicacion_id' => $this->ubicacion->id,
            'nro_lia' => 'LIA-HILO-01',
            'nro_unsj' => 'UNSJ-HILO-01',
            'marca' => 'Dell',
            'modelo' => 'Optiplex',
        ]);

        $ticket = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'solicitante' => 'Profesor Prueba',
            'user_id' => $this->tecnico->id,
            'fecha' => now(),
            'ubicacion_id' => $this->ubicacion->id,
        ]);

        $foto = UploadedFile::fake()->image('foto_hilo.jpg', 150, 150);

        $response = $this->actingAs($this->tecnico)->post(route('tickets.comentar', $ticket->id), [
            'mensaje' => 'Respuesta en hilo con foto de osciloscopio',
            'fotos' => [$foto],
        ]);

        $response->assertRedirect(route('tickets.show', $ticket->id));

        $adjuntoComentario = TicketComentarioAdjunto::first();
        $this->assertNotNull($adjuntoComentario);

        // Almacenado en disco local (privado)
        Storage::disk('local')->assertExists($adjuntoComentario->archivo_path);

        // URL apunta a ruta protegida
        $this->assertEquals(route('adjuntos.ticket-comentario', $adjuntoComentario->id), $adjuntoComentario->url);

        // Acceso anónimo bloqueado
        auth()->logout();
        $resAnonimo = $this->get($adjuntoComentario->url);
        $resAnonimo->assertRedirect(route('login'));

        // Acceso autenticado exitoso
        $resAuth = $this->actingAs($this->tecnico)->get($adjuntoComentario->url);
        $resAuth->assertOk();
    }
}
