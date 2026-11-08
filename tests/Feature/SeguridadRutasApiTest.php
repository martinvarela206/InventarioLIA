<?php

namespace Tests\Feature;

use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Rol;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SeguridadRutasApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Rol::firstOrCreate(['rol' => 'app_admin']);
        Rol::firstOrCreate(['rol' => 'asset_admin']);
        Rol::firstOrCreate(['rol' => 'item_admin']);
        Rol::firstOrCreate(['rol' => 'rev']);
    }

    public function test_peticion_anonima_a_api_user_devuelve_401(): void
    {
        $response = $this->getJson('/api/user');
        $response->assertStatus(401);
    }

    public function test_peticion_anonima_a_api_elementos_devuelve_401(): void
    {
        $response = $this->getJson('/api/elementos');
        $response->assertStatus(401);
    }

    public function test_peticion_anonima_a_api_movimientos_devuelve_401_y_no_crea_movimiento_fantasma(): void
    {
        $admin = Usuario::factory()->create(['email' => 'admin@unsj.edu.ar']);
        $admin->roles()->attach(Rol::where('rol', 'app_admin')->first());

        $tipo = Tipo::firstOrCreate(['nombre' => 'Laptop'], ['slug' => 'laptop', 'icono' => 'laptop']);
        $ubicacion = Ubicacion::firstOrCreate(['nombre' => 'Sala Test'], ['activa' => true]);
        $elemento = Elemento::factory()->create([
            'tipo_id' => $tipo->id,
            'nro_lia' => 'API-001',
            'nro_unsj' => 'UNSJ-API-001',
        ]);

        $conteoAntes = Movimiento::count();

        $response = $this->postJson('/api/movimientos', [
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'comentario' => 'Ataque anonimo',
        ]);

        $response->assertStatus(401);
        $this->assertEquals($conteoAntes, Movimiento::count());
    }

    public function test_endpoints_inexistentes_en_movimientos_no_devuelven_500(): void
    {
        $admin = Usuario::factory()->create(['email' => 'admin@unsj.edu.ar']);
        $admin->roles()->attach(Rol::where('rol', 'app_admin')->first());
        Sanctum::actingAs($admin);

        // GET /api/movimientos/{id} ya no está registrado como apiResource completo -> 404 o 405, nunca 500
        $responseGet = $this->getJson('/api/movimientos/1');
        $this->assertNotEquals(500, $responseGet->status());

        $responseDelete = $this->deleteJson('/api/movimientos/1');
        $this->assertNotEquals(500, $responseDelete->status());
    }

    public function test_usuario_autenticado_sin_rol_pertinente_recibe_403_en_api(): void
    {
        $usuarioRevisor = Usuario::factory()->create(['email' => 'revisor@unsj.edu.ar']);
        $usuarioRevisor->roles()->attach(Rol::where('rol', 'rev')->first());
        Sanctum::actingAs($usuarioRevisor);

        $response = $this->getJson('/api/elementos');
        $response->assertStatus(403);
    }

    public function test_usuario_autenticado_con_rol_autorizado_accede_a_api(): void
    {
        $admin = Usuario::factory()->create(['email' => 'admin@unsj.edu.ar']);
        $admin->roles()->attach(Rol::where('rol', 'asset_admin')->first());
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/elementos');
        $this->assertNotEquals(401, $response->status());
        $this->assertNotEquals(403, $response->status());
    }

    public function test_usuario_inactivo_con_token_sanctum_recibe_401_en_api(): void
    {
        $usuarioInactivo = Usuario::factory()->create([
            'email' => 'inactivo_api@unsj.edu.ar',
            'activo' => false,
        ]);
        $usuarioInactivo->roles()->attach(Rol::where('rol', 'asset_admin')->first());
        Sanctum::actingAs($usuarioInactivo);

        $response = $this->getJson('/api/elementos');
        $response->assertStatus(401);
    }
}
