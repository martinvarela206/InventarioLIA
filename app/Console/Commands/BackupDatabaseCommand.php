<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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
                            {--path= : Directorio personalizado para almacenar el dump}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un respaldo (dump SQL) de la base de datos con rotación automática de archivos para contingencia y protocolo institucional.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando proceso de respaldo de base de datos...');

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

        // Intentar volcado nativo si es MySQL o MariaDB
        if (in_array($connectionName, ['mysql', 'mariadb'])) {
            $success = $this->dumpUsingCli($connectionName, $filepath);
        }

        // Si no se usó CLI o falló (ej. entorno de pruebas SQLite o binario CLI ausente), volcar vía DB / PDO
        if (!$success) {
            $success = $this->dumpUsingEloquentPdo($filepath);
        }

        if (!$success || !File::exists($filepath)) {
            $this->error('No se pudo generar el archivo de respaldo.');
            return Command::FAILURE;
        }

        $size = File::size($filepath);
        $sizeKb = number_format($size / 1024, 2);
        $this->info("✓ Respaldo generado con éxito: {$filename} ({$sizeKb} KB)");
        $this->line("  Ubicación: {$filepath}");

        // Rotación de dumps
        $this->rotarBackups($backupDir, $keep);

        return Command::SUCCESS;
    }

    /**
     * Ejecuta mariadb-dump / mysqldump mediante proceso de sistema.
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
                    $authParam = !empty($password) ? " -p" . escapeshellarg($password) : "";
                    $dumpCmd = "{$cmd} -h " . escapeshellarg($host) .
                               " -P " . escapeshellarg((string)$port) .
                               " -u " . escapeshellarg($username) .
                               $authParam .
                               " --single-transaction --quick --skip-lock-tables " .
                               escapeshellarg($database) .
                               " > " . escapeshellarg($filepath);

                    $res = Process::run($dumpCmd);
                    if ($res->successful() && File::exists($filepath) && File::size($filepath) > 0) {
                        return true;
                    }
                }
            } catch (\Throwable $e) {
                // Continuar al fallback
            }
        }

        return false;
    }

    /**
     * Volcado portable estructurado usando PDO / DB (funciona en MySQL, MariaDB, SQLite y testing).
     */
    protected function dumpUsingEloquentPdo(string $filepath): bool
    {
        $buffer = "-- ========================================================\n";
        $buffer .= "-- Respaldo Automatizado Sistema LIA (Inventario LIA)\n";
        $buffer .= "-- Generado: " . Carbon::now()->toIso8601String() . "\n";
        $buffer .= "-- Conexión: " . config('database.default') . "\n";
        $buffer .= "-- ========================================================\n\n";

        try {
            $tables = $this->getAllTables();
            $buffer .= "-- Tablas encontradas: " . count($tables) . "\n\n";

            foreach ($tables as $table) {
                $buffer .= "-- --------------------------------------------------------\n";
                $buffer .= "-- Estructura y datos de la tabla `{$table}`\n";
                $buffer .= "-- --------------------------------------------------------\n";

                // Registros
                $rows = DB::table($table)->get();
                $buffer .= "-- Registros: " . $rows->count() . "\n";

                if ($rows->count() > 0) {
                    foreach ($rows->chunk(50) as $chunk) {
                        foreach ($chunk as $row) {
                            $rowArray = (array)$row;
                            $columns = array_keys($rowArray);
                            $escapedColumns = array_map(fn($col) => "`{$col}`", $columns);
                            $escapedValues = array_map(function ($val) {
                                if (is_null($val)) return 'NULL';
                                if (is_numeric($val) && !is_string($val)) return (string)$val;
                                return "'" . addslashes((string)$val) . "'";
                            }, array_values($rowArray));

                            $buffer .= "INSERT INTO `{$table}` (" . implode(', ', $escapedColumns) . ") VALUES (" . implode(', ', $escapedValues) . ");\n";
                        }
                    }
                }
                $buffer .= "\n";
            }

            File::put($filepath, $buffer);
            return true;
        } catch (\Throwable $e) {
            $this->warn('Error durante el volcado portable: ' . $e->getMessage());
            return false;
        }
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
            $key = 'Tables_in_' . config('database.connections.' . config('database.default') . '.database');
            return array_map(function ($r) use ($key) {
                $vars = get_object_vars($r);
                return reset($vars);
            }, $results);
        }

        return [];
    }

    /**
     * Rotación de backups: conserva sólo los N más recientes y depura los antiguos.
     */
    protected function rotarBackups(string $backupDir, int $keep): void
    {
        $files = File::files($backupDir);
        $backups = [];

        foreach ($files as $file) {
            if (str_starts_with($file->getFilename(), 'backup_') && $file->getExtension() === 'sql') {
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
        $this->info("Archivos de respaldo detectados: {$totalBackups} (Límite de rotación: {$keep})");

        if ($totalBackups > $keep) {
            $aEliminar = array_slice($backups, $keep);
            foreach ($aEliminar as $item) {
                File::delete($item['path']);
                $this->line("  ↳ Rotación: archivo antiguo depurado: {$item['filename']}");
            }
            $this->info("✓ Rotación completada: " . count($aEliminar) . " archivo(s) depurado(s).");
        } else {
            $this->line("  ↳ No se requiere depuración; no se supera el límite de {$keep} respaldos.");
        }
    }
}
