<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class VerificacionRestauracionBackupTest extends TestCase
{
    use RefreshDatabase;

    protected string $backupDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->backupDir = storage_path('app/backups_restore_test');
        File::deleteDirectory($this->backupDir);
        File::makeDirectory($this->backupDir, 0755, true);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->backupDir);
        parent::tearDown();
    }

    public function test_verificacion_sintactica_y_restauracion_de_respaldo(): void
    {
        // 1. Generar un respaldo válido usando el comando de backup
        $exitBackup = Artisan::call('app:backup-database', [
            '--keep' => 3,
            '--path' => $this->backupDir,
        ]);
        $this->assertEquals(0, $exitBackup);

        // 2. Ejecutar verificación de integridad estructural (--verify-only)
        $exitVerify = Artisan::call('inventario:restore-backup', [
            '--path' => $this->backupDir,
            '--verify-only' => true,
        ]);
        $this->assertEquals(0, $exitVerify);

        // 3. Ejecutar restauración forzada (--force)
        $exitRestore = Artisan::call('inventario:restore-backup', [
            '--path' => $this->backupDir,
            '--force' => true,
        ]);
        $this->assertEquals(0, $exitRestore);
    }

    public function test_rechaza_archivo_inexistente_o_vacio(): void
    {
        // Archivo inexistente
        $exitInexistente = Artisan::call('inventario:restore-backup', [
            'file' => 'archivo_inexistente.sql',
            '--path' => $this->backupDir,
        ]);
        $this->assertEquals(1, $exitInexistente);

        // Archivo vacío (0 bytes)
        $emptyPath = $this->backupDir.DIRECTORY_SEPARATOR.'backup_vacio.sql';
        File::put($emptyPath, '');

        $exitVacio = Artisan::call('inventario:restore-backup', [
            'file' => $emptyPath,
            '--path' => $this->backupDir,
        ]);
        $this->assertEquals(1, $exitVacio);
    }
}
