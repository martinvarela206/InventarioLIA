<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Elemento extends Model
{
    use HasFactory;

    protected $table = 'elementos';
    protected $primaryKey = 'nro_lia';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'nro_lia',
        'nro_unsj',
        'tipo_id',
        'descripcion',
        'cantidad',
        'fecha_adquisicion',
        'fecha_vencimiento_garantia',
    ];

    protected $casts = [
        'fecha_adquisicion' => 'date',
        'fecha_vencimiento_garantia' => 'date',
        'descripcion' => 'array',
    ];

    public function getDescripcionTextoAttribute()
    {
        if (!is_array($this->descripcion)) {
            return $this->descripcion;
        }
        return implode(', ', array_values($this->descripcion));
    }

    public function tipo()
    {
        return $this->belongsTo(Tipo::class, 'tipo_id');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'nro_lia', 'nro_lia')->orderBy('fecha', 'desc');
    }

    public function ultimoMovimiento()
    {
        return $this->hasOne(Movimiento::class, 'nro_lia', 'nro_lia')->latestOfMany('fecha');
    }
}
