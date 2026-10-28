@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto py-4 space-y-4">
    <!-- Header Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <span>Trazabilidad</span>
                <span>/</span>
                <span class="text-figma-text-primary">Movimientos</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <span>Historial de Movimientos de Activos</span>
                <span class="text-[10px] bg-figma-bg-deep text-figma-brand font-mono font-bold px-2 py-0.5 rounded border border-figma-border-subtle">
                    {{ $movimientos->total() }} registros
                </span>
            </h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('movimientos.rapido') }}" class="figma-btn-primary text-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Movimiento Rápido</span>
            </a>
            <a href="{{ route('reportes.show', 'movimientos-fechas') }}" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Auditoría Fechas</span>
            </a>
        </div>
    </div>

    <!-- Filters & Search Form -->
    <div class="figma-panel p-3">
        <form action="{{ route('movimientos.index') }}" method="GET" class="space-y-2.5">
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por LIA, UNSJ, marca, solicitante..." 
                        class="figma-input w-full pl-9 pr-3 py-1.5 text-xs">
                    <svg class="w-4 h-4 text-figma-text-tertiary absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button type="submit" class="figma-btn-primary text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Buscar</span>
                </button>
                @if(request()->hasAny(['search', 'tipo_movimiento', 'ubicacion_id']))
                    <a href="{{ route('movimientos.index') }}" class="figma-btn-secondary text-xs">
                        <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Limpiar</span>
                    </a>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-figma-border-subtle text-xs">
                <span class="font-bold text-figma-text-tertiary uppercase text-[10px] tracking-wider mr-1">Filtrar por:</span>

                <select name="tipo_movimiento" onchange="this.form.submit()" class="figma-input px-2.5 py-1 text-xs">
                    <option value="">Todos los Tipos</option>
                    <option value="interno" {{ request('tipo_movimiento') === 'interno' ? 'selected' : '' }}>Traslados Internos</option>
                    <option value="externo_prestamo" {{ request('tipo_movimiento') === 'externo_prestamo' ? 'selected' : '' }}>Préstamos Externos</option>
                </select>

                <select name="ubicacion_id" onchange="this.form.submit()" class="figma-input px-2.5 py-1 text-xs">
                    <option value="">Todas las Salas</option>
                    @foreach($ubicaciones as $ub)
                        <option value="{{ $ub->id }}" {{ request('ubicacion_id') == $ub->id ? 'selected' : '' }}>{{ $ub->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="figma-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-figma-bg-deep border-b border-figma-border-subtle text-[10px] font-bold text-figma-text-secondary uppercase tracking-wider">
                        <th class="py-2.5 px-3">Fecha / Hora</th>
                        <th class="py-2.5 px-3">Tipo Evento</th>
                        <th class="py-2.5 px-3">Activo Involucrado</th>
                        <th class="py-2.5 px-3">Destino / Puesto</th>
                        <th class="py-2.5 px-3">Situación</th>
                        <th class="py-2.5 px-3">Solicitante / Detalle</th>
                        <th class="py-2.5 px-3 text-right">Responsable</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-figma-border-subtle text-figma-text-primary text-xs">
                    @forelse($movimientos as $m)
                        <tr class="hover:bg-figma-bg-deep/50 transition-colors">
                            <td class="py-2.5 px-3 whitespace-nowrap font-mono text-figma-text-secondary text-[11px]">
                                {{ $m->fecha ? $m->fecha->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                {!! $m->presenter()->tipoBadge() !!}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <a href="{{ route('elementos.show', $m->elemento_id) }}" class="font-bold text-figma-brand hover:underline font-mono">
                                    {{ $m->elemento?->nro_lia ?: 'LIA: -' }}
                                </a>
                                <span class="text-[10px] text-figma-text-tertiary block font-mono">
                                    {{ strtoupper($m->elemento?->tipo?->nombre ?? '') }} {{ $m->elemento?->marca }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3">
                                <strong class="text-figma-text-primary">{{ $m->presenter()->destinoTexto() }}</strong>
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                {!! $m->presenter()->estadoBadge() !!}
                            </td>
                            <td class="py-2.5 px-3">
                                @if($m->esPrestamo())
                                    <div class="font-bold text-figma-text-primary">{{ $m->solicitante }}</div>
                                    <div class="text-[10px] text-figma-text-tertiary font-mono">
                                        Salida: {{ $m->fecha_prestamo ? $m->fecha_prestamo->format('d/m/Y') : '-' }}
                                        @if($m->fecha_devolucion)
                                            <span class="text-emerald-400 font-bold ml-1">| Devuelto {{ Carbon\Carbon::parse($m->fecha_devolucion)->format('d/m/Y') }}</span>
                                        @else
                                            <span class="text-amber-400 font-bold ml-1">| Activo ({{ $m->diasPrestado() }}d)</span>
                                        @endif
                                    </div>
                                @endif
                                @if($m->comentario)
                                    <div class="text-figma-text-secondary italic mt-0.5 line-clamp-1 text-[11px]">"{{ $m->comentario }}"</div>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-right whitespace-nowrap text-figma-text-secondary font-mono text-[11px]">
                                {{ $m->usuario?->nombre ?? 'Sistema' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-figma-text-tertiary italic text-xs">
                                No se encontraron movimientos con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movimientos->hasPages())
            <div class="p-3 border-t border-figma-border-subtle bg-figma-bg-deep">
                {{ $movimientos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
