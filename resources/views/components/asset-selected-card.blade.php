@props([
    'id' => 'asset-selected-card',
    'elemento' => null,
    'onReset' => 'cambiarActivoSeleccionado',
    'showChangeButton' => true,
])

<div id="{{ $id }}" class="bg-amber-50/70 border border-amber-300/80 rounded-2xl p-4 sm:p-5 transition-all shadow-2xs {{ $elemento ? '' : 'hidden' }}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Detalle del Activo -->
        <div class="flex items-start sm:items-center gap-3.5">
            <!-- Icono / Avatar del Tipo -->
            <div id="{{ $id }}-avatar" class="w-12 h-12 bg-white rounded-xl border border-amber-200 flex items-center justify-center font-black text-amber-800 text-sm shadow-xs shrink-0">
                @if($elemento)
                    {{ strtoupper(substr($elemento->tipo?->nombre ?? 'LIA', 0, 3)) }}
                @else
                    ACT
                @endif
            </div>

            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span id="{{ $id }}-lia" class="font-mono font-black text-gray-900 text-base">
                        {{ $elemento?->nro_lia ?: 'Sin Nro LIA' }}
                    </span>
                    <span id="{{ $id }}-unsj" class="{{ $elemento && $elemento->nro_unsj ? '' : 'hidden' }} text-xs font-mono font-bold text-gray-700 bg-white px-2 py-0.5 rounded border border-gray-200">
                        UNSJ: {{ $elemento?->nro_unsj }}
                    </span>
                    <span id="{{ $id }}-tipo" class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-amber-200/80 text-amber-900 uppercase tracking-wider">
                        {{ ucfirst($elemento?->tipo?->nombre ?? 'Activo') }}
                    </span>
                </div>

                <div class="text-xs text-gray-600 mt-1 flex flex-wrap items-center gap-1.5">
                    <span id="{{ $id }}-modelo" class="font-bold text-gray-900">
                        {{ $elemento ? trim(($elemento->marca ?? '') . ' ' . ($elemento->modelo ?? '')) : '' }}
                    </span>
                    <span class="text-gray-400">•</span>
                    <span>Ubicación actual: </span>
                    <strong id="{{ $id }}-ubicacion" class="font-bold text-gray-800">
                        @if($elemento)
                            {{ $elemento->ultimoMovimiento?->ubicacion?->nombre ?? 'Sin asignar' }}
                            @if($elemento->ultimoMovimiento?->puesto || $elemento->puesto)
                                ({{ $elemento->ultimoMovimiento?->puesto ?: $elemento->puesto }})
                            @endif
                        @else
                            -
                        @endif
                    </strong>
                </div>
            </div>
        </div>

        <!-- Badges de Estado y Botón Cambiar -->
        <div class="flex items-center sm:flex-col sm:items-end justify-between sm:justify-center gap-2.5 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-amber-200/60">
            <div id="{{ $id }}-badge-container">
                @if($elemento)
                    {!! $elemento->presenter()->statusBadge() !!}
                @endif
            </div>

            @if($showChangeButton)
                <button type="button" 
                        onclick="{{ $onReset }}()"
                        class="text-xs font-bold text-amber-900 hover:text-black bg-white hover:bg-amber-100/70 border border-amber-300 px-3 py-1.5 rounded-xl shadow-2xs transition-all inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Cambiar equipo</span>
                </button>
            @endif
        </div>
    </div>
</div>
