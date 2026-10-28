@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Header & Selector de Sala Quiet Chrome -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-figma-border-subtle pb-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-figma-text-tertiary uppercase tracking-wider mb-1">
                <a href="{{ route('dashboard.index') }}" class="hover:text-figma-text-primary transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('auditorias.index') }}" class="hover:text-figma-text-primary transition-colors">Auditorías</a>
                <span>/</span>
                <span class="text-figma-text-primary font-bold">{{ $ubicacionSeleccionada->nombre }}</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary tracking-tight flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-figma-brand/15 text-figma-brand flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </span>
                <span>Auditoría Física de Ubicación: {{ $ubicacionSeleccionada->nombre }}</span>
            </h1>
        </div>

        <div class="flex items-center gap-3">
            <!-- Selector de Laboratorio / Sala -->
            <form action="{{ route('auditorias.create') }}" method="GET" class="flex items-center gap-2">
                <select name="ubicacion_id" onchange="this.form.submit()" class="figma-input px-3 py-1.5 text-xs font-semibold">
                    @foreach($ubicaciones as $ub)
                        <option value="{{ $ub->id }}" {{ $ubicacionSeleccionada->id == $ub->id ? 'selected' : '' }}>
                            {{ $ub->nombre }}
                        </option>
                    @endforeach
                </select>
            </form>

            <form action="{{ route('auditorias.finalizar', $auditoria->id) }}" method="POST">
                @csrf
                <button type="submit" onclick="return confirm('¿Confirma que desea cerrar formalmente esta sesión de auditoría?')" class="figma-btn-primary py-1.5 px-3.5 text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>Finalizar Sesión</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Barra de Estado y Progreso en Tiempo Real -->
    <div class="figma-panel p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="w-2.5 h-2.5 rounded-full {{ $auditoria->estado === 'finalizada' ? 'bg-emerald-400' : 'bg-amber-400 animate-ping' }}"></span>
            <div>
                <span class="text-[10px] font-bold text-figma-text-tertiary uppercase tracking-wider block">Sesión Activa</span>
                <span class="text-xs font-mono font-bold text-figma-text-primary">Auditoría #{{ $auditoria->id }} • {{ $auditoria->fecha ? $auditoria->fecha->format('d/m/Y') : now()->format('d/m/Y') }}</span>
            </div>
        </div>

        <!-- KPI Metrics -->
        <div class="grid grid-cols-3 gap-3 text-center sm:text-left">
            <div class="figma-card px-3.5 py-1.5">
                <span class="text-[10px] font-bold text-figma-text-tertiary uppercase tracking-wider block">Auditados</span>
                <span id="kpi_auditados" class="text-sm font-mono font-bold text-figma-text-primary">{{ $auditoria->total_auditados }}</span>
            </div>
            <div class="figma-card px-3.5 py-1.5 border-emerald-500/30 bg-emerald-500/5">
                <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider block">OK</span>
                <span id="kpi_ok" class="text-sm font-mono font-bold text-emerald-400">{{ $auditoria->total_ok }}</span>
            </div>
            <div class="figma-card px-3.5 py-1.5 border-rose-500/30 bg-rose-500/5">
                <span class="text-[10px] font-bold text-rose-400 uppercase tracking-wider block">Inconsistencias</span>
                <span id="kpi_inconsistencias" class="text-sm font-mono font-bold text-rose-400">{{ $auditoria->total_inconsistencias }}</span>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 1: GRILLA DE PUESTOS DE TRABAJO (MESONES) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold text-figma-text-primary uppercase tracking-wider flex items-center gap-2">
                <span class="w-5 h-5 rounded-md bg-figma-brand/15 text-figma-brand flex items-center justify-center text-xs">🖥</span>
                <span>Grilla de Puestos y Mesones ({{ count($puestos) }} puestos)</span>
            </h2>
            <span class="text-[11px] text-figma-text-tertiary">Verifique físicamente cada puesto y marque su estado</span>
        </div>

        @if(empty($puestos))
            <div class="p-8 text-center figma-panel border-dashed text-figma-text-tertiary text-xs">
                No hay puestos identificados en esta sala.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                @foreach($puestos as $nomPuesto => $itemsPuesto)
                    @php
                        $det = $detallesPorPuesto->get($nomPuesto);
                        $estadoCard = $det ? $det->estado : 'pendiente';
                        $ticketId = $det?->ticket_id;
                    @endphp
                    <div id="card_puesto_{{ Str::slug($nomPuesto) }}" 
                         class="figma-card p-3.5 space-y-3 transition-all
                         {{ $estadoCard === 'ok' ? 'border-emerald-500/50 bg-emerald-500/5' : ($estadoCard === 'inconsistencia' ? 'border-rose-500/50 bg-rose-500/5' : '') }}">
                        
                        <!-- Cabecera del puesto -->
                        <div class="flex items-center justify-between border-b border-figma-border-subtle pb-2">
                            <span class="font-bold text-xs text-figma-text-primary">{{ $nomPuesto }}</span>
                            <span id="badge_estado_{{ Str::slug($nomPuesto) }}" 
                                  class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider
                                  {{ $estadoCard === 'ok' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($estadoCard === 'inconsistencia' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-figma-bg-deep text-figma-text-tertiary border border-figma-border-subtle') }}">
                                {{ $estadoCard === 'inconsistencia' ? 'Incidencia #' . $ticketId : $estadoCard }}
                            </span>
                        </div>

                        <!-- Activos asignados en el puesto -->
                        <div class="space-y-1.5 text-xs min-h-[60px]">
                            @forelse($itemsPuesto as $item)
                                <div class="flex items-center justify-between text-figma-text-secondary bg-figma-bg-deep px-2 py-1 rounded border border-figma-border-subtle">
                                    <span class="font-mono font-bold text-figma-text-primary text-[11px]">{{ $item->nro_lia ?: 'S/N' }}</span>
                                    <span class="text-[10px] text-figma-text-tertiary">{{ ucfirst($item->tipo?->nombre ?? 'Activo') }}</span>
                                </div>
                            @empty
                                <span class="text-figma-text-tertiary italic text-[11px] block pt-2">Sin equipos asignados</span>
                            @endforelse
                        </div>

                        <!-- Botones de Acción In Situ -->
                        <div class="pt-2 border-t border-figma-border-subtle flex items-center gap-2">
                            <button type="button" 
                                    onclick="marcarPuestoOk('{{ $nomPuesto }}', '{{ Str::slug($nomPuesto) }}')" 
                                    class="flex-1 py-1.5 px-2 rounded-lg bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500 hover:text-black font-semibold text-xs transition-colors text-center cursor-pointer">
                                ✓ OK
                            </button>
                            <button type="button" 
                                    onclick="abrirModalInconsistencia('{{ $nomPuesto }}', '{{ Str::slug($nomPuesto) }}', {{ count($itemsPuesto) ? $itemsPuesto[0]->id : 'null' }})" 
                                    class="flex-1 py-1.5 px-2 rounded-lg bg-rose-500/15 border border-rose-500/30 text-rose-400 hover:bg-rose-500 hover:text-white font-semibold text-xs transition-colors text-center cursor-pointer">
                                ⚠ Inconsistencia
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- SECCIÓN 2: ELEMENTOS GENERALES DE LA SALA -->
    @if(count($elementosGenerales))
        <div class="space-y-4 pt-4 border-t border-figma-border-subtle">
            <div class="flex items-center justify-between">
                <h2 class="text-xs font-bold text-figma-text-primary uppercase tracking-wider flex items-center gap-2">
                    <span class="w-5 h-5 rounded-md bg-figma-brand/15 text-figma-brand flex items-center justify-center text-xs">📦</span>
                    <span>Elementos Generales de la Sala (Armarios, Switches, Proyectores)</span>
                </h2>
                <span class="text-[11px] text-figma-text-tertiary">{{ count($elementosGenerales) }} elementos</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($elementosGenerales as $gen)
                    @php
                        $detGen = $detallesPorElemento->get($gen->id);
                        $estadoGen = $detGen ? $detGen->estado : 'pendiente';
                        $ticketIdGen = $detGen?->ticket_id;
                    @endphp
                    <div id="card_elemento_{{ $gen->id }}" 
                         class="figma-card p-3.5 space-y-3 transition-all
                         {{ $estadoGen === 'ok' ? 'border-emerald-500/50 bg-emerald-500/5' : ($estadoGen === 'inconsistencia' ? 'border-rose-500/50 bg-rose-500/5' : '') }}">
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-mono font-bold text-xs text-figma-text-primary block">{{ $gen->nro_lia ?: 'Sin Nro LIA' }}</span>
                                <span class="text-[11px] text-figma-text-tertiary">{{ ucfirst($gen->tipo?->nombre ?? 'N/A') }} • {{ $gen->marca }} {{ $gen->modelo }}</span>
                            </div>
                            <span id="badge_elemento_{{ $gen->id }}" 
                                  class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider
                                  {{ $estadoGen === 'ok' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($estadoGen === 'inconsistencia' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-figma-bg-deep text-figma-text-tertiary border border-figma-border-subtle') }}">
                                {{ $estadoGen === 'inconsistencia' ? 'Ticket #' . $ticketIdGen : $estadoGen }}
                            </span>
                        </div>

                        <div class="pt-2 border-t border-figma-border-subtle flex items-center gap-2">
                            <button type="button" 
                                    onclick="marcarElementoOk({{ $gen->id }})" 
                                    class="flex-1 py-1.5 px-2 rounded-lg bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500 hover:text-black font-semibold text-xs transition-colors text-center cursor-pointer">
                                ✓ OK
                            </button>
                            <button type="button" 
                                    onclick="abrirModalInconsistencia('General', 'general', {{ $gen->id }})" 
                                    class="flex-1 py-1.5 px-2 rounded-lg bg-rose-500/15 border border-rose-500/30 text-rose-400 hover:bg-rose-500 hover:text-white font-semibold text-xs transition-colors text-center cursor-pointer">
                                ⚠ Inconsistencia
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- MODAL CONTEXTUAL DE APERTURA DE TICKET IN SITU -->
<div id="modal_inconsistencia" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="figma-panel max-w-lg w-full p-5 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between border-b border-figma-border-subtle pb-3">
            <h3 class="text-xs font-bold text-figma-text-primary uppercase tracking-wider flex items-center gap-2">
                <span class="w-5 h-5 rounded-md bg-rose-500/20 text-rose-400 flex items-center justify-center text-xs font-bold">⚠</span>
                <span>Reportar Inconsistencia y Abrir Ticket In Situ</span>
            </h3>
            <button type="button" onclick="cerrarModalInconsistencia()" class="text-figma-text-tertiary hover:text-figma-text-primary font-bold text-sm">×</button>
        </div>

        <form id="form_modal_inconsistencia" onsubmit="enviarInconsistencia(event)" class="space-y-3.5">
            <input type="hidden" id="modal_puesto_input" name="puesto" value="">
            <input type="hidden" id="modal_elemento_input" name="elemento_id" value="">
            <input type="hidden" id="modal_puesto_slug" value="">

            <div>
                <span class="text-[10px] font-bold text-figma-text-tertiary uppercase tracking-wider block mb-1">Puesto / Origen:</span>
                <span id="modal_puesto_display" class="font-bold text-xs text-figma-text-primary block figma-card p-2.5"></span>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase tracking-wider mb-1">
                    Clasificación de la Discrepancia <span class="text-rose-500 font-bold">*</span>
                </label>
                <select name="tipo_discrepancia" id="modal_tipo_discrepancia" required class="figma-input w-full px-3 py-2 text-xs font-medium">
                    <option value="rotura">Equipo roto / Daño físico / Falla de funcionamiento</option>
                    <option value="faltante">Equipo faltante / Sustracción / No encontrado en puesto</option>
                    <option value="sobrante">Equipo ajeno / Ubicación indebida en este puesto</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase tracking-wider mb-1">
                    Descripción del Problema Observado <span class="text-rose-500 font-bold">*</span>
                </label>
                <textarea name="descripcion" id="modal_descripcion" rows="3" required placeholder="Detalle qué sucede: monitor roto, faltan cables HDMI, equipo no enciende..."
                          class="figma-input w-full p-2.5 text-xs leading-relaxed"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-figma-border-subtle">
                <button type="button" onclick="cerrarModalInconsistencia()" class="figma-btn-secondary">
                    Cancelar
                </button>
                <button type="submit" id="btn_submit_modal" class="inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-500 text-white font-semibold text-xs py-2 px-4 rounded-lg shadow-xs transition-colors cursor-pointer">
                    <span>Generar Ticket y Registrar</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const auditoriaId = {{ $auditoria->id }};
    const csrfToken = '{{ csrf_token() }}';

    function actualizarTotalesUI(totales) {
        if (!totales) return;
        document.getElementById('kpi_auditados').innerText = totales.total_auditados;
        document.getElementById('kpi_ok').innerText = totales.total_ok;
        document.getElementById('kpi_inconsistencias').innerText = totales.total_inconsistencias;
    }

    async function marcarPuestoOk(puestoNombre, slug) {
        try {
            const res = await fetch(`/auditorias/${auditoriaId}/detalle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    puesto: puestoNombre,
                    estado: 'ok'
                })
            });
            const data = await res.json();
            if (data.success) {
                const card = document.getElementById(`card_puesto_${slug}`);
                if (card) {
                    card.className = 'figma-card p-3.5 space-y-3 transition-all border-emerald-500/50 bg-emerald-500/5';
                }
                const badge = document.getElementById(`badge_estado_${slug}`);
                if (badge) {
                    badge.className = 'px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider bg-emerald-500/15 text-emerald-400 border border-emerald-500/30';
                    badge.innerText = 'OK';
                }
                actualizarTotalesUI(data.totales);
            }
        } catch (err) {
            console.error('Error al guardar progreso:', err);
        }
    }

    async function marcarElementoOk(elementoId) {
        try {
            const res = await fetch(`/auditorias/${auditoriaId}/detalle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    elemento_id: elementoId,
                    estado: 'ok'
                })
            });
            const data = await res.json();
            if (data.success) {
                const card = document.getElementById(`card_elemento_${elementoId}`);
                if (card) {
                    card.className = 'figma-card p-3.5 space-y-3 transition-all border-emerald-500/50 bg-emerald-500/5';
                }
                const badge = document.getElementById(`badge_elemento_${elementoId}`);
                if (badge) {
                    badge.className = 'px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider bg-emerald-500/15 text-emerald-400 border border-emerald-500/30';
                    badge.innerText = 'OK';
                }
                actualizarTotalesUI(data.totales);
            }
        } catch (err) {
            console.error('Error al guardar elemento:', err);
        }
    }

    function abrirModalInconsistencia(puestoNombre, slug, elementoId) {
        document.getElementById('modal_puesto_input').value = puestoNombre;
        document.getElementById('modal_puesto_slug').value = slug;
        document.getElementById('modal_elemento_input').value = elementoId || '';
        document.getElementById('modal_puesto_display').innerText = puestoNombre + (elementoId ? ` (Elemento #${elementoId})` : '');
        document.getElementById('modal_descripcion').value = '';
        document.getElementById('modal_inconsistencia').classList.remove('hidden');
    }

    function cerrarModalInconsistencia() {
        document.getElementById('modal_inconsistencia').classList.add('hidden');
    }

    async function enviarInconsistencia(e) {
        e.preventDefault();
        const puesto = document.getElementById('modal_puesto_input').value;
        const slug = document.getElementById('modal_puesto_slug').value;
        const elementoId = document.getElementById('modal_elemento_input').value;
        const tipo = document.getElementById('modal_tipo_discrepancia').value;
        const descripcion = document.getElementById('modal_descripcion').value;

        const btn = document.getElementById('btn_submit_modal');
        btn.disabled = true;
        btn.innerText = 'Generando Ticket...';

        try {
            let res;
            if (elementoId) {
                res = await fetch(`/auditorias/${auditoriaId}/ticket`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        puesto: puesto,
                        elemento_id: elementoId,
                        tipo_discrepancia: tipo,
                        descripcion: descripcion
                    })
                });
            } else {
                res = await fetch(`/auditorias/${auditoriaId}/detalle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        puesto: puesto,
                        estado: 'inconsistencia',
                        tipo_discrepancia: tipo,
                        observaciones: descripcion
                    })
                });
            }

            const data = await res.json();
            if (data.success) {
                const card = document.getElementById(`card_puesto_${slug}`) || document.getElementById(`card_elemento_${elementoId}`);
                if (card) {
                    card.className = 'figma-card p-3.5 space-y-3 transition-all border-rose-500/50 bg-rose-500/5';
                }
                const badge = document.getElementById(`badge_estado_${slug}`) || document.getElementById(`badge_elemento_${elementoId}`);
                if (badge) {
                    badge.className = 'px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider bg-rose-500/15 text-rose-400 border border-rose-500/30';
                    badge.innerText = data.ticket_id ? `TICKET #${data.ticket_id}` : 'INCONSISTENCIA';
                }
                actualizarTotalesUI(data.totales);
                cerrarModalInconsistencia();
            }
        } catch (err) {
            console.error('Error al generar inconsistencia:', err);
        } finally {
            btn.disabled = false;
            btn.innerText = 'Generar Ticket y Registrar';
        }
    }
</script>
@endsection
