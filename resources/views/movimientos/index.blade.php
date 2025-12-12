@extends('layouts.app')

@section('content')
    <h2 class="text-center mt-10 text-[#dba800] text-2xl font-semibold">Lista de Movimientos</h2>

    @can('write-data')
        <a href="{{ route('movimientos.create') }}" class="inline-block mt-5 ml-[5%] bg-[#dba800] text-[#111] px-5 py-2 rounded font-semibold transition-colors duration-200 hover:bg-[#fbc101] no-underline border-2 border-[#dba800] hover:border-[#fbc101]">Añadir Movimiento</a>
    @endcan

    <!-- Búsqueda -->
    <form action="{{ route('movimientos.index') }}" method="GET" class="w-[90%] mx-auto mt-6">
        <div class="flex gap-3 items-center">
            <input type="text" name="search" placeholder="Buscar por Nro LIA, Estado, Ubicación, Usuario o Comentario..." value="{{ request('search') }}" class="w-full rounded-lg border border-gray-200 px-4 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#dba800]" />
            <button type="submit" class="bg-[#dba800] hover:bg-[#fbc101] text-[#111] font-semibold py-2 px-4 rounded shadow border-2 border-[#dba800] hover:border-[#fbc101]">Buscar</button>
            @if(request('search'))
                <a href="{{ route('movimientos.index', request()->except('search')) }}" class="text-gray-600 hover:text-gray-800">Limpiar</a>
            @endif
        </div>
        @if(request('estado'))
            <input type="hidden" name="estado" value="{{ request('estado') }}">
        @endif
        @if(request('ultimo'))
            <input type="hidden" name="ultimo" value="1">
        @endif
    </form>

    <!-- Filtros -->
    <div class="flex justify-end gap-4 w-[90%] mx-auto mt-4">
        <a href="{{ route('movimientos.index', array_merge(request()->all(), ['ultimo' => !request()->boolean('ultimo')])) }}" 
           class="px-4 py-1 rounded-full border border-amber-700 text-sm font-semibold transition-colors {{ request()->boolean('ultimo') ? 'bg-amber-700 text-white' : 'text-amber-700 hover:bg-amber-50' }}">
           Último
        </a>
        
        <div class="flex items-center rounded-full border {{ request('estado') ? 'bg-amber-700 border-amber-700 text-white' : 'border-amber-700 text-amber-700 hover:bg-amber-50' }} overflow-hidden transition-colors">
            @if(request('estado'))
                <a href="{{ route('movimientos.index', array_merge(request()->except('estado'))) }}" class="pl-4 pr-1 text-sm font-semibold hover:text-amber-200 cursor-pointer" title="Quitar filtro">Estado</a>
            @else
                <span class="pl-4 pr-1 text-sm font-semibold cursor-pointer" onclick="this.parentElement.querySelector('select').focus()">Estado</span>
            @endif
            <select 
                name="estado" 
                onchange="filterByEstadoMov(this.value)" 
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
        function filterByEstadoMov(estadoNombre) {
            const url = new URL(window.location.href);
            if (estadoNombre) {
                url.searchParams.set('estado', estadoNombre);
            } else {
                url.searchParams.delete('estado');
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }
        
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
            
            // Focus back on input if it has value
            if (searchInput.value) {
                searchInput.focus();
                const val = searchInput.value;
                searchInput.value = '';
                searchInput.value = val;
            }
        });
    </script>

    <div class="w-[90%] mx-auto mt-4 shadow-md bg-white rounded-lg overflow-hidden">
        <table class="min-w-full table-auto block md:table">
            <thead class="hidden md:table-header-group">
                <tr class="bg-amber-400 text-amber-900 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">Nro LIA</th>
                    <th class="py-3 px-6 text-left">Estado</th>
                    <th class="py-3 px-6 text-left">Ubicación</th>
                    <th class="py-3 px-6 text-left">Fecha</th>
                    <th class="py-3 px-6 text-left">Comentario</th>
                    <th class="py-3 px-6 text-left">Usuario</th>
                    @can('write-data')
                        <th class="py-3 px-6 text-center">Acciones</th>
                    @endcan
                </tr>
            </thead>
            <tbody class="text-gray-900 text-sm font-medium block md:table-row-group">
                @foreach($movimientos as $movimiento)
                    <tr class="block md:table-row odd:bg-amber-50 even:bg-amber-100 border-b border-gray-300 hover:bg-amber-200 mb-4 md:mb-0 rounded-lg shadow-sm md:shadow-none p-4 md:p-0">
                        <td class="py-2 px-4 md:py-3 md:px-6 text-left whitespace-nowrap block md:table-cell flex justify-between md:block">
                             <span class="font-bold md:hidden text-amber-900">Nro LIA:</span>
                            <a href="{{ route('elementos.show', $movimiento->nro_lia) }}" class="text-amber-700 hover:text-amber-900 font-semibold">
                                {{ $movimiento->nro_lia }}
                            </a>
                        </td>
                        <td class="py-2 px-4 md:py-3 md:px-6 text-left block md:table-cell flex justify-between md:block items-center">
                            <span class="font-bold md:hidden text-amber-900">Estado:</span>
                            <span class="px-2 py-1 rounded text-xs font-semibold
                                {{ $movimiento->estado?->nombre === 'prestado' ? 'bg-blue-200 text-blue-800' : '' }}
                                {{ $movimiento->estado?->nombre === 'dado de baja' ? 'bg-red-200 text-red-800' : '' }}
                                {{ $movimiento->estado?->nombre === 'funcionando' ? 'bg-green-200 text-green-800' : '' }}
                                {{ $movimiento->estado?->nombre === 'guardado' ? 'bg-yellow-200 text-yellow-800' : '' }}
                                {{ $movimiento->estado?->nombre === 'ingresado' ? 'bg-gray-200 text-gray-800' : '' }}">
                                {{ ucfirst($movimiento->estado?->nombre ?? 'N/A') }}
                            </span>
                        </td>
                        <td class="py-2 px-4 md:py-3 md:px-6 text-left block md:table-cell flex justify-between md:block">
                             <span class="font-bold md:hidden text-amber-900">Ubicación:</span>
                            {{ $movimiento->ubicacion?->nombre ?? 'Sin ubicación' }}
                        </td>
                        <td class="py-2 px-4 md:py-3 md:px-6 text-left block md:table-cell flex justify-between md:block">
                             <span class="font-bold md:hidden text-amber-900">Fecha:</span>
                            {{ $movimiento->fecha->format('Y-m-d') }}
                        </td>
                        <td class="py-2 px-4 md:py-3 md:px-6 text-left block md:table-cell">
                             <span class="font-bold md:hidden text-amber-900 block mb-1">Comentario:</span>
                            {{ $movimiento->comentario ?? '-' }}
                        </td>
                        <td class="py-2 px-4 md:py-3 md:px-6 text-left block md:table-cell flex justify-between md:block">
                             <span class="font-bold md:hidden text-amber-900">Usuario:</span>
                            {{ $movimiento->usuario->name }}
                        </td>
                        @can('write-data')
                            <td class="py-2 px-4 md:py-3 md:px-6 text-center block md:table-cell flex justify-between md:block items-center">
                                 <span class="font-bold md:hidden text-amber-900">Acciones:</span>
                                <div class="flex item-center justify-end md:justify-center gap-2">
                                    <a href="{{ route('movimientos.edit', $movimiento->id) }}" class="text-amber-700 hover:text-amber-900">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('movimientos.destroy', $movimiento->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este movimiento?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        @endcan
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="w-[90%] mx-auto mt-4 mb-6">
        {{ $movimientos->appends(request()->query())->links('vendor.pagination.responsive-custom') }}
    </div>
@endsection
