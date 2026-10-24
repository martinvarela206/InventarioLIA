@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <span>Historial de Movimientos y Préstamos</span>
                <span class="text-xs bg-amber-100 text-amber-900 font-bold px-2.5 py-0.5 rounded-full border border-amber-300">
                    {{ $movimientos->total() }} registros
                </span>
            </h1>
            <p class="text-sm text-gray-600 mt-0.5">Trazabilidad inmutable de traslados físicos entre salas y préstamos a docentes</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('movimientos.rapido') }}" class="inline-flex items-center gap-1.5 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold px-4 py-2.5 rounded-xl shadow-sm transition-all text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Nuevo Movimiento Rápido
            </a>
            <a href="{{ route('reportes.show', 'movimientos-fechas') }}" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-3.5 py-2.5 rounded-xl shadow-sm transition-all text-sm">
                📄 Ver Bitácora Oficial
            </a>
        </div>
    </div>

    <!-- Filters & Search Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form action="{{ route('movimientos.index') }}" method="GET" class="space-y-3">
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por LIA, UNSJ, marca, solicitante, o comentarios..." 
                        class="w-full rounded-xl border border-gray-300 pl-10 pr-4 py-2 text-sm shadow-inner focus:outline-none focus:ring-2 focus:ring-amber-400 font-medium">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button type="submit" class="bg-amber-400 hover:bg-amber-500 text-black font-bold px-4 py-2 rounded-xl text-sm transition-colors">
                    Buscar
                </button>
                @if(request()->hasAny(['search', 'tipo_movimiento', 'ubicacion_id']))
                    <a href="{{ route('movimientos.index') }}" class="text-xs text-gray-500 hover:text-black self-center px-2">Limpiar</a>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-100 text-xs">
                <span class="font-bold text-gray-400 uppercase tracking-wider mr-1">Filtrar por:</span>

                <select name="tipo_movimiento" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 font-semibold text-gray-700">
                    <option value="">Todos los Tipos</option>
                    <option value="interno" {{ request('tipo_movimiento') === 'interno' ? 'selected' : '' }}>Traslados Internos</option>
                    <option value="externo_prestamo" {{ request('tipo_movimiento') === 'externo_prestamo' ? 'selected' : '' }}>Préstamos Externos</option>
                </select>

                <select name="ubicacion_id" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 font-semibold text-gray-700">
                    <option value="">Todas las Salas</option>
                    @foreach($ubicaciones as $ub)
                        <option value="{{ $ub->id }}" {{ request('ubicacion_id') == $ub->id ? 'selected' : '' }}>{{ $ub->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Fecha / Hora</th>
                        <th class="py-3 px-4">Tipo Evento</th>
                        <th class="py-3 px-4">Activo Involucrado</th>
                        <th class="py-3 px-4">Destino / Puesto</th>
                        <th class="py-3 px-4">Situación</th>
                        <th class="py-3 px-4">Solicitante / Detalle</th>
                        <th class="py-3 px-4 text-right">Responsable</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-800 text-xs">
                    @forelse($movimientos as $m)
                        <tr class="hover:bg-amber-50/40 transition-colors">
                            <td class="py-3 px-4 whitespace-nowrap font-mono">
                                {{ $m->fecha ? $m->fecha->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                {!! $m->presenter()->tipoBadge() !!}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <a href="{{ route('elementos.show', $m->elemento_id) }}" class="font-bold text-gray-900 hover:text-amber-700 font-mono">
                                    {{ $m->elemento?->nro_lia ?: 'LIA: -' }}
                                </a>
                                <span class="text-[11px] text-gray-500 block">
                                    {{ strtoupper($m->elemento?->tipo?->nombre ?? '') }} {{ $m->elemento?->marca }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <strong class="text-gray-900">{{ $m->presenter()->destinoTexto() }}</strong>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                {!! $m->presenter()->estadoBadge() !!}
                            </td>
                            <td class="py-3 px-4">
                                @if($m->esPrestamo())
                                    <div class="font-bold text-amber-900">{{ $m->solicitante }}</div>
                                    <div class="text-[10px] text-gray-500">
                                        Salida: {{ $m->fecha_prestamo ? $m->fecha_prestamo->format('d/m/Y') : '-' }}
                                        @if($m->fecha_devolucion)
                                            <span class="text-emerald-700 font-bold ml-1">| Devuelto {{ Carbon\Carbon::parse($m->fecha_devolucion)->format('d/m/Y') }}</span>
                                        @else
                                            <span class="text-amber-800 font-bold ml-1">| Activo ({{ $m->diasPrestado() }}d)</span>
                                        @endif
                                    </div>
                                @endif
                                @if($m->comentario)
                                    <div class="text-gray-600 italic mt-0.5 line-clamp-1">"{{ $m->comentario }}"</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap text-gray-500 font-medium">
                                {{ $m->usuario?->nombre ?? 'Sistema' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400">
                                No se encontraron movimientos con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movimientos->hasPages())
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                {{ $movimientos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
