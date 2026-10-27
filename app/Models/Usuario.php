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

    public function isUserAdmin(): bool
    {
        return $this->hasRole('user_admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('user_admin');
    }

    public function isAppAdmin(): bool
    {
        return $this->hasRole('app_admin');
    }

    public function isItemAdmin(): bool
    {
        return $this->hasRole('item_admin');
    }

    public function isTec(): bool
    {
        return $this->hasRole('tec');
    }

    public function isSubTec(): bool
    {
        return $this->hasRole('subtec');
    }

    public function isRev(): bool
    {
        return $this->hasRole('rev');
    }

    public function isRegistered(): bool
    {
        return $this->hasRole('registered');
    }

    public function canManageMovimientos(): bool
    {
        return $this->hasRole('tec');
    }

    public function canManageElementos(): bool
    {
        return $this->hasRole('item_admin');
    }

    public function canManageUsers(): bool
    {
        return $this->hasRole('user_admin');
    }

    public function canManageApp(): bool
    {
        return $this->hasRole('app_admin');
    }

    public function canEditProfile(): bool
    {
        return $this->hasRole('registered');
    }

    /**
     * Permisos jerárquicos sobre el ciclo de vida del Ticket:
     * - Revisor (rev): inicia y comenta/responde en el hilo de diagnóstico, NUNCA soluciona ni cierra.
     * - SubTécnico (subtec): registra y propone la solución técnica, NUNCA asocia movimientos ni cierra formalmente.
     * - Técnico (tec/item_admin): cierra formalmente el ticket (resuelto o baja) y asocia movimientos físicos.
     */
    public function canSolveTickets(): bool
    {
        return $this->hasRole(['subtec', 'tec', 'item_admin', 'user_admin', 'app_admin']);
    }

    public function canCloseTickets(): bool
    {
        return $this->hasRole(['tec', 'item_admin', 'user_admin', 'app_admin']);
    }

    public function canAssociateMovementsToTicket(): bool
    {
        return $this->hasRole(['tec', 'item_admin', 'user_admin', 'app_admin']);
    }

    /**
     * Determina si el usuario tiene al menos un rol operativo más allá de registered.
     * Si posee cualquier rol (rev, subtec, tec, item_admin, user_admin, app_admin), ingresa al panel y nunca ve bienvenida.
     */
    public function hasActivePermissions(): bool
    {
        return $this->hasRole(['rev', 'subtec', 'tec', 'item_admin', 'user_admin', 'app_admin']);
    }

    /**
     * Determina si el usuario posee EXCLUSIVAMENTE el rol registered.
     */
    public function isOnlyRegistered(): bool
    {
        $roles = $this->roles->pluck('rol');
        return $roles->contains('registered') && $roles->diff(['registered'])->isEmpty();
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class, 'user_id');
    }
}
