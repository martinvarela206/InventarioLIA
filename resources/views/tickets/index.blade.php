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

        <!-- Buscador de tickets -->
        <form action="{{ route('tickets.index') }}" method="GET" class="w-full sm:w-72">
            <input type="hidden" name="estado" value="{{ $estado }}">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar LIA, UNSJ, problema..." 
                       class="w-full bg-white border border-gray-300 rounded-xl pl-9 pr-4 py-2 text-xs font-semibold text-gray-900 focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>
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
            <p class="text-xs text-gray-500 mt-1">Actualmente no existen registros bajo el estado "{{ ucfirst($estado) }}".</p>
            @if($estado !== 'abierto')
                <div class="mt-4">
                    <a href="{{ route('tickets.index', ['estado' => 'abierto']) }}" class="text-xs font-bold text-amber-700 hover:text-amber-900 underline">
                        Ver tickets abiertos
                    </a>
                </div>
            @endif
        </div>
    @else
        <div class="space-y-3">
            @foreach($tickets as $t)
                <div class="bg-white rounded-2xl border border-gray-200 shadow-xs hover:shadow-md transition-all p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- Columna Izquierda: Identidad y Falla -->
                    <div class="space-y-2 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-black text-gray-700 bg-gray-100 px-2 py-0.5 rounded font-mono">#{{ $t->id }}</span>
                            
                            @if($t->estado_ticket === 'resuelto')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Resuelto</span>
                            @elseif($t->estado_ticket === 'baja')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">Baja / Irreparable</span>
                            @elseif($t->estado_ticket === 'solucionado')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">Solución Propuesta</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 animate-pulse">Abierto / En Taller</span>
                            @endif

                            <span class="text-xs text-gray-400 font-medium">
                                Apertura: <strong class="text-gray-700">{{ $t->fecha ? $t->fecha->format('d/m/Y H:i') : '-' }}</strong> ({{ $t->fecha ? $t->fecha->diffForHumans() : '' }})
                            </span>
                        </div>

                        <!-- Info del Activo -->
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('elementos.show', $t->elemento_id) }}" class="text-base font-black text-gray-900 hover:text-amber-600 transition-colors">
                                {{ $t->elemento?->nro_lia ?: 'Sin Nro LIA' }}
                            </a>
                            @if($t->elemento?->nro_unsj)
                                <span class="text-xs font-bold text-gray-600 bg-gray-50 border border-gray-200 px-2 py-0.5 rounded">UNSJ: {{ $t->elemento->nro_unsj }}</span>
                            @endif
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">{{ ucfirst($t->elemento?->tipo?->nombre ?? 'N/A') }}</span>
                            <span class="text-xs text-gray-500">• {{ $t->elemento?->marca }} {{ $t->elemento?->modelo }}</span>
                        </div>

                        <!-- Descripción de la Falla -->
                        <p class="text-xs text-gray-700 bg-gray-50/70 p-3 rounded-xl border border-gray-100 leading-relaxed">
                            <strong class="text-gray-900 font-bold block mb-0.5">Problema reportado:</strong>
                            {{ $t->comentario }}
                        </p>

                        <!-- Informante y Ubicación -->
                        <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 pt-0.5">
                            <span>Informante: <strong class="text-gray-800">{{ $t->solicitante ?: ($t->usuario?->nombre ?? 'Sistema') }}</strong></span>
                            <span>•</span>
                            <span>Ubicación: <strong class="text-gray-800">{{ $t->ubicacion?->nombre ?? 'Depósito' }} ({{ $t->puesto ?: 'S/P' }})</strong></span>
                            @if($t->adjuntos->isNotEmpty())
                                <span>•</span>
                                <span class="inline-flex items-center gap-1 font-bold text-amber-700">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ $t->adjuntos->count() }} {{ $t->adjuntos->count() === 1 ? 'foto' : 'fotos' }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Columna Derecha: Fotos miniatura y Botón de Acción -->
                    <div class="flex flex-col sm:flex-row md:flex-col items-end justify-between gap-3 min-w-[160px]">
                        @if($t->adjuntos->isNotEmpty())
                            <div class="flex items-center gap-1.5">
                                @foreach($t->adjuntos->take(3) as $adj)
                                    <div class="w-10 h-10 rounded-lg overflow-hidden border border-gray-200 shadow-2xs bg-gray-100">
                                        <img src="{{ $adj->url }}" class="w-full h-full object-cover">
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <a href="{{ route('tickets.show', $t->id) }}" class="inline-flex items-center gap-1.5 bg-gray-900 hover:bg-amber-500 hover:text-black text-white font-bold text-xs py-2 px-4 rounded-xl transition-all shadow-sm">
                            <span>Gestionar / Resolver</span>
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
