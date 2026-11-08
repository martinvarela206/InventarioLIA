<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageSanitizer
{
    /**
     * Sanitiza una imagen eliminando metadatos EXIF, GPS y posibles cargas útiles
     * re-encodificándola con GD y almacenándola en el disco solicitado.
     */
    public static function sanitizeAndStore(UploadedFile $file, string $directory, string $disk = 'local'): string
    {
        $realPath = $file->getRealPath();
        $mime = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        if (empty($extension)) {
            $extension = $file->guessExtension() ?? 'jpg';
        }

        if (extension_loaded('gd') && file_exists($realPath) && is_readable($realPath)) {
            try {
                $image = match ($mime) {
                    'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($realPath),
                    'image/png' => @imagecreatefrompng($realPath),
                    'image/webp' => @imagecreatefromwebp($realPath),
                    default => @imagecreatefromstring((string) file_get_contents($realPath)),
                };

                if ($image === false) {
                    throw new \RuntimeException('No se pudo procesar la imagen para su sanitización. Formato corrupto o no admitido.');
                }

                $randomName = Str::random(40) . '.' . $extension;
                $tempFile = tempnam(sys_get_temp_dir(), 'lia_img_');

                if ($mime === 'image/png') {
                    imagealphablending($image, false);
                    imagesavealpha($image, true);
                    imagepng($image, $tempFile, 8);
                } elseif ($mime === 'image/webp') {
                    imagewebp($image, $tempFile, 85);
                } else {
                    imagejpeg($image, $tempFile, 85);
                }

                imagedestroy($image);

                $path = $directory . '/' . $randomName;
                $stream = fopen($tempFile, 'r');
                Storage::disk($disk)->put($path, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                @unlink($tempFile);

                return $path;
            } catch (\Throwable $e) {
                // Principio de seguridad estricto: rechazar explícitamente en lugar de almacenar binarios crudos sin sanitizar
                throw new \RuntimeException('Fallo al sanitizar la imagen: ' . $e->getMessage(), 0, $e);
            }
        }

        // Si GD no está disponible en el entorno y no estamos en producción, permitir almacenamiento estándar para pruebas
        if (!app()->isProduction()) {
            return $file->store($directory, $disk);
        }

        throw new \RuntimeException('La extensión GD es obligatoria en producción para garantizar la sanitización de adjuntos multimedia.');
    }
}
