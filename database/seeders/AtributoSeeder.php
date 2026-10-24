<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Atributo;
use App\Models\Tipo;

class AtributoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Catálogo de Atributos Técnicos
        $atributos = [
            'Procesador' => ['tipo_dato' => 'texto', 'unidad_medida' => null, 'descripcion' => 'Modelo de CPU o Microprocesador'],
            'RAM' => ['tipo_dato' => 'texto', 'unidad_medida' => 'GB', 'descripcion' => 'Memoria RAM instalada'],
            'Disco' => ['tipo_dato' => 'texto', 'unidad_medida' => null, 'descripcion' => 'Almacenamiento (HDD, SSD, NVMe)'],
            'Capacidad' => ['tipo_dato' => 'texto', 'unidad_medida' => 'GB', 'descripcion' => 'Capacidad de almacenamiento'],
            'Año de Compra' => ['tipo_dato' => 'numero', 'unidad_medida' => null, 'descripcion' => 'Año de adquisición o compra'],
            'Puertos' => ['tipo_dato' => 'numero', 'unidad_medida' => null, 'descripcion' => 'Cantidad de puertos de red'],
            'Velocidad' => ['tipo_dato' => 'texto', 'unidad_medida' => 'Mbps/Gbps', 'descripcion' => 'Velocidad de transferencia'],
            'Pulgadas' => ['tipo_dato' => 'numero', 'unidad_medida' => 'Pulgadas', 'descripcion' => 'Tamaño de pantalla'],
            'Resolución' => ['tipo_dato' => 'texto', 'unidad_medida' => null, 'descripcion' => 'Resolución nativa de video'],
            'Potencia' => ['tipo_dato' => 'numero', 'unidad_medida' => 'Watts', 'descripcion' => 'Potencia eléctrica'],
        ];

        $models = [];
        foreach ($atributos as $nombre => $datos) {
            $models[$nombre] = Atributo::firstOrCreate(
                ['nombre' => $nombre],
                $datos
            );
        }

        // 2. Mapeo de Atributos por Tipo de Hardware (Pivot tipo_atributo)
        $asignaciones = [
            'computadora' => [
                'Procesador' => ['orden' => 1, 'requerido' => false],
                'RAM' => ['orden' => 2, 'requerido' => false],
                'Disco' => ['orden' => 3, 'requerido' => false],
                'Año de Compra' => ['orden' => 4, 'requerido' => false],
            ],
            'servidor' => [
                'Procesador' => ['orden' => 1, 'requerido' => false],
                'RAM' => ['orden' => 2, 'requerido' => false],
                'Disco' => ['orden' => 3, 'requerido' => false],
                'Año de Compra' => ['orden' => 4, 'requerido' => false],
            ],
            'notebook' => [
                'Procesador' => ['orden' => 1, 'requerido' => false],
                'RAM' => ['orden' => 2, 'requerido' => false],
                'Disco' => ['orden' => 3, 'requerido' => false],
                'Pulgadas' => ['orden' => 4, 'requerido' => false],
                'Año de Compra' => ['orden' => 5, 'requerido' => false],
            ],
            'basura tecnologica' => [
                'Procesador' => ['orden' => 1, 'requerido' => false],
                'RAM' => ['orden' => 2, 'requerido' => false],
                'Disco' => ['orden' => 3, 'requerido' => false],
                'Año de Compra' => ['orden' => 4, 'requerido' => false],
            ],
            'disco' => [
                'Capacidad' => ['orden' => 1, 'requerido' => false],
                'Año de Compra' => ['orden' => 2, 'requerido' => false],
            ],
            'switch' => [
                'Puertos' => ['orden' => 1, 'requerido' => false],
                'Velocidad' => ['orden' => 2, 'requerido' => false],
            ],
            'punto de acceso' => [
                'Velocidad' => ['orden' => 1, 'requerido' => false],
            ],
            'monitor' => [
                'Pulgadas' => ['orden' => 1, 'requerido' => false],
                'Resolución' => ['orden' => 2, 'requerido' => false],
            ],
            'fuente' => [
                'Potencia' => ['orden' => 1, 'requerido' => false],
            ],
            'ups' => [
                'Potencia' => ['orden' => 1, 'requerido' => false],
            ],
        ];

        foreach ($asignaciones as $tipoNombre => $attrs) {
            $tipo = Tipo::where('nombre', $tipoNombre)->first();
            if (!$tipo) continue;

            $syncData = [];
            foreach ($attrs as $attrNombre => $cfg) {
                if (isset($models[$attrNombre])) {
                    $syncData[$models[$attrNombre]->id] = [
                        'orden' => $cfg['orden'],
                        'es_requerido' => $cfg['requerido'],
                    ];
                }
            }
            $tipo->atributos()->syncWithoutDetaching($syncData);
        }
    }
}
