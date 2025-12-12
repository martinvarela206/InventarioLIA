<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Estado;

class EstadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estados = [
            ['nombre' => 'ingresado', 'descripcion' => 'Elemento ingresado al sistema'],
            ['nombre' => 'guardado', 'descripcion' => 'Elemento guardado en depósito'],
            ['nombre' => 'funcionando', 'descripcion' => 'Elemento en uso/funcionando'],
            ['nombre' => 'dado de baja', 'descripcion' => 'Elemento dado de baja'],
            ['nombre' => 'prestado', 'descripcion' => 'Elemento prestado'],
        ];

        foreach ($estados as $estado) {
            Estado::create($estado);
        }
    }
}
