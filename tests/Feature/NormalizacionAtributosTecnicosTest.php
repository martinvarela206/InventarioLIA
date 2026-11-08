<?php

namespace Tests\Feature;

use App\Models\Atributo;
use App\Models\Elemento;
use App\Models\Rol;
use App\Models\Tipo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NormalizacionAtributosTecnicosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Rol::firstOrCreate(['rol' => 'app_admin']);
        Rol::firstOrCreate(['rol' => 'asset_admin']);
        Rol::firstOrCreate(['rol' => 'tec']);
    }

    public function test_sincronizacion_bidireccional_de_atributos_tecnicos(): void
    {
        $tipo = Tipo::firstOrCreate(['nombre' => 'Servidor'], ['slug' => 'servidor', 'icono' => 'server']);
        $attrCpu = Atributo::create([
            'nombre' => 'Procesador',
            'tipo_dato' => 'texto',
            'unidad_medida' => null,
            'descripcion' => 'CPU',
        ]);
        $attrRam = Atributo::create([
            'nombre' => 'Memoria RAM',
            'tipo_dato' => 'texto',
            'unidad_medida' => 'GB',
            'descripcion' => 'RAM',
        ]);
        $tipo->atributos()->attach([$attrCpu->id, $attrRam->id]);

        $elemento = Elemento::factory()->create([
            'tipo_id' => $tipo->id,
            'descripcion' => null,
        ]);

        // 1. Sincronizar atributos técnicos
        $elemento->sincronizarAtributosTecnicos([
            $attrCpu->id => 'AMD EPYC 7742',
            $attrRam->id => '128',
        ]);

        // Verificar persistencia en tabla normalizada
        $this->assertDatabaseHas('elemento_atributo_valor', [
            'elemento_id' => $elemento->id,
            'atributo_id' => $attrCpu->id,
            'valor' => 'AMD EPYC 7742',
        ]);
        $this->assertDatabaseHas('elemento_atributo_valor', [
            'elemento_id' => $elemento->id,
            'atributo_id' => $attrRam->id,
            'valor' => '128',
        ]);

        // Verificar sincronización en columna JSON descripcion
        $elemento->refresh();
        $this->assertEquals('AMD EPYC 7742', $elemento->descripcion['Procesador']);
        $this->assertEquals('128', $elemento->descripcion['Memoria RAM']);

        // 2. getValorAtributo formatea con unidad de medida
        $this->assertEquals('128 GB', $elemento->getValorAtributo('Memoria RAM'));
        $this->assertEquals('AMD EPYC 7742', $elemento->getValorAtributo('Procesador'));

        // 3. Remover un atributo pasándolo vacío
        $elemento->sincronizarAtributosTecnicos([
            $attrCpu->id => '',
        ]);

        $this->assertDatabaseMissing('elemento_atributo_valor', [
            'elemento_id' => $elemento->id,
            'atributo_id' => $attrCpu->id,
        ]);

        $elemento->refresh();
        $this->assertArrayNotHasKey('Procesador', $elemento->descripcion ?? []);
        $this->assertEquals('-', $elemento->getValorAtributo('Procesador'));
    }

    public function test_creacion_con_descripcion_json_propaga_a_tabla_normalizada(): void
    {
        $tipo = Tipo::firstOrCreate(['nombre' => 'PC'], ['slug' => 'pc', 'icono' => 'pc']);
        $attrGpu = Atributo::create([
            'nombre' => 'Placa de Video',
            'tipo_dato' => 'texto',
            'unidad_medida' => null,
            'descripcion' => 'GPU',
        ]);
        $tipo->atributos()->attach($attrGpu->id);

        // Crear elemento directamente con array descripcion
        $elemento = Elemento::create([
            'nro_lia' => 'LIA-NORM-01',
            'tipo_id' => $tipo->id,
            'marca' => 'Dell',
            'cantidad' => 1,
            'descripcion' => [
                'Placa de Video' => 'NVIDIA RTX 4070',
                'Color' => 'Negro', // Atributo no estructurado
            ],
        ]);

        // Debe haberse propagado automáticamente a elemento_atributo_valor
        $this->assertDatabaseHas('elemento_atributo_valor', [
            'elemento_id' => $elemento->id,
            'atributo_id' => $attrGpu->id,
            'valor' => 'NVIDIA RTX 4070',
        ]);

        // Atributo no estructurado se consulta por fallback
        $this->assertEquals('Negro', $elemento->getValorAtributo('Color'));
    }
}
