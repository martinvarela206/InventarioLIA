<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auditoria extends Model
{
    use HasFactory;

    protected $table = 'auditorias';

    protected $fillable = [
        'ubicacion_id',
        'user_id',
        'fecha',
        'estado',
        'total_auditados',
        'total_ok',
        'total_inconsistencias',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'total_auditados' => 'integer',
        'total_ok' => 'integer',
        'total_inconsistencias' => 'integer',
    ];

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(AuditoriaDetalle::class, 'auditoria_id');
    }

    /**
     * Recalcula automáticamente los totales consolidados a partir de los detalles.
     */
    public function recalcularTotales(): self
    {
        $this->total_auditados = $this->detalles()->count();
        $this->total_ok = $this->detalles()->where('estado', 'ok')->count();
        $this->total_inconsistencias = $this->detalles()->where('estado', 'inconsistencia')->count();
        $this->save();

        return $this;
    }
}
