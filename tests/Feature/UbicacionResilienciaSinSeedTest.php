<?php

namespace Tests\Feature;

use App\Models\Ubicacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UbicacionResilienciaSinSeedTest extends TestCase
{
    use RefreshDatabase;

    // No se llama a $this->seed() para simular base de datos sin inicializar

    public function test_resolver_ubicacion_canonica_tolera_base_de_datos_sin_seedear(): void
    {
        $this->assertEquals(0, Ubicacion::count());

        $sala = Ubicacion::resolverUbicacionCanonica('Laboratorio de Drones y Robotica');

        $this->assertInstanceOf(Ubicacion::class, $sala);
        $this->assertEquals('Laboratorio de Drones y Robotica', $sala->nombre);
        $this->assertTrue($sala->activa);
        $this->assertEquals(1, Ubicacion::count());
    }
}
