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
            if (! Schema::hasColumn('movimientos', 'titulo')) {
                $table->string('titulo', 255)->nullable()->after('estado_ticket');
            }
        });

        Schema::table('ticket_comentarios', function (Blueprint $table) {
            if (! Schema::hasColumn('ticket_comentarios', 'es_solucion')) {
                $table->boolean('es_solucion')->default(false)->after('tipo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_comentarios', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_comentarios', 'es_solucion')) {
                $table->dropColumn('es_solucion');
            }
        });

        Schema::table('movimientos', function (Blueprint $table) {
            if (Schema::hasColumn('movimientos', 'titulo')) {
                $table->dropColumn('titulo');
            }
        });
    }
};
