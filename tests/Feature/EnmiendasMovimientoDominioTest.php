<?php

namespace Tests\Feature;

use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnmiendasMovimientoDominioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_enmendar_ticket_resuelto_preserva_estado_ticket_y_no_crea_ticket_fantasma(): void
    {
        $tipo = Tipo::first();
        $ubicacion = Ubicacion::where('activa', true)->first();
        $tec = Usuario::whereHas('roles', fn ($q) => $q->where('rol', 'tec'))->first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-ENM-TKT-01',
            'tipo_id' => $tipo->id,
            'modelo' => 'PC Enmendable',
        ]);

        $ticketResuelto = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'reparacion',
            'estado_ticket' => 'resuelto',
            'ubicacion_id' => $ubicacion->id,
            'user_id' => $tec->id,
            'fecha' => now()->subDay(),
            'comentario' => 'Ticket resuelto con error de tipeo en puesto',
            'puesto' => 'Puesto 01',
        ]);

        $enmienda = $ticketResuelto->crearEnmienda([
            'puesto' => 'Puesto 02',
        ], $tec->id, 'Corrección de puesto físico real');

        $this->assertEquals($ticketResuelto->id, $enmienda->correccion_de_id);
        $this->assertEquals('resuelto', $enmienda->estado_ticket);
        $this->assertFalse($enmienda->esTicketAbierto()); // No es ticket fantasma!
        $this->assertEquals(0, Movimiento::ticketsAbiertos()->where('id', $enmienda->id)->count());
    }

    public function test_enmendar_prestamo_abierto_no_duplica_prestamos_activos(): void
    {
        $tipo = Tipo::first();
        $tec = Usuario::whereHas('roles', fn ($q) => $q->where('rol', 'tec'))->first();

        $elemento = Elemento::create([
            'nro_lia' => 'TST-ENM-PREST-01',
            'tipo_id' => $tipo->id,
            'modelo' => 'Notebook Enmendable',
        ]);

        $prestamo = Movimiento::create([
            'elemento_id' => $elemento->id,
            'tipo_movimiento' => 'externo_prestamo',
            'user_id' => $tec->id,
            'fecha' => now()->subDays(2),
            'solicitante' => 'Prof. Gmez', // Tipeo en apellido
            'fecha_prestamo' => now()->subDays(2)->toDateString(),
            'fecha_devolucion' => null,
        ]);

        $this->assertEquals(1, Movimiento::prestadosActivos()->where('elemento_id', $elemento->id)->count());

        // Enmendar el préstamo para corregir el nombre del solicitante
        $enmienda = $prestamo->crearEnmienda([
            'solicitante' => 'Prof. Gómez',
        ], $tec->id, 'Corrección ortográfica de apellido del solicitante');

        $prestamo->refresh();
        $enmienda->refresh();

        // El préstamo original debe quedar cerrado con fecha_devolucion
        $this->assertNotNull($prestamo->fecha_devolucion);
        $this->assertNull($enmienda->fecha_devolucion);
        $this->assertEquals('Prof. Gómez', $enmienda->solicitante);

        // Debe haber exactamente 1 préstamo activo (el nuevo enmendado), sin duplicación
        $this->assertEquals(1, Movimiento::prestadosActivos()->where('elemento_id', $elemento->id)->count());
        $this->assertEquals($enmienda->id, Movimiento::prestadosActivos()->where('elemento_id', $elemento->id)->first()->id);
    }
}
