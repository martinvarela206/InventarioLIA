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
        Schema::create('ticket_comentarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('movimientos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('usuarios')->onDelete('cascade');
            $table->text('mensaje');
            $table->string('tipo', 30)->default('comentario'); // 'comentario', 'solucion_propuesta'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_comentarios');
    }
};
