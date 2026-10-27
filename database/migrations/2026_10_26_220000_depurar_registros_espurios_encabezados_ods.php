<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\ElementoAtributoValor;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Identificar activos espurios generados por la lectura de filas de cabecera en ODS
        $espurios = Elemento::whereIn('nro_lia', ['Disco', 'RAM', 'CPU', 'Procesador', 'Modelo'])
            ->orWhere(function ($q) {
                $q->whereJsonContains('descripcion->Procesador', 'Procesador')
                  ->whereJsonContains('descripcion->RAM', 'RAM');
            })
            ->get();

        $elementoIds = $espurios->pluck('id')->toArray();
        if (empty($elementoIds)) {
            return;
        }

        // 1. Eliminar valores pivot de atributos asociados
        ElementoAtributoValor::whereIn('elemento_id', $elementoIds)->delete();

        // 2. Desacoplar auto-referencias internas de movimientos para evitar bloqueos por FK
        Movimiento::whereIn('elemento_id', $elementoIds)->update([
            'correccion_de_id' => null,
            'ticket_id' => null,
        ]);

        // 3. Eliminar movimientos asociados a los activos espurios
        Movimiento::whereIn('elemento_id', $elementoIds)->delete();

        // 4. Eliminar elementos espurios
        Elemento::whereIn('id', $elementoIds)->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se restauran registros espurios corruptos
    }
};
