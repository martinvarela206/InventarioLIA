@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2">
    <!-- Header bar -->
    <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <span>Catálogo de Activos e Inventario LIA</span>
                <span class="text-sm font-semibold bg-amber-100 text-amber-900 px-2.5 py-0.5 rounded-full border border-amber-300">
                    {{ $elementos->total() }} registros
                </span>
            </h1>
            <p class="text-sm text-gray-600 mt-0.5">Gestión y control patrimonial de computadoras, periféricos y equipamiento</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('movimientos.rapido') }}" class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-black font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Movimiento Rápido
            </a>
            @if(Auth::user()->isItemAdmin() || Auth::user()->isAdmin())
                <a href="{{ route('elementos.create') }}" class="inline-flex items-center gap-1.5 bg-[#111] hover:bg-gray-800 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Añadir Activo
                </a>
            @endif
        </div>
    </div>

    <!-- Search & Multi-Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form action="{{ route('elementos.index') }}" method="GET" class="space-y-3">
            <div class="flex gap-2 items-center">
                <div class="relative flex-1">
                    <input type="text" name="search" placeholder="Buscar por LIA, UNSJ, marca, modelo, Nro de serie o características..." 
                        value="{{ request('search') }}" 
                        class="w-full rounded-xl border border-gray-300 pl-10 pr-4 py-2.5 text-sm shadow-inner focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 font-medium" />
                    <svg class="w-5 h-5 text-gray-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button type="submit" class="bg-amber-400 hover:bg-amber-500 text-black font-bold py-2.5 px-5 rounded-xl shadow-sm transition-all text-sm">
                    Buscar
                </button>
                @if(request()->hasAny(['search', 'tipo', 'estado', 'ubicacion', 'gb', 'garantia']))
                    <a href="{{ route('elementos.index') }}" class="text-sm font-semibold text-gray-500 hover:text-black px-2 py-2">
                        Limpiar filtros
                    </a>
                @endif
            </div>

            <!-- Filters Pill Row -->
            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-100 text-xs">
                <span class="font-bold text-gray-400 uppercase tracking-wider mr-1">Filtros:</span>

                <!-- Filter Tipo -->
                <select name="tipo" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 font-semibold text-gray-700 cursor-pointer hover:bg-gray-100">
                    <option value="">Todos los Tipos</option>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo->nombre }}" {{ request('tipo') === $tipo->nombre ? 'selected' : '' }}>
                            {{ ucfirst($tipo->nombre) }}
                        </option>
                    @endforeach
                </select>

                <!-- Filter Estado -->
                <select name="estado" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 font-semibold text-gray-700 cursor-pointer hover:bg-gray-100">
                    <option value="">Todos los Estados</option>
                    @foreach($estados as $estado)
                        <option value="{{ $estado->nombre }}" {{ request('estado') === $estado->nombre ? 'selected' : '' }}>
                            {{ ucfirst($estado->nombre) }}
                        </option>
                    @endforeach
                </select>

                <!-- Filter Ubicación -->
                <select name="ubicacion" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 font-semibold text-gray-700 cursor-pointer hover:bg-gray-100">
                    <option value="">Todas las Salas</option>
                    @foreach($ubicaciones as $ub)
                        <option value="{{ $ub->id }}" {{ request('ubicacion') == $ub->id ? 'selected' : '' }}>
                            {{ $ub->nombre }}
                        </option>
                    @endforeach
                </select>

                <!-- Filter Garantía -->
                <select name="garantia" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 font-semibold text-gray-700 cursor-pointer hover:bg-gray-100">
                    <option value="">Garantía: Todas</option>
                    <option value="vigente" {{ request('garantia') === 'vigente' ? 'selected' : '' }}>Garantía Vigente</option>
                    <option value="vencida" {{ request('garantia') === 'vencida' ? 'selected' : '' }}>Garantía Vencida</option>
                    <option value="sin_datos" {{ request('garantia') === 'sin_datos' ? 'selected' : '' }}>Sin Datos</option>
                </select>

                <!-- Filter GB con cursor-pointer arreglado -->
                <div class="flex items-center rounded-lg border border-gray-200 bg-gray-50 px-2 py-1 cursor-pointer hover:bg-gray-100">
                    <label for="filter-gb-input" class="text-xs font-bold text-gray-700 cursor-pointer mr-1">RAM / Disco (GB):</label>
                    <input type="number" id="filter-gb-input" name="gb" value="{{ request('gb') }}" placeholder="Ej: 16" 
                        class="w-14 bg-transparent border-none text-xs font-bold focus:ring-0 cursor-pointer p-0 text-gray-900" 
                        onchange="this.form.submit()">
                </div>
            </div>
        </form>
    </div>

    <!-- Assets Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Identificadores</th>
                        <th class="py-3 px-4">Tipo / Categoría</th>
                        <th class="py-3 px-4">Marca y Modelo</th>
                        <th class="py-3 px-4">Ubicación y Puesto</th>
                        <th class="py-3 px-4">Estado</th>
                        <th class="py-3 px-4">Garantía / Adq.</th>
                        <th class="py-3 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @forelse($elementos as $el)
                        <tr class="hover:bg-amber-50/50 transition-colors">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <a href="{{ route('elementos.show', $el->id) }}" class="font-mono font-bold text-gray-900 hover:text-amber-600 block">
                                    {{ $el->nro_lia ?: 'LIA: -' }}
                                </a>
                                <span class="font-mono text-xs text-gray-500">
                                    UNSJ: {{ $el->nro_unsj ?: '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-800">
                                    {{ strtoupper($el->tipo?->nombre ?? 'Activo') }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-gray-900">{{ $el->marca ?: '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $el->modelo ?: '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-900">{{ $el->presenter()->puestoUbicacion() }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                {!! $el->presenter()->statusBadge() !!}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-xs">
                                {!! $el->presenter()->garantiaBadge() !!}
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('elementos.show', $el->id) }}" class="p-1.5 text-gray-500 hover:text-black hover:bg-gray-100 rounded-md transition-colors" title="Ver detalle dual-pane">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('movimientos.rapido', ['elemento_id' => $el->id]) }}" class="p-1.5 text-amber-600 hover:text-amber-800 hover:bg-amber-100 rounded-md transition-colors" title="Mover o prestar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    </a>
                                    @if(Auth::user()->isItemAdmin() || Auth::user()->isAdmin())
                                        <a href="{{ route('elementos.edit', $el->id) }}" class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md transition-colors" title="Editar ficha">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-gray-400 text-sm">
                                No se encontraron activos que coincidan con los criterios de búsqueda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($elementos->hasPages())
            <div class="p-4 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
                {{ $elementos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
