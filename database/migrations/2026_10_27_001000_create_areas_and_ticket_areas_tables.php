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
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->string('color', 30)->default('gray');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('ticket_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('movimientos')->onDelete('cascade');
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['ticket_id', 'area_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_areas');
        Schema::dropIfExists('areas');
    }
};
