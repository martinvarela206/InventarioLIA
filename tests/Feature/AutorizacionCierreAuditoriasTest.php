<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Rol;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutorizacionCierreAuditoriasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Rol::firstOrCreate(['rol' => 'app_admin']);
        Rol::firstOrCreate(['rol' => 'tec']);
        Rol::firstOrCreate(['rol' => 'rev']);
    }

    public function test_get_auditorias_crear_no_crea_registros_en_base_de_datos(): void
    {
        $tec = Usuario::factory()->create(['activo' => true]);
        $tec->roles()->attach(Rol::where('rol', 'tec')->first());

        $sala = Ubicacion::firstOrCreate(['nombre' => 'Sala Sin Auditoria'], ['activa' => true]);

        $conteoAntes = Auditoria::count();

        // Petición GET ordinaria (prefetch / crawler / navegación)
        $response = $this->actingAs($tec)->get(route('auditorias.create', ['ubicacion_id' => $sala->id]));
        $response->assertStatus(200);

        // Ningún registro debe haberse persistido en BD por el GET
        $this->assertEquals($conteoAntes, Auditoria::count());
    }

    public function test_post_auditorias_inicia_formalmente_sesion_de_auditoria(): void
    {
        $tec = Usuario::factory()->create(['activo' => true]);
        $tec->roles()->attach(Rol::where('rol', 'tec')->first());

        $sala = Ubicacion::firstOrCreate(['nombre' => 'Sala A Auditar'], ['activa' => true]);

        $response = $this->actingAs($tec)->post(route('auditorias.store'), [
            'ubicacion_id' => $sala->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('auditorias', [
            'ubicacion_id' => $sala->id,
            'user_id' => $tec->id,
            'estado' => 'en_progreso',
        ]);
    }

    public function test_bloquea_modificaciones_en_auditoria_finalizada(): void
    {
        $tec = Usuario::factory()->create(['activo' => true]);
        $tec->roles()->attach(Rol::where('rol', 'tec')->first());

        $tipo = Tipo::firstOrCreate(['nombre' => 'Laptop'], ['slug' => 'laptop', 'icono' => 'laptop']);
        $sala = Ubicacion::firstOrCreate(['nombre' => 'Sala Cerrada'], ['activa' => true]);
        $elemento = Elemento::factory()->create(['tipo_id' => $tipo->id]);

        $auditoria = Auditoria::create([
            'ubicacion_id' => $sala->id,
            'user_id' => $tec->id,
            'estado' => 'finalizada',
            'fecha' => now(),
        ]);

        // 1. guardarDetalle en auditoria finalizada devuelve 422
        $resDetalle = $this->actingAs($tec)->postJson(route('auditorias.guardarDetalle', $auditoria->id), [
            'puesto' => 'Puesto 01',
            'elemento_id' => $elemento->id,
            'estado' => 'ok',
        ]);
        $resDetalle->assertStatus(422);

        // 2. crearTicketInconsistencia en auditoria finalizada devuelve 422
        $resTicket = $this->actingAs($tec)->postJson(route('auditorias.crearTicketInconsistencia', $auditoria->id), [
            'puesto' => 'Puesto 01',
            'elemento_id' => $elemento->id,
            'tipo_discrepancia' => 'rotura',
            'descripcion' => 'Intento de ticket en auditoria cerrada',
        ]);
        $resTicket->assertStatus(422);

        // 3. finalizar sobre auditoria ya finalizada devuelve 422
        $resFinalizar = $this->actingAs($tec)->postJson(route('auditorias.finalizar', $auditoria->id));
        $resFinalizar->assertStatus(422);
    }

    public function test_autorizacion_por_objeto_bloquea_usuario_ajeno(): void
    {
        $auditorTitular = Usuario::factory()->create(['activo' => true]);
        $auditorTitular->roles()->attach(Rol::where('rol', 'tec')->first());

        $otroUsuario = Usuario::factory()->create(['activo' => true]);
        $otroUsuario->roles()->attach(Rol::where('rol', 'tec')->first());

        $sala = Ubicacion::firstOrCreate(['nombre' => 'Sala Auditoria Titular'], ['activa' => true]);

        $auditoria = Auditoria::create([
            'ubicacion_id' => $sala->id,
            'user_id' => $auditorTitular->id,
            'estado' => 'en_progreso',
            'fecha' => now(),
        ]);

        // Usuario ajeno intenta guardar detalle en sesion de otro
        $resAjeno = $this->actingAs($otroUsuario)->postJson(route('auditorias.guardarDetalle', $auditoria->id), [
            'puesto' => 'Puesto 01',
            'estado' => 'ok',
        ]);
        $resAjeno->assertStatus(403);

        // Usuario titular sí puede
        $resTitular = $this->actingAs($auditorTitular)->postJson(route('auditorias.guardarDetalle', $auditoria->id), [
            'puesto' => 'Puesto 01',
            'estado' => 'ok',
        ]);
        $resTitular->assertStatus(200);
    }
}
