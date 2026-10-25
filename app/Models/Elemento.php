<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use App\Presenters\ElementoPresenter;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Elemento extends Model
{
    use HasFactory;

    protected $table = 'elementos';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'nro_lia',
        'nro_unsj',
        'tipo_id',
        'marca',
        'modelo',
        'nro_serie',
        'anio_compra',
        'puesto',
        'observaciones',
        'descripcion',
        'cantidad',
        'fecha_adquisicion',
        'fecha_vencimiento_garantia',
    ];

    protected $casts = [
        'fecha_adquisicion' => 'date',
        'fecha_vencimiento_garantia' => 'date',
        'descripcion' => 'array',
        'anio_compra' => 'integer',
        'cantidad' => 'integer',
    ];

    /**
     * Normaliza un identificador patrimonial (LIA o UNSJ), convirtiendo strings vacíos,
     * guiones o variantes 'S/N' en NULL de forma estandarizada.
     */
    public static function normalizarIdentificador(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }
        $limpio = trim($valor);
        return ($limpio === '' || strtoupper($limpio) === 'S/N' || $limpio === '-') ? null : $limpio;
    }

    /**
     * Transforma arrays paralelos de claves y valores de formulario en un array asociativo limpio.
     */
    public static function parsearDescripcion(array $keys = [], array $values = []): ?array
    {
        $descripcion = [];
        foreach ($keys as $i => $k) {
            $k = trim((string)$k);
            $v = isset($values[$i]) ? trim((string)$values[$i]) : '';
            if ($k !== '' && $v !== '') {
                $descripcion[$k] = $v;
            }
        }
        return !empty($descripcion) ? $descripcion : null;
    }

    protected static function booted(): void
    {
        static::saving(function ($elemento) {
            $elemento->nro_lia = static::normalizarIdentificador($elemento->nro_lia);
            $elemento->nro_unsj = static::normalizarIdentificador($elemento->nro_unsj);
        });
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(Tipo::class, 'tipo_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'elemento_id')->orderBy('fecha', 'desc');
    }

    public function ultimoMovimiento(): HasOne
    {
        return $this->hasOne(Movimiento::class, 'elemento_id')->latestOfMany('id');
    }

    public function valoresAtributos(): HasMany
    {
        return $this->hasMany(ElementoAtributoValor::class, 'elemento_id')->with('atributo');
    }

    /**
     * Obtiene el valor formateado de un atributo técnico específico (vía relación o JSON).
     */
    public function getValorAtributo(string $nombreAtributo): string
    {
        $nomLower = mb_strtolower(trim($nombreAtributo));

        if ($this->relationLoaded('valoresAtributos') || $this->valoresAtributos()->exists()) {
            foreach ($this->valoresAtributos as $va) {
                if ($va->atributo && mb_strtolower(trim($va->atributo->nombre)) === $nomLower) {
                    $val = trim((string)$va->valor);
                    if ($val !== '') {
                        $unidad = $va->atributo->unidad_medida ? ' ' . $va->atributo->unidad_medida : '';
                        return $val . $unidad;
                    }
                }
            }
        }

        if (is_array($this->descripcion)) {
            foreach ($this->descripcion as $key => $val) {
                if (mb_strtolower(trim((string)$key)) === $nomLower) {
                    $v = trim((string)$val);
                    if ($v !== '') {
                        return $v;
                    }
                }
            }
        }

        return '-';
    }

    public function presenter(): ElementoPresenter
    {
        return new ElementoPresenter($this);
    }

    public function getDescripcionTextoAttribute(): string
    {
        if ($this->valoresAtributos->isNotEmpty()) {
            return $this->valoresAtributos->map(function ($va) {
                $nom = $va->atributo?->nombre ?? 'Dato';
                $unidad = $va->atributo?->unidad_medida ? ' ' . $va->atributo->unidad_medida : '';
                return "{$nom}: {$va->valor}{$unidad}";
            })->implode(' | ');
        }

        if (is_array($this->descripcion)) {
            $parts = [];
            foreach ($this->descripcion as $key => $val) {
                if (!empty($val)) {
                    $parts[] = is_string($key) ? "{$key}: {$val}" : $val;
                }
            }
            return implode(' | ', $parts);
        }
        return (string)($this->descripcion ?? '');
    }

    // --- QUERY SCOPES SEMÁNTICOS (SNIPE-IT PATTERN) ---

    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->whereDoesntHave('tipo', function ($tq) {
            $tq->where('nombre', 'basura tecnologica');
        })->whereHas('ultimoMovimiento', function ($q) {
            $q->where(function ($sub) {
                $sub->whereNull('fecha_prestamo')
                    ->orWhereNotNull('fecha_devolucion');
            })->whereNotIn('tipo_movimiento', ['baja', 'reparacion']);
        });
    }

    public function scopePrestadosActivos(Builder $query): Builder
    {
        return $query->whereHas('ultimoMovimiento', function ($q) {
            $q->whereNotNull('fecha_prestamo')
              ->whereNull('fecha_devolucion');
        });
    }

    public function scopeEnUbicacion(Builder $query, int|string $ubicacionId, ?string $puesto = null): Builder
    {
        return $query->whereHas('ultimoMovimiento', function ($q) use ($ubicacionId, $puesto) {
            $q->where('ubicacion_id', $ubicacionId);
            if ($puesto !== null && $puesto !== '') {
                $q->where('puesto', $puesto);
            }
        });
    }

    public function scopeObsoletos(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereHas('ultimoMovimiento', function ($mq) {
                $mq->where('tipo_movimiento', 'baja');
            })->orWhereHas('tipo', function ($tq) {
                $tq->where('nombre', 'basura tecnologica');
            });
        });
    }

    public function scopeEnReparacion(Builder $query): Builder
    {
        return $query->whereHas('ultimoMovimiento', function ($mq) {
            $mq->where('tipo_movimiento', 'reparacion')
               ->where(function ($sub) {
                   $sub->whereNull('estado_ticket')
                       ->orWhere('estado_ticket', 'abierto');
               });
        });
    }

    public function scopeBuscarIdentificador(Builder $query, string $term): Builder
    {
        $term = trim($term);
        return $query->where(function ($q) use ($term) {
            $q->where('nro_lia', 'LIKE', "%{$term}%")
              ->orWhere('nro_unsj', 'LIKE', "%{$term}%")
              ->orWhere('marca', 'LIKE', "%{$term}%")
              ->orWhere('modelo', 'LIKE', "%{$term}%")
              ->orWhere('nro_serie', 'LIKE', "%{$term}%")
              ->orWhere('puesto', 'LIKE', "%{$term}%")
              ->orWhere('observaciones', 'LIKE', "%{$term}%")
              ->orWhere('descripcion', 'LIKE', "%{$term}%")
              ->orWhere('anio_compra', 'LIKE', "%{$term}%")
              ->orWhereHas('tipo', function ($tq) use ($term) {
                  $tq->where('nombre', 'LIKE', "%{$term}%");
              })
              ->orWhereHas('valoresAtributos', function ($vaq) use ($term) {
                  $vaq->where('valor', 'LIKE', "%{$term}%");
              })
              ->orWhereHas('ultimoMovimiento', function ($mq) use ($term) {
                  $mq->where('solicitante', 'LIKE', "%{$term}%")
                     ->orWhere('comentario', 'LIKE', "%{$term}%")
                     ->orWhere('puesto', 'LIKE', "%{$term}%")
                     ->orWhereHas('ubicacion', function ($uq) use ($term) {
                         $uq->where('nombre', 'LIKE', "%{$term}%");
                     });
              });
        });
    }
}
