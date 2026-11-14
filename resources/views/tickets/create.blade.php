@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6 space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                <a href="{{ route('dashboard.index') }}" class="hover:text-amber-600 transition-colors">Dashboard</a>
                <span>/</span>
                @if(Auth::user()->hasRole('tec'))
                    <a href="{{ route('tickets.index') }}" class="hover:text-amber-600 transition-colors">Tickets</a>
                    <span>/</span>
                @endif
                <span class="text-gray-900">Iniciar Ticket</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-amber-100 text-amber-700 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </span>
                <span>Apertura de Ticket de Incidencia Técnica</span>
            </h1>
        </div>
        <div>
            @if(Auth::user()->hasRole('tec'))
                <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-bold px-4 py-2 rounded-xl text-sm transition-all shadow-xs">
                    ← Bandeja de Tickets
                </a>
            @else
                <a href="{{ route('dashboard.index') }}" class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-bold px-4 py-2 rounded-xl text-sm transition-all shadow-xs">
                    ← Volver al Dashboard
                </a>
            @endif
        </div>
    </div>

    <!-- Indicador de obligatoriedad -->
    <div class="flex items-center justify-between text-xs text-gray-500 bg-amber-50/60 border border-amber-200/60 px-4 py-2.5 rounded-xl">
        <span class="flex items-center gap-1.5 font-medium text-amber-900">
            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Los campos marcados con <span class="text-rose-600 font-bold">*</span> son obligatorios para el seguimiento técnico.
        </span>
    </div>

    @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-sm">
            <p class="font-bold mb-1">Por favor corrija los siguientes errores:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="form-crear-ticket">
        @csrf

        <!-- SELECTOR DUAL DE MODALIDAD: ACTIVO VS GENERAL -->
        <div class="bg-gray-100 p-1.5 rounded-2xl flex items-center gap-2 max-w-xl mx-auto shadow-2xs border border-gray-200">
            <button type="button" id="tab-modalidad-activo" onclick="setModalidad('activo')"
                    class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-extrabold flex items-center justify-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Sobre Activo Patrimonial</span>
            </button>
            <button type="button" id="tab-modalidad-general" onclick="setModalidad('general')"
                    class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-extrabold flex items-center justify-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Infraestructura / Espacio LIA</span>
            </button>
        </div>
        <input type="hidden" name="modalidad" id="modalidad_input" value="{{ old('modalidad', (old('ambito_ticket_id') && !old('elemento_id') && !$elemento) ? 'general' : 'activo') }}">

        <!-- SECCIÓN 1: IDENTIFICACIÓN DEL ORIGEN (ACTIVO O ÁMBITO) -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs p-6 space-y-4">
            <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-6 h-6 bg-[#fbc101] text-black rounded-lg flex items-center justify-center text-xs font-black">1</span>
                    <span id="seccion-1-titulo">Activo que Requiere Intervención</span>
                    <span class="text-rose-500 font-bold">*</span>
                </h2>
                <span class="text-xs text-gray-400" id="seccion-1-subtitulo">Identifique el equipo por Nro LIA o UNSJ</span>
            </div>

            <!-- Panel Modalidad A: Activo Patrimonial Específico -->
            <div id="panel-modalidad-activo" class="space-y-4">
                <!-- Tarjeta del equipo preseleccionado o seleccionado -->
                <x-asset-selected-card 
                    id="ticket-asset-card"
                    :elemento="$elemento"
                    onReset="cambiarActivoTicket"
                />

                <!-- Buscador en tiempo real de activo -->
                <div id="ticket-buscador-container" class="{{ $elemento ? 'hidden' : '' }} space-y-2">
                    <x-asset-search 
                        id="ticket-asset-search"
                        mode="select"
                        placeholder="Escriba Nro LIA (ej. LIA-042), Nro UNSJ o modelo..."
                        hiddenName="elemento_id"
                        hiddenValue="{{ old('elemento_id', $elemento?->id) }}"
                        onSelect="seleccionarActivoTicket"
                    />
                    <p class="text-xs text-gray-400">Escriba al menos 2 caracteres para desplegar sugerencias con ubicación en vivo.</p>
                </div>
            </div>

            <!-- Panel Modalidad B: Incidencia General / Infraestructura / Espacios -->
            <div id="panel-modalidad-general" class="hidden space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5" for="ambito_ticket_select">
                        Ámbito o Espacio Físico Afectado <span class="text-rose-500 font-bold">*</span>
                    </label>
                    <select name="ambito_ticket_id" id="ambito_ticket_select"
                            class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#fbc101] focus:border-amber-400 transition-all">
                        <option value="">-- Seleccione el ámbito institucional afectado --</option>
                        @foreach($ambitos as $amb)
                            <option value="{{ $amb->id }}" {{ old('ambito_ticket_id') == $amb->id ? 'selected' : '' }}>
                                {{ $amb->nombre }} @if($amb->descripcion) • {{ $amb->descripcion }} @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1">Opciones configuradas por la administración institucional del LIA.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5" for="puesto_general_input">
                        Sector, Puesto o Referencia Física Específica (Opcional)
                    </label>
                    <input type="text" name="puesto" id="puesto_general_input" value="{{ old('puesto') }}"
                           placeholder="Ej. Puesto 14, Pared Oeste, Tablero Eléctrico Principal, Climatizador 2..."
                           class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#fbc101] focus:border-amber-400">
                    <span class="text-[11px] text-gray-400 mt-1 block">Permite al técnico ubicar exactamente el punto de falla dentro del espacio seleccionado.</span>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 2: DETALLES DEL PROBLEMA -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs p-6 space-y-4">
            <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-6 h-6 bg-[#fbc101] text-black rounded-lg flex items-center justify-center text-xs font-black">2</span>
                    Descripción de la Falla y Síntomas
                    <span class="text-rose-500 font-bold">*</span>
                </h2>
                <span class="text-xs text-gray-400">Detalle el problema observado</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Área o Especialidad Inicial Involucrada
                </label>
                <select name="area_id" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#fbc101] focus:border-amber-400 transition-all">
                    <option value="">-- Seleccionar área inicial (opcional) --</option>
                    @foreach($areas as $ar)
                        <option value="{{ $ar->id }}" {{ old('area_id') == $ar->id ? 'selected' : '' }}>
                            {{ $ar->nombre }} @if($ar->descripcion) - {{ $ar->descripcion }} @endif
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-gray-400 mt-1">El área es acumulativa: los técnicos podrán sumar más áreas de especialidad si la falla lo requiere sin desvincular la original.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Detalle de los Síntomas y Anomalías <span class="text-rose-500 font-bold">*</span>
                </label>
                <textarea name="comentario" rows="4" required
                          placeholder="Describa el comportamiento anómalo detectado: reinicios espontáneos, código de error en pantalla, capacitor inflado, artefactos visuales, daño físico, ruido en ventilador, etc."
                          class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#fbc101] focus:border-amber-400 transition-all">{{ old('comentario') }}</textarea>
            </div>
        </div>

        <!-- SECCIÓN 3: INFORMANTE Y LOGÍSTICA DE UBICACIÓN -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs p-6 space-y-5">
            <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-6 h-6 bg-[#fbc101] text-black rounded-lg flex items-center justify-center text-xs font-black">3</span>
                    Informante y Logística de Ubicación
                </h2>
                <span class="text-xs text-gray-400">Trazabilidad de origen y destino del activo</span>
            </div>

            <!-- Informante / Solicitante -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                    Informante / Solicitante <span class="text-rose-500 font-bold">*</span>
                </label>
                @if($isTec)
                    <input type="text" name="solicitante" list="lista-solicitantes-ticket" value="{{ old('solicitante', $informante) }}" required
                           class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#fbc101] focus:border-amber-400">
                    <datalist id="lista-solicitantes-ticket">
                        @foreach($solicitantesFrecuentes ?? [] as $sol)
                            <option value="{{ $sol }}"></option>
                        @endforeach
                    </datalist>
                    <span class="text-[11px] text-gray-400 mt-1 block">Como técnico, puede seleccionar de la lista o tipear un informante/docente externo.</span>
                @else
                    <input type="text" value="{{ $informante }}" readonly disabled
                           class="w-full bg-gray-100 border border-gray-200 text-gray-500 rounded-xl px-3.5 py-2.5 text-sm font-semibold cursor-not-allowed">
                    <input type="hidden" name="solicitante" value="{{ $informante }}">
                    <span class="text-[11px] text-gray-400 mt-1 block">Autocompletado con su identidad institucional registrada (bloqueado para rol Revisor).</span>
                @endif
            </div>

            <!-- Bloque Condicional para Modalidad General -->
            <div id="bloque-logistica-general" class="hidden bg-amber-50/50 border border-[#fbc101] rounded-xl p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="text-xs text-amber-900">
                    <strong class="font-extrabold block text-sm mb-0.5">Intervención In Situ en el Espacio Reportado</strong>
                    <span>Al tratarse de una incidencia de infraestructura o espacio físico, la inspección y resolución técnica se ejecutarán directamente en el lugar indicado sin traslados de inventario.</span>
                </div>
            </div>

            <!-- Separación clara para Modalidad Activo: Origen vs Decisión de Traslado -->
            <div id="bloque-logistica-activo" class="pt-2 border-t border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Origen (Solo Lectura Informativa) -->
                <div class="bg-gray-50/80 border border-gray-200 rounded-xl p-4 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Ubicación Actual Detectada (Origen)</span>
                        </div>
                        <div class="text-sm font-black text-gray-900" id="ubicacion_actual_texto">
                            @if($elemento)
                                {{ $elemento->ultimoMovimiento?->ubicacion?->nombre ?? 'Sin ubicación registrada' }}
                                <span class="text-xs font-normal text-gray-600 block mt-0.5">
                                    Puesto: <strong class="text-gray-800">{{ $elemento->ultimoMovimiento?->puesto ?: ($elemento->puesto ?: 'Sin puesto asignado') }}</strong>
                                </span>
                            @else
                                <span class="italic text-gray-400 font-normal text-xs">Se detectará automáticamente al seleccionar el equipo arriba.</span>
                            @endif
                        </div>
                    </div>
                    <span class="text-[11px] text-gray-400 mt-3 pt-2 border-t border-gray-200/60 block">
                        Ubicación física actual registrada en inventario para este equipo.
                    </span>
                </div>

                <!-- Decisión de Traslado / Destino Físico -->
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                        Decisión de Traslado Físico durante la Reparación
                    </label>

                    <div class="space-y-2">
                        <!-- Opción 1: In Situ -->
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 hover:border-amber-400 hover:bg-amber-50/20 cursor-pointer transition-all has-checked:border-amber-500 has-checked:bg-amber-50/40">
                            <input type="radio" name="tipo_traslado" value="insitu" id="traslado_insitu" 
                                   {{ old('tipo_traslado', 'insitu') === 'insitu' && !old('ubicacion_id') ? 'checked' : '' }}
                                   onchange="toggleTrasladoFisico(false)"
                                   class="mt-1 text-amber-500 focus:ring-amber-400">
                            <div>
                                <span class="text-sm font-bold text-gray-900 block">Permanece en su sala para inspección in situ</span>
                                <span class="text-xs text-gray-500 leading-snug block mt-0.5">El equipo no se traslada; el equipo técnico asistirá a su sala de origen a revisarlo.</span>
                            </div>
                        </label>

                        <!-- Opción 2: Traslado a Taller / Depósito -->
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 hover:border-amber-400 hover:bg-amber-50/20 cursor-pointer transition-all has-checked:border-amber-500 has-checked:bg-amber-50/40">
                            <input type="radio" name="tipo_traslado" value="taller" id="traslado_taller"
                                   {{ old('tipo_traslado') === 'taller' || old('ubicacion_id') ? 'checked' : '' }}
                                   onchange="toggleTrasladoFisico(true)"
                                   class="mt-1 text-amber-500 focus:ring-amber-400">
                            <div class="flex-1">
                                <span class="text-sm font-bold text-gray-900 block">Trasladar físicamente a Taller de Hardware / Depósito</span>
                                <span class="text-xs text-gray-500 leading-snug block mt-0.5">El activo se retira de la sala y se mueve a un espacio técnico para reparación de banco.</span>
                            </div>
                        </label>
                    </div>

                    <!-- Selector condicional de Destino Físico si se traslada -->
                    <div id="selector_destino_container" class="{{ old('tipo_traslado') === 'taller' || old('ubicacion_id') ? '' : 'hidden' }} pt-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Seleccionar Sala de Destino Técnico <span class="text-rose-500">*</span>
                        </label>
                        <select name="ubicacion_id" id="ubicacion_destino_select" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-gray-900 focus:bg-white focus:ring-2 focus:ring-[#fbc101] focus:border-amber-400">
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
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs p-6 space-y-4">
            <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-6 h-6 bg-[#fbc101] text-black rounded-lg flex items-center justify-center text-xs font-black">4</span>
                    Evidencia Fotográfica
                </h2>
                <span class="text-xs text-gray-400">Cámara en vivo o selección desde galería</span>
            </div>

            <div>
                <label for="fotos_input" class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 hover:border-amber-400 hover:bg-amber-50/20 rounded-2xl p-6 cursor-pointer transition-all">
                    <div class="w-12 h-12 bg-amber-100 text-amber-700 rounded-full flex items-center justify-center mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-gray-800">Tomar foto con la cámara o seleccionar imágenes</span>
                    <span class="text-xs text-gray-400 mt-1">PNG, JPG, WebP hasta 5MB por imagen. Puede seleccionar varias fotos.</span>
                    <input type="file" name="fotos[]" id="fotos_input" accept="image/*;capture=camera" multiple class="hidden" onchange="previewFotos(event)">
                </label>
            </div>

            <!-- Contenedor de miniaturas previsualizadas -->
            <div id="preview_fotos_container" class="hidden grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4">
            @if(Auth::user()->hasRole('tec'))
                <a href="{{ route('tickets.index') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-bold text-sm hover:bg-gray-100 transition-colors w-full sm:w-auto text-center">
                    Cancelar
                </a>
            @else
                <a href="{{ route('dashboard.index') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-bold text-sm hover:bg-gray-100 transition-colors w-full sm:w-auto text-center">
                    Cancelar
                </a>
            @endif
            <button type="submit" class="inline-flex items-center justify-center gap-2 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold px-6 py-2.5 rounded-xl text-sm transition-all shadow-xs w-full sm:w-auto text-center">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>Crear Ticket de Incidencia</span>
            </button>
        </div>
    </form>
</div>

<script>
    // Conmutador interactivo entre Modalidad Activo y Modalidad General
    function setModalidad(modo) {
        const inputModo = document.getElementById('modalidad_input');
        const tabActivo = document.getElementById('tab-modalidad-activo');
        const tabGeneral = document.getElementById('tab-modalidad-general');
        const panelActivo = document.getElementById('panel-modalidad-activo');
        const panelGeneral = document.getElementById('panel-modalidad-general');
        const seccion1Titulo = document.getElementById('seccion-1-titulo');
        const seccion1Subtitulo = document.getElementById('seccion-1-subtitulo');
        const bloqueLogisticaActivo = document.getElementById('bloque-logistica-activo');
        const bloqueLogisticaGeneral = document.getElementById('bloque-logistica-general');
        const hiddenElementoInput = document.getElementById('ticket-asset-search-hidden');
        const ambitoSelect = document.getElementById('ambito_ticket_select');

        if (inputModo) inputModo.value = modo;

        if (modo === 'general') {
            // Estilos de pestañas
            tabGeneral.className = "flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-black flex items-center justify-center gap-2 transition-all bg-[#fbc101] text-black shadow-xs";
            tabActivo.className = "flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition-all text-gray-600 hover:text-gray-900";

            // Paneles
            panelGeneral.classList.remove('hidden');
            panelActivo.classList.add('hidden');
            bloqueLogisticaGeneral.classList.remove('hidden');
            bloqueLogisticaActivo.classList.add('hidden');

            seccion1Titulo.innerText = "Ámbito o Espacio Físico Afectado";
            seccion1Subtitulo.innerText = "Seleccione el laboratorio o servicio del LIA involucrado";

            // Limpiar selección de hardware si se pasa a general
            if (hiddenElementoInput) hiddenElementoInput.disabled = true;
            if (ambitoSelect) ambitoSelect.disabled = false;
        } else {
            // Estilos de pestañas
            tabActivo.className = "flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-black flex items-center justify-center gap-2 transition-all bg-[#fbc101] text-black shadow-xs";
            tabGeneral.className = "flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold flex items-center justify-center gap-2 transition-all text-gray-600 hover:text-gray-900";

            // Paneles
            panelActivo.classList.remove('hidden');
            panelGeneral.classList.add('hidden');
            bloqueLogisticaActivo.classList.remove('hidden');
            bloqueLogisticaGeneral.classList.add('hidden');

            seccion1Titulo.innerText = "Activo que Requiere Intervención";
            seccion1Subtitulo.innerText = "Identifique el equipo por Nro LIA o UNSJ";

            if (hiddenElementoInput) hiddenElementoInput.disabled = false;
            if (ambitoSelect) {
                ambitoSelect.disabled = true;
                ambitoSelect.value = '';
            }
        }
    }

    // Inicializar modalidad según valor previo o input oculto
    document.addEventListener('DOMContentLoaded', function() {
        const modoInicial = document.getElementById('modalidad_input')?.value || 'activo';
        setModalidad(modoInicial);
    });

    // Control de visibilidad para traslado a taller
    function toggleTrasladoFisico(requiereTraslado) {
        const container = document.getElementById('selector_destino_container');
        if (container) {
            if (requiereTraslado) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }
    }

    // Manejador del componente unificado de búsqueda
    window.seleccionarActivoTicket = function(item) {
        const idInput = document.getElementById('ticket-asset-search-hidden');
        if (idInput) idInput.value = item.id;

        const card = document.getElementById('ticket-asset-card');
        const buscadorContainer = document.getElementById('ticket-buscador-container');

        const lia = item.nro_lia && item.nro_lia !== 'S/N' ? item.nro_lia : 'Sin Nro LIA';
        const unsj = item.nro_unsj && item.nro_unsj !== 'S/N' ? item.nro_unsj : '';
        const tipo = item.tipo || 'Activo';
        const marcaModelo = `${item.marca || ''} ${item.modelo || ''}`.trim() || 'Activo sin marca';
        const sala = item.ubicacion_actual || 'Sin sala registrada';
        const puesto = item.puesto ? ` (${item.puesto})` : '';
        const ubicacionCompleta = `${sala}${puesto}`;

        document.getElementById('ticket-asset-card-avatar').innerText = tipo.substring(0, 3).toUpperCase();
        document.getElementById('ticket-asset-card-lia').innerText = lia;
        
        const unsjEl = document.getElementById('ticket-asset-card-unsj');
        if (unsj) {
            unsjEl.innerText = `UNSJ: ${unsj}`;
            unsjEl.classList.remove('hidden');
        } else {
            unsjEl.classList.add('hidden');
        }

        document.getElementById('ticket-asset-card-tipo').innerText = tipo.toUpperCase();
        document.getElementById('ticket-asset-card-modelo').innerText = marcaModelo;
        document.getElementById('ticket-asset-card-ubicacion').innerText = ubicacionCompleta;

        const badgeContainer = document.getElementById('ticket-asset-card-badge-container');
        const badgeClase = item.es_prestamo_activo ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800';
        const badgeTexto = item.es_prestamo_activo ? 'Prestado' : 'En Sala';
        badgeContainer.innerHTML = `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold ${badgeClase}">${badgeTexto}</span>`;

        card.classList.remove('hidden');
        buscadorContainer.classList.add('hidden');

        // Actualizar Sección 3 (Logística de Origen)
        const ubicacionTexto = document.getElementById('ubicacion_actual_texto');
        if (ubicacionTexto) {
            ubicacionTexto.innerHTML = `
                ${ubicacionCompleta}
                <span class="text-xs font-normal text-gray-600 block mt-0.5">Ubicación física detectada del activo</span>
            `;
        }
    };

    window.cambiarActivoTicket = function() {
        const idInput = document.getElementById('ticket-asset-search-hidden');
        if (idInput) idInput.value = '';

        const card = document.getElementById('ticket-asset-card');
        const buscadorContainer = document.getElementById('ticket-buscador-container');
        if (card) card.classList.add('hidden');
        if (buscadorContainer) {
            buscadorContainer.classList.remove('hidden');
            const input = document.getElementById('ticket-asset-search-input');
            if (input) {
                input.value = '';
                input.focus();
            }
        }

        const ubicacionTexto = document.getElementById('ubicacion_actual_texto');
        if (ubicacionTexto) {
            ubicacionTexto.innerHTML = '<span class="italic text-gray-400 font-normal text-xs">Se detectará automáticamente al seleccionar el equipo arriba.</span>';
        }
    };

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
                    div.className = 'relative rounded-xl overflow-hidden border border-gray-200 shadow-2xs aspect-video bg-gray-100 flex items-center justify-center';
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
