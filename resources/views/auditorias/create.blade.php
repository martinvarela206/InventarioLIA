@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Header & Selector de Sala -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                <a href="{{ route('dashboard.index') }}" class="hover:text-amber-600 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('auditorias.index') }}" class="hover:text-amber-600 transition-colors">Auditorías</a>
                <span>/</span>
                <span class="text-gray-900">{{ $ubicacionSeleccionada->nombre }}</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-indigo-100 text-indigo-700 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </span>
                <span>Auditoría Física de Ubicación: {{ $ubicacionSeleccionada->nombre }}</span>
            </h1>
        </div>

        <div class="flex items-center gap-3">
            <!-- Selector de Laboratorio / Sala -->
            <form action="{{ route('auditorias.create') }}" method="GET" class="flex items-center gap-2">
                <select name="ubicacion_id" onchange="this.form.submit()" class="bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs font-bold text-gray-800 shadow-xs focus:ring-2 focus:ring-indigo-400">
                    @foreach($ubicaciones as $ub)
                        <option value="{{ $ub->id }}" {{ $ubicacionSeleccionada->id == $ub->id ? 'selected' : '' }}>
                            {{ $ub->nombre }}
                        </option>
                    @endforeach
                </select>
            </form>

            <form action="{{ route('auditorias.finalizar', $auditoria->id) }}" method="POST">
                @csrf
                <button type="submit" onclick="return confirm('¿Confirma que desea cerrar formalmente esta sesión de auditoría?')" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold px-4 py-2 rounded-xl text-xs shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>Finalizar Sesión</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Barra de Estado y Progreso en Tiempo Real -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="w-3 h-3 rounded-full {{ $auditoria->estado === 'finalizada' ? 'bg-emerald-500' : 'bg-amber-500 animate-ping' }}"></span>
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Sesión Activa</span>
                <span class="text-sm font-black text-gray-900">Auditoría #{{ $auditoria->id }} • {{ $auditoria->fecha ? $auditoria->fecha->format('d/m/Y') : now()->format('d/m/Y') }}</span>
            </div>
        </div>

        <!-- KPI Metrics -->
        <div class="grid grid-cols-3 gap-3 text-center sm:text-left">
            <div class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Auditados</span>
                <span id="kpi_auditados" class="text-base font-black text-gray-900">{{ $auditoria->total_auditados }}</span>
            </div>
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-2">
                <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider block">Verificados OK</span>
                <span id="kpi_ok" class="text-base font-black text-emerald-800">{{ $auditoria->total_ok }}</span>
            </div>
            <div class="bg-rose-50 border border-rose-200 rounded-xl px-4 py-2">
                <span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider block">Inconsistencias</span>
                <span id="kpi_inconsistencias" class="text-base font-black text-rose-800">{{ $auditoria->total_inconsistencias }}</span>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 1: GRILLA DE PUESTOS DE TRABAJO (MESONES) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-black text-gray-900 flex items-center gap-2">
                <span class="p-1.5 bg-gray-100 text-gray-700 rounded-lg text-xs font-black">🖥</span>
                <span>Grilla de Puestos y Mesones ({{ count($puestos) }} puestos)</span>
            </h2>
            <span class="text-xs text-gray-400">Verifique físicamente cada puesto y marque su estado</span>
        </div>

        @if(empty($puestos))
            <div class="p-8 text-center bg-white rounded-2xl border border-dashed border-gray-200 text-gray-400 text-xs font-medium">
                No hay puestos identificados en esta sala.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($puestos as $nomPuesto => $itemsPuesto)
                    @php
                        $det = $detallesPorPuesto->get($nomPuesto);
                        $estadoCard = $det ? $det->estado : 'pendiente';
                        $ticketId = $det?->ticket_id;
                    @endphp
                    <div id="card_puesto_{{ Str::slug($nomPuesto) }}" 
                         class="rounded-2xl border p-4 space-y-3 transition-all shadow-xs
                         {{ $estadoCard === 'ok' ? 'bg-emerald-50/70 border-emerald-300' : ($estadoCard === 'inconsistencia' ? 'bg-rose-50/70 border-rose-300' : 'bg-white border-gray-200') }}">
                        
                        <!-- Cabecera del puesto -->
                        <div class="flex items-center justify-between border-b border-gray-100/80 pb-2">
                            <span class="font-black text-sm text-gray-900">{{ $nomPuesto }}</span>
                            <span id="badge_estado_{{ Str::slug($nomPuesto) }}" 
                                  class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider
                                  {{ $estadoCard === 'ok' ? 'bg-emerald-200 text-emerald-950' : ($estadoCard === 'inconsistencia' ? 'bg-rose-200 text-rose-950' : 'bg-gray-100 text-gray-600') }}">
                                {{ $estadoCard === 'inconsistencia' ? 'Incidencia #' . $ticketId : $estadoCard }}
                            </span>
                        </div>

                        <!-- Activos asignados en el puesto -->
                        <div class="space-y-1.5 text-xs min-h-[60px]">
                            @forelse($itemsPuesto as $item)
                                <div class="flex items-center justify-between text-gray-700 bg-white/70 px-2 py-1 rounded border border-gray-200/50">
                                    <span class="font-bold text-gray-900">{{ $item->nro_lia ?: 'S/N' }}</span>
                                    <span class="text-[11px] text-gray-500">{{ ucfirst($item->tipo?->nombre ?? 'Activo') }}</span>
                                </div>
                            @empty
                                <span class="text-gray-400 italic text-[11px] block pt-2">Sin equipos asignados formalmente</span>
                            @endforelse
                        </div>

                        <!-- Botones de Acción In Situ -->
                        <div class="pt-2 border-t border-gray-100/80 flex items-center gap-2">
                            <button type="button" 
                                    onclick="marcarPuestoOk('{{ $nomPuesto }}', '{{ Str::slug($nomPuesto) }}')" 
                                    class="flex-1 bg-white hover:bg-emerald-600 hover:text-white border border-gray-300 hover:border-emerald-600 text-emerald-700 font-extrabold text-xs py-1.5 px-2 rounded-xl transition-all shadow-2xs text-center">
                                ✓ ¡OK!
                            </button>
                            <button type="button" 
                                    onclick="abrirModalInconsistencia('{{ $nomPuesto }}', '{{ Str::slug($nomPuesto) }}', {{ count($itemsPuesto) ? $itemsPuesto[0]->id : 'null' }})" 
                                    class="flex-1 bg-white hover:bg-rose-600 hover:text-white border border-gray-300 hover:border-rose-600 text-rose-700 font-extrabold text-xs py-1.5 px-2 rounded-xl transition-all shadow-2xs text-center">
                                ⚠ ¡Inconsistencia!
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- SECCIÓN 2: ELEMENTOS GENERALES DE LA SALA (SIN MESÓN) -->
    @if(count($elementosGenerales))
        <div class="space-y-4 pt-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-black text-gray-900 flex items-center gap-2">
                    <span class="p-1.5 bg-gray-100 text-gray-700 rounded-lg text-xs font-black">📦</span>
                    <span>Elementos Generales de la Sala (Armarios, Switches, Proyectores, Impresoras)</span>
                </h2>
                <span class="text-xs text-gray-400">{{ count($elementosGenerales) }} elementos generales</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($elementosGenerales as $gen)
                    @php
                        $detGen = $detallesPorElemento->get($gen->id);
                        $estadoGen = $detGen ? $detGen->estado : 'pendiente';
                        $ticketIdGen = $detGen?->ticket_id;
                    @endphp
                    <div id="card_elemento_{{ $gen->id }}" 
                         class="rounded-2xl border p-4 space-y-3 transition-all shadow-xs
                         {{ $estadoGen === 'ok' ? 'bg-emerald-50/70 border-emerald-300' : ($estadoGen === 'inconsistencia' ? 'bg-rose-50/70 border-rose-300' : 'bg-white border-gray-200') }}">
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-black text-sm text-gray-900 block">{{ $gen->nro_lia ?: 'Sin Nro LIA' }}</span>
                                <span class="text-xs text-gray-500 font-medium">{{ ucfirst($gen->tipo?->nombre ?? 'N/A') }} • {{ $gen->marca }} {{ $gen->modelo }}</span>
                            </div>
                            <span id="badge_elemento_{{ $gen->id }}" 
                                  class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider
                                  {{ $estadoGen === 'ok' ? 'bg-emerald-200 text-emerald-950' : ($estadoGen === 'inconsistencia' ? 'bg-rose-200 text-rose-950' : 'bg-gray-100 text-gray-600') }}">
                                {{ $estadoGen === 'inconsistencia' ? 'Ticket #' . $ticketIdGen : $estadoGen }}
                            </span>
                        </div>

                        <div class="pt-2 border-t border-gray-100/80 flex items-center gap-2">
                            <button type="button" 
                                    onclick="marcarElementoOk({{ $gen->id }})" 
                                    class="flex-1 bg-white hover:bg-emerald-600 hover:text-white border border-gray-300 hover:border-emerald-600 text-emerald-700 font-extrabold text-xs py-1.5 px-2 rounded-xl transition-all shadow-2xs text-center">
                                ✓ ¡OK!
                            </button>
                            <button type="button" 
                                    onclick="abrirModalInconsistencia('General', 'general', {{ $gen->id }})" 
                                    class="flex-1 bg-white hover:bg-rose-600 hover:text-white border border-gray-300 hover:border-rose-600 text-rose-700 font-extrabold text-xs py-1.5 px-2 rounded-xl transition-all shadow-2xs text-center">
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
<div id="modal_inconsistencia" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl border border-gray-200 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-base font-black text-gray-900 flex items-center gap-2">
                <span class="p-1.5 bg-rose-100 text-rose-700 rounded-lg text-xs font-black">⚠</span>
                <span>Reportar Inconsistencia y Abrir Ticket In Situ</span>
            </h3>
            <button type="button" onclick="cerrarModalInconsistencia()" class="text-gray-400 hover:text-gray-700 font-black text-lg">×</button>
        </div>

        <form id="form_modal_inconsistencia" onsubmit="enviarInconsistencia(event)" class="space-y-4">
            <input type="hidden" id="modal_puesto_input" name="puesto" value="">
            <input type="hidden" id="modal_elemento_input" name="elemento_id" value="">
            <input type="hidden" id="modal_puesto_slug" value="">

            <div>
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-1">Puesto / Origen:</span>
                <span id="modal_puesto_display" class="font-extrabold text-sm text-gray-900 block bg-gray-50 p-2.5 rounded-xl border border-gray-200"></span>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Clasificación de la Discrepancia <span class="text-rose-500 font-bold">*</span>
                </label>
                <select name="tipo_discrepancia" id="modal_tipo_discrepancia" required class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2 text-xs font-bold text-gray-900 focus:bg-white focus:ring-2 focus:ring-rose-400">
                    <option value="rotura">Equipo roto / Daño físico / Falla de funcionamiento</option>
                    <option value="faltante">Equipo faltante / Sustracción / No encontrado en puesto</option>
                    <option value="sobrante">Equipo ajeno / Ubicación indebida en este puesto</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Descripción del Problema Observado <span class="text-rose-500 font-bold">*</span>
                </label>
                <textarea name="descripcion" id="modal_descripcion" rows="3" required placeholder="Detalle qué sucede: monitor roto, faltan cables HDMI, equipo no enciende..."
                          class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2.5 text-xs text-gray-900 focus:bg-white focus:ring-2 focus:ring-rose-400"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                <button type="button" onclick="cerrarModalInconsistencia()" class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 font-bold text-xs hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="btn_submit_modal" class="bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs px-5 py-2 rounded-xl transition-all shadow-md flex items-center gap-1.5">
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

    // Marcar Puesto Completo como OK
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
                    card.className = 'rounded-2xl border p-4 space-y-3 transition-all shadow-xs bg-emerald-50/70 border-emerald-300';
                }
                const badge = document.getElementById(`badge_estado_${slug}`);
                if (badge) {
                    badge.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-200 text-emerald-950';
                    badge.innerText = 'OK';
                }
                actualizarTotalesUI(data.totales);
            }
        } catch (err) {
            console.error('Error al guardar progreso:', err);
        }
    }

    // Marcar Elemento General como OK
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
                    card.className = 'rounded-2xl border p-4 space-y-3 transition-all shadow-xs bg-emerald-50/70 border-emerald-300';
                }
                const badge = document.getElementById(`badge_elemento_${elementoId}`);
                if (badge) {
                    badge.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-200 text-emerald-950';
                    badge.innerText = 'OK';
                }
                actualizarTotalesUI(data.totales);
            }
        } catch (err) {
            console.error('Error al guardar elemento:', err);
        }
    }

    // Abrir Modal de Inconsistencia
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

    // Enviar Inconsistencia vía AJAX y crear Ticket In Situ
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
            // Si hay elementoId se genera ticket vinculado, de lo contrario se guarda detalle de anomalía en puesto
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
                // Actualizar Card en la UI
                const card = document.getElementById(`card_puesto_${slug}`) || document.getElementById(`card_elemento_${elementoId}`);
                if (card) {
                    card.className = 'rounded-2xl border p-4 space-y-3 transition-all shadow-xs bg-rose-50/70 border-rose-300';
                }
                const badge = document.getElementById(`badge_estado_${slug}`) || document.getElementById(`badge_elemento_${elementoId}`);
                if (badge) {
                    badge.className = 'px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-rose-200 text-rose-950';
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
