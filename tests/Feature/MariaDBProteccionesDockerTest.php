<?php

namespace Tests\Feature;

use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Rol;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MariaDBProteccionesDockerTest extends TestCase
{
    use RefreshDatabase;

    public function test_docker_compose_configura_protecciones_seguridad_mariadb(): void
    {
        $composePath = base_path('docker-compose.yml');
        $this->assertFileExists($composePath);

        $content = File::get($composePath);

        // Volúmenes de configuración de seguridad e inicialización
        $this->assertStringContainsString('./docker/mariadb/conf.d:/etc/mysql/conf.d:ro', $content);
        $this->assertStringContainsString('./docker/mariadb/init:/docker-entrypoint-initdb.d:ro', $content);

        // Bandera de prevención de borrado/actualización masiva sin índice
        $this->assertStringContainsString('--sql-safe-updates=ON', $content);
    }

    public function test_archivo_configuracion_security_cnf_contiene_directivas_estrictas(): void
    {
        $cnfPath = base_path('docker/mariadb/conf.d/security.cnf');
        $this->assertFileExists($cnfPath);

        $cnf = File::get($cnfPath);
        $this->assertStringContainsString('sql_safe_updates = ON', $cnf);
        $this->assertStringContainsString('foreign_key_checks = ON', $cnf);
        $this->assertStringContainsString('local_infile = 0', $cnf);
        $this->assertStringContainsString('plugin_load_add = server_audit', $cnf);
        $this->assertStringContainsString('server_audit = FORCE_PLUS_PERMANENT', $cnf);
    }

    public function test_script_inicializacion_aplica_principio_de_menor_privilegio(): void
    {
        $initScriptPath = base_path('docker/mariadb/init/01-security-hardening.sh');
        $this->assertFileExists($initScriptPath);

        $script = File::get($initScriptPath);
        $this->assertStringContainsString('REVOKE FILE, SUPER, PROCESS, SHUTDOWN', $script);
        $this->assertStringContainsString('GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, INDEX, REFERENCES, TRIGGER', $script);
    }

    public function test_trigger_impide_hard_delete_de_elementos_por_sql_directo(): void
    {
        $this->seed();
        $tipo = Tipo::first();

        $elemento = Elemento::create([
            'nro_lia' => 'LIA-PROT-ELEM-01',
            'tipo_id' => $tipo->id,
            'marca' => 'Dell',
        ]);

        $this->expectException(QueryException::class);
        DB::table('elementos')->where('id', $elemento->id)->delete();
    }

    public function test_trigger_impide_delete_de_usuario_con_movimientos_historicos(): void
    {
        $this->seed();
        $user = Usuario::create([
            'nombre' => 'Custodia Legal Test',
            'email' => 'custodia@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);

        $tipo = Tipo::first();
        $ubicacion = Ubicacion::first();

        $elemento = Elemento::create([
            'nro_lia' => 'LIA-PROT-USER-01',
            'tipo_id' => $tipo->id,
            'marca' => 'HP',
        ]);

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $user->id,
            'fecha' => now(),
            'comentario' => 'Movimiento histórico',
        ]);

        $this->expectException(QueryException::class);
        DB::table('usuarios')->where('id', $user->id)->delete();
    }

    public function test_trigger_impide_delete_de_ubicacion_con_movimientos(): void
    {
        $this->seed();
        $ubicacion = Ubicacion::create([
            'nombre' => 'Laboratorio Histórico Alpha',
            'activa' => true,
        ]);

        $user = Usuario::first();
        $tipo = Tipo::first();

        $elemento = Elemento::create([
            'nro_lia' => 'LIA-PROT-UBIC-01',
            'tipo_id' => $tipo->id,
            'marca' => 'Lenovo',
        ]);

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $user->id,
            'fecha' => now(),
            'comentario' => 'Prueba de sala con movimientos',
        ]);

        $this->expectException(QueryException::class);
        DB::table('ubicaciones')->where('id', $ubicacion->id)->delete();
    }

    public function test_trigger_impide_delete_de_roles_canonicos(): void
    {
        $this->seed();
        $rolAdmin = Rol::where('rol', 'app_admin')->first();
        $this->assertNotNull($rolAdmin);

        $this->expectException(QueryException::class);
        DB::table('roles')->where('id', $rolAdmin->id)->delete();
    }

    public function test_trigger_impide_delete_de_tipos_con_elementos_asociados(): void
    {
        $this->seed();
        $tipo = Tipo::first();

        Elemento::create([
            'nro_lia' => 'LIA-PROT-TIPO-01',
            'tipo_id' => $tipo->id,
            'marca' => 'Asus',
        ]);

        $this->expectException(QueryException::class);
        DB::table('tipos')->where('id', $tipo->id)->delete();
    }
}
