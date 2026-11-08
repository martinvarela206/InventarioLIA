<?php

namespace Tests\Feature;

use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Rol;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use App\Models\UsuarioTrayectoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BorradoForenseHuellaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Rol::firstOrCreate(['rol' => 'app_admin']);
        Rol::firstOrCreate(['rol' => 'user_admin']);
        Rol::firstOrCreate(['rol' => 'asset_admin']);
        Rol::firstOrCreate(['rol' => 'tec']);
    }

    public function test_rechaza_anonimizar_usuario_con_menos_de_6_anios_de_egreso(): void
    {
        $admin = Usuario::factory()->create(['activo' => true]);
        $admin->roles()->attach(Rol::where('rol', 'user_admin')->first());

        $reciente = Usuario::factory()->create([
            'activo' => false,
            'fecha_egreso' => now()->subYears(3),
        ]);

        $response = $this->actingAs($admin)->post(route('users.anonimizar', $reciente->id));

        $response->assertStatus(422);
        $this->assertFalse((bool) $reciente->fresh()->huella_borrada);
    }

    public function test_rechaza_anonimizar_usuario_activo(): void
    {
        $admin = Usuario::factory()->create(['activo' => true]);
        $admin->roles()->attach(Rol::where('rol', 'user_admin')->first());

        $activo = Usuario::factory()->create([
            'activo' => true,
            'fecha_egreso' => now()->subYears(7),
        ]);

        $response = $this->actingAs($admin)->post(route('users.anonimizar', $activo->id));

        $response->assertStatus(422);
        $this->assertFalse((bool) $activo->fresh()->huella_borrada);
    }

    public function test_anonimizacion_exitosa_limpia_datos_trayectorias_y_solicitante_en_movimientos(): void
    {
        $admin = Usuario::factory()->create(['activo' => true]);
        $admin->roles()->attach(Rol::where('rol', 'user_admin')->first());

        $exColaborador = Usuario::factory()->create([
            'nombre' => 'Juan Perez Histórico',
            'email' => 'juan.perez@unsj.edu.ar',
            'dni' => '28999888',
            'activo' => false,
            'fecha_egreso' => now()->subYears(7),
        ]);

        UsuarioTrayectoria::create([
            'user_id' => $exColaborador->id,
            'cargo' => 'Becario',
            'fecha_inicio' => now()->subYears(9),
            'fecha_fin' => now()->subYears(7),
            'notas' => 'Nota personal confidencial',
        ]);

        $tipo = Tipo::firstOrCreate(['nombre' => 'Laptop'], ['slug' => 'laptop', 'icono' => 'laptop']);
        $ubicacion = Ubicacion::firstOrCreate(['nombre' => 'Sala A'], ['activa' => true]);
        $elemento = Elemento::factory()->create(['tipo_id' => $tipo->id]);

        $movimiento = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'externo_prestamo',
            'solicitante' => 'Juan Perez Histórico (DNI 28999888)',
            'user_id' => $admin->id,
            'fecha' => now()->subYears(7),
            'comentario' => 'Préstamo a becario',
        ]);

        $response = $this->actingAs($admin)->post(route('users.anonimizar', $exColaborador->id));

        $response->assertRedirect(route('users.index'));
        $exColaborador->refresh();

        $this->assertTrue((bool) $exColaborador->huella_borrada);
        $this->assertTrue($exColaborador->esAnonimizado());
        $this->assertEquals("Usuario Histórico #{$exColaborador->id}", $exColaborador->nombre);
        $this->assertEquals("historico_{$exColaborador->id}@lia.local", $exColaborador->email);
        $this->assertNull($exColaborador->dni);

        // Notas personales de trayectorias eliminadas preservando integridad relacional
        $this->assertNull(UsuarioTrayectoria::where('user_id', $exColaborador->id)->first()->notas);

        // Movimiento anonimizado
        $movimiento->refresh();
        $this->assertStringNotContainsString('Juan Perez Histórico', $movimiento->solicitante);
        $this->assertStringNotContainsString('28999888', $movimiento->solicitante);
        $this->assertStringContainsString('Ex-colaborador anonimizado', $movimiento->solicitante);
    }

    public function test_bloquea_edicion_posterior_de_usuario_anonimizado(): void
    {
        $admin = Usuario::factory()->create(['activo' => true]);
        $admin->roles()->attach(Rol::where('rol', 'user_admin')->first());

        $anonimizado = Usuario::factory()->create([
            'nombre' => 'Usuario Histórico #99',
            'email' => 'historico_99@lia.local',
            'huella_borrada' => true,
            'activo' => false,
        ]);

        // edit redirige con mensaje de error
        $responseEdit = $this->actingAs($admin)->get(route('users.edit', $anonimizado->id));
        $responseEdit->assertRedirect(route('users.index'));

        // update responde 403
        $responseUpdate = $this->actingAs($admin)->put(route('users.update', $anonimizado->id), [
            'nombre' => 'Intento de Edición',
            'email' => 'intento@unsj.edu.ar',
        ]);
        $responseUpdate->assertStatus(403);
    }
}
