<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->after('comentario');
        });

        // Poblar created_at en registros existentes utilizando la fecha histórica de movimiento
        DB::table('movimientos')->whereNull('created_at')->update([
            'created_at' => DB::raw('fecha'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->dropColumn('created_at');
        });
    }
};
