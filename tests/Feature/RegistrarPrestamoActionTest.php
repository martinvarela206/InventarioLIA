<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use App\Models\Rol;
use App\Enums\EstadoElemento;
use App\Actions\Movimientos\RegistrarPrestamoAction;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class RegistrarPrestamoActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_accion_presta_exitosamente_activo_disponible(): void
    {
        $tipo = Tipo::first();
        $ubicacion = Ubicacion::where('activa', true)->first();
        $tec = Usuario::whereHas('roles', fn($q) => $q->where('rol', 'tec'))->first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-PREST-01',
            'tipo_id' => $tipo->id,
            'modelo' => 'ThinkCentre',
        ]);

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $tec->id,
            'fecha' => now(),
        ]);

        $action = app(RegistrarPrestamoAction::class);
        $movimiento = $action->execute($elemento, [
            'solicitante' => 'Dra. Maria Rodriguez',
            'fecha_prestamo' => now()->toDateString(),
            'comentario' => 'Prestamo para congreso cientifico',
        ], $tec);

        $this->assertInstanceOf(Movimiento::class, $movimiento);
        $this->assertEquals('externo_prestamo', $movimiento->tipo_movimiento);
        $this->assertEquals('Dra. Maria Rodriguez', $movimiento->solicitante);
        $this->assertNull($movimiento->fecha_devolucion);

        $elemento->refresh();
        $this->assertEquals(EstadoElemento::PRESTADO, $elemento->estado);
    }

    public function test_accion_rechaza_prestar_activo_en_baja(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn($q) => $q->where('rol', 'tec'))->first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-PREST-BAJA',
            'tipo_id' => $tipo->id,
            'modelo' => 'PC Obsoleta',
        ]);

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'baja',
            'user_id' => $tec->id,
            'fecha' => now(),
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No es posible prestar un activo dado de baja');

        app(RegistrarPrestamoAction::class)->execute($elemento, [
            'solicitante' => 'Investigador',
        ], $tec);
    }

    public function test_accion_rechaza_prestar_activo_en_reparacion(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn($q) => $q->where('rol', 'tec'))->first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-PREST-REP',
            'tipo_id' => $tipo->id,
            'modelo' => 'Servidor Danado',
        ]);

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'abierto',
            'user_id' => $tec->id,
            'fecha' => now(),
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No es posible prestar un activo que se encuentra en taller técnico');

        app(RegistrarPrestamoAction::class)->execute($elemento, [
            'solicitante' => 'Investigador',
        ], $tec);
    }

    public function test_accion_rechaza_prestar_activo_en_limbo(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn($q) => $q->where('rol', 'tec'))->first();

        $salaInactiva = Ubicacion::create([
            'nombre' => 'Sala Desmantelada 99',
            'activa' => false,
        ]);

        $elemento = Elemento::create([
            'nro_lia' => 'TST-PREST-LIMBO',
            'tipo_id' => $tipo->id,
            'modelo' => 'Switch Huérfano',
        ]);

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $salaInactiva->id,
            'user_id' => $tec->id,
            'fecha' => now(),
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No es posible prestar un activo que se encuentra en limbo');

        app(RegistrarPrestamoAction::class)->execute($elemento, [
            'solicitante' => 'Investigador',
        ], $tec);
    }

    public function test_accion_rechaza_prestar_activo_ya_prestado(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn($q) => $q->where('rol', 'tec'))->first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-PREST-DUPL',
            'tipo_id' => $tipo->id,
            'modelo' => 'Notebook Docente',
        ]);

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'externo_prestamo',
            'user_id' => $tec->id,
            'solicitante' => 'Ing. Alberto Silva',
            'fecha' => now(),
            'fecha_prestamo' => now()->toDateString(),
            'fecha_devolucion' => null,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('El activo ya posee un préstamo activo sin devolver a cargo de: Ing. Alberto Silva');

        app(RegistrarPrestamoAction::class)->execute($elemento, [
            'solicitante' => 'Otro Docente',
        ], $tec);
    }

    public function test_rechaza_prestamo_sin_solicitante_o_con_fecha_futura(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn($q) => $q->where('rol', 'tec'))->first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-PREST-FUTURO',
            'tipo_id' => $tipo->id,
            'modelo' => 'Tablet',
        ]);

        try {
            app(RegistrarPrestamoAction::class)->execute($elemento, [
                'solicitante' => '',
            ], $tec);
            $this->fail('Debió fallar por solicitante vacío');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('solicitante', $e->errors());
        }

        try {
            app(RegistrarPrestamoAction::class)->execute($elemento, [
                'solicitante' => 'Prof. Juan',
                'fecha_prestamo' => Carbon::now()->addDays(2)->toDateString(),
            ], $tec);
            $this->fail('Debió fallar por fecha futura');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('fecha_prestamo', $e->errors());
        }
    }

    public function test_controller_bloquea_traslado_interno_de_activo_prestado(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn($q) => $q->where('rol', 'tec'))->first();
        $ubicacion = Ubicacion::where('activa', true)->first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-CTRL-BLOQ',
            'tipo_id' => $tipo->id,
            'modelo' => 'Proyector EPSON',
        ]);

        Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'externo_prestamo',
            'user_id' => $tec->id,
            'solicitante' => 'Prof. Perez',
            'fecha' => now(),
            'fecha_prestamo' => now()->toDateString(),
            'fecha_devolucion' => null,
        ]);

        $response = $this->actingAs($tec)->post(route('movimientos.store'), [
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'interno',
            'ubicacion_id' => $ubicacion->id,
        ]);

        $response->assertSessionHasErrors(['elemento_id']);
    }

    public function test_request_rechaza_tipos_movimiento_no_autorizados(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn($q) => $q->where('rol', 'tec'))->first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-REQ-BLOQ',
            'tipo_id' => $tipo->id,
            'modelo' => 'Router Cisco',
        ]);

        $response = $this->actingAs($tec)->post(route('movimientos.store'), [
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'baja',
        ]);

        $response->assertSessionHasErrors(['tipo_movimiento']);
    }
}
