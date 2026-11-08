<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SchedulerYDockerConfigTest extends TestCase
{
    public function test_docker_compose_posee_servicio_scheduler_y_volumenes_persistentes(): void
    {
        $composePath = base_path('docker-compose.yml');
        $this->assertFileExists($composePath);

        $content = File::get($composePath);

        // Servicio scheduler configurado con schedule:work
        $this->assertStringContainsString('container_name: laravel_scheduler', $content);
        $this->assertStringContainsString('php', $content);
        $this->assertStringContainsString('schedule:work', $content);

        // Volúmenes de almacenamiento privado y de respaldos
        $this->assertStringContainsString('storage_private:/var/www/html/storage/app/private', $content);
        $this->assertStringContainsString('storage_backups:/var/www/html/storage/app/backups', $content);
        $this->assertStringContainsString('storage_private:', $content);
        $this->assertStringContainsString('storage_backups:', $content);
    }

    public function test_dockerfile_incluye_mariadb_client(): void
    {
        $dockerfilePath = base_path('Dockerfile');
        $this->assertFileExists($dockerfilePath);

        $content = File::get($dockerfilePath);
        $this->assertStringContainsString('mariadb-client', $content);
        $this->assertStringContainsString('ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]', $content);
        $this->assertStringContainsString('ARG PHP_VERSION', $content);
        $this->assertStringContainsString('libjpeg-dev', $content);
        $this->assertStringContainsString('libwebp-dev', $content);
        $this->assertStringContainsString('libzip-dev', $content);
    }

    public function test_entrypoint_sh_implementa_fail_fast_y_carece_de_fallbacks_silenciosos(): void
    {
        $entrypointPath = base_path('docker/entrypoint.sh');
        $this->assertFileExists($entrypointPath);

        $content = File::get($entrypointPath);
        $this->assertStringContainsString('set -Eeuo pipefail', $content);
        $this->assertStringNotContainsString('|| true', $content);
        $this->assertStringContainsString('php artisan migrate --force', $content);
        $this->assertStringContainsString('exec "$@"', $content);
    }

    public function test_scheduler_registra_rutina_diaria_de_backup_con_adjuntos(): void
    {
        $schedule = app()->make(Schedule::class);
        $events = collect($schedule->events());

        $backupEvent = $events->first(function ($event) {
            return str_contains($event->command, 'app:backup-database');
        });

        $this->assertNotNull($backupEvent, 'El comando app:backup-database debe estar programado en el scheduler.');
        $this->assertStringContainsString('--with-attachments', $backupEvent->command);
        $this->assertStringContainsString('--keep=14', $backupEvent->command);
        // Expresión cron '0 3 * * *' (diario a las 03:00)
        $this->assertEquals('0 3 * * *', $backupEvent->expression);
    }
}
