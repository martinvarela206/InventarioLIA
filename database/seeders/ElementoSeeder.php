<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\Movimiento;
use App\Models\Elemento;
use App\Models\Tipo;
use Carbon\Carbon;

class ElementoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Movimiento::truncate();
        Elemento::truncate();
        Schema::enableForeignKeyConstraints();

        $computadoraTipo = Tipo::where('nombre', 'computadora')->first() ?? Tipo::first();
        $monitorTipo = Tipo::where('nombre', 'monitor')->first() ?? Tipo::first();
        $switchTipo = Tipo::where('nombre', 'switch')->first() ?? Tipo::first();
        $notebookTipo = Tipo::where('nombre', 'notebook')->first() ?? Tipo::first();

        $elementosSample = [
            [
                'nro_lia' => 'LIA-FS-01',
                'nro_unsj' => '02-05950',
                'tipo_id' => $computadoraTipo->id,
                'marca' => 'Dell',
                'modelo' => 'OptiPlex 3080',
                'nro_serie' => 'SN-DELL-3080-01',
                'anio_compra' => 2022,
                'puesto' => 'Puesto 01',
                'observaciones' => 'PC de escritorio docente en Federico Sánchez',
                'descripcion' => ['CPU' => 'Intel Core i5-10500', 'RAM' => '16GB DDR4', 'Disco' => '512GB SSD NVMe'],
                'cantidad' => 1,
                'fecha_adquisicion' => Carbon::now()->subYears(2),
                'fecha_vencimiento_garantia' => Carbon::now()->addYear(),
            ],
            [
                'nro_lia' => 'LIA-FS-02',
                'nro_unsj' => '02-05951',
                'tipo_id' => $computadoraTipo->id,
                'marca' => 'Dell',
                'modelo' => 'OptiPlex 3080',
                'nro_serie' => 'SN-DELL-3080-02',
                'anio_compra' => 2022,
                'puesto' => 'Puesto 02',
                'observaciones' => 'PC estudiante',
                'descripcion' => ['CPU' => 'Intel Core i5-10500', 'RAM' => '16GB DDR4', 'Disco' => '512GB SSD NVMe'],
                'cantidad' => 1,
                'fecha_adquisicion' => Carbon::now()->subYears(2),
                'fecha_vencimiento_garantia' => Carbon::now()->addYear(),
            ],
            [
                'nro_lia' => 'LIA-MON-01',
                'nro_unsj' => '02-06101',
                'tipo_id' => $monitorTipo->id,
                'marca' => 'Samsung',
                'modelo' => 'S24F350',
                'nro_serie' => 'SAM-MON-8910',
                'anio_compra' => 2021,
                'puesto' => 'Puesto 01',
                'observaciones' => 'Monitor 24 pulgadas IPS',
                'descripcion' => ['Resolución' => '1920x1080', 'Conector' => 'HDMI/VGA'],
                'cantidad' => 1,
                'fecha_adquisicion' => Carbon::now()->subYears(3),
                'fecha_vencimiento_garantia' => Carbon::now()->subMonths(6),
            ],
            [
                'nro_lia' => 'LIA-SW-01',
                'nro_unsj' => '02-07200',
                'tipo_id' => $switchTipo->id,
                'marca' => 'Cisco',
                'modelo' => 'Catalyst 2960-X',
                'nro_serie' => 'FCW2048G01Z',
                'anio_compra' => 2019,
                'puesto' => null,
                'observaciones' => 'Switch troncal de 24 puertos Gigabit en Rack Redes',
                'descripcion' => ['Puertos' => '24 GbE + 2 SFP+', 'PoE' => 'No'],
                'cantidad' => 1,
                'fecha_adquisicion' => Carbon::now()->subYears(5),
                'fecha_vencimiento_garantia' => Carbon::now()->subYears(2),
            ],
            [
                'nro_lia' => 'LIA-NB-01',
                'nro_unsj' => null, // Sin UNSJ asignado aún
                'tipo_id' => $notebookTipo->id,
                'marca' => 'Lenovo',
                'modelo' => 'ThinkPad E14',
                'nro_serie' => 'PF24K9LA',
                'anio_compra' => 2023,
                'puesto' => null,
                'observaciones' => 'Notebook institucional para préstamo a investigadores',
                'descripcion' => ['CPU' => 'AMD Ryzen 7 5700U', 'RAM' => '16GB', 'Disco' => '512GB SSD'],
                'cantidad' => 1,
                'fecha_adquisicion' => Carbon::now()->subYear(),
                'fecha_vencimiento_garantia' => Carbon::now()->addMonths(12),
            ],
        ];

        foreach ($elementosSample as $data) {
            Elemento::create($data);
        }
    }
}
