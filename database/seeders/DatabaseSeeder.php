<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $seeders = [
            RoleSeeder::class,
            UserSeeder::class,
            TipoSeeder::class,
            AtributoSeeder::class,
            UbicacionSeeder::class,
            AppAdminSeeder::class,
            ReporteConfiguradoSeeder::class,
        ];

        // Seeders con datos simulados o masivos de prueba restringidos a entornos de desarrollo y testing
        if (app()->environment(['local', 'testing'])) {
            $seeders[] = InventarioOdsSeeder::class;
            $seeders[] = MovimientoFakeSeeder::class;
        }

        $this->call($seeders);
    }
}
