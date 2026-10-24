<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tipo;

class TipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'servidor', 'descripcion' => 'Servidor de cómputo y almacenamiento'],
            ['nombre' => 'disco', 'descripcion' => 'Disco rígido HDD, SSD o NVMe'],
            ['nombre' => 'punto de acceso', 'descripcion' => 'Punto de acceso inalámbrico (AP / Access Point)'],
            ['nombre' => 'placa de red', 'descripcion' => 'Placa o adaptador de red Ethernet / WiFi'],
            ['nombre' => 'ups', 'descripcion' => 'Unidad de alimentación ininterrumpida (UPS / Estabilizador)'],
            ['nombre' => 'switch', 'descripcion' => 'Switch de red'],
            ['nombre' => 'pachera', 'descripcion' => 'Panel de parcheo (Patchera / Patch panel)'],
            ['nombre' => 'notebook', 'descripcion' => 'Computadora portátil (Notebook / Laptop)'],
            ['nombre' => 'computadora', 'descripcion' => 'Computadora de escritorio (PC / Gabinete)'],
            ['nombre' => 'monitor', 'descripcion' => 'Monitor o pantalla'],
            ['nombre' => 'mouse', 'descripcion' => 'Ratón o mouse óptico/inalámbrico'],
            ['nombre' => 'impresora', 'descripcion' => 'Impresora láser, chorro de tinta o multifunción'],
            ['nombre' => 'teclado', 'descripcion' => 'Teclado de computadora'],
            ['nombre' => 'parlante', 'descripcion' => 'Parlantes y sistemas de audio'],
            ['nombre' => 'proyector', 'descripcion' => 'Proyector audiovisual / Cañón'],
            ['nombre' => 'fuente', 'descripcion' => 'Fuente de alimentación eléctrica de PC'],
            ['nombre' => 'herramienta', 'descripcion' => 'Herramientas de taller, crimpeadoras y testers'],
            ['nombre' => 'accesorio', 'descripcion' => 'Cables, adaptadores, conectores y misceláneos'],
            ['nombre' => 'basura tecnologica', 'descripcion' => 'Material obsoleto para reciclaje o descarte institucional'],
        ];

        foreach ($tipos as $tipo) {
            Tipo::firstOrCreate(['nombre' => $tipo['nombre']], $tipo);
        }
    }
}
