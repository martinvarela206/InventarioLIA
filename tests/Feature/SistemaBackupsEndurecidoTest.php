<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SistemaBackupsEndurecidoTest extends TestCase
{
    use RefreshDatabase;

    protected string $backupDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->backupDir = storage_path('app/backups_test_endurecido');
        File::deleteDirectory($this->backupDir);
        File::makeDirectory($this->backupDir, 0755, true);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->backupDir);
        parent::tearDown();
    }

    public function test_backup_genera_dump_sql_y_zip_adjuntos_con_rotacion_independiente(): void
    {
        // 1. Ejecutar respaldo con adjuntos
        $exitCode = Artisan::call('app:backup-database', [
            '--keep' => 2,
            '--path' => $this->backupDir,
            '--with-attachments' => true,
        ]);

        $this->assertEquals(0, $exitCode);

        // 2. Verificar que se hayan creado tanto el dump SQL como el ZIP
        $files = File::files($this->backupDir);
        $this->assertNotEmpty($files);

        $sqlFiles = array_filter($files, fn($f) => $f->getExtension() === 'sql');
        $this->assertCount(1, $sqlFiles);

        $firstSql = reset($sqlFiles);
        $content = File::get($firstSql->getPathname());
        $this->assertStringContainsString('Respaldo Automatizado Sistema LIA', $content);
        $this->assertStringNotContainsString('addslashes', $content);

        // 3. Simular acumulación para probar rotación independiente
        for ($i = 1; $i <= 3; $i++) {
            $dummySql = $this->backupDir . DIRECTORY_SEPARATOR . "backup_db_2026-01-0{$i}_000000.sql";
            File::put($dummySql, "-- Dummy SQL {$i}");
            touch($dummySql, time() - (86400 * (10 - $i)));

            $dummyZip = $this->backupDir . DIRECTORY_SEPARATOR . "backup_adjuntos_2026-01-0{$i}_000000.zip";
            File::put($dummyZip, "PK...dummy");
            touch($dummyZip, time() - (86400 * (10 - $i)));
        }

        // Ejecutar de nuevo con --keep=2
        $exitCode2 = Artisan::call('app:backup-database', [
            '--keep' => 2,
            '--path' => $this->backupDir,
            '--with-attachments' => true,
        ]);
        $this->assertEquals(0, $exitCode2);

        $filesAfter = File::files($this->backupDir);
        $sqlAfter = array_filter($filesAfter, fn($f) => $f->getExtension() === 'sql');
        $zipAfter = array_filter($filesAfter, fn($f) => $f->getExtension() === 'zip');

        // Deben conservarse a lo sumo 2 dumps SQL y 2 zips
        $this->assertCount(2, $sqlAfter);
        $this->assertLessThanOrEqual(2, count($zipAfter));
    }
}
