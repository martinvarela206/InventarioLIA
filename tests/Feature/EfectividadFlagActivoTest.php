<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
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
        $this->assertFalse((bool)$target->activo);
        $this->assertCount(0, $target->tokens);
    }

    public function test_middleware_expulsa_a_usuario_cuya_cuenta_se_desactiva_en_vivo(): void
    {
        $usuario = Usuario::factory()->create(['email' => 'sesion@unsj.edu.ar', 'activo' => false]);
        $usuario->roles()->attach(Rol::where('rol', 'tec')->first());

        // Simulamos petición de usuario inactivo al dashboard: es redirigido a bienvenida
        $response = $this->actingAs($usuario)->get('/dashboard');

        $response->assertRedirect(route('bienvenida'));
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
