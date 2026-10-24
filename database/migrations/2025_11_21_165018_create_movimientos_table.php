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
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('elemento_id')->constrained('elementos')->onDelete('cascade');
            $table->string('tipo_movimiento', 30)->default('interno'); // 'interno', 'externo_prestamo'
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones')->onDelete('set null');
            $table->string('puesto', 50)->nullable();
            $table->foreignId('user_id')->constrained('usuarios')->onDelete('cascade');
            $table->dateTime('fecha');
            $table->string('solicitante', 150)->nullable();
            $table->date('fecha_prestamo')->nullable();
            $table->date('fecha_devolucion')->nullable();
            $table->foreignId('correccion_de_id')->nullable()->constrained('movimientos')->onDelete('restrict');
            $table->text('comentario')->nullable();

            $table->index('tipo_movimiento');
            $table->index('fecha');
            $table->index('fecha_prestamo');
            $table->index('fecha_devolucion');
            $table->index('correccion_de_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
