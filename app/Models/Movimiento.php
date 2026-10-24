<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Presenters\MovimientoPresenter;

class Movimiento extends Model
{
    use HasFactory;

    protected $table = 'movimientos';
    public $timestamps = false;

    protected $fillable = [
        'elemento_id',
        'tipo_movimiento',
        'ubicacion_id',
        'puesto',
        'estado_id',
        'user_id',
        'fecha',
        'solicitante',
        'fecha_prestamo',
        'fecha_devolucion',
        'correccion_de_id',
        'comentario',
    ];

    protected static function booted()
    {
        static::updating(function ($movimiento) {
            $dirtyKeys = array_keys($movimiento->getDirty());
            // Únicamente se tolera actualizar fecha_devolucion al registrar el check-in de retorno
            if (array_diff($dirtyKeys, ['fecha_devolucion'])) {
                throw new \LogicException('Los movimientos del inventario son inmutables por política estricta de auditoría. Para enmendar datos de un movimiento previo, registre un movimiento de corrección.');
            }
        });

        static::deleting(function ($movimiento) {
            throw new \LogicException('Está terminantemente prohibido eliminar movimientos históricos del inventario por política de auditoría y trazabilidad forense.');
        });
    }

    protected $casts = [
        'fecha' => 'datetime',
        'fecha_prestamo' => 'date',
        'fecha_devolucion' => 'date',
    ];

    public function correccionDe()
    {
        return $this->belongsTo(Movimiento::class, 'correccion_de_id');
    }

    public function correcciones()
    {
        return $this->hasMany(Movimiento::class, 'correccion_de_id');
    }

    public function esCorreccion(): bool
    {
        return !is_null($this->correccion_de_id);
    }

    public function elemento()
    {
        return $this->belongsTo(Elemento::class, 'elemento_id');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    public function presenter(): MovimientoPresenter
    {
        return new MovimientoPresenter($this);
    }

    public function esPrestamo(): bool
    {
        return $this->tipo_movimiento === 'externo_prestamo' || !empty($this->fecha_prestamo);
    }

    public function diasPrestado(): ?int
    {
        if (!$this->fecha_prestamo) {
            return null;
        }
        $fin = $this->fecha_devolucion ? Carbon::parse($this->fecha_devolucion) : Carbon::now();
        return (int)Carbon::parse($this->fecha_prestamo)->diffInDays($fin);
    }

    public function scopePrestadosActivos($query)
    {
        return $query->whereNotNull('fecha_prestamo')->whereNull('fecha_devolucion');
    }

    public function scopeInternos($query)
    {
        return $query->where('tipo_movimiento', 'interno');
    }
}
