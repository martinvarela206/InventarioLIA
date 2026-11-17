@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                <a href="{{ route('dashboard.index') }}" class="hover:text-amber-600 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-gray-900">Tickets</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-amber-100 text-amber-700 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </span>
                <span>Bandeja de Tickets y Reparaciones Técnicas</span>
            </h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('tickets.create') }}" class="inline-flex items-center gap-2 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold px-4 py-2.5 rounded-xl shadow-sm transition-all text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Iniciar Ticket
            </a>
        </div>
    </div>

    <!-- Pestañas de Filtrado por Estado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1">
            <a href="{{ route('tickets.index', ['estado' => 'abierto', 'search' => request('search')]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-2 {{ $estado === 'abierto' ? 'bg-amber-500 text-black shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                <span>Abiertos / En Taller</span>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold {{ $estado === 'abierto' ? 'bg-black text-amber-300' : 'bg-amber-100 text-amber-900' }}">{{ $conteoAbiertos }}</span>
            </a>

            <a href="{{ route('tickets.index', ['estado' => 'resuelto', 'search' => request('search')]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-2 {{ $estado === 'resuelto' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                <span>Resueltos</span>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold {{ $estado === 'resuelto' ? 'bg-emerald-800 text-white' : 'bg-emerald-100 text-emerald-900' }}">{{ $conteoResueltos }}</span>
            </a>

            <a href="{{ route('tickets.index', ['estado' => 'baja', 'search' => request('search')]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-2 {{ $estado === 'baja' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                <span>Bajas / Irreparables</span>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold {{ $estado === 'baja' ? 'bg-rose-800 text-white' : 'bg-rose-100 text-rose-900' }}">{{ $conteoBajas }}</span>
            </a>

            <a href="{{ route('tickets.index', ['estado' => 'todos', 'search' => request('search')]) }}" 
               class="px-4 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-2 {{ $estado === 'todos' ? 'bg-gray-800 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                <span>Todos</span>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold {{ $estado === 'todos' ? 'bg-black text-gray-300' : 'bg-gray-100 text-gray-700' }}">{{ $conteoTodos }}</span>
            </a>
        </div>

        <!-- Filtros y Buscador de tickets -->
        <form action="{{ route('tickets.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
            <input type="hidden" name="estado" value="{{ $estado }}">
            
            <!-- Selector de Área -->
            <select name="area_id" onchange="this.form.submit()" class="w-full sm:w-44 bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs font-bold text-gray-700 focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                <option value="">Todas las Áreas</option>
                @foreach($areas as $ar)
                    <option value="{{ $ar->id }}" {{ request('area_id') == $ar->id ? 'selected' : '' }}>
                        {{ $ar->nombre }}
                    </option>
                @endforeach
            </select>

            <!-- Buscador Interactivo con Autocompletado y Spinner -->
            <x-ticket-search 
                id="bandeja-ticket-search"
                inputName="search"
                value="{{ request('search') }}"
                placeholder="Buscar LIA, UNSJ, problema, ámbito o #ID..."
            />
            @if(request('area_id') || request('search'))
                <a href="{{ route('tickets.index', ['estado' => $estado]) }}" class="text-xs font-bold text-gray-500 hover:text-gray-900 px-2 py-1">
                    Limpiar
                </a>
            @endif
        </form>
    </div>

    <!-- Listado de Tickets -->
    @if($tickets->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-12 text-center">
            <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-gray-900">No hay tickets en esta bandeja</h3>
            <p class="text-xs text-gray-500 mt-1">Actualmente no existen registros bajo el estado "{{ ucfirst($estado) }}"{{ request('area_id') ? ' para el área seleccionada' : '' }}.</p>
            @if($estado !== 'abierto' || request('area_id'))
                <div class="mt-4">
                    <a href="{{ route('tickets.index', ['estado' => 'abierto']) }}" class="text-xs font-bold text-amber-700 hover:text-amber-900 underline">
                        Ver todos los tickets abiertos
                    </a>
                </div>
            @endif
        </div>
    @else
        <div class="space-y-4">
            @foreach($tickets as $t)
                <div class="bg-white rounded-2xl border border-gray-200 shadow-xs hover:shadow-md transition-all p-5 flex flex-col gap-4">
                    <!-- Fila Superior: Identidad, Estado, Modalidad y Fecha -->
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 pb-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-black text-gray-700 bg-gray-100 px-2 py-0.5 rounded font-mono">#{{ $t->id }}</span>
                            
                            @if($t->estado_ticket === 'resuelto')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Resuelto</span>
                            @elseif($t->estado_ticket === 'baja')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">Baja / Irreparable</span>
                            @elseif($t->estado_ticket === 'solucionado')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-900 border border-[#fbc101]">Solución Propuesta</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-100 text-amber-900 border border-[#fbc101] animate-pulse">Abierto / En Taller</span>
                            @endif

                            @if($t->esTicketGeneral())
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[11px] font-black bg-slate-100 text-slate-800 border border-slate-200">
                                    <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    Infraestructura / Espacio
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[11px] font-black bg-blue-50 text-blue-800 border border-blue-200">
                                    <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    Activo
                                </span>
                            @endif
                        </div>

                        <span class="text-xs text-gray-500 font-medium">
                            Apertura: <strong class="text-gray-800">{{ $t->fecha ? $t->fecha->format('d/m/Y H:i') : '-' }}</strong> 
                            <span class="text-gray-400">({{ $t->fecha ? $t->fecha->diffForHumans() : '' }})</span>
                        </span>
                    </div>

                    <!-- Fila de Tipo, Ámbito y Áreas -->
                    <div class="flex flex-wrap items-center gap-2">
                        @if($t->esTicketGeneral())
                            <span class="text-xs font-black text-amber-900 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-lg">
                                Ámbito: {{ $t->ambitoTicket?->nombre ?? 'LIA General' }}
                            </span>
                            @if($t->puesto)
                                <span class="text-xs font-medium text-gray-600 bg-gray-50 border border-gray-200 px-2 py-0.5 rounded">Sector: {{ $t->puesto }}</span>
                            @endif
                        @else
                            <a href="{{ route('elementos.show', $t->elemento_id) }}" class="text-xs font-black text-amber-900 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-lg hover:bg-amber-100 transition-colors">
                                Activo: {{ $t->elemento?->nro_lia ?: 'Sin Nro LIA' }} 
                                @if($t->elemento?->tipo)
                                    ({{ ucfirst($t->elemento->tipo->nombre) }})
                                @endif
                                • {{ $t->elemento?->marca }} {{ $t->elemento?->modelo }}
                            </a>
                            @if($t->elemento?->nro_unsj)
                                <span class="text-xs font-bold text-gray-600 bg-gray-50 border border-gray-200 px-2 py-0.5 rounded">UNSJ: {{ $t->elemento->nro_unsj }}</span>
                            @endif
                        @endif

                        @if($t->areas && $t->areas->isNotEmpty())
                            @foreach($t->areas as $ar)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-black border"
                                      style="background-color: {{ $ar->color ? $ar->color . '20' : '#f3f4f6' }}; color: {{ $ar->color ?: '#374151' }}; border-color: {{ $ar->color ? $ar->color . '60' : '#d1d5db' }};">
                                    {{ $ar->nombre }}
                                </span>
                            @endforeach
                        @endif
                    </div>

                    <!-- Fila Central: Título + Texto a la izquierda, Fotos a la derecha -->
                    <div class="flex flex-col md:flex-row items-start justify-between gap-6">
                        <!-- Título y Texto -->
                        <div class="flex-1 space-y-2 w-full">
                            <h2 class="text-lg font-black text-gray-900 tracking-tight leading-snug hover:text-amber-600 transition-colors">
                                <a href="{{ route('tickets.show', $t->id) }}">
                                    {{ $t->titulo_display }}
                                </a>
                            </h2>

                            @if($t->comentario)
                                <div class="bg-gray-50/80 p-3.5 rounded-xl border border-gray-100">
                                    <x-markdown :content="Str::limit($t->comentario, 320)" class="text-xs text-gray-700 leading-relaxed" />
                                </div>
                            @endif
                        </div>

                        <!-- Caja de Fotos (Bosquejo 1: Fotos generosas con carrusel si hay más de 1) -->
                        @if($t->adjuntos->isNotEmpty())
                            <div class="w-full md:w-64 shrink-0">
                                <x-ticket-carousel :fotos="$t->adjuntos" aspect="aspect-[4/3]" maxHeight="max-h-44" />
                            </div>
                        @endif
                    </div>

                    <!-- Fila Inferior (Pie): Autor e Ingresar -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-gray-100">
                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                            <span>Autor: <strong class="text-gray-800 font-bold">{{ $t->solicitante ?: ($t->usuario?->nombre ?? 'Sistema') }}</strong></span>
                            <span>•</span>
                            <span>Ubicación: <strong class="text-gray-700">{{ $t->esTicketGeneral() ? ($t->ambitoTicket?->nombre ?? 'LIA General') : ($t->ubicacion?->nombre ?? 'Depósito') }} ({{ $t->puesto ?: 'S/P' }})</strong></span>
                            @if($t->comentarios->count() > 0)
                                <span>•</span>
                                <span class="font-bold text-gray-600">{{ $t->comentarios->count() }} {{ $t->comentarios->count() === 1 ? 'respuesta' : 'respuestas' }}</span>
                            @endif
                        </div>

                        <a href="{{ route('tickets.show', $t->id) }}" 
                           class="inline-flex items-center gap-1.5 bg-[#fbc101] hover:bg-amber-400 text-black font-black text-xs py-2 px-5 rounded-xl transition-all shadow-xs hover:shadow-sm">
                            <span>Ingresar</span>
                            <span>→</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-4">
            {{ $tickets->links() }}
        </div>
    @endif
</div>
@endsection
