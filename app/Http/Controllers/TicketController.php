<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Movimiento;
use App\Models\MovimientoAdjunto;
use App\Models\Elemento;
use App\Models\Ubicacion;
use App\Models\Area;

class TicketController extends Controller
{
    /**
     * Formulario de apertura de nuevo ticket de reparación.
     * Accesible tanto para Técnicos (tec) como Revisores (rev).
     */
    public function create(Request $request): View
    {
        $user = Auth::user();
        $informante = $user->nombre ?: $user->email;
        $isTec = $user->hasRole('tec') || $user->hasRole('user_admin') || $user->hasRole('item_admin');

        $elemento = null;
        if ($request->filled('elemento_id')) {
            $elemento = Elemento::with(['tipo', 'ultimoMovimiento.ubicacion'])->find($request->elemento_id);
        } elseif ($request->filled('nro_lia')) {
            $elemento = Elemento::with(['tipo', 'ultimoMovimiento.ubicacion'])->where('nro_lia', trim($request->nro_lia))->first();
        }

        $ubicaciones = Ubicacion::orderBy('nombre')->get();
        $areas = Area::where('activo', true)->orderBy('nombre')->get();
        $solicitantesFrecuentes = Movimiento::whereNotNull('solicitante')
            ->where('solicitante', '!=', '')
            ->distinct()
            ->orderBy('solicitante')
            ->limit(25)
            ->pluck('solicitante');

        return view('tickets.create', compact('informante', 'isTec', 'elemento', 'ubicaciones', 'areas', 'solicitantesFrecuentes'));
    }

    /**
     * Registra el movimiento inicial de reparación y sus adjuntos fotográficos.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'elemento_id' => 'required|exists:elementos,id',
            'area_id' => 'nullable|exists:areas,id',
            'comentario' => 'required|string|min:5',
            'solicitante' => 'nullable|string|max:150',
            'tipo_traslado' => 'nullable|in:insitu,taller',
            'ubicacion_id' => 'nullable|exists:ubicaciones,id',
            'puesto' => 'nullable|string|max:50',
            'fotos' => 'nullable|array',
            'fotos.*' => 'nullable|image|max:10240', // hasta 10MB por imagen
        ]);

        $user = Auth::user();
        $isTec = $user->hasRole('tec') || $user->hasRole('user_admin') || $user->hasRole('item_admin');

        // Para rol Revisor, el solicitante es estrictamente el usuario logueado
        $solicitante = $isTec
            ? (trim((string)$request->input('solicitante')) ?: ($user->nombre ?: $user->email))
            : ($user->nombre ?: $user->email);

        $elemento = Elemento::with('ultimoMovimiento')->findOrFail($validated['elemento_id']);

        // Ubicación: si permanece in situ o no se selecciona destino, hereda ubicación y puesto original
        $esInsitu = ($validated['tipo_traslado'] ?? 'insitu') === 'insitu';
        $ubicacionId = $esInsitu
            ? $elemento->ultimoMovimiento?->ubicacion_id
            : ($validated['ubicacion_id'] ?? $elemento->ultimoMovimiento?->ubicacion_id);
        $puesto = $esInsitu
            ? $elemento->ultimoMovimiento?->puesto
            : ($validated['puesto'] ?? ($validated['ubicacion_id'] ? null : $elemento->ultimoMovimiento?->puesto));

        $ticket = DB::transaction(function () use ($validated, $elemento, $user, $solicitante, $ubicacionId, $puesto, $request) {
            $movimiento = Movimiento::create([
                'elemento_id' => $elemento->id,
                'tipo_movimiento' => 'reparacion',
                'estado_ticket' => 'abierto',
                'ubicacion_id' => $ubicacionId,
                'puesto' => $puesto,
                'user_id' => $user->id,
                'fecha' => now(),
                'solicitante' => $solicitante,
                'comentario' => $validated['comentario'],
            ]);

            if (!empty($validated['area_id'])) {
                $movimiento->areas()->attach($validated['area_id'], [
                    'user_id' => $user->id,
                    'created_at' => now(),
                ]);
            }

            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $foto) {
                    if ($foto && $foto->isValid()) {
                        $path = $foto->store('tickets', 'public');
                        MovimientoAdjunto::create([
                            'movimiento_id' => $movimiento->id,
                            'archivo_path' => $path,
                            'nombre_original' => $foto->getClientOriginalName(),
                            'mime_type' => $foto->getMimeType(),
                        ]);
                    }
                }
            }

            return $movimiento;
        });

        if ($user->hasRole('tec')) {
            return redirect()->route('tickets.show', $ticket->id)
                ->with('success', "Ticket #{$ticket->id} iniciado correctamente para el activo {$elemento->nro_lia}.");
        }

        return redirect()->route('dashboard.index')
            ->with('success', "Ticket #{$ticket->id} registrado correctamente. El equipo técnico ha sido notificado.");
    }

    /**
     * Bandeja de Tickets para usuarios con rol Técnico.
     */
    public function index(Request $request): View
    {
        $estado = $request->input('estado', 'abierto');

        $query = Movimiento::tickets()->with(['elemento.tipo', 'ubicacion', 'usuario', 'adjuntos', 'areas']);

        if ($estado === 'resuelto') {
            $query->where('estado_ticket', 'resuelto');
        } elseif ($estado === 'baja') {
            $query->where('estado_ticket', 'baja');
        } elseif ($estado === 'todos') {
            // todos los tickets
        } else {
            $query->ticketsAbiertos();
        }

        if ($request->filled('area_id')) {
            $query->whereHas('areas', function ($q) use ($request) {
                $q->where('areas.id', $request->area_id);
            });
        }

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('comentario', 'like', "%{$term}%")
                  ->orWhere('solicitante', 'like', "%{$term}%")
                  ->orWhereHas('elemento', function ($eq) use ($term) {
                      $eq->where('nro_lia', 'like', "%{$term}%")
                         ->orWhere('nro_unsj', 'like', "%{$term}%")
                         ->orWhere('marca', 'like', "%{$term}%")
                         ->orWhere('modelo', 'like', "%{$term}%");
                  });
            });
        }

        $tickets = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $areas = Area::where('activo', true)->orderBy('nombre')->get();
        $conteoAbiertos = Movimiento::ticketsAbiertos()->count();
        $conteoResueltos = Movimiento::tickets()->where('estado_ticket', 'resuelto')->count();
        $conteoBajas = Movimiento::tickets()->where('estado_ticket', 'baja')->count();
        $conteoTodos = Movimiento::tickets()->count();

        return view('tickets.index', compact('tickets', 'estado', 'areas', 'conteoAbiertos', 'conteoResueltos', 'conteoBajas', 'conteoTodos'));
    }

    /**
     * Vista de detalle y gestión de un Ticket específico.
     */
    public function show(int $id): View
    {
        $ticket = Movimiento::with([
            'elemento.tipo',
            'elemento.ultimoMovimiento.ubicacion',
            'ubicacion',
            'usuario',
            'adjuntos',
            'resoluciones.usuario',
            'resoluciones.ubicacion',
            'comentarios.usuario.roles',
            'comentarios.adjuntos',
            'comentarios.movimiento.ubicacion',
            'areas',
        ])->findOrFail($id);

        // Movimiento previo al ingreso del ticket para heredar ubicación original
        $movPrevio = Movimiento::where('elemento_id', $ticket->elemento_id)
            ->where('id', '<', $ticket->id)
            ->where('tipo_movimiento', '!=', 'reparacion')
            ->orderBy('id', 'desc')
            ->first();

        // Último préstamo registrado para contexto de auditoría operativa
        $ultimoPrestamo = Movimiento::where('elemento_id', $ticket->elemento_id)
            ->whereNotNull('fecha_prestamo')
            ->orderBy('id', 'desc')
            ->first();

        $ubicaciones = Ubicacion::orderBy('nombre')->get();
        $areas = Area::where('activo', true)->orderBy('nombre')->get();

        return view('tickets.show', compact('ticket', 'movPrevio', 'ultimoPrestamo', 'ubicaciones', 'areas'));
    }

    /**
     * Añade un mensaje o respuesta al hilo de discusión y seguimiento del ticket.
     * Accesible para Revisor (rev), SubTécnico (subtec) y Técnico (tec).
     */
    public function comentar(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'mensaje' => 'required|string|min:3',
            'nueva_area_id' => 'nullable|exists:areas,id',
            'generar_movimiento' => 'nullable|boolean',
            'ubicacion_id' => 'nullable|exists:ubicaciones,id',
            'puesto' => 'nullable|string|max:50',
            'fotos' => 'nullable|array',
            'fotos.*' => 'nullable|image|max:10240',
        ]);

        $ticket = Movimiento::findOrFail($id);
        $user = Auth::user();

        $movimientoGenerado = null;
        if ($user->hasRole('tec') && $request->boolean('generar_movimiento') && !empty($validated['ubicacion_id'])) {
            $movimientoGenerado = Movimiento::create([
                'elemento_id' => $ticket->elemento_id,
                'tipo_movimiento' => 'interno',
                'ubicacion_id' => $validated['ubicacion_id'],
                'puesto' => $validated['puesto'] ?? null,
                'user_id' => $user->id,
                'fecha' => now(),
                'ticket_id' => $ticket->id,
                'comentario' => "Traslado operativo vinculado a Ticket #{$ticket->id}: {$validated['mensaje']}",
            ]);
        }

        $comentario = \App\Models\TicketComentario::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'movimiento_id' => $movimientoGenerado?->id,
            'mensaje' => $validated['mensaje'],
            'tipo' => 'comentario',
        ]);

        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $foto) {
                if ($foto && $foto->isValid()) {
                    $path = $foto->store('ticket_hilos', 'public');
                    \App\Models\TicketComentarioAdjunto::create([
                        'ticket_comentario_id' => $comentario->id,
                        'archivo_path' => $path,
                        'nombre_original' => $foto->getClientOriginalName(),
                        'mime_type' => $foto->getMimeType(),
                    ]);
                }
            }
        }

        if (!empty($validated['nueva_area_id'])) {
            $ticket->areas()->syncWithoutDetaching([
                $validated['nueva_area_id'] => [
                    'user_id' => $user->id,
                    'created_at' => now(),
                ]
            ]);
        }

        $msg = $movimientoGenerado
            ? "Mensaje publicado y movimiento de inventario registrado con éxito."
            : "Mensaje registrado en la bitácora del ticket.";

        return redirect()->route('tickets.show', $ticket->id)->with('success', $msg);
    }

    /**
     * Permite asociar o acumular un área de especialidad adicional al ticket.
     */
    public function agregarArea(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'area_id' => 'required|exists:areas,id',
        ]);

        $ticket = Movimiento::findOrFail($id);
        $user = Auth::user();

        $ticket->areas()->syncWithoutDetaching([
            $validated['area_id'] => [
                'user_id' => $user->id,
                'created_at' => now(),
            ]
        ]);

        $area = Area::find($validated['area_id']);

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', "Área '{$area->nombre}' vinculada acumulativamente al ticket.");
    }

    /**
     * Registra la propuesta o diagnóstico de solución técnica al ticket.
     * Accesible para SubTécnico (subtec) y Técnico (tec). El Revisor (rev) NO puede solucionar.
     * REGLA ESTRICTA: El SubTec nunca registra movimientos ni cierra el ticket.
     */
    public function solucionar(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->canSolveTickets()) {
            abort(403, 'El rol Revisor no posee privilegios para solucionar técnicamente un ticket.');
        }

        $validated = $request->validate([
            'solucion' => 'required|string|min:5',
            'nueva_area_id' => 'nullable|exists:areas,id',
            'fotos' => 'nullable|array',
            'fotos.*' => 'nullable|image|max:10240',
        ]);

        $ticket = Movimiento::findOrFail($id);

        if ($ticket->estado_ticket === 'resuelto' || $ticket->estado_ticket === 'baja') {
            return redirect()->route('tickets.show', $ticket->id)
                ->with('error', 'El ticket ya ha sido finalizado previamente.');
        }

        $ticket->estado_ticket = 'solucionado';
        $ticket->save();

        if (!empty($validated['nueva_area_id'])) {
            $ticket->areas()->syncWithoutDetaching([
                $validated['nueva_area_id'] => [
                    'user_id' => $user->id,
                    'created_at' => now(),
                ]
            ]);
        }

        $comentario = \App\Models\TicketComentario::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'mensaje' => 'SOLUCIÓN TÉCNICA PROPUESTA / EJECUTADA: ' . $validated['solucion'],
            'tipo' => 'solucion_propuesta',
        ]);

        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $foto) {
                if ($foto && $foto->isValid()) {
                    $path = $foto->store('ticket_hilos', 'public');
                    \App\Models\TicketComentarioAdjunto::create([
                        'ticket_comentario_id' => $comentario->id,
                        'archivo_path' => $path,
                        'nombre_original' => $foto->getClientOriginalName(),
                        'mime_type' => $foto->getMimeType(),
                    ]);
                }
            }
        }

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Solución técnica registrada. Pendiente de cierre formal y reasignación física por un Técnico.');
    }

    /**
     * Resuelve un ticket técnico mediante un nuevo movimiento (Append-Only)
     * o lo declara irreparable pasándolo a baja (Basura Tecnológica).
     * Exclusivo para Técnico (tec) e item_admin. Rev y SubTec están bloqueados.
     */
    public function resolver(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->canCloseTickets()) {
            abort(403, 'Únicamente el personal Técnico o Administrador de Ítems puede cerrar formalmente un ticket y asociar movimientos de inventario.');
        }

        $validated = $request->validate([
            'accion' => 'required|in:resolver,baja',
            'solucion' => 'required|string|min:5',
            'ubicacion_id' => 'nullable|exists:ubicaciones,id',
            'puesto' => 'nullable|string|max:50',
        ]);

        $ticket = Movimiento::with('elemento')->findOrFail($id);

        DB::transaction(function () use ($ticket, $validated, $user) {
            $esBaja = ($validated['accion'] === 'baja');

            $tipoMov = $esBaja ? 'baja' : 'interno';
            $estadoTicket = $esBaja ? 'baja' : 'resuelto';

            $comentario = $esBaja
                ? "BAJA PATRIMONIAL (Irreparable - Ticket #{$ticket->id}): {$validated['solucion']}"
                : "RESOLUCIÓN TICKET #{$ticket->id}: {$validated['solucion']}";

            Movimiento::create([
                'elemento_id' => $ticket->elemento_id,
                'tipo_movimiento' => $tipoMov,
                'estado_ticket' => $estadoTicket,
                'ubicacion_id' => $validated['ubicacion_id'] ?? $ticket->ubicacion_id,
                'puesto' => $validated['puesto'] ?? $ticket->puesto,
                'user_id' => $user->id,
                'fecha' => now(),
                'ticket_id' => $ticket->id,
                'correccion_de_id' => $ticket->id,
                'comentario' => $comentario,
            ]);

            // Actualizar estado del ticket origen
            $ticket->estado_ticket = $estadoTicket;
            $ticket->save();
        });

        $msg = ($validated['accion'] === 'baja')
            ? "El activo {$ticket->elemento->nro_lia} ha sido declarado irreparable y registrado como Basura Tecnológica / De Baja."
            : "Ticket #{$ticket->id} resuelto con éxito y activo reintegrado a la sala correspondiente.";

        return redirect()->route('tickets.index')->with('success', $msg);
    }
}
