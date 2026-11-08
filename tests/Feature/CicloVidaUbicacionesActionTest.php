<?php

namespace Tests\Feature;

use App\Actions\Ubicaciones\ArchivarUbicacionAction;
use App\Actions\Ubicaciones\RenombrarUbicacionAction;
use App\Enums\EstadoElemento;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CicloVidaUbicacionesActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_renombrar_sala_crea_nueva_entidad_archiva_original_y_pasa_activos_a_limbo(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn ($q) => $q->where('rol', 'tec'))->first();

        // 1. Crear sala original con activos
        $salaOriginal = Ubicacion::create([
            'nombre' => 'Laboratorio Redes 1',
            'descripcion' => 'Sala de telecomunicaciones',
            'activa' => true,
        ]);

        $activo1 = Elemento::create([
            'nro_lia' => 'TST-SALA-01',
            'tipo_id' => $tipo->id,
            'modelo' => 'Switch Cisco',
        ]);
        Movimiento::create([
            'elemento_id' => $activo1->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $salaOriginal->id,
            'user_id' => $tec->id,
            'fecha' => now(),
        ]);

        $this->assertEquals(EstadoElemento::OPERATIVO, $activo1->fresh()->estado);

        // 2. Renombrar la sala
        $action = app(RenombrarUbicacionAction::class);
        $res = $action->execute($salaOriginal, 'Laboratorio Ciberseguridad e IoT', 'Sala actualizada');

        $this->assertTrue($res['renombro']);
        $this->assertEquals(1, $res['activos_en_limbo']);

        // Sala original ahora es histórica/inactiva
        $salaOriginal->refresh();
        $this->assertFalse($salaOriginal->activa);
        $this->assertEquals('Laboratorio Redes 1', $salaOriginal->nombre); // Preserva nombre histórico!

        // Nueva sala creada y activa
        $nuevaSala = $res['nueva_ubicacion'];
        $this->assertTrue($nuevaSala->activa);
        $this->assertEquals('Laboratorio Ciberseguridad e IoT', $nuevaSala->nombre);
        $this->assertNotEquals($salaOriginal->id, $nuevaSala->id);

        // Activo pasa canónicamente a limbo
        $activo1->refresh();
        $this->assertEquals(EstadoElemento::EN_LIMBO_REUBICACION, $activo1->estado);
        $this->assertTrue(Elemento::enLimbo()->where('id', $activo1->id)->exists());
        $this->assertFalse(Elemento::disponibles()->where('id', $activo1->id)->exists());
    }

    public function test_renombrar_sin_modificar_nombre_solo_actualiza_descripcion(): void
    {
        $sala = Ubicacion::create([
            'nombre' => 'Sala Unica',
            'descripcion' => 'Desc 1',
            'activa' => true,
        ]);

        $res = app(RenombrarUbicacionAction::class)->execute($sala, 'Sala Unica', 'Desc Actualizada');

        $this->assertFalse($res['renombro']);
        $this->assertEquals($sala->id, $res['nueva_ubicacion']->id);
        $sala->refresh();
        $this->assertTrue($sala->activa);
        $this->assertEquals('Desc Actualizada', $sala->descripcion);
    }

    public function test_rechaza_renombrar_con_nombre_duplicado(): void
    {
        Ubicacion::create(['nombre' => 'Laboratorio Existente', 'activa' => true]);
        $sala2 = Ubicacion::create(['nombre' => 'Laboratorio Secundario', 'activa' => true]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Ya existe una sala o laboratorio registrado con el nombre');

        app(RenombrarUbicacionAction::class)->execute($sala2, 'Laboratorio Existente');
    }

    public function test_archivar_sala_la_marca_inactiva_y_pasa_activos_a_limbo(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn ($q) => $q->where('rol', 'tec'))->first();

        $sala = Ubicacion::create([
            'nombre' => 'Laboratorio Antiguo a Desmantelar',
            'activa' => true,
        ]);

        $activo = Elemento::create([
            'nro_lia' => 'TST-ARCH-01',
            'tipo_id' => $tipo->id,
            'modelo' => 'Server Dell',
        ]);
        Movimiento::create([
            'elemento_id' => $activo->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $sala->id,
            'user_id' => $tec->id,
            'fecha' => now(),
        ]);

        $res = app(ArchivarUbicacionAction::class)->execute($sala);

        $this->assertEquals(1, $res['activos_en_limbo']);
        $sala->refresh();
        $this->assertFalse($sala->activa);

        $activo->refresh();
        $this->assertEquals(EstadoElemento::EN_LIMBO_REUBICACION, $activo->estado);
        $this->assertTrue(Elemento::enLimbo()->where('id', $activo->id)->exists());
    }

    public function test_rechaza_archivar_sala_ya_archivada(): void
    {
        $sala = Ubicacion::create([
            'nombre' => 'Sala Ya Inactiva',
            'activa' => false,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('ya se encuentra archivada e inactiva');

        app(ArchivarUbicacionAction::class)->execute($sala);
    }
}
