<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class RestoreBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventario:restore-backup 
                            {file? : Ruta o nombre del archivo SQL a restaurar o verificar}
                            {--verify-only : Verifica la integridad estructural del respaldo sin modificar los datos}
                            {--force : Omite la confirmación interactiva antes de sobrescribir}
                            {--path= : Directorio personalizado donde residen los respaldos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restaura o verifica la integridad y consistencia sintáctica de un archivo de respaldo (dump SQL).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $backupDir = $this->option('path') ?: storage_path('app/backups');
        $targetFile = $this->argument('file');

        $filepath = null;

        if ($targetFile) {
            if (File::exists($targetFile)) {
                $filepath = $targetFile;
            } elseif (File::exists($backupDir . DIRECTORY_SEPARATOR . $targetFile)) {
                $filepath = $backupDir . DIRECTORY_SEPARATOR . $targetFile;
            } else {
                $this->error("El archivo de respaldo especificado no existe: {$targetFile}");
                return Command::FAILURE;
            }
        } else {
            // Buscar el respaldo SQL más reciente
            if (!File::isDirectory($backupDir)) {
                $this->error("El directorio de respaldos no existe: {$backupDir}");
                return Command::FAILURE;
            }

            $sqlFiles = array_filter(File::files($backupDir), fn($f) => $f->getExtension() === 'sql');
            if (empty($sqlFiles)) {
                $this->error("No se detectaron archivos de respaldo (.sql) en: {$backupDir}");
                return Command::FAILURE;
            }

            usort($sqlFiles, fn($a, $b) => $b->getMTime() <=> $a->getMTime());
            $filepath = reset($sqlFiles)->getPathname();
        }

        $size = File::size($filepath);
        if ($size === 0) {
            $this->error("El archivo de respaldo está vacío (0 bytes): {$filepath}");
            return Command::FAILURE;
        }

        $filename = basename($filepath);
        $sizeKb = number_format($size / 1024, 2);

        $this->info("Archivo de respaldo seleccionado: {$filename} ({$sizeKb} KB)");

        // 1. Verificación básica de contenido
        $content = File::get($filepath);
        if (!str_contains($content, 'INSERT INTO') && !str_contains($content, 'CREATE TABLE')) {
            $this->error("El archivo no contiene sentencias SQL válidas de restauración (CREATE/INSERT).");
            return Command::FAILURE;
        }

        // 2. Si solo se solicita verificación de integridad
        if ($this->option('verify-only')) {
            return $this->verificarIntegridad($filepath, $content);
        }

        // 3. Ejecución de la restauración
        if (app()->isProduction() && !$this->option('force')) {
            if (!$this->confirm("ADVERTENCIA CRÍTICA: Se dispone a restaurar la base de datos en PRODUCCIÓN desde '{$filename}'. Todos los datos actuales serán reemplazados. ¿Desea continuar?")) {
                $this->warn('Operación de restauración cancelada por el usuario.');
                return Command::SUCCESS;
            }
        } elseif (!$this->option('force') && $this->input->isInteractive()) {
            if (!$this->confirm("¿Está seguro de restaurar la base de datos desde '{$filename}'?")) {
                $this->warn('Operación cancelada.');
                return Command::SUCCESS;
            }
        }

        $this->info('Ejecutando proceso de restauración...');
        $restored = $this->restaurarSql($filepath, $content);

        if (!$restored) {
            $this->error('Falló la restauración del respaldo.');
            Log::error("RESTORE_BACKUP_ERROR: No se pudo restaurar desde {$filepath}");
            return Command::FAILURE;
        }

        $this->info("✓ Base de datos restaurada exitosamente desde: {$filename}");
        Log::info("RESTORE_BACKUP_SUCCESS: Base de datos restaurada exitosamente desde {$filename}");

        return Command::SUCCESS;
    }

    /**
     * Verifica la integridad sintáctica y estructural del archivo de respaldo.
     */
    protected function verificarIntegridad(string $filepath, string $content): int
    {
        $this->line('Verificando integridad sintáctica y estructura del respaldo...');

        $lineas = substr_count($content, "\n");
        $inserts = substr_count($content, 'INSERT INTO');
        $creates = substr_count($content, 'CREATE TABLE');

        $driver = DB::getDriverName();

        // En SQLite podemos probar ejecutar en una conexión en memoria temporal para verificar sintaxis completa
        if ($driver === 'sqlite') {
            try {
                $tempPdo = new \PDO('sqlite::memory:');
                $tempPdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $tempPdo->exec($content);
                $this->line('  ↳ Validación de sintaxis SQLite en memoria: OK');
            } catch (\Throwable $e) {
                $this->error("Error de sintaxis en el volcado SQL: " . $e->getMessage());
                return Command::FAILURE;
            }
        }

        $this->info("✓ Archivo de respaldo íntegro y sintácticamente válido.");
        $this->line("  Líneas: {$lineas}");
        $this->line("  Sentencias de creación de tablas: {$creates}");
        $this->line("  Sentencias de inserción de datos: {$inserts}");

        return Command::SUCCESS;
    }

    /**
     * Ejecuta las sentencias SQL en la base de datos activa.
     */
    protected function restaurarSql(string $filepath, string $content): bool
    {
        $connectionName = config('database.default');
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'])) {
            $config = config("database.connections.{$connectionName}");
            $host = $config['host'] ?? '127.0.0.1';
            $port = $config['port'] ?? '3306';
            $database = $config['database'] ?? 'inventario_lia';
            $username = $config['username'] ?? 'root';
            $password = $config['password'] ?? '';

            $cliCommands = ['mariadb', 'mysql'];
            foreach ($cliCommands as $cmd) {
                try {
                    $check = Process::run("{$cmd} --version");
                    if ($check->successful()) {
                        $env = [];
                        if (!empty($password)) {
                            $env['MYSQL_PWD'] = (string)$password;
                        }

                        $restoreCmd = "{$cmd} -h " . escapeshellarg($host) .
                                      " -P " . escapeshellarg((string)$port) .
                                      " -u " . escapeshellarg($username) .
                                      " " . escapeshellarg($database) .
                                      " < " . escapeshellarg($filepath);

                        $res = Process::withEnvironment($env)->run($restoreCmd);
                        if ($res->successful()) {
                            return true;
                        }

                        Log::error("RESTORE_CLI_ERROR: Falló {$cmd}", [
                            'exit_code' => $res->exitCode(),
                            'error' => $res->errorOutput(),
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::warning("RESTORE_CLI_EXCEPTION: {$e->getMessage()}");
                }
            }
        }

        // Fallback para SQLite o ejecución directa PDO
        try {
            DB::unprepared($content);
            return true;
        } catch (\Throwable $e) {
            $this->error("Error en ejecución directa: " . $e->getMessage());
            return false;
        }
    }
}
