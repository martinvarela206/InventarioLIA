<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Carbon\Carbon;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup-database 
                            {--keep=10 : Cantidad máxima de respaldos a conservar en la rotación}
                            {--path= : Directorio personalizado para almacenar el dump}
                            {--with-attachments : Incluir archivo ZIP de respaldo de adjuntos multimedia en disco}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un respaldo (dump SQL) de la base de datos y adjuntos con rotación automática de archivos para contingencia y protocolo institucional.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando proceso de respaldo de base de datos y contingencia...');

        $keep = (int)$this->option('keep');
        if ($keep < 1) {
            $keep = 10;
        }

        $backupDir = $this->option('path') ?: storage_path('app/backups');
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = Carbon::now()->format('Y-m-d_His');
        $connectionName = config('database.default');
        $databaseName = config("database.connections.{$connectionName}.database", 'inventario_lia');
        $cleanDbName = is_string($databaseName) ? pathinfo($databaseName, PATHINFO_FILENAME) : 'db';
        if (empty($cleanDbName) || $cleanDbName === ':memory:') {
            $cleanDbName = 'inventario_lia';
        }
        $filename = "backup_{$cleanDbName}_{$timestamp}.sql";
        $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        $success = false;

        // Intentar volcado nativo seguro si es MySQL o MariaDB
        if (in_array($connectionName, ['mysql', 'mariadb'])) {
            $success = $this->dumpUsingCli($connectionName, $filepath);
            // En producción, si el volcado nativo falla no se permite volcado silencioso corrupto
            if (!$success && app()->isProduction()) {
                $this->error('Error crítico: El volcado nativo de MariaDB/MySQL falló en producción. Operación abortada.');
                Log::critical("BACKUP_ABORTADO_PRODUCCION: mariadb-dump/mysqldump falló para {$connectionName}.");
                return Command::FAILURE;
            }
        }

        // Si no se usó CLI o falló en entorno local/testing (ej. SQLite o sin binario CLI instalado), volcar vía PDO seguro
        if (!$success) {
            $success = $this->dumpUsingEloquentPdo($filepath);
        }

        if (!$success || !File::exists($filepath)) {
            $this->error('No se pudo generar el archivo de respaldo.');
            return Command::FAILURE;
        }

        $size = File::size($filepath);
        $sizeKb = number_format($size / 1024, 2);
        $this->info("✓ Respaldo SQL generado con éxito: {$filename} ({$sizeKb} KB)");
        $this->line("  Ubicación: {$filepath}");

        // Respaldo de adjuntos multimedia si se solicita la opción
        if ($this->option('with-attachments')) {
            $adjuntosOk = $this->respaldarAdjuntos($backupDir, $timestamp, $keep);
            if (!$adjuntosOk) {
                return Command::FAILURE;
            }
        }

        // Rotación de dumps SQL
        $this->rotarArchivosPorPatron($backupDir, 'backup_', 'sql', $keep);

        return Command::SUCCESS;
    }

    /**
     * Ejecuta mariadb-dump / mysqldump mediante proceso de sistema aislando credenciales en MYSQL_PWD.
     */
    protected function dumpUsingCli(string $connectionName, string $filepath): bool
    {
        $config = config("database.connections.{$connectionName}");
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $database = $config['database'] ?? 'inventario_lia';
        $username = $config['username'] ?? 'root';
        $password = $config['password'] ?? '';

        $cliCommands = ['mariadb-dump', 'mysqldump'];
        foreach ($cliCommands as $cmd) {
            try {
                $check = Process::run("{$cmd} --version");
                if ($check->successful()) {
                    // Aislar credenciales mediante MYSQL_PWD para prevenir exposición en ps aux
                    $env = [];
                    if (!empty($password)) {
                        $env['MYSQL_PWD'] = (string)$password;
                    }

                    $dumpCmd = "{$cmd} -h " . escapeshellarg($host) .
                               " -P " . escapeshellarg((string)$port) .
                               " -u " . escapeshellarg($username) .
                               " --single-transaction --quick --skip-lock-tables " .
                               escapeshellarg($database) .
                               " > " . escapeshellarg($filepath);

                    $res = Process::withEnvironment($env)->run($dumpCmd);
                    if ($res->successful() && File::exists($filepath) && File::size($filepath) > 0) {
                        return true;
                    }

                    Log::error("BACKUP_CLI_ERROR: Ejecución de {$cmd} falló", [
                        'exit_code' => $res->exitCode(),
                        'error_output' => $res->errorOutput(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning("BACKUP_CLI_EXCEPTION: {$e->getMessage()}");
            }
        }

        return false;
    }

    /**
     * Volcado portable seguro usando PDO / DB con escape SQL estándar (DB::getPdo()->quote).
     */
    protected function dumpUsingEloquentPdo(string $filepath): bool
    {
        $driver = DB::getDriverName();
        $buffer = "-- ========================================================\n";
        $buffer .= "-- Respaldo Automatizado Sistema LIA (Inventario LIA)\n";
        $buffer .= "-- Generado: " . Carbon::now()->toIso8601String() . "\n";
        $buffer .= "-- Conexión: " . config('database.default') . "\n";
        $buffer .= "-- ========================================================\n\n";

        if ($driver === 'sqlite') {
            $buffer .= "PRAGMA foreign_keys = OFF;\n\n";
        } else {
            $buffer .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
        }

        try {
            $pdo = DB::getPdo();
            $tables = $this->getAllTables();
            $buffer .= "-- Tablas encontradas: " . count($tables) . "\n\n";

            foreach ($tables as $table) {
                $buffer .= "-- --------------------------------------------------------\n";
                $buffer .= "-- Estructura y datos de la tabla `{$table}`\n";
                $buffer .= "-- --------------------------------------------------------\n";
                $buffer .= "DROP TABLE IF EXISTS `{$table}`;\n";

                // Estructura (CREATE TABLE)
                $createSql = $this->getTableCreateSql($table);
                if ($createSql) {
                    $buffer .= "{$createSql};\n\n";
                }

                // Registros
                $rows = DB::table($table)->get();
                $buffer .= "-- Registros: " . $rows->count() . "\n";

                if ($rows->count() > 0) {
                    foreach ($rows->chunk(50) as $chunk) {
                        foreach ($chunk as $row) {
                            $rowArray = (array)$row;
                            $columns = array_keys($rowArray);
                            $escapedColumns = array_map(fn($col) => "`{$col}`", $columns);
                            $escapedValues = array_map(function ($val) use ($pdo) {
                                if (is_null($val)) return 'NULL';
                                if (is_int($val) || is_float($val)) return (string)$val;
                                return $pdo->quote((string)$val);
                            }, array_values($rowArray));

                            $buffer .= "INSERT INTO `{$table}` (" . implode(', ', $escapedColumns) . ") VALUES (" . implode(', ', $escapedValues) . ");\n";
                        }
                    }
                }
                $buffer .= "\n";
            }

            if ($driver === 'sqlite') {
                $buffer .= "PRAGMA foreign_keys = ON;\n";
            } else {
                $buffer .= "SET FOREIGN_KEY_CHECKS = 1;\n";
            }

            File::put($filepath, $buffer);
            return true;
        } catch (\Throwable $e) {
            $this->warn('Error durante el volcado portable: ' . $e->getMessage());
            Log::error("BACKUP_PDO_ERROR: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Respalda los adjuntos multimedia en un archivo ZIP con rotación.
     */
    protected function respaldarAdjuntos(string $backupDir, string $timestamp, int $keep): bool
    {
        if (!class_exists(\ZipArchive::class)) {
            $this->error('ERROR CRÍTICO: La extensión PHP ZipArchive no está disponible. No es posible generar el respaldo de adjuntos solicitado.');
            Log::error('BACKUP_ATTACHMENTS_ERROR: ZipArchive extension is not available.');
            return false;
        }

        $zipFilename = "backup_adjuntos_{$timestamp}.zip";
        $zipPath = $backupDir . DIRECTORY_SEPARATOR . $zipFilename;

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            $this->error("ERROR CRÍTICO: No se pudo crear o escribir el archivo ZIP de adjuntos en: {$zipPath}");
            Log::error("BACKUP_ATTACHMENTS_ERROR: Cannot open zip archive at {$zipPath}");
            return false;
        }

        $dirsToBackup = [
            'private' => storage_path('app/private'),
            'public' => storage_path('app/public'),
        ];

        $totalArchivos = 0;
        foreach ($dirsToBackup as $prefix => $dir) {
            if (File::isDirectory($dir)) {
                $files = File::allFiles($dir);
                foreach ($files as $file) {
                    $relative = $prefix . '/' . $file->getRelativePathname();
                    $zip->addFile($file->getRealPath(), $relative);
                    $totalArchivos++;
                }
            }
        }

        $zip->close();

        if ($totalArchivos > 0 && File::exists($zipPath)) {
            $sizeKb = number_format(File::size($zipPath) / 1024, 2);
            $this->info("✓ Adjuntos multimedia respaldados: {$zipFilename} ({$sizeKb} KB, {$totalArchivos} archivos)");
        } else {
            File::delete($zipPath);
            $this->line('  ↳ No se detectaron archivos multimedia para respaldar.');
        }

        // Rotación de zips de adjuntos
        $this->rotarArchivosPorPatron($backupDir, 'backup_adjuntos_', 'zip', $keep);

        return true;
    }

    /**
     * Obtiene la sentencia DDL de creación de una tabla.
     */
    protected function getTableCreateSql(string $table): ?string
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $row = DB::selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$table]);
            if ($row && !empty($row->sql)) {
                $sql = trim($row->sql);
                if (stripos($sql, 'CREATE TABLE IF NOT EXISTS') === false && stripos($sql, 'CREATE TABLE') === 0) {
                    $sql = 'CREATE TABLE IF NOT EXISTS ' . substr($sql, strlen('CREATE TABLE '));
                }
                return $sql;
            }
            return null;
        }

        if (in_array($driver, ['mysql', 'mariadb'])) {
            $row = DB::selectOne("SHOW CREATE TABLE `{$table}`");
            if ($row) {
                $vars = get_object_vars($row);
                return $vars['Create Table'] ?? null;
            }
        }

        return null;
    }

    /**
     * Obtiene el listado de tablas disponibles en la conexión activa.
     */
    protected function getAllTables(): array
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $results = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';");
            return array_map(fn($r) => $r->name, $results);
        }

        if (in_array($driver, ['mysql', 'mariadb'])) {
            $results = DB::select('SHOW TABLES');
            return array_map(function ($r) {
                $vars = get_object_vars($r);
                return reset($vars);
            }, $results);
        }

        return [];
    }

    /**
     * Rotación de archivos de respaldo por prefijo y extensión.
     */
    protected function rotarArchivosPorPatron(string $backupDir, string $prefix, string $extension, int $keep): void
    {
        $files = File::files($backupDir);
        $backups = [];

        foreach ($files as $file) {
            if (str_starts_with($file->getFilename(), $prefix) && $file->getExtension() === $extension) {
                $backups[] = [
                    'path' => $file->getPathname(),
                    'mtime' => $file->getMTime(),
                    'filename' => $file->getFilename(),
                    'size' => $file->getSize(),
                ];
            }
        }

        // Ordenar del más reciente al más antiguo
        usort($backups, fn($a, $b) => $b['mtime'] <=> $a['mtime']);

        $totalBackups = count($backups);
        if ($totalBackups > $keep) {
            $aEliminar = array_slice($backups, $keep);
            foreach ($aEliminar as $item) {
                File::delete($item['path']);
                $this->line("  ↳ Rotación: archivo antiguo depurado: {$item['filename']}");
            }
            $this->info("✓ Rotación completada ({$prefix}*.{$extension}): " . count($aEliminar) . " archivo(s) depurado(s).");
        }
    }
}
