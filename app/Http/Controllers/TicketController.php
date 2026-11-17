<?php

namespace App\Http\Controllers;

use App\Actions\Tickets\AbrirTicketAction;
use App\Actions\Tickets\ResolverTicketAction;
use App\Http\Requests\StoreTicketRequest;
use App\Models\AmbitoTicket;
use App\Models\Area;
use App\Models\Elemento;
use App\Models\Movimiento;
use App\Models\TicketComentario;
use App\Models\TicketComentarioAdjunto;
use App\Models\Ubicacion;
use App\Services\ImageSanitizer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

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
        $isTec = $user->hasRole('tec') || $user->hasRole('user_admin') || $user->hasRole('asset_admin');

        $elemento = null;
        if ($request->filled('elemento_id')) {
            $elemento = Elemento::with(['tipo', 'ultimoMovimiento.ubicacion'])->find($request->elemento_id);
        } elseif ($request->filled('nro_lia')) {
            $elemento = Elemento::with(['tipo', 'ultimoMovimiento.ubicacion'])->where('nro_lia', trim($request->nro_lia))->first();
        }

        $ubicaciones = Ubicacion::orderBy('nombre')->get();
        $areas = Area::where('activo', true)->orderBy('nombre')->get();
        $ambitos = AmbitoTicket::activos()->get();
        $solicitantesFrecuentes = Movimiento::whereNotNull('solicitante')
            ->where('solicitante', '!=', '')
            ->select('solicitante')
            ->selectRaw('COUNT(*) as total_solicitudes')
            ->groupBy('solicitante')
            ->orderByDesc('total_solicitudes')
            ->limit(25)
            ->pluck('solicitante');

        return view('tickets.create', compact('informante', 'isTec', 'elemento', 'ubicaciones', 'areas', 'solicitantesFrecuentes', 'ambitos'));
    }

    /**
     * Registra el movimiento inicial de reparación y sus adjuntos fotográficos.
     */
    public function store(StoreTicketRequest $request, AbrirTicketAction $abrirTicketAction): RedirectResponse
    {
        $validated = $request->validated();

        $user = Auth::user();
        $isTec = $user->hasRole(['tec', 'subtec', 'user_admin', 'item_admin', 'asset_admin']);

        // Para rol Revisor (solo lectura), se bloquea cualquier alteración física de ubicación
        if (! $isTec) {
            $validated['tipo_traslado'] = 'insitu';
            unset($validated['ubicacion_id'], $validated['puesto']);
        }

        // Para rol Revisor, el solicitante es estrictamente el usuario logueado
        $solicitante = $isTec
            ? (trim((string) $request->input('solicitante')) ?: ($user->nombre ?: $user->email))
            : ($user->nombre ?: $user->email);

        $validated['solicitante'] = $solicitante;
        $fotos = $request->file('fotos') ?? [];

        $elementoId = ! empty($validated['elemento_id']) ? (int) $validated['elemento_id'] : null;

        try {
            $ticket = $abrirTicketAction->execute($elementoId, $validated, $user, $fotos);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        $elemento = $ticket->elemento;

        if ($user->hasRole('tec')) {
            $msg = $elemento
                ? "Ticket #{$ticket->id} iniciado correctamente para el activo {$elemento->nro_lia}."
                : "Ticket general de infraestructura #{$ticket->id} registrado correctamente.";

            return redirect()->route('tickets.show', $ticket->id)
                ->with('success', $msg);
        }

        $msg = $elemento
            ? "Ticket #{$ticket->id} registrado correctamente. El equipo técnico ha sido notificado."
            : "Incidencia general de infraestructura #{$ticket->id} reportada con éxito.";

        return redirect()->route('dashboard.index')
            ->with('success', $msg);
    }

    /**
     * Bandeja de Tickets para usuarios con rol Técnico.
     */
    public function index(Request $request): View
    {
        $estado = $request->input('estado', 'abierto');

        $query = Movimiento::tickets()->with(['elemento.tipo', 'ambitoTicket', 'ubicacion', 'usuario', 'adjuntos', 'areas']);

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
                    ->orWhereHas('ambitoTicket', function ($aq) use ($term) {
                        $aq->where('nombre', 'like', "%{$term}%")
                            ->orWhere('descripcion', 'like', "%{$term}%");
                    })
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
     * Endpoint API para autocompletado y búsqueda rápida en vivo de tickets.
     * Retorna máximo 5 coincidencias para el desplegable interactivo.
     */
    public function apiBuscar(Request $request): JsonResponse
    {
        $term = trim((string) $request->get('q', ''));
        if (strlen($term) < 1) {
            return response()->json([]);
        }

        // Si el término incluye prefijos como '#' o 'TCK-', normalizar para buscar por ID numérico
        $cleanId = ltrim(preg_replace('/^tck-?/i', '', $term) ?? '', '#');

        $query = Movimiento::tickets()
            ->with(['elemento.tipo', 'ambitoTicket', 'ubicacion'])
            ->where(function ($q) use ($term, $cleanId) {
                if (is_numeric($cleanId) && (int) $cleanId > 0) {
                    $q->orWhere('id', (int) $cleanId);
                }
                $q->orWhere('comentario', 'like', "%{$term}%")
                    ->orWhere('solicitante', 'like', "%{$term}%")
                    ->orWhereHas('ambitoTicket', function ($aq) use ($term) {
                        $aq->where('nombre', 'like', "%{$term}%")
                            ->orWhere('descripcion', 'like', "%{$term}%");
                    })
                    ->orWhereHas('elemento', function ($eq) use ($term) {
                        $eq->where('nro_lia', 'like', "%{$term}%")
                            ->orWhere('nro_unsj', 'like', "%{$term}%")
                            ->orWhere('marca', 'like', "%{$term}%")
                            ->orWhere('modelo', 'like', "%{$term}%");
                    });
            })
            ->orderBy('id', 'desc')
            ->limit(5);

        $tickets = $query->get()->map(function (Movimiento $t) {
            $esGeneral = $t->esTicketGeneral();
            $ambitoNombre = $t->ambitoTicket?->nombre ?? 'Infraestructura General';

            $activoTexto = null;
            if (! $esGeneral && $t->elemento) {
                $el = $t->elemento;
                $nro = $el->nro_lia ?: ($el->nro_unsj ?: 'S/N');
                $tipo = $el->tipo?->nombre ? ucfirst($el->tipo->nombre) : 'Equipo';
                $activoTexto = "{$nro} • {$tipo} {$el->marca} {$el->modelo}";
            }

            $estadoLabel = match ($t->estado_ticket) {
                'resuelto' => 'Resuelto',
                'baja' => 'Baja',
                'solucionado' => 'Solución Propuesta',
                default => 'Abierto',
            };

            return [
                'id' => $t->id,
                'codigo' => '#'.str_pad((string) $t->id, 4, '0', STR_PAD_LEFT),
                'asunto' => Str::limit($t->comentario, 65),
                'solicitante' => $t->solicitante ?: 'Sin solicitante',
                'estado' => $t->estado_ticket ?: 'abierto',
                'estado_label' => $estadoLabel,
                'es_general' => $esGeneral,
                'ambito_nombre' => $ambitoNombre,
                'activo_info' => $activoTexto,
                'url' => route('tickets.show', $t->id),
            ];
        });

        return response()->json($tickets);
    }

    /**
     * Vista de detalle y gestión de un Ticket específico.
     */
    public function show(int $id): View
    {
        $ticket = Movimiento::with([
            'elemento.tipo',
            'elemento.ultimoMovimiento.ubicacion',
            'ambitoTicket',
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

        if (! $ticket->esTicket()) {
            abort(404, 'El movimiento patrimonial indicado no corresponde a un ticket de soporte técnico.');
        }

        // Movimiento previo al ingreso del ticket para heredar ubicación original (solo si aplica a activo)
        $movPrevio = $ticket->elemento_id ? Movimiento::where('elemento_id', $ticket->elemento_id)
            ->where('id', '<', $ticket->id)
            ->where('tipo_movimiento', '!=', 'reparacion')
            ->orderBy('id', 'desc')
            ->first() : null;

        // Último préstamo registrado para contexto de auditoría operativa (solo si aplica a activo)
        $ultimoPrestamo = $ticket->elemento_id ? Movimiento::where('elemento_id', $ticket->elemento_id)
            ->whereNotNull('fecha_prestamo')
            ->orderBy('id', 'desc')
            ->first() : null;

        $ubicaciones = Ubicacion::orderBy('nombre')->get();
        $areas = Area::where('activo', true)->orderBy('nombre')->get();

        return view('tickets.show', compact('ticket', 'movPrevio', 'ultimoPrestamo', 'ubicaciones', 'areas'));
    }

    /**
     * Exporta el informe técnico oficial del ticket en formato PDF con adjuntos en Base64.
     */
    public function exportarPdf(int $id): Response
    {
        $ticket = Movimiento::with([
            'elemento.tipo',
            'ambitoTicket',
            'ubicacion',
            'usuario',
            'adjuntos',
            'resoluciones.usuario',
            'resoluciones.ubicacion',
            'comentarios.usuario',
            'comentarios.adjuntos',
            'areas',
        ])->findOrFail($id);

        if (! $ticket->esTicket()) {
            abort(404, 'El movimiento indicado no corresponde a un ticket de soporte técnico.');
        }

        // Procesar imágenes a Base64 para garantizar renderizado seguro en el motor Dompdf sin peticiones loopback
        $fotosBase64 = [];

        // 1. Fotos adjuntas al ticket inicial
        foreach ($ticket->adjuntos as $adj) {
            $path = Storage::disk('local')->path($adj->archivo_path);
            if (file_exists($path)) {
                $mime = $adj->mime_type ?: (mime_content_type($path) ?: 'image/jpeg');
                $data = base64_encode((string) file_get_contents($path));
                $fotosBase64[] = [
                    'nombre' => $adj->nombre_original,
                    'contexto' => 'Apertura',
                    'data_uri' => "data:{$mime};base64,{$data}",
                ];
            }
        }

        // 2. Fotos adjuntas en comentarios del hilo
        foreach ($ticket->comentarios as $com) {
            foreach ($com->adjuntos as $adj) {
                $path = Storage::disk('local')->path($adj->archivo_path);
                if (file_exists($path)) {
                    $mime = $adj->mime_type ?: (mime_content_type($path) ?: 'image/jpeg');
                    $data = base64_encode((string) file_get_contents($path));
                    $fotosBase64[] = [
                        'nombre' => $adj->nombre_original,
                        'contexto' => 'Comentario #'.$com->id,
                        'data_uri' => "data:{$mime};base64,{$data}",
                    ];
                }
            }
        }

        $pdf = Pdf::loadView('tickets.pdf', compact('ticket', 'fotosBase64'))
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        $fileName = 'ticket-'.str_pad((string) $ticket->id, 5, '0', STR_PAD_LEFT).'-informe.pdf';

        return $pdf->download($fileName);
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
            'fotos' => 'nullable|array|max:5',
            'fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $ticket = Movimiento::findOrFail($id);
        if (! $ticket->esTicket()) {
            abort(404, 'El movimiento patrimonial indicado no corresponde a un ticket de soporte técnico.');
        }
        $user = Auth::user();

        $movimientoGenerado = null;
        if ($user->hasRole('tec') && $request->boolean('generar_movimiento') && ! empty($validated['ubicacion_id'])) {
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

        $comentario = TicketComentario::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'movimiento_id' => $movimientoGenerado?->id,
            'mensaje' => $validated['mensaje'],
            'tipo' => 'comentario',
        ]);

        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $foto) {
                if ($foto && $foto->isValid()) {
                    $path = ImageSanitizer::sanitizeAndStore($foto, 'ticket_hilos', 'local');
                    TicketComentarioAdjunto::create([
                        'ticket_comentario_id' => $comentario->id,
                        'archivo_path' => $path,
                        'nombre_original' => $foto->getClientOriginalName(),
                        'mime_type' => $foto->getMimeType(),
                    ]);
                }
            }
        }

        if (! empty($validated['nueva_area_id'])) {
            $ticket->areas()->syncWithoutDetaching([
                $validated['nueva_area_id'] => [
                    'user_id' => $user->id,
                    'created_at' => now(),
                ],
            ]);
        }

        $msg = $movimientoGenerado
            ? 'Mensaje publicado y movimiento de inventario registrado con éxito.'
            : 'Mensaje registrado en la bitácora del ticket.';

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
        if (! $ticket->esTicket()) {
            abort(404, 'El movimiento patrimonial indicado no corresponde a un ticket de soporte técnico.');
        }
        $user = Auth::user();

        $ticket->areas()->syncWithoutDetaching([
            $validated['area_id'] => [
                'user_id' => $user->id,
                'created_at' => now(),
            ],
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
        if (! $user->canSolveTickets()) {
            abort(403, 'El rol Revisor no posee privilegios para solucionar técnicamente un ticket.');
        }

        $validated = $request->validate([
            'solucion' => 'required|string|min:5',
            'nueva_area_id' => 'nullable|exists:areas,id',
            'fotos' => 'nullable|array|max:5',
            'fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        try {
            DB::transaction(function () use ($id, $validated, $user, $request) {
                $ticket = Movimiento::where('id', $id)->lockForUpdate()->firstOrFail();

                if (! $ticket->esTicket()) {
                    abort(404, 'El movimiento patrimonial indicado no corresponde a un ticket de soporte técnico.');
                }

                if ($ticket->estado_ticket === 'resuelto' || $ticket->estado_ticket === 'baja') {
                    throw ValidationException::withMessages([
                        'solucion' => 'El ticket ya ha sido finalizado previamente.',
                    ]);
                }

                $ticket->estado_ticket = 'solucionado';
                $ticket->save();

                if (! empty($validated['nueva_area_id'])) {
                    $ticket->areas()->syncWithoutDetaching([
                        $validated['nueva_area_id'] => [
                            'user_id' => $user->id,
                            'created_at' => now(),
                        ],
                    ]);
                }

                $comentario = TicketComentario::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'mensaje' => 'SOLUCIÓN TÉCNICA PROPUESTA / EJECUTADA: '.$validated['solucion'],
                    'tipo' => 'solucion_propuesta',
                ]);

                if ($request->hasFile('fotos')) {
                    foreach ($request->file('fotos') as $foto) {
                        if ($foto && $foto->isValid()) {
                            $path = ImageSanitizer::sanitizeAndStore($foto, 'ticket_hilos', 'local');
                            TicketComentarioAdjunto::create([
                                'ticket_comentario_id' => $comentario->id,
                                'archivo_path' => $path,
                                'nombre_original' => $foto->getClientOriginalName(),
                                'mime_type' => $foto->getMimeType(),
                            ]);
                        }
                    }
                }
            });
        } catch (ValidationException $e) {
            return redirect()->route('tickets.show', $id)->with('error', $e->getMessage());
        }

        return redirect()->route('tickets.show', $id)
            ->with('success', 'Solución técnica registrada. Pendiente de cierre formal y reasignación física por un Técnico.');
    }

    /**
     * Resuelve un ticket técnico mediante un nuevo movimiento (Append-Only)
     * o lo declara irreparable pasándolo a baja (Basura Tecnológica).
     * Exclusivo para Técnico (tec) y asset_admin. Rev y SubTec están bloqueados.
     */
    public function resolver(Request $request, int $id, ResolverTicketAction $resolverTicketAction): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->canCloseTickets()) {
            abort(403, 'Únicamente el personal Técnico o Administrador de Ítems puede cerrar formalmente un ticket y asociar movimientos de inventario.');
        }

        $validated = $request->validate([
            'accion' => 'required|in:resolver,baja',
            'solucion' => 'required|string|min:5',
            'ubicacion_id' => 'nullable|exists:ubicaciones,id',
            'puesto' => 'nullable|string|max:50',
        ]);

        $ticket = Movimiento::findOrFail($id);
        if (! $ticket->esTicket()) {
            abort(404, 'El movimiento patrimonial indicado no corresponde a un ticket de soporte técnico.');
        }

        try {
            $movimientoResolucion = $resolverTicketAction->execute($id, $validated, $user);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        if ($ticket->esTicketGeneral()) {
            $msg = "Incidencia general #{$id} resuelta y cerrada con éxito.";
        } elseif ($validated['accion'] === 'baja') {
            $nroLia = $movimientoResolucion->elemento?->nro_lia ?? 'reportado';
            $msg = "El activo {$nroLia} ha sido declarado irreparable y registrado como Basura Tecnológica / De Baja.";
        } else {
            $msg = "Ticket #{$id} resuelto con éxito y activo reintegrado a la sala correspondiente.";
        }

        return redirect()->route('tickets.index')->with('success', $msg);
    }
}
