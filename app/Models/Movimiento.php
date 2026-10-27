<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Presenters\MovimientoPresenter;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Movimiento extends Model
{
    use HasFactory;

    protected $table = 'movimientos';
    public $timestamps = false;

    protected $fillable = [
        'elemento_id',
        'tipo_movimiento',
        'estado_ticket',
        'ubicacion_id',
        'puesto',
        'user_id',
        'fecha',
        'solicitante',
        'fecha_prestamo',
        'fecha_devolucion',
        'correccion_de_id',
        'ticket_id',
        'comentario',
    ];

    protected static function booted(): void
    {
        static::updating(function ($movimiento) {
            $dirtyKeys = array_keys($movimiento->getDirty());
            // Únicamente se tolera actualizar fecha_devolucion y estado_ticket al registrar check-in o cierre de ticket
            if (array_diff($dirtyKeys, ['fecha_devolucion', 'estado_ticket'])) {
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

    /**
     * Registra un nuevo movimiento de enmienda / corrección enlazado auditadamente a este movimiento.
     */
    public function crearEnmienda(array $datos, int $usuarioId, string $motivo): self
    {
        $comentario = "ENMIENDA / CORRECCIÓN de Movimiento #{$this->id}: {$motivo}";
        if (!empty($datos['comentario'])) {
            $comentario .= " | {$datos['comentario']}";
        }

        return static::create([
            'elemento_id' => $this->elemento_id,
            'tipo_movimiento' => $this->tipo_movimiento,
            'ubicacion_id' => $datos['ubicacion_id'] ?? $this->ubicacion_id,
            'puesto' => array_key_exists('puesto', $datos) ? $datos['puesto'] : $this->puesto,
            'user_id' => $usuarioId,
            'fecha' => now(),
            'solicitante' => array_key_exists('solicitante', $datos) ? $datos['solicitante'] : $this->solicitante,
            'fecha_prestamo' => $this->fecha_prestamo,
            'fecha_devolucion' => $this->fecha_devolucion,
            'correccion_de_id' => $this->id,
            'comentario' => $comentario,
        ]);
    }

    public function correccionDe(): BelongsTo
    {
        return $this->belongsTo(Movimiento::class, 'correccion_de_id');
    }

    public function correcciones(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'correccion_de_id');
    }

    public function esCorreccion(): bool
    {
        return !is_null($this->correccion_de_id);
    }

    public function elemento(): BelongsTo
    {
        return $this->belongsTo(Elemento::class, 'elemento_id');
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    public function usuario(): BelongsTo
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

    public function scopePrestadosActivos(Builder $query): Builder
    {
        return $query->whereNotNull('fecha_prestamo')->whereNull('fecha_devolucion');
    }

    public function adjuntos(): HasMany
    {
        return $this->hasMany(MovimientoAdjunto::class, 'movimiento_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Movimiento::class, 'ticket_id');
    }

    public function resoluciones(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'ticket_id');
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(TicketComentario::class, 'ticket_id')->orderBy('id', 'asc');
    }

    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'ticket_areas', 'ticket_id', 'area_id')
            ->withPivot('user_id', 'created_at');
    }

    public function esReparacion(): bool
    {
        return $this->tipo_movimiento === 'reparacion';
    }

    public function esBaja(): bool
    {
        return $this->tipo_movimiento === 'baja';
    }

    public function esTicketAbierto(): bool
    {
        return $this->tipo_movimiento === 'reparacion' && in_array($this->estado_ticket, ['abierto', 'solucionado', null, '']);
    }

    public function scopeTicketsAbiertos(Builder $query): Builder
    {
        return $query->where('tipo_movimiento', 'reparacion')
            ->where(function ($q) {
                $q->whereNull('estado_ticket')
                  ->orWhereIn('estado_ticket', ['abierto', 'solucionado']);
            });
    }

    public function scopeTickets(Builder $query): Builder
    {
        return $query->where('tipo_movimiento', 'reparacion');
    }

    public function scopeBajas(Builder $query): Builder
    {
        return $query->where('tipo_movimiento', 'baja');
    }

    public function scopeInternos(Builder $query): Builder
    {
        return $query->where('tipo_movimiento', 'interno');
    }
}
