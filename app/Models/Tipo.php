<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tipo extends Model
{
    protected $table = 'tipos';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function elementos()
    {
        return $this->hasMany(Elemento::class, 'tipo_id');
    }

    /**
     * Resuelve o crea un tipo normalizando la cadena de texto de entrada.
     */
    public static function firstOrCreateNormalizado(string $nombre, ?string $descripcion = null): self
    {
        $raw = trim($nombre);
        $clean = mb_strtolower($raw, 'UTF-8');
        
        // Quitar acentos para comparación
        $unaccented = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $clean);
        $unaccented = preg_replace('/[^a-z0-9\s]/', '', $unaccented);
        $unaccented = trim(preg_replace('/\s+/', ' ', $unaccented));

        // Mapeos de sinónimos comunes del relevamiento
        $map = [
            'cpu' => 'computadora',
            'pc' => 'computadora',
            'laboratorio' => 'computadora',
            'ap' => 'punto de acceso',
            'ap punto de acceso' => 'punto de acceso',
            'punto de acceso' => 'punto de acceso',
            'discos' => 'disco',
            'disco' => 'disco',
            'pacheras' => 'pachera',
            'pachera' => 'pachera',
            'notebooks' => 'notebook',
            'notebook' => 'notebook',
            'monitores' => 'monitor',
            'monitor' => 'monitor',
            'raton' => 'mouse',
            'mouse' => 'mouse',
            'teclados' => 'teclado',
            'teclado' => 'teclado',
            'impresoras' => 'impresora',
            'impresora' => 'impresora',
            'parlantes' => 'parlante',
            'parlante' => 'parlante',
            'proyectores' => 'proyector',
            'proyector' => 'proyector',
            'fuentes' => 'fuente',
            'fuente' => 'fuente',
            'herramientas' => 'herramienta',
            'herramienta' => 'herramienta',
            'accesorios' => 'accesorio',
            'accesorio' => 'accesorio',
            'basura' => 'basura tecnologica',
            'basura tecnologica' => 'basura tecnologica',
            'cajon hardware' => 'accesorio',
            'cajon hardwareadministracion' => 'accesorio',
            'placa de red' => 'placa de red',
            'red' => 'placa de red',
            'ups' => 'ups',
            'switch' => 'switch',
            'servidor' => 'servidor',
        ];

        $nombreFinal = $map[$unaccented] ?? ($map[$clean] ?? $clean);

        return static::firstOrCreate(
            ['nombre' => $nombreFinal],
            ['descripcion' => $descripcion ?? ucfirst($nombreFinal)]
        );
    }
}
