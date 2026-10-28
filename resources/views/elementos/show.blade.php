@extends('layouts.app')

@section('content')
<div class="max-w-[1300px] mx-auto py-4 space-y-4">
    <!-- Breadcrumb & Top Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider">
            <a href="{{ route('elementos.index') }}" class="hover:text-figma-brand transition-colors">Inventario</a>
            <span>/</span>
            <span class="text-figma-text-primary font-mono">{{ $elemento->presenter()->identificadorCompleto() }}</span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('tickets.create', ['elemento_id' => $elemento->id]) }}" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                <span>Iniciar Ticket</span>
            </a>
            @if(Auth::user()->canManageMovimientos())
                <a href="{{ route('movimientos.rapido', ['elemento_id' => $elemento->id]) }}" class="figma-btn-primary text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Movimiento / Préstamo</span>
                </a>
            @endif
            @if(Auth::user()->canManageElementos())
                <a href="{{ route('elementos.edit', $elemento->id) }}" class="figma-btn-secondary text-xs">
                    <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    <span>Editar Ficha</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Dual-Pane Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
        
        <!-- Left Pane: Identification & Current State Card -->
        <div class="lg:col-span-4 figma-panel overflow-hidden sticky top-20 space-y-4 p-5">
            <!-- Header Banner -->
            <div class="space-y-2 pb-3 border-b border-figma-border-subtle">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[10px] font-mono font-bold uppercase tracking-wider px-2 py-0.5 bg-figma-bg-deep text-figma-text-secondary border border-figma-border-subtle rounded">
                        {{ strtoupper($elemento->tipo?->nombre ?? 'Activo') }}
                    </span>
                    {!! $elemento->presenter()->statusBadge() !!}
                </div>
                <h1 class="text-lg font-bold text-figma-text-primary leading-tight">
                    {{ $elemento->marca ?: 'Genérico' }} {{ $elemento->modelo }}
                </h1>
                <p class="text-[10px] font-mono text-figma-text-tertiary">ID Técnico: #{{ $elemento->id }}</p>
            </div>

            <!-- Identifiers Grid -->
            <div class="space-y-3.5 text-xs">
                <div>
                    <span class="text-[10px] font-bold text-figma-text-tertiary uppercase tracking-wider block mb-1.5">Identificadores Oficiales</span>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="figma-card p-2.5">
                            <span class="text-[10px] text-figma-text-tertiary block">Nro LIA</span>
                            <span class="font-mono font-bold text-figma-brand text-xs">{{ $elemento->nro_lia ?: 'Sin Asignar' }}</span>
                        </div>
                        <div class="figma-card p-2.5">
                            <span class="text-[10px] text-figma-text-tertiary block">Nro UNSJ</span>
                            <span class="font-mono font-bold text-figma-text-primary text-xs">{{ $elemento->nro_unsj ?: 'Sin Asignar' }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-2 pt-2 border-t border-figma-border-subtle">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-figma-text-tertiary">Ubicación Actual</span>
                        <span class="font-semibold text-figma-text-primary text-right">{{ $elemento->presenter()->puestoUbicacion() }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-figma-text-tertiary">Nro de Serie</span>
                        <span class="font-mono text-figma-text-secondary">{{ $elemento->nro_serie ?: 'S/N' }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-figma-text-tertiary">Año de Compra</span>
                        <span class="font-mono text-figma-text-secondary">{{ $elemento->anio_compra ?: ($elemento->fecha_adquisicion ? $elemento->fecha_adquisicion->format('Y') : '-') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-figma-text-tertiary">Garantía</span>
                        <span>{!! $elemento->presenter()->garantiaBadge() !!}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-figma-text-tertiary">Cantidad</span>
                        <span class="font-mono font-bold text-figma-text-primary">{{ $elemento->cantidad }} unidad(es)</span>
                    </div>
                </div>

                <!-- Préstamo Activo Warning (if applicable) -->
                @if($elemento->ultimoMovimiento && $elemento->ultimoMovimiento->esPrestamo() && !$elemento->ultimoMovimiento->fecha_devolucion)
                    <div class="p-3 rounded bg-amber-500/10 border border-amber-500/30 text-xs space-y-1">
                        <div class="flex items-center gap-1.5 text-amber-400 font-bold uppercase text-[10px] tracking-wide">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                            <span>Préstamo Externo Activo</span>
                        </div>
                        <p class="text-figma-text-primary">
                            Solicitante: <strong>{{ $elemento->ultimoMovimiento->solicitante }}</strong>
                        </p>
                        <p class="text-figma-text-tertiary text-[11px] font-mono">
                            Salida: {{ $elemento->ultimoMovimiento->fecha_prestamo ? $elemento->ultimoMovimiento->fecha_prestamo->format('d/m/Y') : '-' }} (hace {{ $elemento->ultimoMovimiento->diasPrestado() }} días)
                        </p>
                    </div>
                @endif
            </div>

            <!-- Direct Actions -->
            <div class="pt-3 border-t border-figma-border-subtle">
                <a href="{{ route('reportes.show', ['tipo' => 'ubicacion-trazabilidad', 'elemento_id' => $elemento->id]) }}" class="figma-btn-secondary w-full justify-center text-xs">
                    <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Reporte Oficial de Trazabilidad</span>
                </a>
            </div>
        </div>

        <!-- Right Pane: Tabs (Timeline, Specs, Notes) -->
        <div class="lg:col-span-8 figma-panel p-5 space-y-4">
            <!-- Tabs Navigation (Figma Segment Ribbon) -->
            <div class="pb-3 border-b border-figma-border-subtle">
                <div class="figma-pill-ribbon w-fit" id="detail-tabs">
                    <button type="button" onclick="cambiarPestana('timeline')" id="tab-btn-timeline" class="figma-pill-item figma-pill-active text-xs flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Línea de Tiempo</span>
                        <span class="font-mono text-[10px] opacity-80">({{ $elemento->movimientos->count() }})</span>
                    </button>
                    <button type="button" onclick="cambiarPestana('specs')" id="tab-btn-specs" class="figma-pill-item text-xs flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                        <span>Especificaciones</span>
                    </button>
                    <button type="button" onclick="cambiarPestana('notas')" id="tab-btn-notas" class="figma-pill-item text-xs flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        <span>Notas y Componentes</span>
                    </button>
                </div>
            </div>

            <!-- Tab Content 1: Timeline -->
            <div id="tab-content-timeline" class="tab-pane">
                <div class="relative pl-5 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-px before:bg-figma-border-subtle">
                    @forelse($elemento->movimientos as $index => $m)
                        <div class="relative group">
                            <!-- Bullet -->
                            <div class="absolute -left-[17px] top-2 w-2.5 h-2.5 rounded-full border-2 {{ $index === 0 ? 'bg-figma-brand border-figma-brand' : 'bg-figma-bg-panel border-figma-border-subtle' }}"></div>
                            
                            <!-- Card -->
                            <div class="figma-card p-3.5 space-y-2 hover:border-figma-brand transition-colors">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        {!! $m->presenter()->tipoBadge() !!}
                                        {!! $m->presenter()->estadoBadge() !!}
                                        @if($index === 0)
                                            <span class="text-[9px] font-mono font-bold bg-figma-brand/20 text-figma-brand border border-figma-brand/30 px-1.5 py-0.5 rounded">Actual</span>
                                        @endif
                                    </div>
                                    <span class="text-[11px] text-figma-text-tertiary font-mono">
                                        {{ $m->fecha ? $m->fecha->format('d/m/Y H:i') : '-' }}
                                    </span>
                                </div>

                                <div class="text-xs space-y-1.5 text-figma-text-primary">
                                    <p class="flex items-center gap-1.5">
                                        <span class="text-figma-text-tertiary text-[10px] uppercase font-bold">Destino:</span>
                                        <strong class="text-figma-text-primary">{{ $m->presenter()->destinoTexto() }}</strong>
                                    </p>
                                    @if($m->esPrestamo())
                                        <div class="p-2 rounded bg-figma-bg-deep border border-figma-border-subtle text-[11px]">
                                            <span class="text-figma-text-tertiary">Solicitante:</span> <strong class="text-figma-text-primary">{{ $m->solicitante ?: 'No registrado' }}</strong>
                                            @if($m->fecha_devolucion)
                                                <span class="text-emerald-400 font-bold block mt-0.5">✓ Devuelto el: {{ Carbon\Carbon::parse($m->fecha_devolucion)->format('d/m/Y') }}</span>
                                            @else
                                                <span class="text-amber-400 font-bold block mt-0.5">● En curso ({{ $m->diasPrestado() }} días)</span>
                                            @endif
                                        </div>
                                    @endif
                                    @if($m->comentario)
                                        <p class="text-xs text-figma-text-secondary italic p-2 rounded bg-figma-bg-deep border border-figma-border-subtle">
                                            "{{ $m->comentario }}"
                                        </p>
                                    @endif
                                    
                                    <div class="flex items-center justify-between text-[10px] text-figma-text-tertiary pt-2 border-t border-figma-border-subtle font-mono">
                                        <div>
                                            @if($m->correccion_de_id)
                                                <span class="text-purple-400 font-bold">
                                                    Subsanación #{{ $m->correccion_de_id }}
                                                </span>
                                            @else
                                                <span>Mov #{{ $m->id }}</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span>Por: <strong class="text-figma-text-primary">{{ $m->usuario?->nombre ?? 'Sistema' }}</strong></span>
                                            <button type="button" onclick="abrirModalCorreccion({{ $m->id }}, '{{ $m->ubicacion_id }}', '{{ $m->puesto }}', '{{ addslashes($m->solicitante ?? '') }}')" class="text-[10px] text-figma-brand hover:underline font-bold px-1 py-0.5 rounded transition-colors">
                                                Enmendar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-figma-text-tertiary text-xs italic">
                            No existen movimientos registrados para este activo.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Tab Content 2: Technical Hardware Specs (Atributos Dinámicos Pivot) -->
            <div id="tab-content-specs" class="tab-pane hidden space-y-3">
                <h3 class="text-xs font-bold text-figma-text-primary uppercase tracking-wider">Configuración y Atributos Técnicos</h3>
                @if($elemento->valoresAtributos->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                        @foreach($elemento->valoresAtributos as $va)
                            <div class="figma-card p-3">
                                <span class="text-[10px] font-bold text-figma-text-tertiary uppercase tracking-wider block">
                                    {{ $va->atributo?->nombre ?? 'Característica' }}
                                </span>
                                <span class="text-xs font-mono font-bold text-figma-text-primary mt-1 block">
                                    {{ $va->valor }}
                                    @if($va->atributo?->unidad_medida)
                                        <span class="text-[10px] text-figma-text-tertiary font-normal ml-0.5">({{ $va->atributo->unidad_medida }})</span>
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                @elseif(is_array($elemento->descripcion) && count($elemento->descripcion) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                        @foreach($elemento->descripcion as $specKey => $specVal)
                            <div class="figma-card p-3">
                                <span class="text-[10px] font-bold text-figma-text-tertiary uppercase tracking-wider block">{{ $specKey }}</span>
                                <span class="text-xs font-mono font-bold text-figma-text-primary mt-1 block">{{ $specVal }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-10 text-center text-figma-text-tertiary text-xs italic">
                        <p>No se cargaron especificaciones de hardware detalladas para este dispositivo.</p>
                        @if(Auth::user()->canManageElementos())
                            <a href="{{ route('elementos.edit', $elemento->id) }}" class="inline-block mt-2 text-xs text-figma-brand hover:underline font-semibold">Configurar especificaciones</a>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Tab Content 3: Notes & Observations -->
            <div id="tab-content-notas" class="tab-pane hidden space-y-3">
                <h3 class="text-xs font-bold text-figma-text-primary uppercase tracking-wider">Observaciones y Notas Patrimoniales</h3>
                @if($elemento->observaciones)
                    <div class="figma-card p-4 text-figma-text-primary text-xs whitespace-pre-line leading-relaxed">
                        {{ $elemento->observaciones }}
                    </div>
                @else
                    <p class="text-figma-text-tertiary text-xs italic py-8 text-center">Sin notas ni observaciones registradas.</p>
                @endif
            </div>

        </div>
    </div>
</div>

<!-- Modal de Enmienda / Corrección Oficial de Movimiento Inmutable -->
<div id="modal-correccion" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="figma-panel max-w-lg w-full p-5 space-y-3.5 shadow-2xl">
        <div class="flex items-center justify-between pb-2 border-b border-figma-border-subtle">
            <div>
                <h3 class="text-sm font-bold text-figma-text-primary" id="correccion-titulo">Enmendar Movimiento</h3>
                <p class="text-[11px] text-figma-text-tertiary mt-0.5">El movimiento original permanecerá inmutable. Se creará una enmienda oficial enlazada.</p>
            </div>
            <button type="button" onclick="cerrarModalCorreccion()" class="text-figma-text-tertiary hover:text-figma-text-primary text-base leading-none">✕</button>
        </div>

        <form id="form-correccion" method="POST" action="" class="space-y-3">
            @csrf

            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">
                    Motivo de la Corrección / Error Cometido <span class="text-rose-400">*</span>
                </label>
                <input type="text" name="motivo_correccion" id="correccion-motivo" required placeholder="Ej. Se asignó por error Puesto 02 en lugar de Puesto 08" class="figma-input w-full px-2.5 py-1.5 text-xs font-semibold">
                <p class="text-[10px] text-figma-text-tertiary mt-1">Este motivo quedará asentado en la bitácora de auditoría.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                <div>
                    <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Sala Correcta</label>
                    <select name="ubicacion_id" id="correccion-ubicacion" class="figma-input w-full px-2 py-1.5 text-xs font-semibold">
                        @foreach(\App\Models\Ubicacion::all() as $ub)
                            <option value="{{ $ub->id }}">{{ $ub->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Puesto Correcto</label>
                    <input type="text" name="puesto" id="correccion-puesto" placeholder="Ej. Puesto 04" class="figma-input w-full px-2.5 py-1.5 text-xs">
                </div>
            </div>

            <div id="campo-solicitante-correccion" class="hidden">
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Solicitante del Préstamo</label>
                <input type="text" name="solicitante" id="correccion-solicitante" class="figma-input w-full px-2.5 py-1.5 text-xs">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Observaciones Adicionales</label>
                <textarea name="comentario" rows="2" placeholder="Detalle técnico adicional..." class="figma-input w-full px-2.5 py-1.5 text-xs"></textarea>
            </div>

            <div class="pt-2 border-t border-figma-border-subtle flex items-center justify-end gap-2">
                <button type="button" onclick="cerrarModalCorreccion()" class="figma-btn-secondary text-xs">Cancelar</button>
                <button type="submit" class="figma-btn-primary text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Registrar Enmienda</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function cambiarPestana(target) {
        document.querySelectorAll('#detail-tabs .figma-pill-item').forEach(btn => {
            btn.classList.remove('figma-pill-active');
        });
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.add('hidden');
        });

        const activeBtn = document.getElementById('tab-btn-' + target);
        const activePane = document.getElementById('tab-content-' + target);

        if (activeBtn) {
            activeBtn.classList.add('figma-pill-active');
        }
        if (activePane) {
            activePane.classList.remove('hidden');
        }
    }

    function abrirModalCorreccion(movimientoId, ubicacionId, puesto, solicitante) {
        document.getElementById('form-correccion').action = '/movimientos/' + movimientoId + '/correccion';
        document.getElementById('correccion-titulo').innerText = 'Enmendar Movimiento #' + movimientoId;
        
        if (ubicacionId) {
            document.getElementById('correccion-ubicacion').value = ubicacionId;
        }
        document.getElementById('correccion-puesto').value = puesto || '';
        
        const wrapSolicitante = document.getElementById('campo-solicitante-correccion');
        if (solicitante) {
            wrapSolicitante.classList.remove('hidden');
            document.getElementById('correccion-solicitante').value = solicitante;
        } else {
            wrapSolicitante.classList.add('hidden');
        }

        document.getElementById('modal-correccion').classList.remove('hidden');
    }

    function cerrarModalCorreccion() {
        document.getElementById('modal-correccion').classList.add('hidden');
        document.getElementById('form-correccion').reset();
    }
</script>
@endsection
