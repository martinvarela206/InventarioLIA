<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EfectividadFlagActivoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Rol::firstOrCreate(['rol' => 'app_admin']);
        Rol::firstOrCreate(['rol' => 'user_admin']);
        Rol::firstOrCreate(['rol' => 'registered']);
        Rol::firstOrCreate(['rol' => 'asset_admin']);
        Rol::firstOrCreate(['rol' => 'tec']);
    }

    public function test_login_rechaza_usuario_inactivo_y_muestra_error_especifico(): void
    {
        $usuarioInactivo = Usuario::factory()->create([
            'nombre' => 'Inactivo Test',
            'email' => 'inactivo@unsj.edu.ar',
            'password' => Hash::make('password123'),
            'activo' => false,
        ]);
        $usuarioInactivo->roles()->attach(Rol::where('rol', 'tec')->first());

        $response = $this->post('/login', [
            'login' => 'inactivo@unsj.edu.ar',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
        // Mensaje unificado anti-enumeración de usuarios
        $this->assertStringContainsString('no coinciden con nuestros registros', session('errors')->first('login'));
    }

    public function test_login_permite_acceso_a_usuario_activo(): void
    {
        $usuarioActivo = Usuario::factory()->create([
            'nombre' => 'Activo Test',
            'email' => 'activo@unsj.edu.ar',
            'password' => Hash::make('password123'),
            'activo' => true,
        ]);
        $usuarioActivo->roles()->attach(Rol::where('rol', 'tec')->first());

        $response = $this->post('/login', [
            'login' => 'activo@unsj.edu.ar',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($usuarioActivo);
        $response->assertRedirect(route('dashboard.index'));
    }

    public function test_registro_crea_usuario_inactivo_y_no_hace_auto_login(): void
    {
        $response = $this->post('/register', [
            'nombre' => 'Nuevo Postulante',
            'email' => 'nuevo@unsj.edu.ar',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terminos_consentimiento' => '1',
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('revision.index'));
        $this->assertDatabaseHas('usuarios', [
            'email' => 'nuevo@unsj.edu.ar',
            'activo' => false,
        ]);
    }

    public function test_toggle_activo_desactiva_cuenta_y_revoca_tokens_sanctum(): void
    {
        $userAdmin = Usuario::factory()->create(['email' => 'admin@unsj.edu.ar', 'activo' => true]);
        $userAdmin->roles()->attach(Rol::where('rol', 'user_admin')->first());

        $target = Usuario::factory()->create(['email' => 'target@unsj.edu.ar', 'activo' => true]);
        $token = $target->createToken('test-token');

        $this->assertCount(1, $target->fresh()->tokens);

        $response = $this->actingAs($userAdmin)->post(route('users.toggleActivo', $target->id));

        $response->assertRedirect(route('users.index'));
        $target->refresh();
        $this->assertFalse((bool) $target->activo);
        $this->assertCount(0, $target->tokens);
    }

    public function test_middleware_expulsa_a_usuario_cuya_cuenta_se_desactiva_en_vivo(): void
    {
        $usuario = Usuario::factory()->create(['email' => 'sesion@unsj.edu.ar', 'activo' => false]);
        $usuario->roles()->attach(Rol::where('rol', 'tec')->first());

        // Al estar EnsureUserIsActive en el pipeline web global, el usuario es desconectado y enviado al login
        $response = $this->actingAs($usuario)->get('/dashboard');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_toggle_activo_rota_remember_token_al_desactivar(): void
    {
        $userAdmin = Usuario::factory()->create(['email' => 'admin@unsj.edu.ar', 'activo' => true]);
        $userAdmin->roles()->attach(Rol::where('rol', 'user_admin')->first());

        $tokenPrevio = 'token_inicial_de_sesion_recordada_12345';
        $target = Usuario::factory()->create([
            'email' => 'target_remember@unsj.edu.ar',
            'activo' => true,
            'remember_token' => $tokenPrevio,
        ]);

        $this->actingAs($userAdmin)->post(route('users.toggleActivo', $target->id));

        $target->refresh();
        $this->assertFalse((bool) $target->activo);
        $this->assertNotEquals($tokenPrevio, $target->remember_token);
        $this->assertNotEmpty($target->remember_token);
    }

    public function test_toggle_activo_bloquea_autodesactivacion(): void
    {
        $admin = Usuario::factory()->create(['email' => 'self_admin@unsj.edu.ar', 'activo' => true]);
        $admin->roles()->attach(Rol::where('rol', 'user_admin')->first());

        $response = $this->actingAs($admin)->post(route('users.toggleActivo', $admin->id));

        $response->assertSessionHasErrors(['error']);
        $admin->refresh();
        $this->assertTrue((bool) $admin->activo);
    }

    public function test_user_admin_no_puede_desactivar_a_un_app_admin(): void
    {
        $userAdmin = Usuario::factory()->create(['email' => 'user_admin_only@unsj.edu.ar', 'activo' => true]);
        $userAdmin->roles()->attach(Rol::where('rol', 'user_admin')->first());

        $appAdmin = Usuario::factory()->create(['email' => 'target_app_admin@unsj.edu.ar', 'activo' => true]);
        $appAdmin->roles()->attach(Rol::where('rol', 'app_admin')->first());

        $response = $this->actingAs($userAdmin)->post(route('users.toggleActivo', $appAdmin->id));

        $response->assertSessionHasErrors(['error']);
        $appAdmin->refresh();
        $this->assertTrue((bool) $appAdmin->activo);
    }

    public function test_no_se_puede_desactivar_al_ultimo_app_admin_o_user_admin_activo(): void
    {
        $roleAppAdmin = Rol::where('rol', 'app_admin')->first();
        $roleUserAdmin = Rol::where('rol', 'user_admin')->first();

        // Limpiamos cualquier otro app_admin activo
        Usuario::whereHas('roles', fn ($q) => $q->where('rol', 'app_admin'))->update(['activo' => false]);

        $actorAdmin = Usuario::factory()->create(['email' => 'actor_admin@unsj.edu.ar', 'activo' => true]);
        $actorAdmin->roles()->attach([$roleAppAdmin->id, $roleUserAdmin->id]);

        $segundoAppAdmin = Usuario::factory()->create(['email' => 'segundo_app_admin@unsj.edu.ar', 'activo' => true]);
        $segundoAppAdmin->roles()->attach([$roleAppAdmin->id, $roleUserAdmin->id]);

        // Hay 2 app_admin activos; el actor desactiva al segundo app_admin (permitido)
        $response1 = $this->actingAs($actorAdmin)->post(route('users.toggleActivo', $segundoAppAdmin->id));
        $response1->assertRedirect(route('users.index'));
        $segundoAppAdmin->refresh();
        $this->assertFalse((bool) $segundoAppAdmin->activo);

        // Ahora solo queda 1 app_admin activo ($actorAdmin).
        // Creamos otro usuario con user_admin que intente desactivar al único app_admin activo:
        $otroUserAdmin = Usuario::factory()->create(['email' => 'solo_user_admin@unsj.edu.ar', 'activo' => true]);
        $otroUserAdmin->roles()->attach($roleUserAdmin->id);

        // Intentar desactivar al único app_admin activo: es bloqueado por jerarquía y por ser el único
        $response2 = $this->actingAs($otroUserAdmin)->post(route('users.toggleActivo', $actorAdmin->id));
        $response2->assertSessionHasErrors(['error']);
        $actorAdmin->refresh();
        $this->assertTrue((bool) $actorAdmin->activo);
    }

    public function test_usuario_inactivo_no_posee_permisos_activos_aunque_tenga_roles(): void
    {
        $usuario = Usuario::factory()->create(['activo' => false]);
        $usuario->roles()->attach(Rol::where('rol', 'app_admin')->first());

        $this->assertFalse($usuario->hasActivePermissions());

        $usuario->activo = true;
        $usuario->save();

        $this->assertTrue($usuario->hasActivePermissions());
    }
}
