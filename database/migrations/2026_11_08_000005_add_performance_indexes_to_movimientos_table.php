<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->index(['elemento_id', 'id'], 'idx_movimientos_elemento_id_id');
            $table->index(['elemento_id', 'fecha'], 'idx_movimientos_elemento_id_fecha');
            $table->index('user_id', 'idx_movimientos_user_id');
            $table->index('ubicacion_id', 'idx_movimientos_ubicacion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->dropIndex('idx_movimientos_elemento_id_id');
            $table->dropIndex('idx_movimientos_elemento_id_fecha');
            $table->dropIndex('idx_movimientos_user_id');
            $table->dropIndex('idx_movimientos_ubicacion_id');
        });
    }
};
