@extends('layouts.app')

@section('content')
    <h2 class="text-center mt-10 text-[#dba800] text-2xl font-semibold">Lista de Elementos</h2>
    
    @can('write-data')
        <a href="{{ route('elementos.create') }}" class="inline-block mt-5 ml-[10%] bg-[#dba800] text-[#111] px-5 py-2 rounded font-semibold transition-colors duration-200 hover:bg-[#fbc101] no-underline border-2 border-[#dba800] hover:border-[#fbc101]">Añadir Elemento</a>
    @endcan

    <form action="{{ route('elementos.index') }}" method="GET" class="w-4/5 mx-auto mt-6">
        <div class="flex gap-3 items-center">
            <input type="text" name="search" placeholder="Buscar por Nro LIA, Tipo o Descripción..." value="{{ request('search') }}" class="w-full rounded-lg border border-gray-200 px-4 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#dba800]" />
            <button type="submit" class="bg-[#dba800] hover:bg-[#fbc101] text-[#111] font-semibold py-2 px-4 rounded shadow border-2 border-[#dba800] hover:border-[#fbc101]">Buscar</button>
            @if(request('search'))
                <a href="{{ route('elementos.index', request()->except('search')) }}" class="text-gray-600 hover:text-gray-800">Limpiar</a>
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

    <div class="flex justify-end gap-4 w-4/5 mx-auto mt-4 items-center">
        <form action="{{ route('elementos.index') }}" method="GET" class="flex items-center">
            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
            @if(request('tipo')) <input type="hidden" name="tipo" value="{{ request('tipo') }}"> @endif
            @if(request('estado')) <input type="hidden" name="estado" value="{{ request('estado') }}"> @endif
            
            <div class="flex items-center rounded-full border {{ request('gb') ? 'bg-amber-700 border-amber-700 text-white' : 'border-amber-700 text-amber-700 hover:bg-amber-50' }} overflow-hidden transition-colors">
                @if(request('gb'))
                    <a href="{{ route('elementos.index', array_merge(request()->except('gb'))) }}" class="pl-4 pr-1 text-sm font-semibold hover:text-amber-200 cursor-pointer" title="Quitar filtro">GB</a>
                @else
                    <span class="pl-4 pr-1 text-sm font-semibold cursor-pointer" onclick="this.parentElement.querySelector('input[name=\'gb\']').focus()">GB</span>
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
                <a href="{{ route('elementos.index', array_merge(request()->except('tipo'))) }}" class="pl-4 pr-1 text-sm font-semibold hover:text-amber-200 cursor-pointer" title="Quitar filtro">Tipo</a>
            @else
                <span class="pl-4 pr-1 text-sm font-semibold cursor-pointer" onclick="this.parentElement.querySelector('select').focus()">Tipo</span>
            @endif
            <select 
                name="tipo" 
                onchange="filterByTipoElem(this.value)" 
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
                <a href="{{ route('elementos.index', array_merge(request()->except('estado'))) }}" class="pl-4 pr-1 text-sm font-semibold hover:text-amber-200 cursor-pointer" title="Quitar filtro">Estado</a>
            @else
                <span class="pl-4 pr-1 text-sm font-semibold cursor-pointer" onclick="this.parentElement.querySelector('select').focus()">Estado</span>
            @endif
            <select 
                name="estado" 
                onchange="filterByEstadoElem(this.value)" 
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
        function filterByTipoElem(tipoNombre) {
            const url = new URL(window.location.href);
            if (tipoNombre) {
                url.searchParams.set('tipo', tipoNombre);
            } else {
                url.searchParams.delete('tipo');
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }
        
        function filterByEstadoElem(estadoNombre) {
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

            if (searchInput.value) {
                searchInput.focus();
                const val = searchInput.value;
                searchInput.value = '';
                searchInput.value = val;
            }
        });
    </script>

    <div class="w-4/5 mx-auto mt-8 shadow-md bg-white rounded-lg overflow-hidden">
        <table class="min-w-full table-auto block md:table">
            <thead class="hidden md:table-header-group">
                <tr class="bg-amber-400 text-amber-900 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">Nro LIA</th>
                    <!-- <th class="py-3 px-6 text-left">Nro UNSJ</th> -->
                    <th class="py-3 px-6 text-left">Tipo</th>
                    <th class="py-3 px-6 text-left">Descripción</th>
                    <th class="py-3 px-6 text-left">Vencimiento</th>
                    <th class="py-3 px-6 text-left">Cantidad</th>
                    <th class="py-3 px-6 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody class="text-gray-900 text-sm font-medium block md:table-row-group">
                @foreach($elementos as $elemento)
                    <tr class="block md:table-row odd:bg-amber-50 even:bg-amber-100 border-b border-gray-300 hover:bg-amber-200 cursor-pointer mb-4 md:mb-0 rounded-lg shadow-sm md:shadow-none p-4 md:p-0" onclick="window.location='{{ route('elementos.show', $elemento->nro_lia) }}'">
                        <td class="py-2 px-4 md:py-3 md:px-6 text-left whitespace-nowrap block md:table-cell flex justify-between md:block">
                            <span class="font-bold md:hidden text-amber-900">Nro LIA:</span>
                            {{ $elemento->nro_lia }}
                        </td>
                        <!-- <td class="py-3 px-6 text-left">{{ $elemento->nro_unsj }}</td> -->
                        <td class="py-2 px-4 md:py-3 md:px-6 text-left block md:table-cell flex justify-between md:block">
                            <span class="font-bold md:hidden text-amber-900">Tipo:</span>
                            {{ strtoupper($elemento->tipo?->nombre ?? 'N/A') }}
                        </td>
                        <td class="py-2 px-4 md:py-3 md:px-6 text-left block md:table-cell">
                            <span class="font-bold md:hidden text-amber-900 block mb-1">Descripción:</span>
                            {{ $elemento->descripcion_texto }}
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
                            <span class="font-bold md:hidden text-amber-900">Cantidad:</span>
                            {{ $elemento->cantidad }}
                        </td>
                        <td class="py-2 px-4 md:py-3 md:px-6 text-left block md:table-cell flex justify-between md:block items-center" onclick="event.stopPropagation()">
                            <span class="font-bold md:hidden text-amber-900">Acciones:</span>
                            <div class="flex items-center justify-end md:justify-start gap-1">
                                @can('write-data')
                                    <a href="{{ route('elementos.edit', $elemento->nro_lia) }}" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110" title="Modificar">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>
                                @endcan
                                @can('delete-data')
                                    <form action="{{ route('elementos.destroy', $elemento->nro_lia) }}" method="POST" onsubmit="return confirm('¿Seguro que desea eliminar este elemento?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-4 mr-2 transform hover:text-red-500 hover:scale-110" title="Eliminar">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

        <div class="mt-4">
            {{ $elementos->appends(request()->query())->links('vendor.pagination.responsive-custom') }}
        </div>
    </div>
@endsection
