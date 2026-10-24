@extends('layouts.app')

@section('content')
<div class="max-w-[1300px] mx-auto">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600">
            <a href="{{ route('elementos.index') }}" class="hover:text-black font-medium">← Volver al inventario</a>
            <span>/</span>
            <span class="text-black font-semibold">{{ $elemento->presenter()->identificadorCompleto() }}</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('movimientos.rapido', ['elemento_id' => $elemento->id]) }}" class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-black font-bold py-2 px-4 rounded-lg shadow-sm transition-all text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Registrar Movimiento / Préstamo
            </a>
            @if(Auth::user()->isItemAdmin() || Auth::user()->isAdmin())
                <a href="{{ route('elementos.edit', $elemento->id) }}" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-semibold py-2 px-3.5 rounded-lg shadow-sm text-sm transition-all">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Editar Ficha
                </a>
            @endif
        </div>
    </div>

    <!-- Dual-Pane Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left Pane: Identification & Current State Card -->
        <div class="lg:col-span-4 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-20">
            <!-- Header Banner -->
            <div class="bg-gradient-to-r from-amber-100 to-amber-50 p-5 border-b border-amber-200/60">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 bg-amber-200/80 text-amber-900 rounded-md">
                        {{ strtoupper($elemento->tipo?->nombre ?? 'Activo') }}
                    </span>
                    {!! $elemento->presenter()->statusBadge() !!}
                </div>
                <h1 class="text-xl font-bold text-gray-900 leading-tight">
                    {{ $elemento->marca ?: 'Genérico' }} {{ $elemento->modelo }}
                </h1>
                <p class="text-xs text-gray-500 mt-1">ID Técnico: #{{ $elemento->id }}</p>
            </div>

            <!-- Identifiers Grid -->
            <div class="p-5 space-y-4 text-sm divide-y divide-gray-100">
                <div class="pt-1">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Identificadores Oficiales</span>
                    <div class="grid grid-cols-2 gap-3 mt-2">
                        <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                            <span class="text-xs text-gray-500 block">Nro LIA</span>
                            <span class="font-mono font-bold text-gray-900 text-sm">{{ $elemento->nro_lia ?: 'Sin Asignar' }}</span>
                        </div>
                        <div class="bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                            <span class="text-xs text-gray-500 block">Nro UNSJ</span>
                            <span class="font-mono font-bold text-gray-900 text-sm">{{ $elemento->nro_unsj ?: 'Sin Asignar' }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 space-y-2.5">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Ubicación Actual</span>
                        <span class="font-semibold text-gray-900 text-right">{{ $elemento->presenter()->puestoUbicacion() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Nro de Serie</span>
                        <span class="font-mono text-gray-800">{{ $elemento->nro_serie ?: 'S/N' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Año de Adquisición</span>
                        <span class="font-medium text-gray-800">{{ $elemento->anio_compra ?: ($elemento->fecha_adquisicion ? $elemento->fecha_adquisicion->format('Y') : '-') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Garantía</span>
                        <span>{!! $elemento->presenter()->garantiaBadge() !!}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Cantidad</span>
                        <span class="font-semibold text-gray-900">{{ $elemento->cantidad }} unidad(es)</span>
                    </div>
                </div>

                <!-- Préstamo Activo Warning (if applicable) -->
                @if($elemento->ultimoMovimiento && $elemento->ultimoMovimiento->esPrestamo() && !$elemento->ultimoMovimiento->fecha_devolucion)
                    <div class="pt-4 bg-amber-50 p-3 rounded-lg border border-amber-200 mt-2">
                        <div class="flex items-center gap-2 text-amber-900 font-bold text-xs uppercase tracking-wide">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                            Préstamo Externo Activo
                        </div>
                        <p class="text-xs text-amber-800 mt-1">
                            Solicitante: <strong>{{ $elemento->ultimoMovimiento->solicitante }}</strong>
                        </p>
                        <p class="text-xs text-amber-700">
                            Salida: {{ $elemento->ultimoMovimiento->fecha_prestamo ? $elemento->ultimoMovimiento->fecha_prestamo->format('d/m/Y') : '-' }} (hace {{ $elemento->ultimoMovimiento->diasPrestado() }} días)
                        </p>
                    </div>
                @endif
            </div>

            <!-- Direct Actions -->
            <div class="p-4 bg-gray-50 border-t border-gray-100 flex flex-col gap-2">
                <a href="{{ route('reportes.show', ['tipo' => 'ubicacion-trazabilidad', 'elemento_id' => $elemento->id]) }}" class="text-center text-xs text-gray-600 hover:text-black font-semibold py-1.5 border border-gray-200 rounded-md bg-white hover:bg-gray-100 transition-colors">
                    📄 Ver Reporte de Trazabilidad Oficial
                </a>
            </div>
        </div>

        <!-- Right Pane: Tabs (Timeline, Specs, Notes) -->
        <div class="lg:col-span-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="flex gap-6 -mb-px" id="detail-tabs">
                    <button type="button" onclick="cambiarPestana('timeline')" id="tab-btn-timeline" class="tab-button border-b-2 border-amber-500 text-amber-700 font-bold py-3 text-sm flex items-center gap-2 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Línea de Tiempo ({{ $elemento->movimientos->count() }})
                    </button>
                    <button type="button" onclick="cambiarPestana('specs')" id="tab-btn-specs" class="tab-button border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-semibold py-3 text-sm flex items-center gap-2 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                        Especificaciones de Hardware
                    </button>
                    <button type="button" onclick="cambiarPestana('notas')" id="tab-btn-notas" class="tab-button border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-semibold py-3 text-sm flex items-center gap-2 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        Notas y Componentes
                    </button>
                </nav>
            </div>

            <!-- Tab Content 1: Timeline -->
            <div id="tab-content-timeline" class="tab-pane">
                <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-amber-200">
                    @forelse($elemento->movimientos as $index => $m)
                        <div class="relative group">
                            <!-- Bullet -->
                            <div class="absolute -left-[29px] top-1.5 w-3.5 h-3.5 rounded-full {{ $index === 0 ? 'bg-amber-500 ring-4 ring-amber-100' : 'bg-gray-300' }}"></div>
                            
                            <!-- Card -->
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200/80 hover:border-amber-300 transition-colors">
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                    <div class="flex items-center gap-2">
                                        {!! $m->presenter()->tipoBadge() !!}
                                        {!! $m->presenter()->estadoBadge() !!}
                                        @if($index === 0)
                                            <span class="text-[10px] font-bold bg-amber-200 text-amber-900 px-1.5 py-0.5 rounded">Estado Actual</span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-gray-500 font-mono">
                                        {{ $m->fecha ? $m->fecha->format('d/m/Y H:i') : '-' }}
                                    </span>
                                </div>

                                <div class="text-sm space-y-1.5 text-gray-700">
                                    <p>
                                        <span class="text-gray-500 text-xs uppercase font-semibold">Destino:</span>
                                        <strong class="text-gray-900">{{ $m->presenter()->destinoTexto() }}</strong>
                                    </p>
                                    @if($m->esPrestamo())
                                        <p class="text-amber-900 bg-amber-50/80 p-2 rounded border border-amber-200 text-xs">
                                            <strong>Docente / Solicitante:</strong> {{ $m->solicitante ?: 'No registrado' }}
                                            @if($m->fecha_devolucion)
                                                <br><span class="text-emerald-700 font-semibold">Devuelto el: {{ Carbon\Carbon::parse($m->fecha_devolucion)->format('d/m/Y') }}</span>
                                            @else
                                                <br><span class="text-amber-700 font-semibold">En curso ({{ $m->diasPrestado() }} días)</span>
                                            @endif
                                        </p>
                                    @endif
                                    @if($m->comentario)
                                        <p class="text-xs text-gray-600 italic bg-white p-2 rounded border border-gray-100">
                                            "{{ $m->comentario }}"
                                        </p>
                                    @endif
                                    <p class="text-[11px] text-gray-400 text-right">
                                        Registrado por: {{ $m->usuario?->nombre ?? 'Sistema' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-gray-400 text-sm">
                            No existen movimientos registrados para este activo.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Tab Content 2: Technical Hardware Specs -->
            <div id="tab-content-specs" class="tab-pane hidden">
                <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Configuración de Hardware Estructurada</h3>
                    @if(is_array($elemento->descripcion) && count($elemento->descripcion) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($elemento->descripcion as $specKey => $specVal)
                                <div class="bg-white p-3.5 rounded-lg border border-gray-200 shadow-2xs">
                                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">{{ $specKey }}</span>
                                    <span class="text-sm font-bold text-gray-900 mt-1 block">{{ $specVal }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-12 text-center text-gray-400 text-sm">
                            <p>No se cargaron especificaciones de hardware detalladas para este dispositivo.</p>
                            @if(Auth::user()->isItemAdmin() || Auth::user()->isAdmin())
                                <a href="{{ route('elementos.edit', $elemento->id) }}" class="inline-block mt-3 text-xs text-amber-700 underline font-semibold">Añadir especificaciones (CPU, RAM, Disco)</a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tab Content 3: Notes & Observations -->
            <div id="tab-content-notas" class="tab-pane hidden">
                <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-3">Observaciones y Notas Patrimoniales</h3>
                    @if($elemento->observaciones)
                        <div class="bg-white p-4 rounded-lg border border-gray-200 text-gray-800 text-sm whitespace-pre-line leading-relaxed shadow-2xs">
                            {{ $elemento->observaciones }}
                        </div>
                    @else
                        <p class="text-gray-400 text-sm italic py-8 text-center">Sin notas ni observaciones registradas.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function cambiarPestana(target) {
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('border-amber-500', 'text-amber-700', 'font-bold');
            btn.classList.add('border-transparent', 'text-gray-500', 'font-semibold');
        });
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.add('hidden');
        });

        const activeBtn = document.getElementById('tab-btn-' + target);
        const activePane = document.getElementById('tab-content-' + target);

        if (activeBtn) {
            activeBtn.classList.add('border-amber-500', 'text-amber-700', 'font-bold');
            activeBtn.classList.remove('border-transparent', 'text-gray-500', 'font-semibold');
        }
        if (activePane) {
            activePane.classList.remove('hidden');
        }
    }
</script>
@endsection
