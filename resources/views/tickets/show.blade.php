@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Breadcrumb & Header Quiet Chrome -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-figma-border-subtle pb-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-figma-text-tertiary uppercase tracking-wider mb-1">
                <a href="{{ route('dashboard.index') }}" class="hover:text-figma-text-primary transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('tickets.index') }}" class="hover:text-figma-text-primary transition-colors">Tickets</a>
                <span>/</span>
                <span class="text-figma-text-primary font-bold">Ticket #{{ $ticket->id }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-xl font-bold text-figma-text-primary tracking-tight flex items-center gap-2.5">
                    <span class="font-mono">Ticket #{{ $ticket->id }}</span>
                </h1>
                
                @if($ticket->estado_ticket === 'resuelto')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                        Resuelto / Finalizado
                    </span>
                @elseif($ticket->estado_ticket === 'baja')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/15 text-rose-400 border border-rose-500/30">
                        Declarado Irreparable (Baja Patrimonial)
                    </span>
                @elseif($ticket->estado_ticket === 'solucionado')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-500/15 text-blue-400 border border-blue-500/30">
                        Solución Técnica Registrada (Pendiente de Cierre)
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/15 text-amber-400 border border-amber-500/30 animate-pulse">
                        Abierto / En Taller de Reparaciones
                    </span>
                @endif
            </div>
        </div>
        <div>
            <a href="{{ route('tickets.index') }}" class="figma-btn-secondary">
                ← Volver a Bandeja
            </a>
        </div>
    </div>

    <!-- Dual Pane Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Columna Izquierda: Activo, Falla y Fotos (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Ficha del Activo -->
            <div class="figma-panel p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-figma-border-subtle pb-3">
                    <h2 class="text-[11px] font-bold text-figma-text-tertiary uppercase tracking-wider">Activo Involucrado</h2>
                    <a href="{{ route('elementos.show', $ticket->elemento_id) }}" class="text-xs font-semibold text-figma-brand hover:underline flex items-center gap-1">
                        Ver Ficha Completa →
                    </a>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-figma-brand/15 text-figma-brand border border-figma-brand/30 rounded-lg flex items-center justify-center font-mono font-bold text-xs shrink-0">
                        {{ strtoupper(substr($ticket->elemento?->tipo?->nombre ?? 'LIA', 0, 3)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-base font-mono font-bold text-figma-text-primary">{{ $ticket->elemento?->nro_lia ?: 'Sin Nro LIA' }}</span>
                            @if($ticket->elemento?->nro_unsj)
                                <span class="text-[10px] font-mono font-bold text-figma-text-secondary bg-figma-bg-deep px-1.5 py-0.5 rounded border border-figma-border-subtle">UNSJ: {{ $ticket->elemento->nro_unsj }}</span>
                            @endif
                        </div>
                        <div class="text-xs text-figma-text-secondary mt-0.5">
                            {{ ucfirst($ticket->elemento?->tipo?->nombre ?? 'N/A') }} • <span class="font-bold text-figma-text-primary">{{ $ticket->elemento?->marca }}</span> {{ $ticket->elemento?->modelo }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-3 border-t border-figma-border-subtle text-xs">
                    <div>
                        <span class="text-[11px] text-figma-text-tertiary block font-medium">Ubicación Actual</span>
                        <span class="font-semibold text-figma-text-primary">{{ $ticket->ubicacion?->nombre ?? 'Depósito' }}</span>
                    </div>
                    <div>
                        <span class="text-[11px] text-figma-text-tertiary block font-medium">Puesto</span>
                        <span class="font-semibold text-figma-text-primary">{{ $ticket->puesto ?: ($ticket->elemento?->puesto ?: 'S/P') }}</span>
                    </div>
                </div>
            </div>

            <!-- Falla Reportada -->
            <div class="figma-panel p-5 space-y-3">
                <h2 class="text-[11px] font-bold text-figma-text-tertiary uppercase tracking-wider border-b border-figma-border-subtle pb-2">Diagnóstico Inicial de la Falla</h2>
                <div class="figma-card p-3.5 text-xs text-figma-text-primary leading-relaxed font-normal">
                    {{ $ticket->comentario }}
                </div>
                <div class="flex items-center justify-between text-[11px] text-figma-text-tertiary pt-1">
                    <span>Informante: <strong class="text-figma-text-secondary">{{ $ticket->solicitante ?: ($ticket->usuario?->nombre ?? 'Sistema') }}</strong></span>
                    <span>Fecha: <strong class="font-mono text-figma-text-secondary">{{ $ticket->fecha ? $ticket->fecha->format('d/m/Y H:i') : '-' }}</strong></span>
                </div>
            </div>

            <!-- Áreas Multidisciplinarias Involucradas -->
            <div class="figma-panel p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-figma-border-subtle pb-2">
                    <h2 class="text-[11px] font-bold text-figma-text-tertiary uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-figma-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        <span>Áreas Involucradas (Multidisciplinario)</span>
                    </h2>
                </div>

                @if($ticket->areas->isEmpty())
                    <p class="text-xs text-figma-text-tertiary italic">No hay áreas específicas vinculadas a este ticket.</p>
                @else
                    <div class="flex flex-wrap items-center gap-1.5">
                        @foreach($ticket->areas as $ar)
                            <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold border shadow-2xs"
                                 style="background-color: {{ $ar->color ? $ar->color . '20' : 'var(--bg-deep)' }}; color: {{ $ar->color ?: 'var(--text-primary)' }}; border-color: {{ $ar->color ? $ar->color . '60' : 'var(--border-subtle)' }};">
                                <span>{{ $ar->nombre }}</span>
                                @if($ar->pivot->created_at)
                                    <span class="text-[10px] opacity-75 font-mono">({{ \Carbon\Carbon::parse($ar->pivot->created_at)->format('d/m H:i') }})</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Sumar Área Adicional -->
                @if($ticket->esTicketAbierto())
                    <form action="{{ route('tickets.agregarArea', $ticket->id) }}" method="POST" class="pt-2 border-t border-figma-border-subtle flex items-center gap-2">
                        @csrf
                        <select name="area_id" required class="figma-input flex-1 px-2.5 py-1.5 text-xs font-medium">
                            <option value="">+ Sumar Área...</option>
                            @foreach($areas as $ar)
                                @if(!$ticket->areas->contains('id', $ar->id))
                                    <option value="{{ $ar->id }}">{{ $ar->nombre }}</option>
                                @endif
                            @endforeach
                        </select>
                        <button type="submit" class="figma-btn-primary py-1.5 px-3 text-xs shrink-0">
                            Sumar
                        </button>
                    </form>
                @endif
            </div>

            <!-- Evidencia Fotográfica -->
            <div class="figma-panel p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-figma-border-subtle pb-2">
                    <h2 class="text-[11px] font-bold text-figma-text-tertiary uppercase tracking-wider">Evidencia Fotográfica ({{ $ticket->adjuntos->count() }})</h2>
                </div>

                @if($ticket->adjuntos->isEmpty())
                    <p class="text-xs text-figma-text-tertiary italic">No se adjuntaron fotos al momento del reporte.</p>
                @else
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($ticket->adjuntos as $adj)
                            <a href="{{ $adj->url }}" target="_blank" title="Ver foto en tamaño original" class="group relative rounded-lg overflow-hidden border border-figma-border-subtle aspect-video bg-figma-bg-deep block hover:border-figma-brand/50 transition-all">
                                <img src="{{ $adj->url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-semibold">
                                    Ampliar ↗
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Columna Derecha: Contexto y Resolución (7 cols) -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Contexto Operativo: Sala previa y Último Préstamo -->
            <div class="figma-panel p-5 space-y-4">
                <h2 class="text-[11px] font-bold text-figma-text-tertiary uppercase tracking-wider border-b border-figma-border-subtle pb-2">Trazabilidad Operativa Previa</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                    <!-- Sala Previa al Ingreso -->
                    <div class="figma-card p-3 space-y-1">
                        <span class="text-[11px] text-figma-text-tertiary font-medium block">Sala Previa al Ingreso</span>
                        @if($movPrevio)
                            <p class="font-bold text-figma-text-primary text-sm">{{ $movPrevio->ubicacion?->nombre ?? 'Sin asignar' }}</p>
                            <p class="text-figma-text-secondary text-xs">Puesto: <strong class="text-figma-text-primary">{{ $movPrevio->puesto ?: 'S/P' }}</strong></p>
                            <p class="text-figma-text-tertiary text-[11px] font-mono mt-0.5">{{ $movPrevio->fecha ? $movPrevio->fecha->format('d/m/Y') : '' }} ({{ $movPrevio->comentario ?: 'Movimiento regular' }})</p>
                        @else
                            <p class="text-figma-text-tertiary italic">No hay registros de movimientos previos.</p>
                        @endif
                    </div>

                    <!-- Último Préstamo Externo -->
                    <div class="figma-card p-3 space-y-1">
                        <span class="text-[11px] text-figma-text-tertiary font-medium block">Último Préstamo Registrado</span>
                        @if($ultimoPrestamo)
                            <p class="font-bold text-figma-text-primary text-sm">{{ $ultimoPrestamo->solicitante ?: 'Docente externo' }}</p>
                            <p class="text-figma-text-secondary text-xs font-mono">
                                Salida: {{ $ultimoPrestamo->fecha_prestamo ? Carbon\Carbon::parse($ultimoPrestamo->fecha_prestamo)->format('d/m/Y') : '-' }} • 
                                @if($ultimoPrestamo->fecha_devolucion)
                                    Retorno: {{ Carbon\Carbon::parse($ultimoPrestamo->fecha_devolucion)->format('d/m/Y') }}
                                @else
                                    <span class="text-amber-400 font-bold">Activo</span>
                                @endif
                            </p>
                        @else
                            <p class="text-figma-text-tertiary italic">El equipo nunca ha sido cedido en préstamo.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Resoluciones Previas si existen -->
            @if($ticket->resoluciones->isNotEmpty())
                <div class="figma-panel p-5 space-y-4">
                    <h2 class="text-[11px] font-bold text-figma-text-tertiary uppercase tracking-wider border-b border-figma-border-subtle pb-2">Resolución Técnica Consolidada</h2>
                    @foreach($ticket->resoluciones as $res)
                        <div class="p-4 rounded-xl {{ $res->tipo_movimiento === 'baja' ? 'bg-rose-500/10 border border-rose-500/30' : 'bg-emerald-500/10 border border-emerald-500/30' }} space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs {{ $res->tipo_movimiento === 'baja' ? 'text-rose-400' : 'text-emerald-400' }}">
                                    {{ $res->tipo_movimiento === 'baja' ? 'Declarado Irreparable (Baja Patrimonial)' : 'Ticket Resuelto y Equipo Reasignado' }}
                                </span>
                                <span class="text-[11px] text-figma-text-tertiary font-mono">{{ $res->fecha ? $res->fecha->format('d/m/Y H:i') : '' }}</span>
                            </div>
                            <p class="text-xs text-figma-text-primary leading-relaxed font-normal">
                                {{ $res->comentario }}
                            </p>
                            <div class="text-[11px] text-figma-text-secondary pt-1 flex flex-wrap items-center gap-3">
                                <span>Técnico Responsable: <strong class="text-figma-text-primary">{{ $res->usuario?->nombre ?: 'Técnico LIA' }}</strong></span>
                                <span>•</span>
                                <span>Ubicación Asignada: <strong class="text-figma-text-primary">{{ $res->ubicacion?->nombre ?? 'N/A' }} ({{ $res->puesto ?: 'S/P' }})</strong></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Hilo Colaborativo Tipo Foro y Seguimiento del Ticket -->
            <div class="figma-panel p-5 sm:p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-figma-border-subtle pb-3">
                    <h2 class="text-xs font-bold text-figma-text-primary uppercase tracking-wider flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-figma-brand/15 text-figma-brand flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </span>
                        <span>Hilo de Seguimiento y Discusión Técnica</span>
                    </h2>
                    <span class="text-[11px] font-mono text-figma-text-tertiary bg-figma-bg-deep px-2 py-0.5 rounded border border-figma-border-subtle">{{ $ticket->comentarios->count() }} {{ $ticket->comentarios->count() === 1 ? 'mensaje' : 'mensajes' }}</span>
                </div>

                @if($ticket->comentarios->isEmpty())
                    <div class="p-8 text-center figma-card border-dashed">
                        <p class="text-xs text-figma-text-tertiary">No se han registrado mensajes en el hilo de este ticket aún. Sea el primero en aportar un comentario o diagnóstico.</p>
                    </div>
                @else
                    <!-- Timeline tipo Foro -->
                    <div class="space-y-3">
                        @foreach($ticket->comentarios as $cmt)
                            <div class="figma-card p-3.5 space-y-2 {{ $cmt->tipo === 'solucion_propuesta' ? 'border-blue-500/40 bg-blue-500/5' : '' }}">
                                <div class="flex items-start gap-3">
                                    <!-- Avatar -->
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-mono font-bold shrink-0 shadow-2xs {{ $cmt->tipo === 'solucion_propuesta' ? 'bg-blue-600 text-white' : 'bg-figma-bg-deep text-figma-text-primary border border-figma-border-subtle' }}">
                                        {{ strtoupper(substr($cmt->usuario?->nombre ?: ($cmt->usuario?->email ?? 'U'), 0, 2)) }}
                                    </div>

                                    <div class="flex-1 min-w-0 space-y-1.5">
                                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-figma-border-subtle pb-1.5">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-bold text-xs text-figma-text-primary">{{ $cmt->usuario?->nombre ?: ($cmt->usuario?->email ?? 'Usuario') }}</span>
                                                
                                                @if($cmt->usuario)
                                                    @if($cmt->usuario->hasRole('tec'))
                                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-mono font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">Técnico</span>
                                                    @elseif($cmt->usuario->hasRole('subtec'))
                                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-mono font-bold bg-indigo-500/15 text-indigo-400 border border-indigo-500/30">SubTécnico</span>
                                                    @elseif($cmt->usuario->hasRole('rev'))
                                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-mono font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">Revisor</span>
                                                    @endif
                                                @endif

                                                @if($cmt->tipo === 'solucion_propuesta')
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/15 text-blue-400 border border-blue-500/30">Diagnóstico / Solución</span>
                                                @endif
                                            </div>

                                            <span class="text-figma-text-tertiary text-[11px] font-mono" title="{{ $cmt->created_at ? $cmt->created_at->format('d/m/Y H:i:s') : '' }}">
                                                {{ $cmt->created_at ? $cmt->created_at->diffForHumans() : '' }}
                                            </span>
                                        </div>

                                        <!-- Mensaje -->
                                        <p class="text-xs text-figma-text-secondary leading-relaxed whitespace-pre-line">{{ $cmt->mensaje }}</p>

                                        <!-- Movimiento de Inventario Asociado a este Mensaje -->
                                        @if($cmt->movimiento)
                                            <div class="mt-2 p-2.5 rounded-lg bg-amber-500/10 border border-amber-500/30 text-xs text-figma-text-primary flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="px-1.5 py-0.5 bg-amber-500/20 text-amber-400 rounded font-bold text-[10px] uppercase">📦 Traslado Físico</span>
                                                    <span class="text-xs">Destino: <strong class="text-figma-text-primary">{{ $cmt->movimiento->ubicacion?->nombre ?? 'N/A' }}</strong> (Puesto: {{ $cmt->movimiento->puesto ?: 'S/P' }})</span>
                                                </div>
                                                <span class="text-[11px] text-amber-400 font-mono font-bold">Movimiento #{{ $cmt->movimiento_id }}</span>
                                            </div>
                                        @endif

                                        <!-- Evidencia Fotográfica en el Mensaje -->
                                        @if($cmt->adjuntos && $cmt->adjuntos->isNotEmpty())
                                            <div class="pt-2">
                                                <span class="text-[10px] uppercase font-bold text-figma-text-tertiary block mb-1">Fotos adjuntas ({{ $cmt->adjuntos->count() }}):</span>
                                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                                    @foreach($cmt->adjuntos as $foto)
                                                        <a href="{{ $foto->url }}" target="_blank" class="group relative rounded-lg overflow-hidden border border-figma-border-subtle aspect-video bg-figma-bg-deep block hover:border-figma-brand/50 transition-all">
                                                            <img src="{{ $foto->url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-[10px] font-semibold">
                                                                Ampliar ↗
                                                            </div>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Formulario de Respuesta en el Foro -->
                <form action="{{ route('tickets.comentar', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="pt-4 border-t border-figma-border-subtle space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-figma-text-secondary uppercase tracking-wider mb-1">
                            Añadir Respuesta o Evidencia al Foro
                        </label>
                        <textarea name="mensaje" rows="2" required placeholder="Escriba un comentario, aclaración de falla o respuesta técnica en el hilo..."
                                  class="figma-input w-full p-3 text-xs leading-relaxed"></textarea>
                    </div>

                    <!-- Opción de Traslado Físico -->
                    @if(Auth::user()->hasRole('tec'))
                        <div class="p-3 figma-card space-y-2 border-amber-500/30 bg-amber-500/5">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-figma-text-primary">
                                <input type="checkbox" name="generar_movimiento" value="1" id="toggle_movimiento_check"
                                       onchange="document.getElementById('contenedor_movimiento_ticket').classList.toggle('hidden', !this.checked)"
                                       class="rounded text-figma-brand focus:ring-figma-brand">
                                <span>Registrar traslado o cambio de ubicación física con esta respuesta</span>
                            </label>

                            <div id="contenedor_movimiento_ticket" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                <div>
                                    <label class="block text-[10px] font-bold text-figma-text-secondary mb-1 uppercase">Nueva Sala de Ubicación:</label>
                                    <select name="ubicacion_id" class="figma-input w-full px-2.5 py-1.5 text-xs">
                                        <option value="">Seleccione sala de destino...</option>
                                        @foreach($ubicaciones as $ub)
                                            <option value="{{ $ub->id }}">{{ $ub->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-figma-text-secondary mb-1 uppercase">Puesto (opcional):</label>
                                    <input type="text" name="puesto" placeholder="Ej: Puesto 04, Rack 2"
                                           class="figma-input w-full px-2.5 py-1.5 text-xs">
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center">
                        <!-- Adjuntos fotográficos opcionales -->
                        <div>
                            <label class="block text-[10px] font-bold text-figma-text-tertiary mb-1 uppercase">
                                Adjuntar fotos (opcional):
                            </label>
                            <input type="file" name="fotos[]" multiple accept="image/*"
                                   class="block w-full text-xs text-figma-text-tertiary file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border file:border-figma-border-subtle file:text-xs file:font-semibold file:bg-figma-bg-deep file:text-figma-text-primary hover:file:border-figma-brand/50 cursor-pointer">
                        </div>

                        <!-- Selector de Área adicional opcional y Botón Publicar -->
                        <div class="flex items-center gap-2 justify-end">
                            <span class="text-[11px] text-figma-text-tertiary whitespace-nowrap">+ Área:</span>
                            <select name="nueva_area_id" class="figma-input px-2 py-1.5 text-xs font-medium">
                                <option value="">Sin nueva área</option>
                                @foreach($areas as $ar)
                                    @if(!$ticket->areas->contains('id', $ar->id))
                                        <option value="{{ $ar->id }}">{{ $ar->nombre }}</option>
                                    @endif
                                @endforeach
                            </select>

                            <button type="submit" class="figma-btn-primary py-1.5 px-3.5 text-xs font-semibold">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                <span>Publicar</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Panel de Solución Técnica para SubTécnico -->
            @if($ticket->esTicketAbierto() && Auth::user()->canSolveTickets() && !Auth::user()->canCloseTickets())
                <div class="figma-panel p-5 space-y-4 border-blue-500/40">
                    <div class="border-b border-figma-border-subtle pb-3">
                        <h2 class="text-xs font-bold text-figma-text-primary uppercase tracking-wider flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-blue-500/20 text-blue-400 flex items-center justify-center text-xs font-bold">⚙</span>
                            Diagnóstico y Solución Técnica (SubTécnico)
                        </h2>
                        <p class="text-[11px] text-figma-text-tertiary mt-0.5">Registre las tareas efectuadas. El cierre formal y asignación de sala serán concluidos por un Técnico.</p>
                    </div>

                    <form action="{{ route('tickets.solucionar', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-bold text-figma-text-secondary uppercase tracking-wider mb-1.5">
                                Solución Técnica Aplicada y Pruebas Efectuadas <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <textarea name="solucion" rows="3" required
                                      placeholder="Detalle el trabajo realizado: mantenimiento, reemplazo de componente, comprobación de booteo, etc."
                                      class="figma-input w-full p-3 text-xs leading-relaxed"></textarea>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-figma-text-tertiary mb-1 uppercase">
                                Fotos de evidencia del arreglo / mediciones (opcional):
                            </label>
                            <input type="file" name="fotos[]" multiple accept="image/*"
                                   class="block w-full text-xs text-figma-text-tertiary file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border file:border-figma-border-subtle file:text-xs file:font-semibold file:bg-figma-bg-deep file:text-figma-text-primary hover:file:border-figma-brand/50 cursor-pointer">
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] text-figma-text-tertiary">+ Área involucrada:</span>
                                <select name="nueva_area_id" class="figma-input px-2.5 py-1.5 text-xs font-medium">
                                    <option value="">Mantener áreas actuales</option>
                                    @foreach($areas as $ar)
                                        @if(!$ticket->areas->contains('id', $ar->id))
                                            <option value="{{ $ar->id }}">{{ $ar->nombre }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="figma-btn-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Registrar Solución Técnica</span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Formulario de Resolución Formal (Técnico) -->
            @if($ticket->esTicketAbierto() && Auth::user()->canCloseTickets())
                <div class="figma-panel p-5 space-y-5 border-amber-500/40">
                    <div class="border-b border-figma-border-subtle pb-3">
                        <h2 class="text-xs font-bold text-figma-text-primary uppercase tracking-wider flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs font-bold">✓</span>
                            Resolución Técnica del Ticket (Cierre Formal y Reasignación)
                        </h2>
                        <p class="text-[11px] text-figma-text-tertiary mt-0.5">Registre la intervención técnica, confirme el destino físico o declare la baja del equipo.</p>
                    </div>

                    <form action="{{ route('tickets.resolver', $ticket->id) }}" method="POST" id="form_resolver_ticket" class="space-y-4">
                        @csrf
                        <input type="hidden" name="accion" id="accion_input" value="resolver">

                        <div>
                            <label class="block text-[11px] font-bold text-figma-text-secondary uppercase tracking-wider mb-1.5">
                                Descripción de la Solución y Pruebas Realizadas <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <textarea name="solucion" rows="4" required
                                      placeholder="Detalle la solución aplicada: cambio de pasta térmica, reemplazo de fuente ATX de 500W, formateo y clonación a SSD, pruebas de memoria con MemTest (0 errores), etc."
                                      class="figma-input w-full p-3 text-xs leading-relaxed">{{ old('solucion') }}</textarea>
                            <span class="text-[10px] text-figma-text-tertiary mt-1 block">Este registro quedará asentado de forma inmutable en el historial cronológico del inventario.</span>
                        </div>

                        <!-- Confirmación de Ubicación Física -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase tracking-wider mb-1.5">
                                    Sala de Destino (Reincorporación)
                                </label>
                                <select name="ubicacion_id" class="figma-input w-full px-3 py-2 text-xs font-medium">
                                    @php
                                        $ubicacionSugerida = $movPrevio?->ubicacion_id ?: $ticket->ubicacion_id;
                                    @endphp
                                    @foreach($ubicaciones as $ub)
                                        <option value="{{ $ub->id }}" {{ old('ubicacion_id', $ubicacionSugerida) == $ub->id ? 'selected' : '' }}>
                                             {{ $ub->nombre }} {{ $movPrevio && $movPrevio->ubicacion_id == $ub->id ? '(Sala Previa)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-[10px] text-figma-text-tertiary mt-1 block">Heredada por defecto del movimiento previo.</span>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase tracking-wider mb-1.5">
                                    Puesto de Destino
                                </label>
                                @php
                                    $puestoSugerido = $movPrevio?->puesto ?: ($ticket->puesto ?: $ticket->elemento?->puesto);
                                @endphp
                                <input type="text" name="puesto" value="{{ old('puesto', $puestoSugerido) }}" placeholder="Ej. Puesto 08, Rack 01..."
                                       class="figma-input w-full px-3 py-2 text-xs font-medium">
                                <span class="text-[10px] text-figma-text-tertiary mt-1 block">Puesto donde el activo quedará prestando servicio.</span>
                            </div>
                        </div>

                        <!-- Botones de Acción Doble -->
                        <div class="pt-3 border-t border-figma-border-subtle flex flex-col sm:flex-row items-center justify-between gap-3">
                            <button type="button" onclick="confirmarBaja()" 
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg bg-rose-600/15 border border-rose-500/30 text-rose-400 hover:bg-rose-600 hover:text-white font-semibold text-xs transition-all cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                Declarar Irreparable (Baja / Basura)
                            </button>

                            <button type="button" onclick="confirmarResolucion()"
                                    class="figma-btn-primary w-full sm:w-auto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Resolver Ticket y Asignar a Sala</span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Notificación informativa para rol Revisor -->
            @if($ticket->esTicketAbierto() && !Auth::user()->canSolveTickets())
                <div class="figma-panel p-5 space-y-2">
                    <div class="flex items-center gap-2 text-xs font-bold text-figma-text-primary uppercase">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Perfil Revisor</span>
                    </div>
                    <p class="text-xs text-figma-text-secondary leading-relaxed">
                        Su perfil le permite participar en la bitácora de mensajes y aportar detalles sobre la falla reportada. La solución técnica y el cierre formal con movimientos de inventario corresponden a los roles SubTécnico y Técnico.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function confirmarResolucion() {
        const form = document.getElementById('form_resolver_ticket');
        const solucion = form.querySelector('textarea[name="solucion"]').value.trim();

        if (solucion.length < 5) {
            alert('Por favor detalle la solución técnica y pruebas realizadas antes de resolver el ticket.');
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
        if (confirm('ATENCIÓN: ¿Está seguro de declarar este equipo como IRREPARABLE? El activo pasará a estado de BAJA / BASURA TECNOLÓGICA en el inventario institucional.')) {
            form.submit();
        }
    }
</script>
@endsection
