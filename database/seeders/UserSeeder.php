<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = \App\Models\Usuario::create(['nombre' => 'admin', 'password' => \Illuminate\Support\Facades\Hash::make('1234')]);
        $admin->roles()->attach([1, 2, 3, 4]);

        $coord = \App\Models\Usuario::create(['nombre' => 'coordinador', 'password' => \Illuminate\Support\Facades\Hash::make('1234')]);
        $coord->roles()->attach([2, 3, 4]);

        $tecnico = \App\Models\Usuario::create(['nombre' => 'tecnico', 'password' => \Illuminate\Support\Facades\Hash::make('1234')]);
        $tecnico->roles()->attach([3, 4]);

        $revisor = \App\Models\Usuario::create(['nombre' => 'revisor', 'password' => \Illuminate\Support\Facades\Hash::make('1234')]);
        $revisor->roles()->attach([4]);
    }
}
