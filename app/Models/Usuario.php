<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'dni',
        'fecha_nacimiento',
        'carrera',
        'carrera_id',
        'cargo_actual',
        'whatsapp',
        'direccion',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'fecha_nacimiento' => 'date',
        'activo' => 'boolean',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'usuario_roles', 'user_id', 'rol_id');
    }

    public function carreraRelacion(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

    public function getCarreraNombreAttribute(): string
    {
        return $this->carreraRelacion?->nombre ?? $this->carrera ?? 'Sin carrera';
    }

    public function trayectorias(): HasMany
    {
        return $this->hasMany(UsuarioTrayectoria::class, 'user_id')->orderBy('fecha_inicio', 'desc');
    }

    public function hasRole(string|array $role): bool
    {
        $roleList = is_array($role) ? $role : func_get_args();
        return $this->roles->pluck('rol')->intersect($roleList)->isNotEmpty();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('user_admin');
    }

    public function isAppAdmin(): bool
    {
        return $this->hasRole(['app_admin', 'user_admin']);
    }

    public function isItemAdmin(): bool
    {
        return $this->hasRole('item_admin');
    }

    public function isTec(): bool
    {
        return $this->hasRole('tec');
    }

    public function isRev(): bool
    {
        return $this->hasRole('rev');
    }

    public function canManageMovimientos(): bool
    {
        return $this->hasRole(['tec', 'item_admin', 'user_admin']);
    }

    public function canManageElementos(): bool
    {
        return $this->hasRole(['item_admin', 'user_admin']);
    }

    public function hasActivePermissions(): bool
    {
        return $this->hasRole(['rev', 'tec', 'item_admin', 'user_admin', 'app_admin']);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'user_id');
    }
}
