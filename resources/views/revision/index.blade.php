@extends('layouts.app')

@section('content')
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold">Revisión de Inventario</h1>
            <p class="text-sm text-gray-600">Explora y verifica la ubicación de los elementos</p>
        </div>
    </div>

    <form action="{{ route('revision.index') }}" method="GET" class="mb-6">
        <div class="flex gap-3 items-center">
            <input type="text" name="search" placeholder="Buscar por Nro LIA, Descripción o Ubicación..." value="{{ request('search') }}" class="w-full rounded-lg border border-gray-200 px-4 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#dba800]" />
            <button type="submit" class="bg-[#dba800] hover:bg-[#fbc101] text-[#111] font-semibold py-2 px-4 rounded shadow">Buscar</button>
            @if(request('search'))
                <a href="{{ route('revision.index', request()->except('search')) }}" class="text-gray-600 hover:text-gray-800">Limpiar</a>
            @endif
        </div>
        @if(request('tipo'))
            <input type="hidden" name="tipo" value="{{ request('tipo') }}">
        @endif
        @if(request('gb'))
            <input type="hidden" name="gb" value="{{ request('gb') }}">
        @endif
        @if(request('estado'))
            <input type="hidden" name="estado" value="{{ request('estado') }}">
        @endif
    </form>

    <div class="flex justify-end gap-4 mb-6 items-center">
        <form action="{{ route('revision.index') }}" method="GET" class="flex items-center">
            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
            @if(request('tipo')) <input type="hidden" name="tipo" value="{{ request('tipo') }}"> @endif
            @if(request('estado')) <input type="hidden" name="estado" value="{{ request('estado') }}"> @endif
            
            <div class="flex items-center rounded-full border {{ request('gb') ? 'bg-amber-700 border-amber-700 text-white' : 'border-amber-700 text-amber-700 hover:bg-amber-50' }} overflow-hidden transition-colors">
                @if(request('gb'))
                    <a href="{{ route('revision.index', array_merge(request()->except('gb'))) }}" class="pl-4 pr-1 text-sm font-semibold hover:text-amber-200 cursor-pointer leading-none" title="Quitar filtro">GB</a>
                @else
                    <span class="pl-4 pr-1 text-sm font-semibold cursor-pointer leading-none" onclick="this.parentElement.querySelector('input[name=\'gb\']').focus()">GB</span>
                @endif
                <input 
                    type="number" 
                    name="gb" 
                    value="{{ request('gb') }}" 
                    class="w-16 py-1 px-1 bg-transparent border-none focus:ring-0 text-sm font-semibold placeholder-amber-700/50 {{ request('gb') ? 'text-white placeholder-white/70' : 'text-amber-700' }} focus:outline-none"
                    placeholder="#"
                    onchange="this.form.submit()"
                >

            </div>
        </form>

        <div class="flex items-center rounded-full border {{ request('tipo') ? 'bg-amber-700 border-amber-700 text-white' : 'border-amber-700 text-amber-700 hover:bg-amber-50' }} overflow-hidden transition-colors">
            @if(request('tipo'))
                <a href="{{ route('revision.index', array_merge(request()->except('tipo'))) }}" class="pl-4 pr-1 text-sm font-semibold hover:text-amber-200 cursor-pointer leading-none" title="Quitar filtro">Tipo</a>
            @else
                <span class="pl-4 pr-1 text-sm font-semibold cursor-pointer leading-none" onclick="this.parentElement.querySelector('select').focus()">Tipo</span>
            @endif
            <select 
                name="tipo" 
                onchange="filterByTipo(this.value)" 
                class="w-32 py-1 px-1 bg-transparent border-none focus:ring-0 text-sm font-semibold {{ request('tipo') ? 'text-white' : 'text-amber-700' }} focus:outline-none [&>option]:text-gray-900 [&>option]:bg-white [&>option:hover]:bg-amber-100 [&>option:checked]:bg-amber-200">
                <option value="">Todos</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->nombre }}" {{ request('tipo') === $tipo->nombre ? 'selected' : '' }}>
                        {{ ucfirst($tipo->nombre) }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="flex items-center rounded-full border {{ request('estado') ? 'bg-amber-700 border-amber-700 text-white' : 'border-amber-700 text-amber-700 hover:bg-amber-50' }} overflow-hidden transition-colors">
            @if(request('estado'))
                <a href="{{ route('revision.index', array_merge(request()->except('estado'))) }}" class="pl-4 pr-1 text-sm font-semibold hover:text-amber-200 cursor-pointer leading-none" title="Quitar filtro">Estado</a>
            @else
                <span class="pl-4 pr-1 text-sm font-semibold cursor-pointer leading-none" onclick="this.parentElement.querySelector('select').focus()">Estado</span>
            @endif
            <select 
                name="estado" 
                onchange="filterByEstado(this.value)" 
                class="w-32 py-1 px-1 bg-transparent border-none focus:ring-0 text-sm font-semibold {{ request('estado') ? 'text-white' : 'text-amber-700' }} focus:outline-none [&>option]:text-gray-900 [&>option]:bg-white [&>option:hover]:bg-amber-100 [&>option:checked]:bg-amber-200">
                <option value="">Todos</option>
                @foreach($estados as $estado)
                    <option value="{{ $estado->nombre }}" {{ request('estado') === $estado->nombre ? 'selected' : '' }}>
                        {{ ucfirst($estado->nombre) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <script>
        function filterByTipo(tipoNombre) {
            const url = new URL(window.location.href);
            if (tipoNombre) {
                url.searchParams.set('tipo', tipoNombre);
            } else {
                url.searchParams.delete('tipo');
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }
        
        function filterByEstado(estadoNombre) {
            const url = new URL(window.location.href);
            if (estadoNombre) {
                url.searchParams.set('estado', estadoNombre);
            } else {
                url.searchParams.delete('estado');
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="search"]');
            const searchForm = searchInput.closest('form');
            let timeout = null;

            searchInput.addEventListener('input', function() {
                clearTimeout(timeout);
                // Trigger search from 1 character or when empty (to clear)
                if (this.value.length >= 1 || this.value.length === 0) {
                    timeout = setTimeout(() => {
                        searchForm.submit();
                    }, 600);
                }
            });
            
            // Focus back on input if it has value (simple UX improvement)
            if (searchInput.value) {
                searchInput.focus();
                // Move cursor to end
                const val = searchInput.value;
                searchInput.value = '';
                searchInput.value = val;
            }
        });
    </script>

    <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
        <table class="min-w-full table-auto block md:table">
            <thead class="hidden md:table-header-group">
                <tr class="bg-amber-400 text-amber-900 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">Nro LIA</th>
                    <th class="py-3 px-6 text-left">Descripción</th>
                    <th class="py-3 px-6 text-left">Estado</th>
                    <th class="py-3 px-6 text-left">Vencimiento</th>
                    <th class="py-3 px-6 text-left">Ubicación Actual</th>
                </tr>
            </thead>
            <tbody class="text-gray-900 text-sm font-medium block md:table-row-group">
                @foreach($elementos as $elemento)
                    <tr class="block md:table-row odd:bg-amber-50 even:bg-amber-100 border-b border-gray-300 hover:bg-amber-200 cursor-pointer mb-4 md:mb-0 rounded-lg shadow-sm md:shadow-none p-4 md:p-0" onclick="window.location='{{ route('elementos.show', $elemento->nro_lia) }}?from=revision'">
                        <td class="py-2 px-4 md:py-3 md:px-6 text-left whitespace-nowrap block md:table-cell flex justify-between md:block">
                             <span class="font-bold md:hidden text-amber-900">Nro LIA:</span>
                            {{ $elemento->nro_lia }}
                        </td>
                        <td class="py-2 px-4 md:py-3 md:px-6 text-left block md:table-cell">
                             <span class="font-bold md:hidden text-amber-900 block mb-1">Descripción:</span>
                            {{ $elemento->descripcion_texto }}
                        </td>
                        <td class="py-2 px-4 md:py-3 md:px-6 text-left block md:table-cell flex justify-between md:block items-center">
                             <span class="font-bold md:hidden text-amber-900">Estado:</span>
                            <span class="px-2 py-1 rounded text-xs font-semibold
                                {{ $elemento->ultimoMovimiento?->estado?->nombre === 'prestado' ? 'bg-blue-200 text-blue-800' : '' }}
                                {{ $elemento->ultimoMovimiento?->estado?->nombre === 'dado de baja' ? 'bg-red-200 text-red-800' : '' }}
                                {{ $elemento->ultimoMovimiento?->estado?->nombre === 'funcionando' ? 'bg-green-200 text-green-800' : '' }}
                                {{ $elemento->ultimoMovimiento?->estado?->nombre === 'guardado' ? 'bg-yellow-200 text-yellow-800' : '' }}
                                {{ $elemento->ultimoMovimiento?->estado?->nombre === 'ingresado' ? 'bg-gray-200 text-gray-800' : '' }}">
                                {{ ucfirst($elemento->ultimoMovimiento?->estado?->nombre ?? 'N/A') }}
                            </span>
                        </td>
                        <td class="py-2 px-4 md:py-3 md:px-6 text-left block md:table-cell flex justify-between md:block">
                             <span class="font-bold md:hidden text-amber-900">Vencimiento:</span>
                            @if($elemento->fecha_vencimiento_garantia)
                                <span class="{{ $elemento->fecha_vencimiento_garantia->isPast() ? 'text-red-600 font-semibold' : 'text-green-600' }}">
                                    {{ $elemento->fecha_vencimiento_garantia->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="py-2 px-4 md:py-3 md:px-6 text-left block md:table-cell flex justify-between md:block">
                             <span class="font-bold md:hidden text-amber-900">Ubicación Actual:</span>
                            @if($elemento->ultimoMovimiento)
                                {{ $elemento->ultimoMovimiento->ubicacion?->nombre ?? 'Sin ubicación' }}
                                <span class="text-xs text-gray-500 block">({{ $elemento->ultimoMovimiento->fecha->format('Y-m-d') }})</span>
                            @else
                                <span class="text-gray-400">Sin movimientos</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $elementos->appends(request()->query())->links('vendor.pagination.responsive-custom') }}
    </div>
@endsection
