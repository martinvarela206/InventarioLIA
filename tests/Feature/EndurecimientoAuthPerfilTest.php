<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class EndurecimientoAuthPerfilTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('ataque@lia.com|127.0.0.1');
    }

    public function test_login_rechaza_nombre_de_usuario_y_exige_correo_electronico(): void
    {
        $rolAdmin = Rol::firstOrCreate(['rol' => 'app_admin'], ['nombre' => 'Administrador']);
        $user = Usuario::factory()->create([
            'nombre' => 'juanperez',
            'email' => 'juan.perez@unsj.edu.ar',
            'password' => Hash::make('Secret123!'),
            'activo' => true,
        ]);
        $user->roles()->attach($rolAdmin->id);

        // Intento de login usando únicamente el 'nombre' (juanperez) sin formato de email
        $responseNombre = $this->post('/login', [
            'login' => 'juanperez',
            'password' => 'Secret123!',
        ]);

        $responseNombre->assertSessionHasErrors('login');
        $this->assertGuest();

        // Intento con correo electrónico válido
        $responseEmail = $this->post('/login', [
            'login' => 'juan.perez@unsj.edu.ar',
            'password' => 'Secret123!',
        ]);

        $responseEmail->assertRedirect(route('dashboard.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_registro_aplica_rate_limiting(): void
    {
        // Enviar 5 peticiones a /register
        for ($i = 0; $i < 5; $i++) {
            $this->post('/register', [
                'nombre' => 'Test Rate Limit',
                'email' => "rate{$i}@unsj.edu.ar",
                'password' => 'ClaveRobusta2026',
                'password_confirmation' => 'ClaveRobusta2026',
                'terminos_consentimiento' => '1',
            ]);
        }

        // El 6to intento debe recibir 429 Too Many Requests
        $bloqueado = $this->post('/register', [
            'nombre' => 'Test Rate Limit 6',
            'email' => 'rate6@unsj.edu.ar',
            'password' => 'ClaveRobusta2026',
            'password_confirmation' => 'ClaveRobusta2026',
            'terminos_consentimiento' => '1',
        ]);

        $this->assertEquals(429, $bloqueado->status());
    }

    public function test_registro_valida_dominio_institucional_cuando_esta_habilitado(): void
    {
        config(['auth.enforce_institutional_domain' => true]);

        // Intento con dominio genérico no institucional (@gmail.com)
        $responseInvalido = $this->post('/register', [
            'nombre' => 'Usuario Externo',
            'email' => 'externo@gmail.com',
            'password' => 'ClaveRobusta2026',
            'password_confirmation' => 'ClaveRobusta2026',
            'terminos_consentimiento' => '1',
        ]);

        $responseInvalido->assertSessionHasErrors(['email']);
        $this->assertStringContainsString('dominio institucional (@unsj.edu.ar)', session('errors')->first('email'));
        $this->assertDatabaseMissing('usuarios', ['email' => 'externo@gmail.com']);

        // Intento con dominio institucional (@unsj.edu.ar)
        $responseValido = $this->post('/register', [
            'nombre' => 'Usuario Institucional',
            'email' => 'usuario@exactas.unsj.edu.ar',
            'password' => 'ClaveRobusta2026',
            'password_confirmation' => 'ClaveRobusta2026',
            'terminos_consentimiento' => '1',
        ]);

        $responseValido->assertSessionHasNoErrors();
        $this->assertDatabaseHas('usuarios', ['email' => 'usuario@exactas.unsj.edu.ar']);
    }

    public function test_actualizacion_de_perfil_exige_contrasena_actual_para_cambiarla(): void
    {
        $rolRegistered = Rol::firstOrCreate(['rol' => 'registered'], ['nombre' => 'Registrado']);
        $user = Usuario::factory()->create([
            'email' => 'cambio.clave@unsj.edu.ar',
            'password' => Hash::make('ClaveVieja2026!'),
            'activo' => true,
        ]);
        $user->roles()->attach($rolRegistered->id);

        // 1. Intento sin current_password
        $resSinCurrent = $this->actingAs($user)->put(route('profile.update'), [
            'nombre' => $user->nombre,
            'password' => 'NuevaClaveSegura2026!',
            'password_confirmation' => 'NuevaClaveSegura2026!',
        ]);

        $resSinCurrent->assertSessionHasErrors(['current_password']);

        // 2. Intento con current_password errónea
        $resCurrentInvalida = $this->actingAs($user)->put(route('profile.update'), [
            'nombre' => $user->nombre,
            'current_password' => 'PasswordIncorrecto',
            'password' => 'NuevaClaveSegura2026!',
            'password_confirmation' => 'NuevaClaveSegura2026!',
        ]);

        $resCurrentInvalida->assertSessionHasErrors(['current_password']);

        // 3. Intento con current_password válida
        $resOk = $this->actingAs($user)->put(route('profile.update'), [
            'nombre' => $user->nombre,
            'current_password' => 'ClaveVieja2026!',
            'password' => 'NuevaClaveSegura2026!',
            'password_confirmation' => 'NuevaClaveSegura2026!',
        ]);

        $resOk->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertTrue(Hash::check('NuevaClaveSegura2026!', $user->password));
    }

    public function test_cambio_de_contrasena_revoca_tokens_y_purga_otras_sesiones(): void
    {
        $rolRegistered = Rol::firstOrCreate(['rol' => 'registered'], ['nombre' => 'Registrado']);
        $user = Usuario::factory()->create([
            'email' => 'tokens.sesiones@unsj.edu.ar',
            'password' => Hash::make('ClaveOriginal2026!'),
            'activo' => true,
        ]);
        $user->roles()->attach($rolRegistered->id);

        // Crear token Sanctum
        $token = $user->createToken('test-token');
        $this->assertDatabaseHas('personal_access_tokens', ['tokenable_id' => $user->id]);

        // Simular sesiones en DB
        DB::table('sessions')->insert([
            [
                'id' => 'sesion_remota_1',
                'user_id' => $user->id,
                'ip_address' => '192.168.1.10',
                'user_agent' => 'Mozilla/5.0 Chrome',
                'payload' => 'payload1',
                'last_activity' => time(),
            ],
            [
                'id' => 'sesion_remota_2',
                'user_id' => $user->id,
                'ip_address' => '192.168.1.20',
                'user_agent' => 'Mozilla/5.0 Firefox',
                'payload' => 'payload2',
                'last_activity' => time(),
            ],
        ]);

        $this->assertEquals(2, DB::table('sessions')->where('user_id', $user->id)->count());

        // Actualizar contraseña con current_password correcta
        $response = $this->actingAs($user)->put(route('profile.update'), [
            'nombre' => $user->nombre,
            'current_password' => 'ClaveOriginal2026!',
            'password' => 'NuevaSuperClave2026!',
            'password_confirmation' => 'NuevaSuperClave2026!',
        ]);

        $response->assertSessionHasNoErrors();

        // Verificar que tokens Sanctum fueron revocados
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);

        // Verificar que las otras sesiones fueron purgadas
        $this->assertEquals(0, DB::table('sessions')->where('user_id', $user->id)->whereIn('id', ['sesion_remota_1', 'sesion_remota_2'])->count());
    }
}
