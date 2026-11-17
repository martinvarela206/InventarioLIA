@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Breadcrumb & Cabecera Superior -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                <a href="{{ route('dashboard.index') }}" class="hover:text-amber-600 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('tickets.index') }}" class="hover:text-amber-600 transition-colors">Tickets</a>
                <span>/</span>
                <span class="text-gray-900 font-bold">Ticket #{{ $ticket->id }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">
                    Ticket #{{ $ticket->id }}
                </h1>
                
                @if($ticket->estado_ticket === 'cerrado' || $ticket->estado_ticket === 'resuelto')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-gray-900 text-white shadow-2xs">
                        ✓ Cerrado
                    </span>
                @elseif($ticket->estado_ticket === 'baja')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                        ✕ Baja / Irreparable
                    </span>
                @elseif($ticket->estado_ticket === 'solucionado')
                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-900 border border-[#fbc101]">
                        ★ Solución Propuesta (Pendiente Cierre Asset Admin)
                    </span>
                @elseif($ticket->estado_ticket === 'en_proceso')
                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-blue-50 text-blue-900 border border-blue-300">
                        ⚙ En Proceso
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-amber-100 text-amber-900 border border-[#fbc101] animate-pulse">
                        ● Abierto
                    </span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('tickets.pdf', $ticket->id) }}" 
               class="inline-flex items-center gap-1.5 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold px-3.5 py-2 rounded-xl text-xs transition-all shadow-xs" 
               title="Descargar Informe Técnico en PDF">
                <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Descargar PDF</span>
            </a>
            <a href="{{ route('tickets.index') }}" 
               class="inline-flex items-center gap-1.5 bg-white hover:bg-amber-50/50 border border-gray-300 text-gray-700 font-bold px-4 py-2 rounded-xl text-xs transition-all shadow-2xs">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver a Bandeja</span>
            </a>
        </div>
    </div>

    <!-- Mensajes de Estado / Flash -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-300 text-emerald-900 rounded-2xl text-xs font-bold flex items-center justify-between shadow-2xs">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-300 text-rose-900 rounded-2xl text-xs font-bold shadow-2xs">
            {{ session('error') }}
        </div>
    @endif

    <!-- DISTRIBUCIÓN BOSQUEJO 2: 2 COLUMNAS (Marginal Izquierda + Principal Derecha) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- ========================================================================= -->
        <!-- COLUMNA IZQUIERDA: METADATOS (Marginal: 4 cols)                           -->
        <!-- ========================================================================= -->
        <div class="lg:col-span-4 space-y-4">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-xs p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                    <h2 class="text-xs font-black text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Metadatos del Ticket</span>
                    </h2>
                    <span class="text-[11px] font-mono font-bold text-gray-400">#{{ $ticket->id }}</span>
                </div>

                <!-- Activo o Ámbito General -->
                <div class="space-y-1">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Ámbito / Espacio Reportado</span>
                    @if($ticket->esTicketGeneral())
                        <div class="p-3 bg-amber-50/70 border border-amber-200/80 rounded-xl space-y-1">
                            <span class="inline-flex items-center gap-1 text-[10px] font-black text-amber-900 bg-amber-100 px-2 py-0.5 rounded-md uppercase">Infraestructura LIA</span>
                            <p class="text-sm font-black text-gray-900">{{ $ticket->ambitoTicket?->nombre ?? 'LIA General' }}</p>
                            @if($ticket->puesto)
                                <p class="text-xs text-gray-600">Sector / Ref: <strong class="text-gray-800">{{ $ticket->puesto }}</strong></p>
                            @endif
                        </div>
                    @else
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1 text-[10px] font-black text-blue-900 bg-blue-100 px-2 py-0.5 rounded-md uppercase">Activo Patrimonial</span>
                                <a href="{{ route('elementos.show', $ticket->elemento_id) }}" class="text-[11px] font-bold text-amber-700 hover:underline">Ver Ficha →</a>
                            </div>
                            <p class="text-sm font-black text-gray-900">
                                {{ $ticket->elemento?->nro_lia ?: 'Sin Nro LIA' }}
                                @if($ticket->elemento?->nro_unsj)
                                    <span class="text-xs font-normal text-gray-500">(UNSJ: {{ $ticket->elemento->nro_unsj }})</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-600">
                                {{ ucfirst($ticket->elemento?->tipo?->nombre ?? 'Equipo') }} • {{ $ticket->elemento?->marca }} {{ $ticket->elemento?->modelo }}
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Ubicación Física Actual -->
                <div class="space-y-1 pt-2 border-t border-gray-100">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Ubicación Física</span>
                    <p class="text-xs font-bold text-gray-800">
                        {{ $ticket->esTicketGeneral() ? ($ticket->ambitoTicket?->nombre ?? 'LIA General') : ($ticket->ubicacion?->nombre ?? 'Depósito') }}
                        @if($ticket->puesto)
                            <span class="text-gray-500 font-normal">({{ $ticket->puesto }})</span>
                        @endif
                    </p>
                </div>

                <!-- Autor / Informante -->
                <div class="space-y-1 pt-2 border-t border-gray-100">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Informante / Autor</span>
                    <p class="text-xs font-bold text-gray-800">
                        {{ $ticket->solicitante ?: ($ticket->usuario?->nombre ?? 'Sistema') }}
                    </p>
                    @if($ticket->usuario)
                        <span class="text-[10px] text-gray-400 block">Registrado por: {{ $ticket->usuario->nombre }}</span>
                    @endif
                </div>

                <!-- Fechas de Trazabilidad -->
                <div class="space-y-1 pt-2 border-t border-gray-100 text-xs">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Tiempos</span>
                    <p class="text-gray-700">
                        Apertura: <strong class="text-gray-900">{{ $ticket->fecha ? $ticket->fecha->format('d/m/Y H:i') : '-' }}</strong>
                    </p>
                    <p class="text-gray-500 text-[11px]">
                        Hace {{ $ticket->fecha ? $ticket->fecha->diffForHumans(null, true) : '-' }}
                    </p>
                </div>

                <!-- Áreas Asignadas (Multidisciplinario) -->
                <div class="space-y-2 pt-2 border-t border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Áreas Involucradas</span>
                        <span class="text-[10px] text-gray-400">({{ $ticket->areas->count() }})</span>
                    </div>

                    @if($ticket->areas->isEmpty())
                        <p class="text-xs text-gray-400 italic">Sin áreas vinculadas.</p>
                    @else
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($ticket->areas as $ar)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-black border"
                                      style="background-color: {{ $ar->color ? $ar->color . '20' : '#f3f4f6' }}; color: {{ $ar->color ?: '#374151' }}; border-color: {{ $ar->color ? $ar->color . '60' : '#d1d5db' }};">
                                    {{ $ar->nombre }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    @if($ticket->esTicketAbierto())
                        <form action="{{ route('tickets.agregarArea', $ticket->id) }}" method="POST" class="pt-2 flex items-center gap-1.5">
                            @csrf
                            <select name="area_id" required class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-2 py-1 text-xs text-gray-800">
                                <option value="">+ Sumar Área...</option>
                                @foreach($areas as $ar)
                                    @if(!$ticket->areas->contains('id', $ar->id))
                                        <option value="{{ $ar->id }}">{{ $ar->nombre }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <button type="submit" class="bg-gray-800 hover:bg-black text-white px-2.5 py-1 rounded-lg text-xs font-bold transition-all shrink-0">
                                +
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Trazabilidad Histórica Operativa (si es activo) -->
                @if(!$ticket->esTicketGeneral() && ($movPrevio || $ultimoPrestamo))
                    <div class="space-y-2 pt-2 border-t border-gray-100 text-xs">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Contexto Operativo</span>
                        @if($movPrevio)
                            <div class="p-2 bg-gray-50 rounded-lg border border-gray-100">
                                <span class="text-gray-400 text-[10px] uppercase font-bold block">Sala Previa a Taller</span>
                                <span class="font-bold text-gray-800">{{ $movPrevio->ubicacion?->nombre ?? 'N/A' }} ({{ $movPrevio->puesto ?: 'S/P' }})</span>
                            </div>
                        @endif
                        @if($ultimoPrestamo)
                            <div class="p-2 bg-gray-50 rounded-lg border border-gray-100">
                                <span class="text-gray-400 text-[10px] uppercase font-bold block">Último Préstamo</span>
                                <span class="font-bold text-gray-800">{{ $ultimoPrestamo->solicitante ?: 'Externo' }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- COLUMNA DERECHA: PRINCIPAL (Conversación y Acciones: 8 cols)               -->
        <!-- ========================================================================= -->
        <div class="lg:col-span-8 space-y-6">

            <!-- TARJETA 1: MENSAJE ORIGINAL DEL TICKET (Bosquejo 2) -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-xs p-6 space-y-4">
                <!-- TÍTULO DEL TICKET -->
                <div class="border-b border-gray-100 pb-3">
                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md mb-2 inline-block">
                        Ticket Original
                    </span>
                    <h2 class="text-xl font-black text-gray-900 tracking-tight leading-snug">
                        {{ $ticket->titulo_display }}
                    </h2>
                </div>

                <!-- TEXTO DEL TICKET CON SOPORTE MARKDOWN -->
                @if($ticket->comentario)
                    <div class="bg-gray-50/70 p-4 rounded-xl border border-gray-100">
                        <x-markdown :content="$ticket->comentario" />
                    </div>
                @endif

                <!-- FOTOS ADJUNTAS ORIGINALES (CARRUSEL DESTACADO) -->
                @if($ticket->adjuntos->isNotEmpty())
                    <div class="pt-2">
                        <span class="text-xs font-bold text-gray-500 block mb-2">
                            Evidencia Fotográfica Adjunta ({{ $ticket->adjuntos->count() }}):
                        </span>
                        <div class="max-w-xl mx-auto">
                            <x-ticket-carousel :fotos="$ticket->adjuntos" aspect="aspect-[16/10]" maxHeight="max-h-80" />
                        </div>
                    </div>
                @endif
            </div>

            <!-- TARJETAS SUBSIGUIENTES: HILO DE RESPUESTAS (Bosquejo 2) -->
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-gray-200 pb-2">
                    <h3 class="text-xs font-black text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Respuestas y Seguimiento ({{ $ticket->comentarios->count() }})</span>
                    </h3>
                </div>

                @if($ticket->comentarios->isEmpty())
                    <div class="p-8 text-center bg-white rounded-2xl border border-dashed border-gray-200 text-gray-400 text-xs">
                        <p class="font-medium">No se han registrado respuestas en este ticket todavía.</p>
                        @if($ticket->esTicketAbierto())
                            <p class="mt-1">Haga clic en <strong class="text-gray-700">Responder</strong> para aportar información o proponer una solución técnica.</p>
                        @endif
                    </div>
                @else
                    @foreach($ticket->comentarios as $cmt)
                        <div class="bg-white rounded-2xl border transition-all p-5 space-y-3 {{ $cmt->es_solucion ? 'border-[#fbc101] bg-amber-50/20 shadow-xs' : 'border-gray-200 shadow-2xs' }}">
                            <!-- Cabecera de la Respuesta -->
                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 pb-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-black shrink-0 {{ $cmt->es_solucion ? 'bg-[#fbc101] text-black' : 'bg-gray-800 text-white' }}">
                                        {{ strtoupper(substr($cmt->usuario?->nombre ?: ($cmt->usuario?->email ?? 'U'), 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="text-xs font-extrabold text-gray-900 block leading-tight">
                                            {{ $cmt->usuario?->nombre ?: ($cmt->usuario?->email ?? 'Usuario') }}
                                        </span>
                                        <span class="text-[10px] text-gray-400">
                                            {{ $cmt->created_at ? $cmt->created_at->format('d/m/Y H:i') : '' }} ({{ $cmt->created_at ? $cmt->created_at->diffForHumans() : '' }})
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    @if($cmt->es_solucion)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-black bg-[#fbc101] text-black shadow-2xs">
                                            ★ Propuesta de Solución
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Texto de la Respuesta con Markdown -->
                            <div class="text-sm">
                                <x-markdown :content="$cmt->mensaje" />
                            </div>

                            <!-- Movimiento asociado a la respuesta si hubo traslado -->
                            @if($cmt->movimiento)
                                <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-950 flex items-center justify-between gap-2">
                                    <span>📦 Traslado registrado hacia: <strong>{{ $cmt->movimiento->ubicacion?->nombre ?? 'N/A' }}</strong> (Puesto: {{ $cmt->movimiento->puesto ?: 'S/P' }})</span>
                                    <span class="font-mono text-[10px] font-bold text-amber-800">Mov #{{ $cmt->movimiento_id }}</span>
                                </div>
                            @endif

                            <!-- Fotos adjuntas en esta respuesta -->
                            @if($cmt->adjuntos && $cmt->adjuntos->isNotEmpty())
                                <div class="pt-2">
                                    <span class="text-[11px] font-bold text-gray-400 block mb-1.5">Fotos adjuntas a esta respuesta:</span>
                                    <div class="max-w-md">
                                        <x-ticket-carousel :fotos="$cmt->adjuntos" aspect="aspect-[16/10]" maxHeight="max-h-60" />
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- ========================================================================= -->
            <!-- BOTONERA INMEDIATA (Bosquejo 2: [RESPONDER] [CERRAR TICKET] / [REABRIR])   -->
            <!-- ========================================================================= -->
            <div class="pt-4 border-t border-gray-200">
                @if($ticket->esTicketAbierto())
                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <!-- Botón Responder: Abre página dedicada de respuesta -->
                        <a href="{{ route('tickets.responder', $ticket->id) }}" 
                           class="inline-flex items-center gap-2 bg-[#fbc101] hover:bg-amber-400 text-black font-black px-6 py-2.5 rounded-xl text-sm transition-all shadow-xs hover:shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                            <span>Responder</span>
                        </a>

                        <!-- Botón Cerrar Ticket: Exclusivo Asset Admin -->
                        @if(Auth::user()->hasRole(['asset_admin', 'item_admin', 'app_admin']))
                            <form action="{{ route('tickets.cerrar', $ticket->id) }}" method="POST" onsubmit="return confirm('¿Confirmar el cierre definitivo de este ticket?')">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center gap-2 bg-gray-900 hover:bg-black text-white font-black px-6 py-2.5 rounded-xl text-sm transition-all shadow-xs hover:shadow-sm">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <span>Cerrar Ticket</span>
                                </button>
                            </form>
                        @endif
                    </div>
                @else
                    <!-- Estado Cerrado -->
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 bg-gray-50 border border-gray-200 rounded-2xl">
                        <div class="flex items-center gap-2 text-xs font-bold text-gray-500">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>Este ticket ha sido cerrado. Ya no se pueden añadir más respuestas.</span>
                        </div>

                        <!-- Botón Reabrir Ticket (Exclusivo Asset Admin) -->
                        @if(Auth::user()->hasRole(['asset_admin', 'item_admin', 'app_admin']))
                            <form action="{{ route('tickets.reabrir', $ticket->id) }}" method="POST" onsubmit="return confirm('¿Desea reabrir este ticket? La propuesta de solución previa será retirada automáticamente y el ticket volverá a estar en proceso.')">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center gap-2 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-300 font-black px-5 py-2 rounded-xl text-xs transition-all shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Reabrir Ticket</span>
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
