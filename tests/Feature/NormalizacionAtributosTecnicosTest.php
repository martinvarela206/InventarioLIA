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

    public function test_sincronizacion_relacional_de_atributos_tecnicos(): void
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
        ]);

        // 1. Sincronizar atributos técnicos tipados
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

        // 2. getValorAtributo formatea con unidad de medida y descripcion_texto concatena
        $elemento->refresh();
        $this->assertEquals('128 GB', $elemento->getValorAtributo('Memoria RAM'));
        $this->assertEquals('AMD EPYC 7742', $elemento->getValorAtributo('Procesador'));
        $this->assertStringContainsString('AMD EPYC 7742', $elemento->descripcion_texto);

        // 3. Remover un atributo pasándolo vacío
        $elemento->sincronizarAtributosTecnicos([
            $attrCpu->id => '',
        ]);

        $this->assertDatabaseMissing('elemento_atributo_valor', [
            'elemento_id' => $elemento->id,
            'atributo_id' => $attrCpu->id,
        ]);

        $elemento->refresh();
        $this->assertEquals('-', $elemento->getValorAtributo('Procesador'));
    }

    public function test_soporte_campos_ad_hoc_en_modelo_relacional(): void
    {
        $tipo = Tipo::firstOrCreate(['nombre' => 'PC'], ['slug' => 'pc', 'icono' => 'pc']);
        $attrGpu = Atributo::create([
            'nombre' => 'Placa de Video',
            'tipo_dato' => 'texto',
            'unidad_medida' => null,
            'descripcion' => 'GPU',
        ]);
        $tipo->atributos()->attach($attrGpu->id);

        $elemento = Elemento::create([
            'nro_lia' => 'LIA-NORM-01',
            'tipo_id' => $tipo->id,
            'marca' => 'Dell',
            'cantidad' => 1,
        ]);

        // Sincronizar atributo estructurado + atributo ad-hoc dinámico
        $elemento->sincronizarAtributosTecnicos(
            [$attrGpu->id => 'NVIDIA RTX 4070'],
            ['Color' => 'Negro Mate']
        );

        // Debe haberse propagado a elemento_atributo_valor
        $this->assertDatabaseHas('elemento_atributo_valor', [
            'elemento_id' => $elemento->id,
            'atributo_id' => $attrGpu->id,
            'valor' => 'NVIDIA RTX 4070',
        ]);

        // El campo ad-hoc debe existir en catalogo atributos y en tabla de valores
        $this->assertDatabaseHas('atributos', [
            'nombre' => 'Color',
        ]);
        $attrColor = Atributo::where('nombre', 'Color')->first();
        $this->assertDatabaseHas('elemento_atributo_valor', [
            'elemento_id' => $elemento->id,
            'atributo_id' => $attrColor->id,
            'valor' => 'Negro Mate',
        ]);

        $this->assertEquals('Negro Mate', $elemento->getValorAtributo('Color'));
    }
}
