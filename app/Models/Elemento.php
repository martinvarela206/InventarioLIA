<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use App\Presenters\ElementoPresenter;

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

    protected static function booted()
    {
        static::saving(function ($elemento) {
            $lia = trim((string)$elemento->nro_lia);
            $unsj = trim((string)$elemento->nro_unsj);

            $elemento->nro_lia = ($lia === '' || strtoupper($lia) === 'S/N' || $lia === '-') ? null : $lia;
            $elemento->nro_unsj = ($unsj === '' || strtoupper($unsj) === 'S/N' || $unsj === '-') ? null : $unsj;
        });
    }

    public function tipo()
    {
        return $this->belongsTo(Tipo::class, 'tipo_id');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'elemento_id')->orderBy('fecha', 'desc');
    }

    public function ultimoMovimiento()
    {
        return $this->hasOne(Movimiento::class, 'elemento_id')->latestOfMany('id');
    }

    public function presenter(): ElementoPresenter
    {
        return new ElementoPresenter($this);
    }

    public function getDescripcionTextoAttribute()
    {
        if (is_array($this->descripcion)) {
            $parts = [];
            foreach ($this->descripcion as $key => $val) {
                if (!empty($val)) {
                    $parts[] = is_string($key) ? "{$key}: {$val}" : $val;
                }
            }
            return implode(' | ', $parts);
        }
        return $this->descripcion ?? '';
    }

    // --- QUERY SCOPES SEMÁNTICOS (SNIPE-IT PATTERN) ---

    public function scopeDisponibles($query)
    {
        return $query->whereHas('ultimoMovimiento', function ($q) {
            $q->whereHas('estado', function ($sq) {
                $sq->where('disponible', true);
            })->whereNull('fecha_prestamo');
        });
    }

    public function scopePrestadosActivos($query)
    {
        return $query->whereHas('ultimoMovimiento', function ($q) {
            $q->whereNotNull('fecha_prestamo')
              ->whereNull('fecha_devolucion');
        });
    }

    public function scopeEnUbicacion($query, $ubicacionId, $puesto = null)
    {
        return $query->whereHas('ultimoMovimiento', function ($q) use ($ubicacionId, $puesto) {
            $q->where('ubicacion_id', $ubicacionId);
            if ($puesto !== null && $puesto !== '') {
                $q->where('puesto', $puesto);
            }
        });
    }

    public function scopeObsoletos($query)
    {
        return $query->where(function ($q) {
            $q->whereHas('tipo', function ($tq) {
                $tq->where('nombre', 'basura tecnologica');
            })->orWhereHas('ultimoMovimiento', function ($mq) {
                $mq->whereHas('estado', function ($eq) {
                    $eq->whereIn('nombre', ['dado de baja', 'donado', 'transferido']);
                });
            });
        });
    }

    public function scopeBuscarIdentificador($query, $term)
    {
        $term = trim($term);
        return $query->where(function ($q) use ($term) {
            $q->where('nro_lia', 'LIKE', "%{$term}%")
              ->orWhere('nro_unsj', 'LIKE', "%{$term}%")
              ->orWhere('marca', 'LIKE', "%{$term}%")
              ->orWhere('modelo', 'LIKE', "%{$term}%")
              ->orWhere('nro_serie', 'LIKE', "%{$term}%");
        });
    }
}
