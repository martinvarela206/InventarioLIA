<?php

use App\Models\Rol;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Rol::firstOrCreate(['rol' => 'subtec']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Rol::where('rol', 'subtec')->delete();
    }
};
