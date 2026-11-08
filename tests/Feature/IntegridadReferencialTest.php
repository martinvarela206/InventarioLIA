<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Usuario;
use App\Models\Tipo;
use App\Models\Ubicacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class IntegridadReferencialTest extends TestCase
{
    use RefreshDatabase;

    private function crearDatosBase(): array
    {
        $this->seed();

        $user = Usuario::create([
            'nombre' => 'Usuario FK Test',
            'email' => 'user_fk@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);

        $tipo = Tipo::first();
        $ubicacion = Ubicacion::first();

        return [$user, $tipo, $ubicacion];
    }

    public function test_no_se_puede_borrar_fisicamente_elemento_con_movimientos_por_foreign_key_restrict(): void
    {
        [$user, $tipo, $ubicacion] = $this->crearDatosBase();

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        $elemento = Elemento::create([
            'nro_lia' => 'LIA-FK-TEST-01',
            'tipo_id' => $tipo->id,
            'marca' => 'Dell',
        ]);

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $user->id,
            'fecha' => now(),
            'comentario' => 'Movimiento de prueba FK',
        ]);

        $this->expectException(QueryException::class);
        DB::table('elementos')->where('id', $elemento->id)->delete();
    }

    public function test_no_se_puede_borrar_fisicamente_usuario_con_movimientos_por_foreign_key_restrict(): void
    {
        [$user, $tipo, $ubicacion] = $this->crearDatosBase();

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        $elemento = Elemento::create([
            'nro_lia' => 'LIA-FK-TEST-02',
            'tipo_id' => $tipo->id,
            'marca' => 'HP',
        ]);

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $user->id,
            'fecha' => now(),
            'comentario' => 'Movimiento de prueba FK usuario',
        ]);

        $this->expectException(QueryException::class);
        DB::table('usuarios')->where('id', $user->id)->delete();
    }
}
