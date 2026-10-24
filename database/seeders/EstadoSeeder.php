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
            ['nombre' => 'funcionando', 'descripcion' => 'Elemento operativo en uso normal', 'disponible' => true],
            ['nombre' => 'almacenado', 'descripcion' => 'Elemento guardado en depósito o armario', 'disponible' => true],
            ['nombre' => 'nuevo', 'descripcion' => 'Elemento recién ingresado sin uso previo', 'disponible' => true],
            ['nombre' => 'en reparacion', 'descripcion' => 'Elemento en servicio técnico o intervención', 'disponible' => false],
            ['nombre' => 'verificar funcionamiento', 'descripcion' => 'Requiere testeo de hardware antes de asignar', 'disponible' => false],
            ['nombre' => 'prestado', 'descripcion' => 'Elemento en préstamo externo a docente/cátedra', 'disponible' => false],
            ['nombre' => 'donado', 'descripcion' => 'Elemento donado a otra institución', 'disponible' => false],
            ['nombre' => 'transferido', 'descripcion' => 'Transferido a otra dependencia de la UNSJ', 'disponible' => false],
            ['nombre' => 'dado de baja', 'descripcion' => 'Baja patrimonial formal o descarte', 'disponible' => false],
        ];

        foreach ($estados as $estado) {
            Estado::firstOrCreate(['nombre' => $estado['nombre']], $estado);
        }
    }
}
