<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('auditorias')) {
            Schema::create('auditorias', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ubicacion_id')->constrained('ubicaciones')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('usuarios')->cascadeOnDelete();
                $table->dateTime('fecha');
                $table->string('estado', 30)->default('en_progreso'); // en_progreso, finalizada
                $table->unsignedInteger('total_auditados')->default(0);
                $table->unsignedInteger('total_ok')->default(0);
                $table->unsignedInteger('total_inconsistencias')->default(0);
                $table->text('observaciones')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('auditoria_detalles')) {
            Schema::create('auditoria_detalles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('auditoria_id')->constrained('auditorias')->cascadeOnDelete();
                $table->string('puesto', 50)->nullable();
                $table->foreignId('elemento_id')->nullable()->constrained('elementos')->nullOnDelete();
                $table->string('estado', 30)->default('ok'); // ok, inconsistencia
                $table->foreignId('ticket_id')->nullable()->constrained('movimientos')->nullOnDelete();
                $table->string('tipo_discrepancia', 50)->nullable(); // faltante, rotura, sobrante
                $table->text('observaciones')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_detalles');
        Schema::dropIfExists('auditorias');
    }
};
