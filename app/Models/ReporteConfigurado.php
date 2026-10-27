<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ReporteConfigurado extends Model
{
    use HasFactory;

    protected $table = 'reportes_configurados';

    protected $fillable = [
        'slug',
        'titulo',
        'subtitulo',
        'descripcion',
        'icono',
        'categoria',
        'filtros_fijos',
        'variables_requeridas',
        'columnas_visibles',
        'columnas_ocultas',
        'es_sistema',
        'activo',
        'orden',
        'creado_por',
    ];

    protected $casts = [
        'filtros_fijos' => 'array',
        'variables_requeridas' => 'array',
        'columnas_visibles' => 'array',
        'columnas_ocultas' => 'array',
        'es_sistema' => 'boolean',
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeOrdenados(Builder $query): Builder
    {
        return $query->orderBy('orden', 'asc')->orderBy('id', 'asc');
    }

    public function requiereUbicacion(): bool
    {
        return !empty($this->variables_requeridas['ubicacion_id']);
    }

    public function requiereNroLia(): bool
    {
        return !empty($this->variables_requeridas['nro_lia']);
    }

    public function requiereRangoFechas(): bool
    {
        return !empty($this->variables_requeridas['fechas']) ||
               (!empty($this->variables_requeridas['fecha_desde']) && !empty($this->variables_requeridas['fecha_hasta']));
    }

    public function requiereTipo(): bool
    {
        return !empty($this->variables_requeridas['tipo_id']);
    }

    public function requierePuesto(): bool
    {
        return !empty($this->variables_requeridas['puesto']);
    }

    public static function generarSlug(string $titulo): string
    {
        $base = Str::slug($titulo);
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
    }
}
