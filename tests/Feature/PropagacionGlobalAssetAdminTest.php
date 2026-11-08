<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropagacionGlobalAssetAdminTest extends TestCase
{
    use RefreshDatabase;

    protected Ubicacion $ubicacion;

    protected Tipo $tipo;

    protected Rol $rolAssetAdmin;

    protected Rol $rolTec;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ubicacion = Ubicacion::firstOrCreate(['nombre' => 'Sala de Servidores'], ['activa' => true]);
        $this->tipo = Tipo::firstOrCreate(['nombre' => 'Servidor Rack'], ['categoria' => 'Servidores']);

        $this->rolAssetAdmin = Rol::firstOrCreate(['rol' => 'asset_admin'], ['nombre' => 'Administrador de Activos']);
        $this->rolTec = Rol::firstOrCreate(['rol' => 'tec'], ['nombre' => 'Técnico']);
    }

    public function test_usuario_con_rol_asset_admin_puede_acceder_a_creacion_de_elementos(): void
    {
        $assetAdmin = Usuario::factory()->create(['activo' => true]);
        $assetAdmin->roles()->attach($this->rolAssetAdmin->id);

        $this->assertTrue($assetAdmin->isAssetAdmin());
        $this->assertTrue($assetAdmin->canManageElementos());

        $response = $this->actingAs($assetAdmin)->get(route('elementos.create'));
        $response->assertOk();
    }

    public function test_usuario_sin_rol_asset_admin_recibe_403_al_intentar_mutar_elementos(): void
    {
        $tecnico = Usuario::factory()->create(['activo' => true]);
        $tecnico->roles()->attach($this->rolTec->id);

        $this->assertFalse($tecnico->canManageElementos());

        $responseCreate = $this->actingAs($tecnico)->get(route('elementos.create'));
        $responseCreate->assertStatus(403);

        $responseStore = $this->actingAs($tecnico)->post(route('elementos.store'), [
            'tipo_id' => $this->tipo->id,
            'ubicacion_id' => $this->ubicacion->id,
            'nro_lia' => 'LIA-FORBIDDEN-01',
            'marca' => 'Dell',
            'modelo' => 'PowerEdge',
        ]);
        $responseStore->assertStatus(403);
    }

    public function test_compatibilidad_bidireccional_con_legacy_item_admin(): void
    {
        $rolItemAdmin = Rol::firstOrCreate(['rol' => 'item_admin'], ['nombre' => 'Admin Items Legacy']);
        $legacyAdmin = Usuario::factory()->create(['activo' => true]);
        $legacyAdmin->roles()->attach($rolItemAdmin->id);

        // Debe resolver positivamente tanto isAssetAdmin como isItemAdmin y canManageElementos
        $this->assertTrue($legacyAdmin->isAssetAdmin());
        $this->assertTrue($legacyAdmin->isItemAdmin());
        $this->assertTrue($legacyAdmin->canManageElementos());

        // La ruta protegida por middleware role:asset_admin debe permitir el acceso al usuario legacy
        $response = $this->actingAs($legacyAdmin)->get(route('elementos.create'));
        $response->assertOk();
    }

    public function test_asset_admin_posee_permisos_sobre_cierre_de_tickets_y_permisos_activos(): void
    {
        $assetAdmin = Usuario::factory()->create(['activo' => true]);
        $assetAdmin->roles()->attach($this->rolAssetAdmin->id);

        $this->assertTrue($assetAdmin->canSolveTickets());
        $this->assertTrue($assetAdmin->canCloseTickets());
        $this->assertTrue($assetAdmin->canAssociateMovementsToTicket());
        $this->assertTrue($assetAdmin->hasActivePermissions());
    }
}
