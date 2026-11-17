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
                
                @if($ticket->estado_ticket === 'resuelto')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        ✓ Resuelto / Finalizado
                    </span>
                @elseif($ticket->estado_ticket === 'baja')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                        ✕ Declarado Irreparable (Baja Patrimonial)
                    </span>
                @elseif($ticket->estado_ticket === 'solucionado')
                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-900 border border-[#fbc101]">
                        ★ Solución Propuesta (Pendiente de Cierre Formal)
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-amber-100 text-amber-900 border border-[#fbc101] animate-pulse">
                        ● Abierto / En Taller
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

    <!-- DISTRIBUCIÓN BOSQUEJO 2: 2 COLUMNAS (Marginal Izquierda + Principal Derecha) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start" x-data="{ responderAbierto: false, cerrarAbierto: false }">
        
        <!-- ========================================================================= -->
        <!-- COLUMNA IZQUIERDA: METADATOS (Marginal en Desktop: 4 cols)                  -->
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
                        <p class="mt-1">Utilice el botón <strong class="text-gray-700">Responder</strong> para aportar información o proponer una solución técnica.</p>
                    </div>
                @else
                    @foreach($ticket->comentarios as $cmt)
                        <div class="bg-white rounded-2xl border transition-all p-5 space-y-3 {{ $cmt->es_solucion ? 'border-amber-400 bg-amber-50/20 shadow-xs' : 'border-gray-200 shadow-2xs' }}">
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
            <!-- ZONA DE ACCIONES INMEDIATAS (Bosquejo 2: [RESPONDER] y [CERRAR TICKET])    -->
            <!-- ========================================================================= -->
            <div class="space-y-4 pt-2">
                <!-- Botonera Principal según Bosquejo 2 -->
                <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                    <!-- Botón Responder -->
                    @if($ticket->esTicketAbierto())
                        <button type="button" 
                                @click="responderAbierto = !responderAbierto; if(responderAbierto) cerrarAbierto = false"
                                class="inline-flex items-center gap-2 bg-[#fbc101] hover:bg-amber-400 text-black font-black px-6 py-2.5 rounded-xl text-sm transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                            <span x-show="!responderAbierto">Responder</span>
                            <span x-show="responderAbierto">Ocultar Respuesta</span>
                        </button>
                    @endif

                    <!-- Botón Cerrar Ticket (Exclusivo Asset Admin) -->
                    @if($ticket->esTicketAbierto() && Auth::user()->canCloseTickets())
                        <button type="button" 
                                @click="cerrarAbierto = !cerrarAbierto; if(cerrarAbierto) responderAbierto = false"
                                class="inline-flex items-center gap-2 bg-gray-900 hover:bg-black text-white font-black px-6 py-2.5 rounded-xl text-sm transition-all shadow-sm">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span x-show="!cerrarAbierto">Cerrar Ticket</span>
                            <span x-show="cerrarAbierto">Cancelar Cierre</span>
                        </button>
                    @endif
                </div>

                <!-- FORMULARIO DESPLEGABLE: RESPONDER TICKET -->
                @if($ticket->esTicketAbierto())
                    <div x-show="responderAbierto" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="bg-white rounded-2xl border border-amber-300 shadow-md p-6 space-y-4">
                        <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                            <h4 class="text-sm font-black text-gray-900 flex items-center gap-2">
                                <span class="p-1 bg-[#fbc101] text-black rounded-lg text-xs">✍</span>
                                <span>Añadir Respuesta al Ticket</span>
                            </h4>
                            <span class="text-[11px] text-amber-800 font-semibold bg-amber-50 border border-amber-200 px-2 py-0.5 rounded">Soporta Markdown</span>
                        </div>

                        <form action="{{ route('tickets.comentar', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <textarea name="mensaje" rows="4" required 
                                          placeholder="Escriba su respuesta, diagnóstico técnico, mediciones o pruebas realizadas (soporta **negritas**, *cursivas*, listas, `código`)..."
                                          class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#fbc101] focus:border-amber-400 transition-all"></textarea>
                            </div>

                            <!-- Selector de Propuesta de Solución (Bosquejo 1 y 2) -->
                            <div class="p-3 bg-amber-50/70 border border-amber-300 rounded-xl">
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input type="checkbox" name="es_solucion" value="1" class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500">
                                    <div>
                                        <span class="text-xs font-black text-amber-950 block">Marcar esta respuesta como propuesta de solución técnica</span>
                                        <span class="text-[11px] text-amber-800 font-normal">Indica que la falla fue subsanada técnicamente. El ticket pasará a estado de solución a la espera del cierre formal por Asset Admin.</span>
                                    </div>
                                </label>
                            </div>

                            <!-- Fotos adjuntas en la respuesta -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                    Adjuntar Fotos de Evidencia (Opcional):
                                </label>
                                <input type="file" name="fotos[]" multiple accept="image/*"
                                       class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-gray-100 file:text-gray-700 hover:file:bg-amber-100 hover:file:text-amber-900 cursor-pointer">
                            </div>

                            <!-- Opciones Técnicas adicionales -->
                            @if(Auth::user()->hasRole('tec'))
                                <div class="pt-2 border-t border-gray-100 flex flex-wrap items-center gap-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-gray-600">+ Área:</span>
                                        <select name="nueva_area_id" class="bg-gray-50 border border-gray-200 rounded-lg px-2 py-1 text-xs text-gray-700">
                                            <option value="">Sin nueva área</option>
                                            @foreach($areas as $ar)
                                                @if(!$ticket->areas->contains('id', $ar->id))
                                                    <option value="{{ $ar->id }}">{{ $ar->nombre }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif

                            <div class="pt-2 border-t border-gray-100 flex items-center justify-end gap-2">
                                <button type="button" @click="responderAbierto = false" class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 text-xs font-bold hover:bg-gray-50">
                                    Cancelar
                                </button>
                                <button type="submit" class="inline-flex items-center gap-1.5 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold px-5 py-2 rounded-xl text-xs transition-all shadow-xs">
                                    <span>Publicar Respuesta</span>
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                <!-- FORMULARIO DESPLEGABLE: CERRAR TICKET (EXCLUSIVO ASSET ADMIN) -->
                @if($ticket->esTicketAbierto() && Auth::user()->canCloseTickets())
                    <div x-show="cerrarAbierto" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="bg-white rounded-2xl border border-gray-900 shadow-xl p-6 space-y-5">
                        <div class="border-b border-gray-100 pb-3">
                            <h4 class="text-base font-black text-gray-900 flex items-center gap-2">
                                <span class="w-6 h-6 bg-gray-900 text-white rounded-lg flex items-center justify-center text-xs font-black">✓</span>
                                <span>Cierre Formal del Ticket (Asset Admin)</span>
                            </h4>
                            <p class="text-xs text-gray-500 mt-0.5">Como Asset Admin, confirme la resolución definitiva, reasigne la sala física del activo o declare la baja si no tiene reparación.</p>
                        </div>

                        <form action="{{ route('tickets.resolver', $ticket->id) }}" method="POST" id="form_resolver_ticket" class="space-y-4">
                            @csrf
                            <input type="hidden" name="accion" id="accion_input" value="resolver">

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Dictamen de Cierre y Pruebas Realizadas <span class="text-rose-500 font-bold">*</span>
                                </label>
                                <textarea name="solucion" rows="3" required
                                          placeholder="Detalle la verificación final: equipo probado bajo carga, pasta térmica cambiada, funcionamiento validado satisfactoriamente..."
                                          class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#fbc101] focus:border-amber-400 transition-all">{{ old('solucion') }}</textarea>
                            </div>

                            @if($ticket->esTicketGeneral())
                                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                                    <button type="button" @click="cerrarAbierto = false" class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 text-xs font-bold hover:bg-gray-50">
                                        Cancelar
                                    </button>
                                    <button type="button" onclick="confirmarResolucionGeneral()"
                                            class="inline-flex items-center gap-2 bg-[#fbc101] hover:bg-amber-400 text-black font-black px-6 py-2.5 rounded-xl text-xs transition-all shadow-xs">
                                        <span>Resolver y Cerrar Incidencia</span>
                                    </button>
                                </div>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                            Sala de Destino (Reincorporación)
                                        </label>
                                        <select name="ubicacion_id" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3 py-2 text-xs font-semibold text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#fbc101]">
                                            @php
                                                $ubicacionSugerida = $movPrevio?->ubicacion_id ?: $ticket->ubicacion_id;
                                            @endphp
                                            @foreach($ubicaciones as $ub)
                                                <option value="{{ $ub->id }}" {{ old('ubicacion_id', $ubicacionSugerida) == $ub->id ? 'selected' : '' }}>
                                                    {{ $ub->nombre }} {{ $movPrevio && $movPrevio->ubicacion_id == $ub->id ? '(Sala Previa)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                            Puesto de Destino
                                        </label>
                                        <input type="text" name="puesto" value="{{ old('puesto', $movPrevio?->puesto ?: $ticket->puesto) }}" placeholder="Ej: Puesto 04"
                                               class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3 py-2 text-xs font-semibold text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#fbc101]">
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                                    <button type="button" onclick="confirmarBaja()" 
                                            class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 bg-white hover:bg-rose-50 text-rose-700 border border-rose-300 font-bold px-4 py-2 rounded-xl text-xs transition-all">
                                        Declarar Irreparable (Baja Patrimonial)
                                    </button>

                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="cerrarAbierto = false" class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 text-xs font-bold hover:bg-gray-50">
                                            Cancelar
                                        </button>
                                        <button type="button" onclick="confirmarResolucion()"
                                                class="inline-flex items-center gap-2 bg-[#fbc101] hover:bg-amber-400 text-black font-black px-6 py-2 rounded-xl text-xs transition-all shadow-xs">
                                            <span>Resolver y Reincorporar a Sala</span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function confirmarResolucionGeneral() {
        const form = document.getElementById('form_resolver_ticket');
        const solucion = form.querySelector('textarea[name="solucion"]').value.trim();

        if (solucion.length < 5) {
            alert('Por favor detalle el dictamen técnico antes de resolver la incidencia.');
            form.querySelector('textarea[name="solucion"]').focus();
            return;
        }

        document.getElementById('accion_input').value = 'resolver';
        if (confirm('¿Confirmar el cierre definitivo de esta incidencia general?')) {
            form.submit();
        }
    }

    function confirmarResolucion() {
        const form = document.getElementById('form_resolver_ticket');
        const solucion = form.querySelector('textarea[name="solucion"]').value.trim();

        if (solucion.length < 5) {
            alert('Por favor detalle el dictamen técnico antes de resolver el ticket.');
            form.querySelector('textarea[name="solucion"]').focus();
            return;
        }

        document.getElementById('accion_input').value = 'resolver';
        if (confirm('¿Confirmar la resolución del ticket y el reingreso del activo a sala?')) {
            form.submit();
        }
    }

    function confirmarBaja() {
        const form = document.getElementById('form_resolver_ticket');
        const solucion = form.querySelector('textarea[name="solucion"]').value.trim();

        if (solucion.length < 5) {
            alert('Por favor detalle el motivo técnico por el cual el activo resulta irreparable.');
            form.querySelector('textarea[name="solucion"]').focus();
            return;
        }

        document.getElementById('accion_input').value = 'baja';
        if (confirm('ATENCIÓN: ¿Está seguro de declarar este equipo como IRREPARABLE? El activo pasará a estado de BAJA / BASURA TECNOLÓGICA.')) {
            form.submit();
        }
    }
</script>
@endsection
