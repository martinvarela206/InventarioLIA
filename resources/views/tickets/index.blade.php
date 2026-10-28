@extends('layouts.app')

@section('content')
<div class="max-w-[1320px] mx-auto px-2 space-y-5">
    <!-- Header Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('dashboard.index') }}" class="hover:text-figma-brand transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-figma-text-primary">Mantenimiento Técnico</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary tracking-tight flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Bandeja de Tickets y Reparaciones Técnicas</span>
            </h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('tickets.create') }}" class="figma-btn-primary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Iniciar Ticket</span>
            </a>
        </div>
    </div>

    <!-- Pestañas Figma Pill Segment + Filtros -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <!-- Figma Pill Tabs Ribbon -->
        <div class="figma-pill-ribbon overflow-x-auto">
            <a href="{{ route('tickets.index', ['estado' => 'abierto', 'search' => request('search'), 'area_id' => request('area_id')]) }}" 
               class="figma-pill-item {{ $estado === 'abierto' ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                <span>Abiertos / Taller</span>
                <span class="font-mono text-[10px] px-1.5 py-0.2 rounded bg-figma-bg-deep font-bold">{{ $conteoAbiertos }}</span>
            </a>

            <a href="{{ route('tickets.index', ['estado' => 'resuelto', 'search' => request('search'), 'area_id' => request('area_id')]) }}" 
               class="figma-pill-item {{ $estado === 'resuelto' ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>Resueltos</span>
                <span class="font-mono text-[10px] px-1.5 py-0.2 rounded bg-figma-bg-deep font-bold">{{ $conteoResueltos }}</span>
            </a>

            <a href="{{ route('tickets.index', ['estado' => 'baja', 'search' => request('search'), 'area_id' => request('area_id')]) }}" 
               class="figma-pill-item {{ $estado === 'baja' ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                <span>Bajas / Irreparables</span>
                <span class="font-mono text-[10px] px-1.5 py-0.2 rounded bg-figma-bg-deep font-bold">{{ $conteoBajas }}</span>
            </a>

            <a href="{{ route('tickets.index', ['estado' => 'todos', 'search' => request('search'), 'area_id' => request('area_id')]) }}" 
               class="figma-pill-item {{ $estado === 'todos' ? 'active' : '' }}">
                <span>Todos</span>
                <span class="font-mono text-[10px] px-1.5 py-0.2 rounded bg-figma-bg-deep font-bold">{{ $conteoTodos }}</span>
            </a>
        </div>

        <!-- Filtros y Buscador -->
        <form action="{{ route('tickets.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-2">
            <input type="hidden" name="estado" value="{{ $estado }}">
            
            <select name="area_id" onchange="this.form.submit()" class="figma-input px-3 py-1.5 w-full sm:w-44 text-xs font-semibold cursor-pointer">
                <option value="">Todas las Áreas</option>
                @foreach($areas as $ar)
                    <option value="{{ $ar->id }}" {{ request('area_id') == $ar->id ? 'selected' : '' }}>
                        {{ $ar->nombre }}
                    </option>
                @endforeach
            </select>

            <div class="relative w-full sm:w-60">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar LIA, serie, problema..." 
                       class="figma-input w-full pl-8 pr-3 py-1.5 text-xs font-medium">
                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-figma-text-tertiary">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
            @if(request('area_id') || request('search'))
                <a href="{{ route('tickets.index', ['estado' => $estado]) }}" class="text-xs font-semibold text-figma-text-tertiary hover:text-figma-text-primary px-2">
                    Limpiar
                </a>
            @endif
        </form>
    </div>

    <!-- Listado de Tickets -->
    @if($tickets->isEmpty())
        <div class="figma-panel p-12 text-center space-y-2">
            <div class="w-12 h-12 bg-figma-card text-figma-text-tertiary rounded-xl flex items-center justify-center mx-auto mb-2 border border-figma-border-subtle">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-figma-text-primary">No hay tickets en esta bandeja</h3>
            <p class="text-xs text-figma-text-tertiary">Actualmente no existen registros bajo el estado "{{ ucfirst($estado) }}"{{ request('area_id') ? ' para el área seleccionada' : '' }}.</p>
            @if($estado !== 'abierto' || request('area_id'))
                <div class="pt-2">
                    <a href="{{ route('tickets.index', ['estado' => 'abierto']) }}" class="text-xs font-semibold text-figma-brand hover:underline">
                        Ver tickets abiertos
                    </a>
                </div>
            @endif
        </div>
    @else
        <div class="space-y-2.5">
            @foreach($tickets as $t)
                <div class="figma-card p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- Columna Izquierda: Identidad y Falla -->
                    <div class="space-y-2 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-mono font-bold text-figma-text-primary bg-figma-bg-deep border border-figma-border-subtle px-1.5 py-0.5 rounded">#{{ $t->id }}</span>
                            
                            @if($t->estado_ticket === 'resuelto')
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Resuelto</span>
                                </span>
                            @elseif($t->estado_ticket === 'baja')
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-500/15 text-rose-400 border border-rose-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    <span>Baja / Irreparable</span>
                                </span>
                            @elseif($t->estado_ticket === 'solucionado')
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-500/15 text-blue-400 border border-blue-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    <span>Solución Propuesta</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    <span>Abierto / En Taller</span>
                                </span>
                            @endif

                            @if($t->areas && $t->areas->isNotEmpty())
                                <div class="flex flex-wrap items-center gap-1">
                                    @foreach($t->areas as $ar)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-bold border border-figma-border-subtle"
                                              style="background-color: {{ $ar->color ? $ar->color . '20' : 'var(--figma-bg-deep)' }}; color: {{ $ar->color ?: 'var(--figma-text-secondary)' }};">
                                            {{ $ar->nombre }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <span class="text-[11px] text-figma-text-tertiary">
                                Apertura: <strong class="text-figma-text-secondary">{{ $t->fecha ? $t->fecha->format('d/m/Y H:i') : '-' }}</strong> ({{ $t->fecha ? $t->fecha->diffForHumans() : '' }})
                            </span>
                        </div>

                        <!-- Info del Activo -->
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('elementos.show', $t->elemento_id) }}" class="font-mono font-bold text-sm text-figma-brand hover:underline">
                                {{ $t->elemento?->nro_lia ?: 'Sin Nro LIA' }}
                            </a>
                            @if($t->elemento?->nro_unsj)
                                <span class="font-mono text-[11px] text-figma-text-tertiary bg-figma-bg-deep border border-figma-border-subtle px-1.5 py-0.5 rounded">UNSJ: {{ $t->elemento->nro_unsj }}</span>
                            @endif
                            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-figma-bg-deep text-figma-text-secondary">{{ ucfirst($t->elemento?->tipo?->nombre ?? 'N/A') }}</span>
                            <span class="text-xs text-figma-text-tertiary">• {{ $t->elemento?->marca }} {{ $t->elemento?->modelo }}</span>
                        </div>

                        <!-- Descripción de la Falla -->
                        <div class="figma-panel p-2.5 text-xs text-figma-text-secondary leading-relaxed bg-figma-bg-deep/50">
                            <strong class="text-figma-text-primary font-semibold block mb-0.5">Problema reportado:</strong>
                            {{ $t->comentario }}
                        </div>

                        <!-- Informante y Ubicación -->
                        <div class="flex flex-wrap items-center gap-3 text-[11px] text-figma-text-tertiary pt-0.5">
                            <span>Informante: <strong class="text-figma-text-secondary">{{ $t->solicitante ?: ($t->usuario?->nombre ?? 'Sistema') }}</strong></span>
                            <span>•</span>
                            <span>Ubicación: <strong class="text-figma-text-secondary">{{ $t->ubicacion?->nombre ?? 'Depósito' }} ({{ $t->puesto ?: 'S/P' }})</strong></span>
                            @if($t->adjuntos->isNotEmpty())
                                <span>•</span>
                                <span class="inline-flex items-center gap-1 font-semibold text-figma-brand">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ $t->adjuntos->count() }} {{ $t->adjuntos->count() === 1 ? 'adjunto' : 'adjuntos' }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Columna Derecha: Fotos miniatura y Botón de Acción -->
                    <div class="flex flex-col sm:flex-row md:flex-col items-end justify-between gap-3 min-w-[150px]">
                        @if($t->adjuntos->isNotEmpty())
                            <div class="flex items-center gap-1.5">
                                @foreach($t->adjuntos->take(3) as $adj)
                                    <div class="w-9 h-9 rounded overflow-hidden border border-figma-border-subtle bg-figma-bg-deep">
                                        <img src="{{ $adj->url }}" class="w-full h-full object-cover">
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <a href="{{ route('tickets.show', $t->id) }}" class="figma-btn-secondary text-xs">
                            <span>Gestionar</span>
                            <span>→</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-3">
            {{ $tickets->links() }}
        </div>
    @endif
</div>
@endsection
