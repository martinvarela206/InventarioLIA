@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6 space-y-6">
    <!-- Breadcrumb & Header Quiet Chrome -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-figma-border-subtle pb-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-figma-text-tertiary uppercase tracking-wider mb-1">
                <a href="{{ route('dashboard.index') }}" class="hover:text-figma-text-primary transition-colors">Dashboard</a>
                <span>/</span>
                @if(Auth::user()->hasRole('tec'))
                    <a href="{{ route('tickets.index') }}" class="hover:text-figma-text-primary transition-colors">Tickets</a>
                    <span>/</span>
                @endif
                <span class="text-figma-text-primary font-bold">Iniciar Ticket</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary tracking-tight flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-figma-brand/15 text-figma-brand flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </span>
                <span>Apertura de Ticket de Reparación Técnica</span>
            </h1>
        </div>
        <div>
            @if(Auth::user()->hasRole('tec'))
                <a href="{{ route('tickets.index') }}" class="figma-btn-secondary">
                    ← Bandeja de Tickets
                </a>
            @else
                <a href="{{ route('dashboard.index') }}" class="figma-btn-secondary">
                    ← Volver al Dashboard
                </a>
            @endif
        </div>
    </div>

    <!-- Indicador de obligatoriedad -->
    <div class="figma-card p-3 flex items-center justify-between text-xs text-figma-text-secondary border-amber-500/30 bg-amber-500/5">
        <span class="flex items-center gap-2 font-medium">
            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Los campos marcados con <span class="text-rose-500 font-bold">*</span> son requeridos para el seguimiento técnico.
        </span>
    </div>

    @if ($errors->any())
        <div class="figma-panel p-4 border-l-4 border-l-rose-500 space-y-1">
            <p class="font-bold text-xs text-rose-500">Por favor verifique los siguientes campos:</p>
            <ul class="list-disc list-inside space-y-0.5 text-xs text-figma-text-secondary">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- SECCIÓN 1: IDENTIFICACIÓN DEL ACTIVO -->
        <div class="figma-panel p-5 space-y-4">
            <div class="border-b border-figma-border-subtle pb-3 flex items-center justify-between">
                <h2 class="text-xs font-bold uppercase tracking-wider text-figma-text-primary flex items-center gap-2">
                    <span class="w-5 h-5 rounded-md bg-figma-brand/15 text-figma-brand flex items-center justify-center text-[11px] font-bold">1</span>
                    Activo que Requiere Intervención
                    <span class="text-rose-500 font-bold">*</span>
                </h2>
                <span class="text-[11px] text-figma-text-tertiary">Identifique el equipo por Nro LIA o UNSJ</span>
            </div>

            <!-- Input oculto para el ID final seleccionado -->
            <input type="hidden" name="elemento_id" id="elemento_id_input" value="{{ old('elemento_id', $elemento?->id) }}">

            @if($elemento)
                <!-- Tarjeta del equipo preseleccionado -->
                <div id="elemento_seleccionado_card" class="figma-card p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-figma-brand/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-figma-brand/15 text-figma-brand border border-figma-brand/30 flex items-center justify-center font-mono font-bold text-xs shrink-0">
                            {{ $elemento->tipo?->nombre ? strtoupper(substr($elemento->tipo->nombre, 0, 3)) : 'LIA' }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-figma-text-primary text-sm">{{ $elemento->nro_lia ?: 'Sin Nro LIA' }}</span>
                                @if($elemento->nro_unsj)
                                    <span class="font-mono text-[10px] font-bold text-figma-text-secondary bg-figma-bg-deep px-1.5 py-0.5 rounded border border-figma-border-subtle">UNSJ: {{ $elemento->nro_unsj }}</span>
                                @endif
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-figma-brand/15 text-figma-brand border border-figma-brand/30">{{ ucfirst($elemento->tipo?->nombre ?? 'N/A') }}</span>
                            </div>
                            <div class="text-xs text-figma-text-secondary mt-0.5">
                                <span class="font-bold text-figma-text-primary">{{ $elemento->marca }}</span> {{ $elemento->modelo }} 
                                • Ubicación actual: <span class="text-figma-text-primary">{{ $elemento->ultimoMovimiento?->ubicacion?->nombre ?? 'Sin asignar' }} ({{ $elemento->ultimoMovimiento?->puesto ?: ($elemento->puesto ?: 'S/P') }})</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="cambiarElemento()" class="text-xs font-semibold text-figma-brand hover:underline self-start sm:self-auto cursor-pointer">
                        Cambiar equipo
                    </button>
                </div>
            @endif

            <!-- Buscador en tiempo real de activo -->
            <div id="buscador_elemento_container" class="{{ $elemento ? 'hidden' : '' }} space-y-2">
                <div class="relative">
                    <input type="text" id="buscador_activo_input" 
                           placeholder="Escriba Nro LIA (ej. LIA-042) o Nro UNSJ..." 
                           autocomplete="off"
                           class="figma-input w-full px-4 py-2.5 text-xs font-medium">
                    <div id="sugerencias_activo" class="hidden absolute z-30 left-0 right-0 mt-1 figma-panel shadow-2xl max-h-60 overflow-y-auto divide-y divide-figma-border-subtle">
                    </div>
                </div>
                <p class="text-[11px] text-figma-text-tertiary">Escriba al menos 2 caracteres para desplegar sugerencias interactivas.</p>
            </div>
        </div>

        <!-- SECCIÓN 2: DETALLES DEL PROBLEMA -->
        <div class="figma-panel p-5 space-y-4">
            <div class="border-b border-figma-border-subtle pb-3 flex items-center justify-between">
                <h2 class="text-xs font-bold uppercase tracking-wider text-figma-text-primary flex items-center gap-2">
                    <span class="w-5 h-5 rounded-md bg-figma-brand/15 text-figma-brand flex items-center justify-center text-[11px] font-bold">2</span>
                    Descripción de la Falla y Síntomas
                    <span class="text-rose-500 font-bold">*</span>
                </h2>
                <span class="text-[11px] text-figma-text-tertiary">Detalle el problema observado</span>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase tracking-wider mb-1.5">
                    Área o Especialidad Inicial Involucrada
                </label>
                <select name="area_id" class="figma-input w-full px-3 py-2 text-xs font-medium">
                    <option value="">-- Seleccionar área inicial (opcional) --</option>
                    @foreach($areas as $ar)
                        <option value="{{ $ar->id }}" {{ old('area_id') == $ar->id ? 'selected' : '' }}>
                            {{ $ar->nombre }} @if($ar->descripcion) - {{ $ar->descripcion }} @endif
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-figma-text-tertiary mt-1">El área es acumulativa: los técnicos podrán sumar más áreas si la falla lo requiere sin desvincular la original.</p>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase tracking-wider mb-1.5">
                    Detalle de los Síntomas y Anomalías <span class="text-rose-500 font-bold">*</span>
                </label>
                <textarea name="comentario" rows="4" required
                          placeholder="Describa el comportamiento anómalo detectado: reinicios espontáneos, código de error en pantalla, capacitor inflado, artefactos visuales, daño físico, ruido en ventilador, etc."
                          class="figma-input w-full p-3 text-xs leading-relaxed">{{ old('comentario') }}</textarea>
            </div>
        </div>

        <!-- SECCIÓN 3: INFORMANTE Y LOGÍSTICA DE UBICACIÓN -->
        <div class="figma-panel p-5 space-y-5">
            <div class="border-b border-figma-border-subtle pb-3 flex items-center justify-between">
                <h2 class="text-xs font-bold uppercase tracking-wider text-figma-text-primary flex items-center gap-2">
                    <span class="w-5 h-5 rounded-md bg-figma-brand/15 text-figma-brand flex items-center justify-center text-[11px] font-bold">3</span>
                    Informante y Logística de Ubicación
                </h2>
                <span class="text-[11px] text-figma-text-tertiary">Trazabilidad de origen y destino del activo</span>
            </div>

            <!-- Informante / Solicitante -->
            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase tracking-wider mb-1.5">
                    Informante / Solicitante <span class="text-rose-500 font-bold">*</span>
                </label>
                @if($isTec)
                    <input type="text" name="solicitante" list="lista-solicitantes-ticket" value="{{ old('solicitante', $informante) }}" required
                           class="figma-input w-full px-3 py-2 text-xs font-medium">
                    <datalist id="lista-solicitantes-ticket">
                        @foreach($solicitantesFrecuentes ?? [] as $sol)
                            <option value="{{ $sol }}"></option>
                        @endforeach
                    </datalist>
                    <span class="text-[11px] text-figma-text-tertiary mt-1 block">Como técnico, puede seleccionar de la lista o tipear un informante/docente externo.</span>
                @else
                    <input type="text" value="{{ $informante }}" readonly disabled
                           class="figma-input w-full px-3 py-2 text-xs font-medium opacity-60 cursor-not-allowed">
                    <input type="hidden" name="solicitante" value="{{ $informante }}">
                    <span class="text-[11px] text-figma-text-tertiary mt-1 block">Autocompletado con su identidad institucional registrada (bloqueado para rol Revisor).</span>
                @endif
            </div>

            <!-- Separación clara: Origen vs Decisión de Traslado -->
            <div class="pt-2 border-t border-figma-border-subtle grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Origen (Solo Lectura Informativa) -->
                <div class="figma-card p-3.5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-1.5 text-[11px] font-bold text-figma-text-tertiary uppercase tracking-wider mb-2">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Ubicación Actual Detectada (Origen)</span>
                        </div>
                        <div class="text-xs font-bold text-figma-text-primary" id="ubicacion_actual_texto">
                            @if($elemento)
                                {{ $elemento->ultimoMovimiento?->ubicacion?->nombre ?? 'Sin ubicación registrada' }}
                                <span class="text-[11px] font-normal text-figma-text-secondary block mt-0.5">
                                    Puesto: <strong class="text-figma-text-primary">{{ $elemento->ultimoMovimiento?->puesto ?: ($elemento->puesto ?: 'Sin puesto asignado') }}</strong>
                                </span>
                            @else
                                <span class="italic text-figma-text-tertiary font-normal text-xs">Se detectará automáticamente al seleccionar el equipo arriba.</span>
                            @endif
                        </div>
                    </div>
                    <span class="text-[10px] text-figma-text-tertiary mt-3 pt-2 border-t border-figma-border-subtle block">
                        Ubicación física actual registrada en inventario para este equipo.
                    </span>
                </div>

                <!-- Decisión de Traslado / Destino Físico -->
                <div class="space-y-3">
                    <label class="block text-[11px] font-bold text-figma-text-secondary uppercase tracking-wider">
                        Decisión de Traslado Físico durante la Reparación
                    </label>

                    <div class="space-y-2">
                        <!-- Opción 1: In Situ -->
                        <label class="figma-card p-3 rounded-xl flex items-start gap-3 cursor-pointer border border-figma-border-subtle hover:border-figma-brand/50 has-checked:border-figma-brand has-checked:bg-figma-brand/5 transition-all">
                            <input type="radio" name="tipo_traslado" value="insitu" id="traslado_insitu" 
                                   {{ old('tipo_traslado', 'insitu') === 'insitu' && !old('ubicacion_id') ? 'checked' : '' }}
                                   onchange="toggleTrasladoFisico(false)"
                                   class="mt-1 text-figma-brand focus:ring-figma-brand">
                            <div>
                                <span class="text-xs font-bold text-figma-text-primary block">Permanece en su sala para inspección in situ</span>
                                <span class="text-[11px] text-figma-text-tertiary leading-snug block mt-0.5">El equipo no se traslada; el equipo técnico asistirá a su sala de origen a revisarlo.</span>
                            </div>
                        </label>

                        <!-- Opción 2: Traslado a Taller / Depósito -->
                        <label class="figma-card p-3 rounded-xl flex items-start gap-3 cursor-pointer border border-figma-border-subtle hover:border-figma-brand/50 has-checked:border-figma-brand has-checked:bg-figma-brand/5 transition-all">
                            <input type="radio" name="tipo_traslado" value="taller" id="traslado_taller"
                                   {{ old('tipo_traslado') === 'taller' || old('ubicacion_id') ? 'checked' : '' }}
                                   onchange="toggleTrasladoFisico(true)"
                                   class="mt-1 text-figma-brand focus:ring-figma-brand">
                            <div class="flex-1">
                                <span class="text-xs font-bold text-figma-text-primary block">Trasladar físicamente a Taller de Hardware / Depósito</span>
                                <span class="text-[11px] text-figma-text-tertiary leading-snug block mt-0.5">El activo se retira de la sala y se mueve a un espacio técnico para reparación de banco.</span>
                            </div>
                        </label>
                    </div>

                    <!-- Selector condicional de Destino Físico si se traslada -->
                    <div id="selector_destino_container" class="{{ old('tipo_traslado') === 'taller' || old('ubicacion_id') ? '' : 'hidden' }} pt-2">
                        <label class="block text-[11px] font-bold text-figma-text-secondary uppercase tracking-wider mb-1">
                            Seleccionar Sala de Destino Técnico <span class="text-rose-500">*</span>
                        </label>
                        <select name="ubicacion_id" id="ubicacion_destino_select" class="figma-input w-full px-3 py-2 text-xs font-medium">
                            <option value="">Seleccione sala o taller...</option>
                            @foreach($ubicaciones as $ub)
                                <option value="{{ $ub->id }}" {{ old('ubicacion_id') == $ub->id || (!old('ubicacion_id') && str_contains(strtolower($ub->nombre), 'hardware')) ? 'selected' : '' }}>
                                    {{ $ub->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 4: ADJUNTOS FOTOGRÁFICOS -->
        <div class="figma-panel p-5 space-y-4">
            <div class="border-b border-figma-border-subtle pb-3 flex items-center justify-between">
                <h2 class="text-xs font-bold uppercase tracking-wider text-figma-text-primary flex items-center gap-2">
                    <span class="w-5 h-5 rounded-md bg-figma-brand/15 text-figma-brand flex items-center justify-center text-[11px] font-bold">4</span>
                    Evidencia Fotográfica
                </h2>
                <span class="text-[11px] text-figma-text-tertiary">Cámara en vivo o selección desde galería</span>
            </div>

            <div>
                <label for="fotos_input" class="flex flex-col items-center justify-center border border-dashed border-figma-border-subtle hover:border-figma-brand/50 hover:bg-figma-brand/5 rounded-xl p-6 cursor-pointer transition-all">
                    <div class="w-10 h-10 bg-figma-brand/15 text-figma-brand rounded-full flex items-center justify-center mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-figma-text-primary">Tomar foto con la cámara o seleccionar imágenes</span>
                    <span class="text-[11px] text-figma-text-tertiary mt-0.5">PNG, JPG, WebP hasta 10MB por imagen. Puede seleccionar varias fotos.</span>
                    <input type="file" name="fotos[]" id="fotos_input" accept="image/*;capture=camera" multiple class="hidden" onchange="previewFotos(event)">
                </label>
            </div>

            <!-- Contenedor de miniaturas previsualizadas -->
            <div id="preview_fotos_container" class="hidden grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-2">
            @if(Auth::user()->hasRole('tec'))
                <a href="{{ route('tickets.index') }}" class="figma-btn-secondary text-center">
                    Cancelar
                </a>
            @else
                <a href="{{ route('dashboard.index') }}" class="figma-btn-secondary text-center">
                    Cancelar
                </a>
            @endif
            <button type="submit" class="figma-btn-primary">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>Crear Ticket de Reparación</span>
            </button>
        </div>
    </form>
</div>

<script>
    // Buscador interactivo de activos
    const inputActivo = document.getElementById('buscador_activo_input');
    const sugerenciasBox = document.getElementById('sugerencias_activo');
    const idInput = document.getElementById('elemento_id_input');
    let timerBuscar = null;

    if (inputActivo) {
        inputActivo.addEventListener('input', function() {
            const query = this.value.trim();
            clearTimeout(timerBuscar);

            if (query.length < 2) {
                sugerenciasBox.classList.add('hidden');
                sugerenciasBox.innerHTML = '';
                return;
            }

            timerBuscar = setTimeout(() => {
                fetch(`{{ route('movimientos.buscar-rapido') }}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(items => {
                        if (!items || items.length === 0) {
                            sugerenciasBox.innerHTML = '<div class="p-3 text-xs text-figma-text-tertiary italic">No se encontraron activos coincidentes.</div>';
                            sugerenciasBox.classList.remove('hidden');
                            return;
                        }

                        let html = '';
                        items.forEach(el => {
                            html += `
                                <div onclick="seleccionarActivo(${el.id}, '${el.nro_lia || ''}', '${el.nro_unsj || ''}', '${el.tipo || ''}', '${el.marca || ''}', '${el.modelo || ''}', '${el.ubicacion || 'Sin sala'}') "
                                     class="p-3 hover:bg-figma-bg-deep cursor-pointer flex items-center justify-between transition-colors">
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-mono font-bold text-figma-text-primary text-xs">${el.nro_lia || 'Sin LIA'}</span>
                                            ${el.nro_unsj ? `<span class="font-mono text-[10px] font-semibold text-figma-text-secondary bg-figma-bg-deep px-1.5 py-0.5 rounded border border-figma-border-subtle">UNSJ: ${el.nro_unsj}</span>` : ''}
                                            <span class="text-[10px] text-figma-brand bg-figma-brand/10 border border-figma-brand/20 px-1.5 py-0.5 rounded font-semibold">${el.tipo}</span>
                                        </div>
                                        <div class="text-[11px] text-figma-text-tertiary mt-0.5">${el.marca || ''} ${el.modelo || ''} • Sala: ${el.ubicacion || 'Sin sala'}</div>
                                    </div>
                                    <span class="text-xs font-semibold text-figma-brand">Seleccionar →</span>
                                </div>
                            `;
                        });
                        sugerenciasBox.innerHTML = html;
                        sugerenciasBox.classList.remove('hidden');
                    })
                    .catch(() => {
                        sugerenciasBox.classList.add('hidden');
                    });
            }, 250);
        });
    }

    function toggleTrasladoFisico(trasladar) {
        const container = document.getElementById('selector_destino_container');
        const select = document.getElementById('ubicacion_destino_select');
        if (!container || !select) return;

        if (trasladar) {
            container.classList.remove('hidden');
            if (!select.value) {
                for (let i = 0; i < select.options.length; i++) {
                    if (select.options[i].text.toLowerCase().includes('hardware')) {
                        select.selectedIndex = i;
                        break;
                    }
                }
            }
        } else {
            container.classList.add('hidden');
            select.value = '';
        }
    }

    function seleccionarActivo(id, lia, unsj, tipo, marca, modelo, ubicacion) {
        idInput.value = id;
        sugerenciasBox.classList.add('hidden');

        const card = document.getElementById('elemento_seleccionado_card');
        const container = document.getElementById('buscador_elemento_container');

        if (card) {
            card.remove();
        }

        const newCard = document.createElement('div');
        newCard.id = 'elemento_seleccionado_card';
        newCard.className = 'figma-card p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-figma-brand/50';
        newCard.innerHTML = `
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-figma-brand/15 text-figma-brand border border-figma-brand/30 flex items-center justify-center font-mono font-bold text-xs shrink-0">
                    ${tipo.substring(0, 3).toUpperCase()}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-bold text-figma-text-primary text-sm">${lia || 'Sin Nro LIA'}</span>
                        ${unsj ? `<span class="font-mono text-[10px] font-bold text-figma-text-secondary bg-figma-bg-deep px-1.5 py-0.5 rounded border border-figma-border-subtle">UNSJ: ${unsj}</span>` : ''}
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-figma-brand/15 text-figma-brand border border-figma-brand/30">${tipo}</span>
                    </div>
                    <div class="text-xs text-figma-text-secondary mt-0.5">
                        <span class="font-bold text-figma-text-primary">${marca}</span> ${modelo} • Ubicación: <span class="text-figma-text-primary">${ubicacion}</span>
                    </div>
                </div>
            </div>
            <button type="button" onclick="cambiarElemento()" class="text-xs font-semibold text-figma-brand hover:underline self-start sm:self-auto cursor-pointer">
                Cambiar equipo
            </button>
        `;

        container.parentNode.insertBefore(newCard, container);
        container.classList.add('hidden');

        const ubicacionTexto = document.getElementById('ubicacion_actual_texto');
        if (ubicacionTexto) {
            ubicacionTexto.innerHTML = `
                ${ubicacion || 'Sin ubicación registrada'}
                <span class="text-[11px] font-normal text-figma-text-secondary block mt-0.5">Ubicación física detectada del activo</span>
            `;
        }
    }

    function cambiarElemento() {
        idInput.value = '';
        const card = document.getElementById('elemento_seleccionado_card');
        if (card) card.classList.add('hidden');
        const container = document.getElementById('buscador_elemento_container');
        if (container) {
            container.classList.remove('hidden');
            inputActivo.value = '';
            inputActivo.focus();
        }

        const ubicacionTexto = document.getElementById('ubicacion_actual_texto');
        if (ubicacionTexto) {
            ubicacionTexto.innerHTML = '<span class="italic text-figma-text-tertiary font-normal text-xs">Se detectará automáticamente al seleccionar el equipo arriba.</span>';
        }
    }

    function previewFotos(event) {
        const container = document.getElementById('preview_fotos_container');
        container.innerHTML = '';
        const files = event.target.files;

        if (files && files.length > 0) {
            container.classList.remove('hidden');
            Array.from(files).forEach((file, idx) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative rounded-lg overflow-hidden border border-figma-border-subtle aspect-video bg-figma-bg-deep flex items-center justify-center';
                    div.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-full object-cover">
                        <span class="absolute bottom-1 right-1 bg-black/70 text-white text-[10px] px-1.5 py-0.5 rounded font-mono font-bold">#${idx+1}</span>
                    `;
                    container.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        } else {
            container.classList.add('hidden');
        }
    }
</script>
@endsection
