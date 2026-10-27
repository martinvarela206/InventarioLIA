<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Rol;

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
