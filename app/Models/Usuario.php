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
        'carrera_id',
        'cargo_id',
        'whatsapp',
        'direccion',
        'activo',
        'fecha_egreso',
        'huella_borrada',
        'anonimizado_el',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'fecha_nacimiento' => 'date',
        'fecha_egreso' => 'date',
        'activo' => 'boolean',
        'huella_borrada' => 'boolean',
        'anonimizado_el' => 'datetime',
    ];

    protected $attributes = [
        'huella_borrada' => false,
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'usuario_roles', 'user_id', 'rol_id');
    }

    public function carreraRelacion(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

    public function cargoRelacion(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }

    public function getCarreraAttribute(): ?string
    {
        return $this->carreraRelacion?->nombre;
    }

    public function getCargoActualAttribute(): ?string
    {
        if ($this->esHistorico()) {
            return 'Ex Colaborador Histórico';
        }
        return $this->cargoRelacion?->nombre;
    }

    public function getCarreraNombreAttribute(): string
    {
        return $this->carreraRelacion?->nombre ?? 'Sin carrera';
    }

    public function trayectorias(): HasMany
    {
        return $this->hasMany(UsuarioTrayectoria::class, 'user_id')->orderBy('fecha_inicio', 'desc');
    }

    /**
     * Verifica si el usuario es ex colaborador con más de 6 años desde su desvinculación laboral.
     */
    public function isExPersonalMayor6Anios(): bool
    {
        if ($this->activo) {
            return false;
        }

        // 1. Verificación directa por fecha_egreso
        if ($this->fecha_egreso) {
            $fechaEgreso = $this->fecha_egreso instanceof \Carbon\Carbon ? $this->fecha_egreso : \Carbon\Carbon::parse($this->fecha_egreso);
            if ($fechaEgreso->diffInYears(now()) >= 6) {
                return true;
            }
        }

        // 2. Verificación por historial de trayectorias
        if ($this->relationLoaded('trayectorias')) {
            $ultima = $this->trayectorias->whereNotNull('fecha_fin')->sortByDesc('fecha_fin')->first();
        } else {
            $ultima = $this->trayectorias()->whereNotNull('fecha_fin')->orderBy('fecha_fin', 'desc')->first();
        }

        if ($ultima && $ultima->fecha_fin) {
            $fechaFin = $ultima->fecha_fin instanceof \Carbon\Carbon ? $ultima->fecha_fin : \Carbon\Carbon::parse($ultima->fecha_fin);
            return $fechaFin->diffInYears(now()) >= 6;
        }

        return false;
    }

    /**
     * Retorna la colección de ex-colaboradores con más de 6 años de cese laboral
     * pendientes de borrado de huella digital (Ley 25.326).
     */
    public static function elegiblesBorradoHuella()
    {
        $seisAniosAtras = now()->subYears(6);
        return static::where('activo', false)
            ->where(function ($q) {
                $q->where('huella_borrada', false)->orWhereNull('huella_borrada');
            })
            ->where(function ($q) use ($seisAniosAtras) {
                $q->where('fecha_egreso', '<=', $seisAniosAtras)
                  ->orWhereHas('trayectorias', function ($t) use ($seisAniosAtras) {
                      $t->whereNotNull('fecha_fin')->where('fecha_fin', '<=', $seisAniosAtras);
                  });
            })
            ->with('trayectorias')
            ->get()
            ->filter(function ($u) {
                return $u->isExPersonalMayor6Anios() && !$u->esHistorico();
            })
            ->values();
    }

    /**
     * Realiza el borrado irreversible de la huella digital y datos protegidos (Ley 25.326).
     * Preserva el ID del usuario para garantizar la trazabilidad forense de movimientos,
     * tickets y auditorías en el sistema patrimonial.
     */
    public function anonimizarHuellaDigital(): bool
    {
        $id = $this->id;

        $this->dni = null;
        $this->whatsapp = null;
        $this->direccion = null;
        $this->fecha_nacimiento = null;
        $this->carrera_id = null;
        $this->cargo_id = null;
        $this->nombre = "Usuario Histórico #{$id}";
        $this->email = "historico_{$id}@lia.local";
        $this->password = bcrypt(\Illuminate\Support\Str::random(40));
        $this->remember_token = null;
        $this->activo = false;
        $this->huella_borrada = true;
        $this->anonimizado_el = now();

        // Revocar roles de gestión si tuviese asociados
        $this->roles()->detach();

        return $this->save();
    }

    public function esAnonimizado(): bool
    {
        return (bool)$this->huella_borrada
            || str_starts_with((string)$this->email, 'historico_')
            || str_starts_with((string)$this->email, 'anonimo_')
            || str_starts_with((string)$this->nombre, 'Usuario Histórico')
            || str_starts_with((string)$this->nombre, 'Usuario Anonimizado');
    }

    public function esHistorico(): bool
    {
        return $this->esAnonimizado();
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
