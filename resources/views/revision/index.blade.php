@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Header Quiet Chrome -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-figma-border-subtle pb-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-figma-text-tertiary uppercase tracking-wider mb-1">
                <a href="{{ route('dashboard.index') }}" class="hover:text-figma-text-primary transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-figma-text-primary font-bold">Revisión de Inventario</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary tracking-tight flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-figma-brand/15 text-figma-brand flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </span>
                <span>Revisión Rápida de Inventario</span>
            </h1>
            <p class="text-xs text-figma-text-tertiary mt-0.5">Explora y verifica la ubicación de los elementos</p>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="figma-panel p-4 space-y-3">
        <form action="{{ route('revision.index') }}" method="GET">
            <div class="flex flex-col sm:flex-row gap-2.5 items-stretch sm:items-center">
                <div class="relative flex-1">
                    <input type="text" name="search" placeholder="Buscar por Nro LIA, Descripción o Ubicación..." value="{{ request('search') }}" 
                           class="figma-input w-full px-3.5 py-2 text-xs font-medium" />
                </div>
                <button type="submit" class="figma-btn-primary py-2 px-4 text-xs font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Buscar</span>
                </button>
                @if(request('search'))
                    <a href="{{ route('revision.index', request()->except('search')) }}" class="figma-btn-secondary py-2 px-3 text-xs">
                        Limpiar
                    </a>
                @endif
            </div>
            @if(request('tipo'))
                <input type="hidden" name="tipo" value="{{ request('tipo') }}">
            @endif
            @if(request('gb'))
                <input type="hidden" name="gb" value="{{ request('gb') }}">
            @endif
        </form>

        <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-figma-border-subtle">
            <span class="text-[10px] font-bold text-figma-text-tertiary uppercase tracking-wider mr-1">Filtros:</span>

            <!-- Filtro GB -->
            <form action="{{ route('revision.index') }}" method="GET" class="flex items-center">
                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                @if(request('tipo')) <input type="hidden" name="tipo" value="{{ request('tipo') }}"> @endif
                
                <div class="inline-flex items-center rounded-lg border border-figma-border-subtle bg-figma-bg-deep px-2 py-1 text-xs">
                    <span class="font-bold text-figma-text-tertiary mr-1 text-[11px]">GB:</span>
                    <input 
                        type="number" 
                        name="gb" 
                        value="{{ request('gb') }}" 
                        class="w-12 bg-transparent border-none p-0 text-xs font-mono font-bold text-figma-text-primary focus:ring-0 focus:outline-none"
                        placeholder="ej. 8"
                        onchange="this.form.submit()"
                    >
                    @if(request('gb'))
                        <a href="{{ route('revision.index', array_merge(request()->except('gb'))) }}" class="text-figma-text-tertiary hover:text-rose-400 ml-1 text-xs">✕</a>
                    @endif
                </div>
            </form>

            <!-- Filtro Tipo -->
            <div class="inline-flex items-center rounded-lg border border-figma-border-subtle bg-figma-bg-deep px-2 py-1 text-xs">
                <span class="font-bold text-figma-text-tertiary mr-1 text-[11px]">Tipo:</span>
                <select 
                    name="tipo" 
                    onchange="filterByTipo(this.value)" 
                    class="bg-transparent border-none p-0 text-xs font-medium text-figma-text-primary focus:ring-0 focus:outline-none">
                    <option value="" class="bg-figma-panel text-figma-text-primary">Todos</option>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo->nombre }}" {{ request('tipo') === $tipo->nombre ? 'selected' : '' }} class="bg-figma-panel text-figma-text-primary">
                            {{ ucfirst($tipo->nombre) }}
                        </option>
                    @endforeach
                </select>
            </div>
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

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="search"]');
            if (!searchInput) return;
            const searchForm = searchInput.closest('form');
            let timeout = null;

            searchInput.addEventListener('input', function() {
                clearTimeout(timeout);
                if (this.value.length >= 2 || this.value.length === 0) {
                    timeout = setTimeout(() => {
                        searchForm.submit();
                    }, 500);
                }
            });
        });
    </script>

    <!-- Tabla de Elementos -->
    <div class="figma-panel overflow-hidden">
        <table class="min-w-full table-auto block md:table">
            <thead class="hidden md:table-header-group">
                <tr class="bg-figma-bg-deep border-b border-figma-border-subtle text-[10px] font-bold text-figma-text-secondary uppercase tracking-wider">
                    <th class="py-3 px-4 text-left">Nro LIA</th>
                    <th class="py-3 px-4 text-left">Descripción</th>
                    <th class="py-3 px-4 text-left">Situación</th>
                    <th class="py-3 px-4 text-left">Garantía</th>
                    <th class="py-3 px-4 text-left">Ubicación Actual</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-figma-border-subtle text-xs font-medium block md:table-row-group">
                @foreach($elementos as $elemento)
                    <tr class="block md:table-row hover:bg-figma-bg-deep/50 cursor-pointer p-3 md:p-0 transition-colors" onclick="window.location='{{ route('elementos.show', $elemento->id) }}?from=revision'">
                        <td class="py-2 px-3 md:py-3 md:px-4 text-left whitespace-nowrap block md:table-cell flex justify-between md:block">
                            <span class="font-bold md:hidden text-figma-text-tertiary">Identificador:</span>
                            <span class="font-mono font-bold text-figma-brand">{{ $elemento->presenter()->identificadorCompleto() }}</span>
                        </td>
                        <td class="py-2 px-3 md:py-3 md:px-4 text-left block md:table-cell text-figma-text-primary">
                            <span class="font-bold md:hidden text-figma-text-tertiary block mb-1">Descripción:</span>
                            {{ $elemento->descripcion_texto }}
                        </td>
                        <td class="py-2 px-3 md:py-3 md:px-4 text-left block md:table-cell flex justify-between md:block items-center">
                            <span class="font-bold md:hidden text-figma-text-tertiary">Situación:</span>
                            {!! $elemento->presenter()->statusBadge() !!}
                        </td>
                        <td class="py-2 px-3 md:py-3 md:px-4 text-left block md:table-cell flex justify-between md:block font-mono">
                            <span class="font-bold md:hidden text-figma-text-tertiary">Vencimiento:</span>
                            @if($elemento->fecha_vencimiento_garantia)
                                <span class="{{ $elemento->fecha_vencimiento_garantia->isPast() ? 'text-rose-400 font-semibold' : 'text-emerald-400' }}">
                                    {{ $elemento->fecha_vencimiento_garantia->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-figma-text-tertiary">-</span>
                            @endif
                        </td>
                        <td class="py-2 px-3 md:py-3 md:px-4 text-left block md:table-cell flex justify-between md:block">
                            <span class="font-bold md:hidden text-figma-text-tertiary">Ubicación Actual:</span>
                            @if($elemento->ultimoMovimiento)
                                <span class="text-figma-text-primary font-semibold">{{ $elemento->ultimoMovimiento->ubicacion?->nombre ?? 'Sin ubicación' }}</span>
                                <span class="text-[10px] text-figma-text-tertiary font-mono block">({{ $elemento->ultimoMovimiento->fecha->format('d/m/Y') }})</span>
                            @else
                                <span class="text-figma-text-tertiary italic">Sin movimientos</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pt-2">
        {{ $elementos->appends(request()->query())->links('vendor.pagination.responsive-custom') }}
    </div>
</div>
@endsection
