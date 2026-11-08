<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'dni',
        'fecha_nacimiento',
        'carrera_id',
        'cargo_id',
        'foto_path',
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
        'dni',
        'direccion',
        'whatsapp',
        'fecha_nacimiento',
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

    public function carreraRelacion(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }

    public function cargoRelacion(): BelongsTo
    {
        return $this->cargo();
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

    public function getFotoUrlAttribute(): string
    {
        if ($this->foto_path) {
            if (str_starts_with($this->foto_path, 'http://') || str_starts_with($this->foto_path, 'https://') || str_starts_with($this->foto_path, '/assets/')) {
                return $this->foto_path;
            }

            return asset('storage/'.$this->foto_path);
        }

        return asset('assets/placeholder-user.png');
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
            $fechaEgreso = $this->fecha_egreso instanceof Carbon ? $this->fecha_egreso : Carbon::parse($this->fecha_egreso);
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
            $fechaFin = $ultima->fecha_fin instanceof Carbon ? $ultima->fecha_fin : Carbon::parse($ultima->fecha_fin);

            return $fechaFin->diffInYears(now()) >= 6;
        }

        return false;
    }

    /**
     * Scope para consultar ex-colaboradores con más de 6 años de cese laboral
     * pendientes de borrado de huella digital a nivel de consulta SQL (Ley 25.326).
     */
    public function scopeElegiblesBorradoHuella($query)
    {
        $seisAniosAtras = now()->subYears(6);

        return $query->where('activo', false)
            ->where(function ($q) {
                $q->where('huella_borrada', false)->orWhereNull('huella_borrada');
            })
            ->where(function ($q) use ($seisAniosAtras) {
                // Caso A: fecha_egreso explícita <= 6 años atrás
                $q->where(function ($q2) use ($seisAniosAtras) {
                    $q2->whereNotNull('fecha_egreso')
                        ->where('fecha_egreso', '<=', $seisAniosAtras);
                })
                // Caso B: fecha_egreso nula, pero con historial de trayectorias donde la última cerró hace >= 6 años
                    ->orWhere(function ($q2) use ($seisAniosAtras) {
                        $q2->whereNull('fecha_egreso')
                            ->whereHas('trayectorias', function ($t) use ($seisAniosAtras) {
                                $t->whereNotNull('fecha_fin')->where('fecha_fin', '<=', $seisAniosAtras);
                            })
                            ->whereDoesntHave('trayectorias', function ($t) use ($seisAniosAtras) {
                                $t->whereNull('fecha_fin')->orWhere('fecha_fin', '>', $seisAniosAtras);
                            });
                    });
            });
    }

    /**
     * Retorna la colección de ex-colaboradores con más de 6 años de cese laboral
     * pendientes de borrado de huella digital (Ley 25.326).
     */
    public static function elegiblesBorradoHuella()
    {
        return static::query()
            ->elegiblesBorradoHuella()
            ->with('trayectorias')
            ->get();
    }

    /**
     * Realiza el borrado irreversible de la huella digital y datos protegidos (Ley 25.326).
     * Preserva el ID del usuario para garantizar la trazabilidad forense de movimientos,
     * tickets y auditorías en el sistema patrimonial.
     */
    public function anonimizarHuellaDigital(): bool
    {
        $id = $this->id;
        $nombreOriginal = $this->nombre;
        $dniOriginal = $this->dni;

        $this->dni = null;
        $this->whatsapp = null;
        $this->direccion = null;
        $this->fecha_nacimiento = null;
        $this->carrera_id = null;
        $this->cargo_id = null;

        // Borrar foto de perfil física si existe en storage
        if ($this->foto_path && ! str_starts_with($this->foto_path, '/assets/')) {
            try {
                Storage::disk('public')->delete($this->foto_path);
            } catch (\Throwable $e) {
                // Log and continue
            }
        }
        $this->foto_path = null;

        $this->nombre = "Usuario Histórico #{$id}";
        $this->email = "historico_{$id}@lia.local";
        $this->password = bcrypt(Str::random(40));
        $this->remember_token = null;
        $this->activo = false;
        $this->huella_borrada = true;
        $this->anonimizado_el = now();

        // Revocar roles de gestión si tuviese asociados
        $this->roles()->detach();
        $this->flushRolesCache();

        // Revocar tokens Sanctum activos
        $this->tokens()->delete();

        // Anonimizar referencias de solicitante en movimientos para proteger datos personales (Ley 25.326)
        if (! empty($nombreOriginal)) {
            Movimiento::where('solicitante', $nombreOriginal)
                ->update(['solicitante' => "Ex-colaborador anonimizado #{$id}"]);
        }
        if (! empty($dniOriginal)) {
            Movimiento::where('solicitante', 'like', "%{$dniOriginal}%")
                ->update(['solicitante' => "Ex-colaborador anonimizado #{$id}"]);
        }

        // Anonimizar notas personales en historial de trayectorias preservando estadísticas institucionales
        $this->trayectorias()->update(['notas' => null]);

        return $this->save();
    }

    public function esAnonimizado(): bool
    {
        return (bool) $this->huella_borrada;
    }

    public function esHistorico(): bool
    {
        return $this->esAnonimizado();
    }

    protected ?array $rolesCache = null;

    /**
     * Retorna el listado plano de nombres de roles del usuario cacheados en la instancia
     * para evitar subconsultas repetidas o instanciación redundante de colecciones.
     *
     * @return array<string>
     */
    public function getCachedRoles(): array
    {
        if ($this->rolesCache !== null) {
            return $this->rolesCache;
        }

        if ($this->relationLoaded('roles')) {
            $this->rolesCache = $this->roles->pluck('rol')->all();
        } else {
            $this->rolesCache = $this->roles()->pluck('rol')->all();
        }

        return $this->rolesCache;
    }

    /**
     * Invalida la caché en memoria de roles del usuario.
     */
    public function flushRolesCache(): void
    {
        $this->rolesCache = null;
        $this->unsetRelation('roles');
    }

    public function refresh()
    {
        $this->flushRolesCache();

        return parent::refresh();
    }

    public function setRelation($relation, $value)
    {
        if ($relation === 'roles') {
            $this->rolesCache = null;
        }

        return parent::setRelation($relation, $value);
    }

    public function hasRole(string|array $role): bool
    {
        $roleList = is_array($role) ? $role : func_get_args();
        // Compatibilidad bidireccional durante la transición item_admin <-> asset_admin
        if (in_array('item_admin', $roleList, true) && ! in_array('asset_admin', $roleList, true)) {
            $roleList[] = 'asset_admin';
        } elseif (in_array('asset_admin', $roleList, true) && ! in_array('item_admin', $roleList, true)) {
            $roleList[] = 'item_admin';
        }

        $myRoles = $this->getCachedRoles();
        foreach ($roleList as $r) {
            if (in_array($r, $myRoles, true)) {
                return true;
            }
        }

        return false;
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

    public function isAssetAdmin(): bool
    {
        return $this->hasRole('asset_admin');
    }

    public function isItemAdmin(): bool
    {
        return $this->hasRole('asset_admin');
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
        return $this->hasRole('asset_admin');
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
     * - Técnico (tec/asset_admin): cierra formalmente el ticket (resuelto o baja) y asocia movimientos físicos.
     */
    public function canSolveTickets(): bool
    {
        return $this->hasRole(['subtec', 'tec', 'asset_admin', 'user_admin', 'app_admin']);
    }

    public function canCloseTickets(): bool
    {
        return $this->hasRole(['tec', 'asset_admin', 'user_admin', 'app_admin']);
    }

    public function canAssociateMovementsToTicket(): bool
    {
        return $this->hasRole(['tec', 'asset_admin', 'user_admin', 'app_admin']);
    }

    /**
     * Determina si el usuario tiene al menos un rol operativo más allá de registered.
     * Si posee cualquier rol (rev, subtec, tec, asset_admin, user_admin, app_admin), ingresa al panel y nunca ve bienvenida.
     */
    public function hasActivePermissions(): bool
    {
        if (! $this->activo) {
            return false;
        }

        return $this->hasRole(['rev', 'subtec', 'tec', 'asset_admin', 'user_admin', 'app_admin']);
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
