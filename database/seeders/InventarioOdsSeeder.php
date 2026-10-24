<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class InventarioOdsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command?->info("Importando inventario oficial completo desde archivo ODS...");
        
        Artisan::call('inventario:import-ods', [
            '--fresh' => true,
        ], $this->command?->getOutput());
    }
}
