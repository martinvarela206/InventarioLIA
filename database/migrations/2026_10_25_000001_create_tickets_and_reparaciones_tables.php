<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Tipo;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Usuario;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ampliar tabla movimientos para soportar estado de tickets y enlace a ticket origen
        Schema::table('movimientos', function (Blueprint $table) {
            $table->string('estado_ticket', 30)->nullable()->index()->after('tipo_movimiento');
            $table->foreignId('ticket_id')->nullable()->constrained('movimientos')->onDelete('restrict')->after('correccion_de_id');
        });

        // 2. Tabla para fotos y evidencias adjuntas en tickets y movimientos
        Schema::create('movimiento_adjuntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movimiento_id')->constrained('movimientos')->onDelete('cascade');
            $table->string('archivo_path');
            $table->string('nombre_original')->nullable();
            $table->string('mime_type')->nullable();
            $table->timestamps();

            $table->index('movimiento_id');
        });

        // 3. Reestructuración de Basura Tecnológica como Estado Operativo:
        // Recategorizar los activos que tenían tipo 'basura tecnologica' a su tipo real físico ('computadora')
        // y generarles su movimiento histórico de baja patrimonial.
        $tipoBasura = Tipo::where('nombre', 'basura tecnologica')->first();
        $tipoComputadora = Tipo::where('nombre', 'computadora')->first();

        if ($tipoBasura && $tipoComputadora) {
            $elementosBasura = Elemento::where('tipo_id', $tipoBasura->id)->get();
            $defaultUser = Usuario::first();

            foreach ($elementosBasura as $el) {
                // Reasignar tipo físico real
                $el->tipo_id = $tipoComputadora->id;
                $el->saveQuietly();

                // Verificar si ya tiene movimiento de baja
                $tieneBaja = Movimiento::where('elemento_id', $el->id)
                    ->where('tipo_movimiento', 'baja')
                    ->exists();

                if (!$tieneBaja) {
                    $ultimo = Movimiento::where('elemento_id', $el->id)->orderBy('id', 'desc')->first();
                    Movimiento::create([
                        'elemento_id' => $el->id,
                        'tipo_movimiento' => 'baja',
                        'estado_ticket' => 'baja',
                        'ubicacion_id' => $ultimo?->ubicacion_id,
                        'puesto' => $ultimo?->puesto,
                        'user_id' => $ultimo?->user_id ?? $defaultUser?->id ?? 1,
                        'fecha' => $ultimo?->fecha ?? now(),
                        'comentario' => 'Baja patrimonial / Basura tecnológica histórica desacoplada de categoría',
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_adjuntos');

        Schema::table('movimientos', function (Blueprint $table) {
            $table->dropForeign(['ticket_id']);
            $table->dropColumn(['estado_ticket', 'ticket_id']);
        });
    }
};
