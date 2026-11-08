<?php

namespace Tests\Feature;

use App\Enums\EstadoElemento;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Rol;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlujoReubicacionUbicacionesTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $assetAdmin;

    protected Usuario $tecnico;

    protected Ubicacion $salaA;

    protected Tipo $tipoPc;

    protected function setUp(): void
    {
        parent::setUp();

        $rolAssetAdmin = Rol::firstOrCreate(['rol' => 'asset_admin'], ['nombre' => 'Admin Activos']);
        $rolTec = Rol::firstOrCreate(['rol' => 'tec'], ['nombre' => 'Técnico']);

        $this->assetAdmin = Usuario::factory()->create(['email' => 'asset@unsj.edu.ar', 'activo' => true]);
        $this->assetAdmin->roles()->attach($rolAssetAdmin->id);

        $this->tecnico = Usuario::factory()->create(['email' => 'tec@unsj.edu.ar', 'activo' => true]);
        $this->tecnico->roles()->attach($rolTec->id);

        $this->salaA = Ubicacion::firstOrCreate(['nombre' => 'Laboratorio Alfa'], ['activa' => true]);
        $this->tipoPc = Tipo::firstOrCreate(['nombre' => 'Desktop PC'], ['categoria' => 'Informatica']);
    }

    public function test_renombrar_ubicacion_con_activos_redirige_a_asistente_de_reubicacion(): void
    {
        $pc = Elemento::create([
            'tipo_id' => $this->tipoPc->id,
            'ubicacion_id' => $this->salaA->id,
            'nro_lia' => 'LIA-REUB-01',
            'marca' => 'Dell',
            'modelo' => 'Optiplex',
        ]);

        Movimiento::create([
            'elemento_id' => $pc->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $this->salaA->id,
            'user_id' => $this->assetAdmin->id,
            'fecha' => now(),
        ]);

        // Renombrar Laboratorio Alfa a Laboratorio Alfa Renovado
        $response = $this->actingAs($this->assetAdmin)->put(route('ubicaciones.update', $this->salaA->id), [
            'nombre' => 'Laboratorio Alfa Renovado',
            'descripcion' => 'Espacio ampliado y modernizado',
        ]);

        $this->salaA->refresh();
        $this->assertFalse($this->salaA->activa);

        $nuevaSala = Ubicacion::where('nombre', 'Laboratorio Alfa Renovado')->first();
        $this->assertNotNull($nuevaSala);
        $this->assertTrue($nuevaSala->activa);

        // El activo queda en estado EN_LIMBO_REUBICACION
        $pc->refresh();
        $this->assertEquals(EstadoElemento::EN_LIMBO_REUBICACION, $pc->estado);

        // Debe redirigir a /ubicaciones/reubicar
        $response->assertRedirect(route('ubicaciones.reubicar', [
            'origen_id' => $this->salaA->id,
            'sugerido_id' => $nuevaSala->id,
        ]));
    }

    public function test_destroy_ubicacion_con_historial_archiva_sala_y_redirige_a_reubicacion(): void
    {
        $pc = Elemento::create([
            'tipo_id' => $this->tipoPc->id,
            'ubicacion_id' => $this->salaA->id,
            'nro_lia' => 'LIA-ARCH-01',
            'marca' => 'HP',
            'modelo' => 'ProDesk',
        ]);

        Movimiento::create([
            'elemento_id' => $pc->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $this->salaA->id,
            'user_id' => $this->assetAdmin->id,
            'fecha' => now(),
        ]);

        $response = $this->actingAs($this->assetAdmin)->delete(route('ubicaciones.destroy', $this->salaA->id));

        $this->salaA->refresh();
        $this->assertFalse($this->salaA->activa);
        $this->assertDatabaseHas('ubicaciones', ['id' => $this->salaA->id]);

        $response->assertRedirect(route('ubicaciones.reubicar', [
            'origen_id' => $this->salaA->id,
        ]));
    }

    public function test_pantalla_reubicacion_protegida_exclusivamente_para_asset_admin(): void
    {
        // Técnico no autorizado recibe 403
        $resTec = $this->actingAs($this->tecnico)->get(route('ubicaciones.reubicar'));
        $resTec->assertStatus(403);

        // Asset Admin autorizado accede correctamente
        $resAdmin = $this->actingAs($this->assetAdmin)->get(route('ubicaciones.reubicar'));
        $resAdmin->assertOk();
        $resAdmin->assertSee('Reubicación de Activos en Limbo');
    }

    public function test_reubicacion_masiva_a_destino_comun_saca_activos_de_limbo(): void
    {
        $salaDestino = Ubicacion::firstOrCreate(['nombre' => 'Laboratorio Gamma'], ['activa' => true]);

        // Archivar sala origen
        $this->salaA->update(['activa' => false]);

        $pc1 = Elemento::create([
            'tipo_id' => $this->tipoPc->id,
            'ubicacion_id' => $this->salaA->id,
            'nro_lia' => 'LIA-LOTE-01',
            'marca' => 'Dell',
            'modelo' => 'Optiplex',
        ]);
        Movimiento::create([
            'elemento_id' => $pc1->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $this->salaA->id,
            'user_id' => $this->assetAdmin->id,
            'fecha' => now(),
        ]);

        $pc2 = Elemento::create([
            'tipo_id' => $this->tipoPc->id,
            'ubicacion_id' => $this->salaA->id,
            'nro_lia' => 'LIA-LOTE-02',
            'marca' => 'Lenovo',
            'modelo' => 'ThinkCentre',
        ]);
        Movimiento::create([
            'elemento_id' => $pc2->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $this->salaA->id,
            'user_id' => $this->assetAdmin->id,
            'fecha' => now(),
        ]);

        $this->assertEquals(EstadoElemento::EN_LIMBO_REUBICACION, $pc1->estado);
        $this->assertEquals(EstadoElemento::EN_LIMBO_REUBICACION, $pc2->estado);

        // Ejecutar reubicación común por POST
        $response = $this->actingAs($this->assetAdmin)->post(route('ubicaciones.reubicar.store'), [
            'items' => [$pc1->id, $pc2->id],
            'ubicacion_comun_id' => $salaDestino->id,
            'comentario' => 'Reubicación total hacia Laboratorio Gamma',
        ]);

        $response->assertRedirect(route('ubicaciones.index'));

        $pc1->refresh();
        $pc2->refresh();
        $this->assertEquals(EstadoElemento::OPERATIVO, $pc1->estado);
        $this->assertEquals(EstadoElemento::OPERATIVO, $pc2->estado);
        $this->assertEquals($salaDestino->id, $pc1->ultimoMovimiento->ubicacion_id);
        $this->assertEquals($salaDestino->id, $pc2->ultimoMovimiento->ubicacion_id);
    }
}
