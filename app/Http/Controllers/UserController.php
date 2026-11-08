<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Carrera;
use App\Models\Cargo;
use App\Models\UsuarioTrayectoria;
use App\Models\Movimiento;
use App\Models\ReporteConfigurado;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public const CARGOS_ESTANDAR = [
        'Director',
        'Secretaria',
        'Coordinador de Área',
        'JTP',
        'Ayudante 1ra',
        'Ayudante 2da',
        'Becario',
        'Pasante',
    ];

    public function index(Request $request): View
    {
        $query = Usuario::with(['roles', 'trayectorias'])->orderBy('id', 'desc');

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhere('dni', 'like', "%{$buscar}%");
            });
        }

        $users = $query->paginate(20)->withQueryString();
        $roles = Rol::all();
        $exColaboradoresElegibles = Usuario::elegiblesBorradoHuella();
        return view('users.index', compact('users', 'roles', 'exColaboradoresElegibles'));
    }

    public function edit(Usuario $user): View
    {
        $roles = Rol::all();
        $carreras = Carrera::orderBy('nombre')->get();
        $cargos = Cargo::activos()->orderBy('orden')->orderBy('nombre')->get();
        return view('users.edit', compact('user', 'roles', 'carreras', 'cargos'));
    }

    public function update(Request $request, Usuario $user): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:usuarios,email,' . $user->id,
            'dni' => 'nullable|string|max:15|unique:usuarios,dni,' . $user->id,
            'fecha_nacimiento' => 'nullable|date',
            'carrera_id' => 'nullable|exists:carreras,id',
            'cargo_id' => 'nullable|exists:cargos,id',
            'whatsapp' => 'nullable|string|max:30',
            'direccion' => 'nullable|string|max:255',
            'activo' => 'nullable|boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $validated['activo'] = $request->has('activo');

        $user->update($validated);

        if ($request->has('roles')) {
            $rolesInput = $request->input('roles', []);
            $appAdminRol = Rol::where('rol', 'app_admin')->first();
            $actor = auth()->user();

            // Blindaje anti-escalada: solo un app_admin puede otorgar o revocar el rol app_admin
            if ($appAdminRol && (!$actor || !$actor->isAppAdmin())) {
                $teniaAppAdmin = $user->hasRole('app_admin');
                $quiereAppAdmin = in_array($appAdminRol->id, $rolesInput);
                if ($teniaAppAdmin !== $quiereAppAdmin) {
                    abort(403, 'Solo un administrador general (app_admin) puede asignar o remover el rol app_admin.');
                }
            }

            // Prevenir que un user_admin modifique sus propios roles en update masivo
            if ($actor && $user->id === $actor->id && !$actor->isAppAdmin()) {
                $rolesInput = $user->roles()->pluck('roles.id')->toArray();
            }

            $rolesAntes = $user->roles()->pluck('rol')->toArray();
            $user->roles()->sync($rolesInput);
            $rolesDespues = $user->fresh()->roles()->pluck('rol')->toArray();

            if ($rolesAntes != $rolesDespues) {
                Log::info("AUDITORIA_ROLES: Sincronización de roles en actualización de usuario", [
                    'actor_id' => $actor?->id,
                    'actor_email' => $actor?->email,
                    'target_id' => $user->id,
                    'target_email' => $user->email,
                    'roles_anteriores' => $rolesAntes,
                    'roles_nuevos' => $rolesDespues,
                    'ip' => $request->ip(),
                ]);
            }
        }

        return redirect()->route('users.index')->with('success', 'Usuario y perfil actualizados correctamente.');
    }

    public function toggleRole(Request $request, Usuario $user): JsonResponse
    {
        $request->validate([
            'role' => 'required|exists:roles,rol'
        ]);

        $roleName = $request->role;
        $role = Rol::where('rol', $roleName)->firstOrFail();
        $actor = auth()->user();

        // 1. Solo app_admin puede tocar el rol app_admin
        if ($roleName === 'app_admin' && (!$actor || !$actor->isAppAdmin())) {
            return response()->json([
                'message' => 'Solo un administrador general (app_admin) puede asignar o revocar el rol app_admin.',
                'status' => 'error'
            ], 403);
        }

        // 2. Prevenir autoasignación o alteración de roles propios por parte de user_admin
        if ($actor && $user->id === $actor->id && !$actor->isAppAdmin()) {
            return response()->json([
                'message' => 'No tienes permisos para modificar tus propios roles.',
                'status' => 'error'
            ], 403);
        }

        // 3. Evitar que el administrador se auto-despoje del rol user_admin si es el único
        if ($roleName === 'user_admin' && $user->id === $actor?->id && $user->hasRole('user_admin')) {
            return response()->json(['message' => 'No puedes remover tu propio rol de user_admin.', 'status' => 'error'], 422);
        }

        if ($user->hasRole($roleName)) {
            $user->roles()->detach($role->id);
            $message = "Rol {$roleName} removido correctamente.";
            $status = 'removed';
        } else {
            $user->roles()->attach($role->id);
            $message = "Rol {$roleName} asignado correctamente.";
            $status = 'assigned';
        }

        Log::info("AUDITORIA_ROLES: Cambio individual de rol", [
            'actor_id' => $actor?->id,
            'actor_email' => $actor?->email,
            'target_id' => $user->id,
            'target_email' => $user->email,
            'rol' => $roleName,
            'accion' => $status,
            'ip' => $request->ip(),
        ]);

        return response()->json(['message' => $message, 'status' => $status]);
    }

    public function toggleActivo(Request $request, Usuario $user): RedirectResponse
    {
        $user->activo = !$user->activo;
        $user->save();

        if (!$user->activo) {
            // Revocar tokens Sanctum activos
            $user->tokens()->delete();

            // Purgar sesiones persistidas en base de datos si existe la tabla
            if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
                \Illuminate\Support\Facades\DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->delete();
            }

            // Si el usuario desactivó su propia cuenta, cerrar sesión inmediatamente
            if (auth()->id() === $user->id) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['login' => 'Has desactivado tu propia cuenta. Tu sesión ha finalizado.']);
            }
        }

        $estado = $user->activo ? 'activada' : 'desactivada';
        return redirect()->route('users.index')->with('success', "Cuenta del usuario {$user->nombre} {$estado}.");
    }

    public function trayectoria(Usuario $user): View
    {
        $user->load('trayectorias');
        $cargos = Cargo::activos()->orderBy('orden')->orderBy('nombre')->get();
        return view('users.trayectoria', compact('user', 'cargos'));
    }

    public function storeTrayectoria(Request $request, Usuario $user): RedirectResponse
    {
        $validated = $request->validate([
            'cargo' => 'required|string|max:100',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'notas' => 'nullable|string',
        ]);

        $user->trayectorias()->create($validated);

        return redirect()->route('users.trayectoria', $user->id)
            ->with('success', 'Periodo de trayectoria registrado con éxito.');
    }

    public function destroyTrayectoria(UsuarioTrayectoria $trayectoria): RedirectResponse
    {
        $userId = $trayectoria->user_id;
        $trayectoria->delete();

        return redirect()->route('users.trayectoria', $userId)
            ->with('success', 'Periodo de trayectoria eliminado.');
    }

    /**
     * Aplica el derecho al olvido y borrado irreversible de huella digital (Ley 25.326)
     * a personal desvinculado con antigüedad mayor a 6 años, preservando integridad relacional.
     */
    public function anonimizar(Usuario $user): RedirectResponse
    {
        if ($user->activo) {
            return redirect()->route('users.index')
                ->with('error', 'No se puede anonimizar un usuario que se encuentra activo en el sistema.');
        }

        $id = $user->id;
        $user->anonimizarHuellaDigital();

        return redirect()->route('users.index')
            ->with('success', "Se ha borrado la huella digital del usuario #{$id} conforme a la Ley 25.326. Sus datos personales han sido anonimizados de forma irreversible.");
    }

    /**
     * Informe consolidado de actividad, auditoría y trazabilidad por usuario (Órbita User Admin).
     * Estructurado en 3 apartados: Tickets, Reportes y Movimientos de inventario.
     */
    public function reporte(Usuario $user): View
    {
        $user->load(['roles', 'trayectorias']);

        // 1. Trazabilidad en Tickets (Aperturas, Diagnósticos y Resoluciones)
        $ticketsCreados = Movimiento::tickets()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('solicitante', $user->nombre);
            })
            ->with(['elemento.tipo', 'ubicacion'])
            ->orderBy('id', 'desc')
            ->get();

        $ticketComentarios = \App\Models\TicketComentario::where('user_id', $user->id)
            ->with(['ticket.elemento.tipo', 'ticket.ubicacion'])
            ->orderBy('id', 'desc')
            ->get();

        $ticketResoluciones = Movimiento::where('user_id', $user->id)
            ->whereNotNull('ticket_id')
            ->with(['elemento.tipo', 'ubicacion'])
            ->orderBy('id', 'desc')
            ->get();

        // 2. Actividad en Catálogo y Configuración de Reportes
        $reportesConfigurados = ReporteConfigurado::where('creado_por', $user->id)
            ->orderBy('id', 'desc')
            ->get();

        // 3. Trazabilidad en Movimientos de Inventario
        $movimientos = Movimiento::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('solicitante', $user->nombre);
            })
            ->with(['elemento.tipo', 'ubicacion'])
            ->orderBy('id', 'desc')
            ->get();

        $stats = [
            'total_tickets' => $ticketsCreados->count() + $ticketComentarios->count() + $ticketResoluciones->count(),
            'total_reportes' => $reportesConfigurados->count(),
            'total_movimientos' => $movimientos->count(),
            'prestamos_activos' => $movimientos->where('tipo_movimiento', 'externo_prestamo')->whereNull('fecha_devolucion')->count(),
        ];

        return view('users.reporte', compact(
            'user',
            'ticketsCreados',
            'ticketComentarios',
            'ticketResoluciones',
            'reportesConfigurados',
            'movimientos',
            'stats'
        ));
    }
}
