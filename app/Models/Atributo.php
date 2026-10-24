<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Atributo extends Model
{
    use HasFactory;

    protected $table = 'atributos';

    protected $fillable = [
        'nombre',
        'tipo_dato',
        'unidad_medida',
        'descripcion',
    ];

    public function tipos(): BelongsToMany
    {
        return $this->belongsToMany(Tipo::class, 'tipo_atributo')
                    ->withPivot('es_requerido', 'orden')
                    ->withTimestamps();
    }

    public function valores(): HasMany
    {
        return $this->hasMany(ElementoAtributoValor::class, 'atributo_id');
    }

    public static function firstOrCreateNormalizado(string $nombre, string $tipoDato = 'texto', ?string $unidadMedida = null): self
    {
        $limpio = trim($nombre);
        return static::firstOrCreate(
            ['nombre' => $limpio],
            [
                'tipo_dato' => $tipoDato,
                'unidad_medida' => $unidadMedida,
                'descripcion' => "Atributo técnico: {$limpio}"
            ]
        );
    }
}
