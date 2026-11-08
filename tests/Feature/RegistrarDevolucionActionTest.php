<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use App\Enums\EstadoElemento;
use App\Actions\Movimientos\RegistrarDevolucionAction;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class RegistrarDevolucionActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_accion_registra_devolucion_exitosa_y_restaura_estado_operativo(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn($q) => $q->where('rol', 'tec'))->first();
        $ubicacionRetorno = Ubicacion::where('activa', true)->first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-DEV-01',
            'tipo_id' => $tipo->id,
            'modelo' => 'Notebook Dell',
        ]);

        $prestamo = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'externo_prestamo',
            'user_id' => $tec->id,
            'fecha' => now()->subDays(3),
            'solicitante' => 'Dra. Ana Gomez',
            'fecha_prestamo' => now()->subDays(3)->toDateString(),
            'fecha_devolucion' => null,
        ]);

        $this->assertEquals(EstadoElemento::PRESTADO, $elemento->fresh()->estado);

        $action = app(RegistrarDevolucionAction::class);
        $reingreso = $action->execute($prestamo, [
            'ubicacion_id' => $ubicacionRetorno->id,
            'puesto' => 'Puesto 03',
            'fecha_devolucion' => now()->toDateString(),
            'comentario' => 'Reingreso en optimas condiciones',
        ], $tec);

        $this->assertInstanceOf(Movimiento::class, $reingreso);
        $this->assertEquals('interno', $reingreso->tipo_movimiento);
        $this->assertEquals($ubicacionRetorno->id, $reingreso->ubicacion_id);
        $this->assertEquals('Puesto 03', $reingreso->puesto);

        $prestamo->refresh();
        $this->assertEquals(now()->toDateString(), $prestamo->fecha_devolucion->toDateString());

        $elemento->refresh();
        $this->assertEquals(EstadoElemento::OPERATIVO, $elemento->estado);
    }

    public function test_accion_rechaza_devolver_prestamo_ya_devuelto_previniendo_doble_clic(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn($q) => $q->where('rol', 'tec'))->first();
        $ubicacion = Ubicacion::where('activa', true)->first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-DEV-DUPL',
            'tipo_id' => $tipo->id,
            'modelo' => 'Proyector Epson',
        ]);

        $prestamo = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'externo_prestamo',
            'user_id' => $tec->id,
            'fecha' => now()->subDays(5),
            'solicitante' => 'Prof. Perez',
            'fecha_prestamo' => now()->subDays(5)->toDateString(),
            'fecha_devolucion' => now()->subDays(1)->toDateString(), // Ya devuelto
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Este préstamo ya fue devuelto previamente');

        app(RegistrarDevolucionAction::class)->execute($prestamo, [
            'ubicacion_id' => $ubicacion->id,
        ], $tec);
    }

    public function test_accion_rechaza_devolucion_sobre_movimiento_no_prestamo(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn($q) => $q->where('rol', 'tec'))->first();
        $ubicacion = Ubicacion::where('activa', true)->first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-DEV-NOPREST',
            'tipo_id' => $tipo->id,
            'modelo' => 'Monitor Samsung',
        ]);

        $movInterno = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $tec->id,
            'fecha' => now(),
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('no corresponde a un préstamo externo');

        app(RegistrarDevolucionAction::class)->execute($movInterno, [
            'ubicacion_id' => $ubicacion->id,
        ], $tec);
    }

    public function test_accion_rechaza_devolver_a_sala_inactiva(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn($q) => $q->where('rol', 'tec'))->first();

        $salaInactiva = Ubicacion::create([
            'nombre' => 'Sala Desmantelada 10',
            'activa' => false,
        ]);

        $elemento = Elemento::create([
            'nro_lia' => 'TST-DEV-INACT',
            'tipo_id' => $tipo->id,
            'modelo' => 'Switch HP',
        ]);

        $prestamo = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'externo_prestamo',
            'user_id' => $tec->id,
            'fecha' => now()->subDays(2),
            'solicitante' => 'Prof. Sanchez',
            'fecha_prestamo' => now()->subDays(2)->toDateString(),
            'fecha_devolucion' => null,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('no está activa o se encuentra archivada');

        app(RegistrarDevolucionAction::class)->execute($prestamo, [
            'ubicacion_id' => $salaInactiva->id,
        ], $tec);
    }

    public function test_accion_rechaza_fecha_devolucion_futura_o_previa_al_prestamo(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn($q) => $q->where('rol', 'tec'))->first();
        $ubicacion = Ubicacion::where('activa', true)->first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-DEV-FECHAS',
            'tipo_id' => $tipo->id,
            'modelo' => 'Mouse Genius',
        ]);

        $prestamo = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'externo_prestamo',
            'user_id' => $tec->id,
            'fecha' => now()->subDays(4),
            'solicitante' => 'Prof. Sanchez',
            'fecha_prestamo' => now()->subDays(4)->toDateString(),
            'fecha_devolucion' => null,
        ]);

        try {
            app(RegistrarDevolucionAction::class)->execute($prestamo, [
                'ubicacion_id' => $ubicacion->id,
                'fecha_devolucion' => now()->addDays(2)->toDateString(),
            ], $tec);
            $this->fail('Debió fallar por fecha futura');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('fecha_devolucion', $e->errors());
        }

        try {
            app(RegistrarDevolucionAction::class)->execute($prestamo, [
                'ubicacion_id' => $ubicacion->id,
                'fecha_devolucion' => now()->subDays(10)->toDateString(),
            ], $tec);
            $this->fail('Debió fallar por fecha anterior al préstamo');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('fecha_devolucion', $e->errors());
        }
    }
}
