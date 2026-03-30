<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\Movimiento;
use App\Models\Elemento;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Estado;
use Faker\Factory as Faker;

class ElementoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to allow truncation
        Schema::disableForeignKeyConstraints();
        Movimiento::truncate();
        Elemento::truncate();
        Schema::enableForeignKeyConstraints();
        $faker = Faker::create();

        $types = [
            'cpu' => [
                'brands' => ['Dell', 'HP', 'Lenovo', 'Asus'],
                'models' => ['OptiPlex 3080', 'ThinkCentre M720', 'ProDesk 600', 'VivoPC'],
                'specs' => [
                    'RAM' => ['8GB', '16GB', '32GB'],
                    'Disk' => ['256GB SSD', '512GB SSD', '1TB HDD'],
                    'Processor' => ['Intel i5', 'Intel i7', 'AMD Ryzen 5']
                ]
            ],
            'switch' => [
                'brands' => ['Cisco', 'TP-Link', 'Ubiquiti'],
                'models' => ['Catalyst 2960', 'TL-SG108', 'UniFi Switch'],
                'specs' => [
                    'Speed' => ['10/100Mbps', '1Gbps'],
                    'Ports' => ['8 puertos', '24 puertos', '48 puertos']
                ]
            ],
            'router' => [
                'brands' => ['Cisco', 'Mikrotik', 'TP-Link'],
                'models' => ['ISR 4000', 'hEX S', 'Archer C7'],
                'specs' => [
                    'Speed' => ['10/100Mbps', '1Gbps'],
                    'Ports' => ['4 puertos', '5 puertos']
                ]
            ],
            'monitor' => [
                'brands' => ['Samsung', 'LG', 'Dell', 'ViewSonic'],
                'models' => ['S24F350', '24MK430', 'P2419H', 'VA2459'],
                'specs' => [
                    'Conn' => ['VGA', 'HDMI', 'DisplayPort', 'VGA/HDMI']
                ]
            ],
            'teclado' => [
                'brands' => ['Logitech', 'Genius', 'Microsoft'],
                'models' => ['K120', 'KB-110', 'Wired 600'],
                'specs' => []
            ],
            'mouse' => [
                'brands' => ['Logitech', 'Genius', 'Microsoft'],
                'models' => ['M90', 'DX-110', 'Basic Optical'],
                'specs' => []
            ],
            'proyector' => [
                'brands' => ['Epson', 'BenQ', 'Sony'],
                'models' => ['PowerLite X41', 'TH685', 'VPL-DX221'],
                'specs' => [
                    'Res' => ['HDMI', 'VGA', '4K']
                ]
            ],
            'disco' => [
                'brands' => ['Western Digital', 'Seagate', 'Kingston'],
                'models' => ['Blue', 'Barracuda', 'A400'],
                'specs' => [
                    'Cap' => ['1TB', '2TB', '500GB', '240GB SSD']
                ]
            ],
            'memoria' => [
                'brands' => ['Kingston', 'Corsair', 'ADATA'],
                'models' => ['ValueRAM', 'Vengeance', 'Premier'],
                'specs' => [
                    'Cap' => ['4GB', '8GB', '16GB']
                ]
            ]
        ];

        // Get tipo IDs from database
        $tipoIds = Tipo::pluck('id', 'nombre')->toArray();

        $elementos = [];
        $counter = 1;

        foreach ($types as $type => $data) {
            // Generar al menos 3 de cada tipo para tener variedad
            for ($i = 0; $i < 4; $i++) {
                $brand = $data['brands'][array_rand($data['brands'])];
                $model = $data['models'][array_rand($data['models'])];
                
                $descParts = ["$brand $model"];
                
                if ($type === 'cpu') {
                    $ram = $data['specs']['RAM'][array_rand($data['specs']['RAM'])];
                    $disk = $data['specs']['Disk'][array_rand($data['specs']['Disk'])];
                    $proc = $data['specs']['Processor'][array_rand($data['specs']['Processor'])];
                    $descParts[] = "RAM $ram";
                    $descParts[] = "Disco $disk";
                    $descParts[] = "$proc";
                } elseif ($type === 'switch' || $type === 'router') {
                    $speed = $data['specs']['Speed'][array_rand($data['specs']['Speed'])];
                    $ports = $data['specs']['Ports'][array_rand($data['specs']['Ports'])];
                    $descParts[] = "$speed";
                    $descParts[] = "$ports";
                } elseif ($type === 'monitor') {
                    $conn = $data['specs']['Conn'][array_rand($data['specs']['Conn'])];
                    $descParts[] = "$conn";
                } elseif ($type === 'proyector') {
                    $res = $data['specs']['Res'][array_rand($data['specs']['Res'])];
                    $descParts[] = "$res";
                } elseif ($type === 'disco' || $type === 'memoria') {
                    $cap = $data['specs']['Cap'][array_rand($data['specs']['Cap'])];
                    $descParts[] = "$cap";
                }

                $nro_lia = 'LIA' . str_pad($counter, 4, '0', STR_PAD_LEFT);
                $nro_unsj = (rand(0, 1) === 1) ? 'UNSJ' . str_pad($counter, 4, '0', STR_PAD_LEFT) : null;

                // Date logic
                $isExpired = $faker->boolean();
                if ($isExpired) {
                    // Generate dates up to 6 years ago to test obsolete CPUs
                    $fechaAdquisicion = $faker->dateTimeBetween('-6 years', '-13 months');
                } else {
                    $fechaAdquisicion = $faker->dateTimeBetween('-3 months', 'now');
                }
                $mesesGarantia = $faker->randomElement([3, 6, 12]);
                $fechaVencimiento = (clone $fechaAdquisicion)->modify("+{$mesesGarantia} months");

                $elementos[] = [
                    'nro_lia' => $nro_lia,
                    'nro_unsj' => $nro_unsj,
                    'tipo_id' => $tipoIds[$type],
                    'descripcion' => json_encode($this->buildDescripcionArray($type, $brand, $model, $data, $descParts)),
                    'cantidad' => rand(1, 10),
                    'fecha_adquisicion' => $fechaAdquisicion,
                    'fecha_vencimiento_garantia' => $fechaVencimiento,
                ];
                $counter++;
            }
        }

        // Insertar en lotes
        foreach (array_chunk($elementos, 50) as $chunk) {
            Elemento::insert($chunk);
        }

        // Usuarios que pueden crear elementos y movimientos (Admin, Coordinador, Tecnico)
        $userIds = [1, 2, 3];
        

        // Get ubicacion IDs and estado IDs from database
        $ubicacionIds = Ubicacion::pluck('id', 'nombre')->toArray();
        $ubicacionNames = array_keys($ubicacionIds);
        $estadoIds = Estado::pluck('id', 'nombre')->toArray();

        // Generar movimientos iniciales y adicionales
        $movimientos = [];
        foreach ($elementos as $elemento) {
            // Movimiento inicial (ingresado en LIA)
            $userInicial = $userIds[array_rand($userIds)];
            $fechaInicial = $elemento['fecha_adquisicion'];
            
            $movimientos[] = [
                'nro_lia' => $elemento['nro_lia'],
                'user_id' => $userInicial,
                'estado_id' => $estadoIds['ingresado'],
                'ubicacion_id' => $ubicacionIds['LIA'],
                'fecha' => $fechaInicial,
                'comentario' => 'Ingreso inicial',
            ];

            // Generate 1-3 additional random movements
            $numMovimientos = rand(1, 3);
            for ($j = 0; $j < $numMovimientos; $j++) {
                // Fecha aleatoria después del ingreso inicial
                $diasDespues = rand(1, 365);
                $fechaMovimiento = (clone $fechaInicial)->modify("+{$diasDespues} days");
                
                // Si la fecha es futura, no la agregamos
                if ($fechaMovimiento > now()) {
                    continue;
                }
                
                $estadoNombre = array_rand($estadoIds);
                $ubicacionId = null;
                $comentario = null;
                
                // Lógica específica por estado
                if ($estadoNombre === 'prestado') {
                    $ubicacionId = $ubicacionIds['Prestado'];
                    $destinatarios = [
                        'profesor Juan Pérez',
                        'profesora María González',
                        'oficina de Administración',
                        'departamento de Sistemas',
                        'profesor Carlos Rodríguez',
                        'oficina de Rectorado',
                        'departamento de Investigación',
                    ];
                    $comentario = 'Prestado a ' . $destinatarios[array_rand($destinatarios)];
                } elseif ($estadoNombre === 'dado de baja') {
                    $ubicacionId = $ubicacionIds['Dado de baja'];
                    $comentario = 'Dado de baja por resolución N° ' . rand(100, 999) . '/' . date('Y');
                } else {
                    // Para otros estados, usar ubicación aleatoria
                    $ubicacionId = $ubicacionIds[$ubicacionNames[array_rand($ubicacionNames)]];
                    $comentario = $faker->optional(0.7)->sentence(6);
                }
                
                $movimientos[] = [
                    'nro_lia' => $elemento['nro_lia'],
                    'user_id' => $userIds[array_rand($userIds)],
                    'estado_id' => $estadoIds[$estadoNombre],
                    'ubicacion_id' => $ubicacionId,
                    'fecha' => $fechaMovimiento,
                    'comentario' => $comentario,
                ];
            }
        }

        foreach (array_chunk($movimientos, 50) as $chunk) {
            Movimiento::insert($chunk);
        }
    }

    private function buildDescripcionArray($type, $brand, $model, $data, $descParts)
    {
        $descripcion = [];
        
        // Siempre incluir marca y modelo
        $descripcion['Marca'] = $brand;
        $descripcion['Modelo'] = $model;
        
        if ($type === 'cpu') {
            $ram = $data['specs']['RAM'][array_rand($data['specs']['RAM'])];
            $disk = $data['specs']['Disk'][array_rand($data['specs']['Disk'])];
            $proc = $data['specs']['Processor'][array_rand($data['specs']['Processor'])];
            $descripcion['RAM'] = $ram;
            $descripcion['Disco'] = $disk;
            $descripcion['Procesador'] = $proc;
        } elseif ($type === 'switch' || $type === 'router') {
            $speed = $data['specs']['Speed'][array_rand($data['specs']['Speed'])];
            $ports = $data['specs']['Ports'][array_rand($data['specs']['Ports'])];
            $descripcion['Velocidad'] = $speed;
            $descripcion['Puertos'] = $ports;
        } elseif ($type === 'monitor') {
            $conn = $data['specs']['Conn'][array_rand($data['specs']['Conn'])];
            $descripcion['Conexión'] = $conn;
        } elseif ($type === 'proyector') {
            $res = $data['specs']['Res'][array_rand($data['specs']['Res'])];
            $descripcion['Resolución'] = $res;
        } elseif ($type === 'disco' || $type === 'memoria') {
            $cap = $data['specs']['Cap'][array_rand($data['specs']['Cap'])];
            $descripcion['Capacidad'] = $cap;
        }
        
        return $descripcion;
    }
}
