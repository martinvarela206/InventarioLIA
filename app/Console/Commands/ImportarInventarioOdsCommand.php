<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\Tipo;
use App\Models\Ubicacion;
use App\Models\Usuario;
use Carbon\Carbon;
use ZipArchive;
use SimpleXMLElement;

use App\Models\Atributo;
use App\Models\ElementoAtributoValor;

class ImportarInventarioOdsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventario:import-ods 
                            {--file= : Ruta al archivo ODS}
                            {--fresh : Limpia las tablas de elementos y movimientos antes de importar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa masivamente los ~1.150 activos del archivo oficial ODS del LIA con fidelidad absoluta a identificadores oficiales';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->option('file') ?: base_path('DocsSistemaLia/material/Inventario LIA.ods');

        if (!file_exists($filePath)) {
            $this->error("El archivo ODS no fue encontrado en: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Iniciando importación masiva desde: {$filePath}");

        if ($this->option('fresh')) {
            $this->warn("Limpiando tablas 'movimientos' y 'elementos'...");
            DB::statement('PRAGMA foreign_keys = OFF;');
            Movimiento::truncate();
            Elemento::truncate();
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            $this->error("No se pudo descomprimir el archivo ODS.");
            return Command::FAILURE;
        }

        $content = $zip->getFromName('content.xml');
        $zip->close();

        if (!$content) {
            $this->error("No se pudo leer content.xml del archivo ODS.");
            return Command::FAILURE;
        }

        $xml = simplexml_load_string($content);
        $xml->registerXPathNamespace('table', 'urn:oasis:names:tc:opendocument:xmlns:table:1.0');
        $xml->registerXPathNamespace('text', 'urn:oasis:names:tc:opendocument:xmlns:text:1.0');

        $tables = $xml->xpath('//table:table');

        // Pre-cargar catálogos
        $defaultUser = Usuario::first() ?? Usuario::create([
            'nombre' => 'Administrador Sistema',
            'email' => 'admin@lia.unsj.edu.ar',
            'password' => bcrypt('1234'),
            'activo' => true,
        ]);

        $summary = [];
        $totalImportados = 0;
        $totalConLia = 0;
        $totalConUnsj = 0;
        $totalSinAmbos = 0;
        $seenTuples = [];

        foreach ($tables as $table) {
            $sheetName = (string)$table->attributes('urn:oasis:names:tc:opendocument:xmlns:table:1.0')['name'];
            $rows = $table->xpath('table:table-row');

            if (count($rows) <= 1) {
                continue;
            }

            $sheetType = $this->resolverTipoPorHoja($sheetName);
            $tipoModel = Tipo::firstOrCreateNormalizado($sheetType);

            // Parsear cabeceras en R0 y R1
            $header0 = $this->extraerValoresFila($rows[0]);
            $header1 = isset($rows[1]) ? $this->extraerValoresFila($rows[1]) : [];

            $colMap = $this->mapearColumnas($header0, $header1, $sheetName);

            $sheetCount = 0;
            $startRow = $colMap['data_start_row'];

            for ($r = $startRow; $r < count($rows); $r++) {
                $rowVals = $this->extraerValoresFila($rows[$r]);

                if (empty(array_filter($rowVals))) {
                    continue;
                }

                $rawLia = $colMap['lia'] !== null && isset($rowVals[$colMap['lia']]) ? trim($rowVals[$colMap['lia']]) : null;
                $rawUnsj = $colMap['unsj'] !== null && isset($rowVals[$colMap['unsj']]) ? trim($rowVals[$colMap['unsj']]) : null;
                $rawMarca = $colMap['marca'] !== null && isset($rowVals[$colMap['marca']]) ? trim($rowVals[$colMap['marca']]) : null;
                $rawModelo = $colMap['modelo'] !== null && isset($rowVals[$colMap['modelo']]) ? trim($rowVals[$colMap['modelo']]) : null;
                $rawUbicacion = $colMap['ubicacion'] !== null && isset($rowVals[$colMap['ubicacion']]) ? trim($rowVals[$colMap['ubicacion']]) : 'Administracion';
                $rawFecha = $colMap['fecha'] !== null && isset($rowVals[$colMap['fecha']]) ? trim($rowVals[$colMap['fecha']]) : null;
                $rawCantidad = $colMap['cantidad'] !== null && isset($rowVals[$colMap['cantidad']]) ? (int)$rowVals[$colMap['cantidad']] : 1;
                $rawComentarios = $colMap['comentarios'] !== null && isset($rowVals[$colMap['comentarios']]) ? trim($rowVals[$colMap['comentarios']]) : null;

                // Filtro estricto anti-cabeceras intermedias: descartar filas que repiten nombres de columnas ODS
                $headerKeywords = ['disco', 'ram', 'cpu', 'procesador', 'modelo', 'nro lia', 'nro_lia', 'nro unsj', 'nro_unsj', 'marca', 'año', 'anio', 'cantidad', 'ubicacion'];
                if (
                    ($rawLia !== null && in_array(mb_strtolower($rawLia), $headerKeywords)) ||
                    ($rawMarca !== null && in_array(mb_strtolower($rawMarca), ['marca', 'fabricante'])) ||
                    ($rawModelo !== null && in_array(mb_strtolower($rawModelo), ['modelo', 'descripcion', 'disco', 'procesador']))
                ) {
                    continue;
                }

                // Subcaracterísticas estructuradas
                $specs = [];
                if ($colMap['procesador'] !== null && isset($rowVals[$colMap['procesador']]) && $rowVals[$colMap['procesador']] !== '') {
                    $specs['Procesador'] = trim($rowVals[$colMap['procesador']]);
                }
                if ($colMap['ram'] !== null && isset($rowVals[$colMap['ram']]) && $rowVals[$colMap['ram']] !== '') {
                    $specs['RAM'] = trim($rowVals[$colMap['ram']]);
                }
                if ($colMap['disco'] !== null && isset($rowVals[$colMap['disco']]) && $rowVals[$colMap['disco']] !== '') {
                    $specs['Disco'] = trim($rowVals[$colMap['disco']]);
                }
                $anio = null;
                if ($colMap['anio'] !== null && isset($rowVals[$colMap['anio']]) && $rowVals[$colMap['anio']] !== '') {
                    $valAnio = trim($rowVals[$colMap['anio']]);
                    if (preg_match('/\b(19\d{2}|20\d{2})\b/', $valAnio, $matches)) {
                        $anio = (int)$matches[1];
                    } else {
                        $specs['Año'] = $valAnio;
                    }
                }

                // Normalización de identificadores oficiales (CERO prefijos artificiales)
                $nroLia = $this->limpiarIdentificador($rawLia);
                $nroUnsj = $this->limpiarIdentificador($rawUnsj);

                if ($nroLia !== null) $totalConLia++;
                if ($nroUnsj !== null) $totalConUnsj++;
                if ($nroLia === null && $nroUnsj === null) $totalSinAmbos++;

                // Prevención de colisión en clave única compuesta (nro_lia, nro_unsj)
                if ($nroLia !== null && $nroUnsj !== null) {
                    $tupleKey = "{$nroLia}___{$nroUnsj}";
                    if (isset($seenTuples[$tupleKey])) {
                        // Si ya existe exactamente esa tupla, enriquecer observaciones
                        $rawComentarios = ($rawComentarios ? $rawComentarios . ' | ' : '') . "Duplicado de planilla: {$nroLia} / {$nroUnsj}";
                        $nroUnsj = null; // Mantener nro_lia pero disociar unsj para no violar índice único
                    } else {
                        $seenTuples[$tupleKey] = true;
                    }
                }

                // Detección de puesto si está especificado en ubicación o nro_lia
                $puesto = null;
                if (preg_match('/Puesto\s*(\d+)/i', (string)$rawUbicacion, $m)) {
                    $puesto = "Puesto " . str_pad($m[1], 2, '0', STR_PAD_LEFT);
                }

                // Resolver Ubicación canónica
                $ubicacionModel = Ubicacion::resolverUbicacionCanonica($rawUbicacion ?: 'Administracion');

                // Fecha del movimiento
                $fechaMovimiento = $this->parsearFecha($rawFecha);

                // Crear activo
                $elemento = Elemento::create([
                    'nro_lia' => $nroLia,
                    'nro_unsj' => $nroUnsj,
                    'tipo_id' => $tipoModel->id,
                    'marca' => ($rawMarca !== '' && $rawMarca !== '-') ? $rawMarca : null,
                    'modelo' => ($rawModelo !== '' && $rawModelo !== '-') ? $rawModelo : null,
                    'anio_compra' => $anio,
                    'puesto' => $puesto,
                    'observaciones' => ($rawComentarios !== '' && $rawComentarios !== '-') ? $rawComentarios : null,
                    'descripcion' => !empty($specs) ? $specs : null,
                    'cantidad' => $rawCantidad > 0 ? $rawCantidad : 1,
                    'fecha_adquisicion' => $fechaMovimiento->toDateString(),
                ]);

                // Almacenar características dinámicas en la tabla pivot elemento_atributo_valor
                foreach ($specs as $attrNombre => $attrValor) {
                    if (!empty($attrValor)) {
                        $attrModel = Atributo::firstOrCreateNormalizado($attrNombre);
                        ElementoAtributoValor::create([
                            'elemento_id' => $elemento->id,
                            'atributo_id' => $attrModel->id,
                            'valor' => (string)$attrValor,
                        ]);
                    }
                }

                // Registrar movimiento inicial histórico (baja patrimonial para basura tecnológica)
                $esBajaHistorica = (mb_strtolower($sheetName) === 'basura tecnologica');
                Movimiento::create([
                    'elemento_id' => $elemento->id,
                    'tipo_movimiento' => $esBajaHistorica ? 'baja' : 'interno',
                    'estado_ticket' => $esBajaHistorica ? 'baja' : null,
                    'ubicacion_id' => $ubicacionModel->id,
                    'puesto' => $puesto,
                    'user_id' => $defaultUser->id,
                    'fecha' => $fechaMovimiento,
                    'comentario' => $esBajaHistorica
                        ? "Baja patrimonial histórica / Basura tecnológica desde planilla ODS ({$sheetName})"
                        : "Carga inicial histórica desde planilla ODS ({$sheetName})",
                ]);

                $sheetCount++;
                $totalImportados++;
            }

            $summary[] = [
                'Hoja' => $sheetName,
                'Tipo Asignado' => $sheetType,
                'Activos Importados' => $sheetCount,
            ];
        }

        $this->newLine();
        $this->table(['Hoja ODS', 'Tipo Asignado', 'Total Importados'], $summary);

        $this->info("==================================================");
        $this->info("REPORTE FINAL DE INTEGRIDAD DE IMPORTACIÓN ODS");
        $this->info("==================================================");
        $this->info("Total Activos Importados: {$totalImportados}");
        $this->info("Con Nro LIA asignado:     {$totalConLia}");
        $this->info("Con Nro UNSJ asignado:    {$totalConUnsj}");
        $this->info("Sin identificadores (S/N): {$totalSinAmbos} (almacenados como NULL sin prefijos ficticios)");
        $this->info("==================================================");

        return Command::SUCCESS;
    }

    private function extraerValoresFila(SimpleXMLElement $row): array
    {
        $cells = $row->xpath('table:table-cell');
        $rowVals = [];

        foreach ($cells as $cell) {
            $repeatAttr = $cell->attributes('urn:oasis:names:tc:opendocument:xmlns:table:1.0')['number-columns-repeated'] ?? null;
            $repeat = $repeatAttr ? (int)$repeatAttr : 1;

            $p = $cell->xpath('text:p');
            $val = '';
            if ($p) {
                $texts = [];
                foreach ($p as $pelem) {
                    $texts[] = (string)$pelem;
                }
                $val = trim(implode(' ', $texts));
            }

            if ($repeat > 1 && $val === '') {
                // omitir stretches vacíos de columnas de libreoffice
                continue;
            }

            for ($i = 0; $i < min($repeat, 25); $i++) {
                $rowVals[] = $val;
            }
        }

        return $rowVals;
    }

    private function mapearColumnas(array $h0, array $h1, string $sheetName): array
    {
        $map = [
            'data_start_row' => 1,
            'fecha' => null,
            'cantidad' => null,
            'comentarios' => null,
            'procesador' => null,
            'ram' => null,
            'disco' => null,
            'anio' => null,
            'lia' => null,
            'unsj' => null,
            'marca' => null,
            'modelo' => null,
            'ubicacion' => null,
        ];

        // Caso especial: Basura Tecnologica tiene fila 0 como "PCs" y fila 1 como encabezados reales
        if (mb_strtolower($sheetName) === 'basura tecnologica') {
            $map['data_start_row'] = 2;
            foreach ($h1 as $idx => $col) {
                $colLower = mb_strtolower($col, 'UTF-8');
                if (str_contains($colLower, 'lia')) $map['lia'] = $idx;
                elseif (str_contains($colLower, 'inventario')) $map['unsj'] = $idx;
                elseif (str_contains($colLower, 'marca')) $map['marca'] = $idx;
                elseif (str_contains($colLower, 'cpu')) $map['procesador'] = $idx;
                elseif (str_contains($colLower, 'ram')) $map['ram'] = $idx;
                elseif (str_contains($colLower, 'disco')) $map['disco'] = $idx;
                elseif (str_contains($colLower, 'año') || str_contains($colLower, 'compra')) $map['anio'] = $idx;
                elseif (str_contains($colLower, 'observaci')) $map['comentarios'] = $idx;
            }
            return $map;
        }

        // Mapeo general de Fila 0
        foreach ($h0 as $idx => $col) {
            $colLower = mb_strtolower($col, 'UTF-8');
            if (str_contains($colLower, 'actualizaci')) $map['fecha'] = $idx;
            elseif (str_contains($colLower, 'lia') || str_contains($colLower, 'nombre equipo')) {
                if ($map['lia'] === null) $map['lia'] = $idx;
            } elseif (str_contains($colLower, 'exactas') || str_contains($colLower, 'inventario')) {
                if ($map['unsj'] === null) $map['unsj'] = $idx;
            } elseif (str_contains($colLower, 'marca')) $map['marca'] = $idx;
            elseif (str_contains($colLower, 'modelo') || str_contains($colLower, 'tipo')) $map['modelo'] = $idx;
            elseif (str_contains($colLower, 'ubicaci')) $map['ubicacion'] = $idx;
            elseif (str_contains($colLower, 'cantidad')) $map['cantidad'] = $idx;
            elseif (str_contains($colLower, 'comentario')) $map['comentarios'] = $idx;
            elseif (str_contains($colLower, 'capacidad')) $map['disco'] = $idx;
            elseif (str_contains($colLower, 'año')) $map['anio'] = $idx;
        }

        // Mapeo de Subcaracterísticas de Fila 1 (Procesador, RAM, Disco, Año)
        foreach ($h1 as $idx => $subCol) {
            $subLower = mb_strtolower($subCol, 'UTF-8');
            if (str_contains($subLower, 'procesador') || str_contains($subLower, 'cpu')) $map['procesador'] = $idx;
            elseif (str_contains($subLower, 'ram')) $map['ram'] = $idx;
            elseif (str_contains($subLower, 'disco')) $map['disco'] = $idx;
            elseif (str_contains($subLower, 'año') || str_contains($subLower, 'ano')) $map['anio'] = $idx;
        }

        return $map;
    }

    private function resolverTipoPorHoja(string $sheetName): string
    {
        $clean = mb_strtolower(trim($sheetName), 'UTF-8');

        $map = [
            'servidor' => 'servidor',
            'discos' => 'disco',
            'ap (punto de acceso)' => 'punto de acceso',
            'placa de red' => 'placa de red',
            'ups' => 'ups',
            'switch' => 'switch',
            'pacheras' => 'pachera',
            'notebook' => 'notebook',
            'tv' => 'monitor',
            'laboratorio' => 'computadora',
            'basura tecnologica' => 'computadora',
            'monitor' => 'monitor',
            'pendiente' => 'computadora',
            'mouse' => 'mouse',
            'impresora' => 'impresora',
            'teclado' => 'teclado',
            'parlante' => 'parlante',
            'proyector' => 'proyector',
            'fuente' => 'fuente',
            'herramienta' => 'herramienta',
            'cajón hardware-administración' => 'accesorio',
            'cajon hardware-administracion' => 'accesorio',
        ];

        return $map[$clean] ?? 'accesorio';
    }

    private function limpiarIdentificador(?string $val): ?string
    {
        if ($val === null) return null;
        $t = trim($val);
        $upper = strtoupper($t);
        if ($t === '' || $upper === 'S/N' || $upper === 'SN' || $t === '-' || $t === '--' || $upper === 'S') {
            return null;
        }
        return $t;
    }

    private function parsearFecha(?string $raw): Carbon
    {
        if (empty($raw)) {
            return Carbon::create(2024, 1, 1, 12, 0, 0);
        }

        // Formato D/M/YYYY o D/MM/YYYY
        if (preg_match('/^(\d{1,2})\/(\d{1,3})\/(\d{4})$/', trim($raw), $m)) {
            $day = (int)$m[1];
            $month = (int)$m[2];
            $year = (int)$m[3];
            if ($month > 12) {
                // Caso como 1/009/2025 -> mes 9
                $month = (int)ltrim($m[2], '0');
                if ($month === 0) $month = 9;
            }
            if (checkdate($month, $day, $year)) {
                return Carbon::create($year, $month, $day, 10, 0, 0);
            }
        }

        return Carbon::create(2024, 1, 1, 12, 0, 0);
    }
}
