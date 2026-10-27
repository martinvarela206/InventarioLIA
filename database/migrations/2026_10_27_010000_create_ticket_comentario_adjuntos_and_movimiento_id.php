<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_comentarios', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_comentarios', 'movimiento_id')) {
                $table->foreignId('movimiento_id')->nullable()->after('tipo')->constrained('movimientos')->nullOnDelete();
            }
        });

        if (!Schema::hasTable('ticket_comentario_adjuntos')) {
            Schema::create('ticket_comentario_adjuntos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_comentario_id')->constrained('ticket_comentarios')->cascadeOnDelete();
                $table->string('archivo_path');
                $table->string('nombre_original')->nullable();
                $table->string('mime_type')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_comentario_adjuntos');

        Schema::table('ticket_comentarios', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_comentarios', 'movimiento_id')) {
                $table->dropForeign(['movimiento_id']);
                $table->dropColumn('movimiento_id');
            }
        });
    }
};
