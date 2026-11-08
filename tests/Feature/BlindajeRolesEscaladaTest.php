<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class BlindajeRolesEscaladaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Rol::firstOrCreate(['rol' => 'app_admin']);
        Rol::firstOrCreate(['rol' => 'user_admin']);
        Rol::firstOrCreate(['rol' => 'asset_admin']);
        Rol::firstOrCreate(['rol' => 'tec']);
        Rol::firstOrCreate(['rol' => 'rev']);
        Rol::firstOrCreate(['rol' => 'registered']);
    }

    public function test_user_admin_no_puede_otorgar_app_admin_en_toggle_role(): void
    {
        $userAdmin = Usuario::factory()->create(['activo' => true]);
        $userAdmin->roles()->attach(Rol::where('rol', 'user_admin')->first());

        $target = Usuario::factory()->create(['activo' => true]);

        $response = $this->actingAs($userAdmin)->postJson(route('users.toggleRole', $target->id), [
            'role' => 'app_admin',
        ]);

        $response->assertStatus(403);
        $this->assertFalse($target->fresh()->hasRole('app_admin'));
    }

    public function test_user_admin_no_puede_auto_modificar_sus_propios_roles(): void
    {
        $userAdmin = Usuario::factory()->create(['activo' => true]);
        $userAdmin->roles()->attach(Rol::where('rol', 'user_admin')->first());

        $response = $this->actingAs($userAdmin)->postJson(route('users.toggleRole', $userAdmin->id), [
            'role' => 'tec',
        ]);

        $response->assertStatus(403);
        $this->assertFalse($userAdmin->fresh()->hasRole('tec'));
    }

    public function test_user_admin_no_puede_promover_a_app_admin_en_update(): void
    {
        $userAdmin = Usuario::factory()->create(['activo' => true]);
        $userAdmin->roles()->attach(Rol::where('rol', 'user_admin')->first());

        $target = Usuario::factory()->create(['activo' => true]);
        $appAdminRol = Rol::where('rol', 'app_admin')->first();

        $response = $this->actingAs($userAdmin)->put(route('users.update', $target->id), [
            'nombre' => $target->nombre,
            'email' => $target->email,
            'roles' => [$appAdminRol->id],
        ]);

        $response->assertStatus(403);
        $this->assertFalse($target->fresh()->hasRole('app_admin'));
    }

    public function test_update_valida_que_roles_existan_en_base_de_datos(): void
    {
        $userAdmin = Usuario::factory()->create(['activo' => true]);
        $userAdmin->roles()->attach(Rol::where('rol', 'user_admin')->first());

        $target = Usuario::factory()->create(['activo' => true]);

        $response = $this->actingAs($userAdmin)->put(route('users.update', $target->id), [
            'nombre' => $target->nombre,
            'email' => $target->email,
            'roles' => [99999], // ID inexistente
        ]);

        $response->assertSessionHasErrors(['roles.0']);
    }

    public function test_app_admin_puede_gestionar_roles_y_se_registra_en_bitacora(): void
    {
        Log::spy();

        $appAdmin = Usuario::factory()->create(['activo' => true]);
        $appAdmin->roles()->attach(Rol::where('rol', 'app_admin')->first());
        $appAdmin->roles()->attach(Rol::where('rol', 'user_admin')->first());

        $target = Usuario::factory()->create(['activo' => true]);

        $response = $this->actingAs($appAdmin)->postJson(route('users.toggleRole', $target->id), [
            'role' => 'app_admin',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($target->fresh()->hasRole('app_admin'));

        Log::shouldHaveReceived('info')
            ->withArgs(function ($message, $context) use ($target) {
                return str_contains($message, 'AUDITORIA_ROLES')
                    && $context['target_id'] === $target->id
                    && $context['rol'] === 'app_admin';
            });
    }
}
