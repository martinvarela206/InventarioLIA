<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SaneamientoSeedersEImportadorOdsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Rol::firstOrCreate(['rol' => 'app_admin']);
        Rol::firstOrCreate(['rol' => 'user_admin']);
        Rol::firstOrCreate(['rol' => 'asset_admin']);
        Rol::firstOrCreate(['rol' => 'tec']);
        Rol::firstOrCreate(['rol' => 'subtec']);
        Rol::firstOrCreate(['rol' => 'rev']);
        Rol::firstOrCreate(['rol' => 'registered']);
    }

    public function test_user_seeder_utiliza_contrasena_robusta_y_no_1234(): void
    {
        $this->seed(UserSeeder::class);

        $admin = Usuario::where('email', 'flavia@lia.com')->first();
        $this->assertNotNull($admin);

        // La contraseña por defecto '1234' NO debe funcionar
        $this->assertFalse(Hash::check('1234', $admin->password));

        // Debe coincidir con la contraseña robusta por defecto
        $this->assertTrue(Hash::check('LiaSegura2026!', $admin->password));
    }

    public function test_importador_ods_bloquea_ejecucion_en_produccion_sin_force(): void
    {
        app()['env'] = 'production';

        $exitCode = Artisan::call('inventario:import-ods', [
            '--file' => 'nonexistent.ods',
        ]);

        $this->assertEquals(1, $exitCode);
        $output = Artisan::output();
        $this->assertStringContainsString('ACCESO DENEGADO', $output);

        app()['env'] = 'testing';
    }

    public function test_importador_ods_bloquea_fresh_en_produccion(): void
    {
        app()['env'] = 'production';

        $exitCode = Artisan::call('inventario:import-ods', [
            '--force' => true,
            '--fresh' => true,
        ]);

        $this->assertEquals(1, $exitCode);
        $output = Artisan::output();
        $this->assertStringContainsString('OPERACIÓN ABORTADA', $output);

        app()['env'] = 'testing';
    }
}
