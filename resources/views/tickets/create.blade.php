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
                <span>Apertura de Ticket de Reparación Técnica</span>
            </h1>
        </div>
        <div>
            @if(Auth::user()->hasRole('tec'))
                <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-bold px-4 py-2 rounded-xl text-sm transition-all shadow-sm">
                    ← Bandeja de Tickets
                </a>
            @else
                <a href="{{ route('dashboard.index') }}" class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-bold px-4 py-2 rounded-xl text-sm transition-all shadow-sm">
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

    <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- SECCIÓN 1: IDENTIFICACIÓN DEL ACTIVO -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
            <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-6 h-6 bg-gray-100 text-gray-700 rounded-lg flex items-center justify-center text-xs font-black">1</span>
                    Activo que Requiere Intervención
                    <span class="text-rose-500 font-bold">*</span>
                </h2>
                <span class="text-xs text-gray-400">Identifique el equipo por Nro LIA o UNSJ</span>
            </div>

            <!-- Input oculto para el ID final seleccionado -->
            <input type="hidden" name="elemento_id" id="elemento_id_input" value="{{ old('elemento_id', $elemento?->id) }}">

            @if($elemento)
                <!-- Tarjeta del equipo preseleccionado -->
                <div id="elemento_seleccionado_card" class="bg-amber-50/70 border border-amber-300 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white rounded-lg border border-amber-200 flex items-center justify-center font-black text-amber-700 text-sm shadow-2xs">
                            {{ $elemento->tipo?->nombre ? strtoupper(substr($elemento->tipo->nombre, 0, 3)) : 'LIA' }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-black text-gray-900 text-base">{{ $elemento->nro_lia ?: 'Sin Nro LIA' }}</span>
                                @if($elemento->nro_unsj)
                                    <span class="text-xs font-bold text-gray-600 bg-white px-2 py-0.5 rounded border border-gray-200">UNSJ: {{ $elemento->nro_unsj }}</span>
                                @endif
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-200/80 text-amber-900">{{ ucfirst($elemento->tipo?->nombre ?? 'N/A') }}</span>
                            </div>
                            <div class="text-xs text-gray-600 mt-0.5">
                                <span class="font-bold">{{ $elemento->marca }}</span> {{ $elemento->modelo }} 
                                • Ubicación actual: <span class="font-semibold text-gray-800">{{ $elemento->ultimoMovimiento?->ubicacion?->nombre ?? 'Sin asignar' }} ({{ $elemento->ultimoMovimiento?->puesto ?: ($elemento->puesto ?: 'S/P') }})</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="cambiarElemento()" class="text-xs font-bold text-amber-800 hover:text-amber-950 underline self-start sm:self-auto">
                        Cambiar equipo
                    </button>
                </div>
            @endif

            <!-- Buscador en tiempo real de activo (si no está seleccionado o si se desea cambiar) -->
            <div id="buscador_elemento_container" class="{{ $elemento ? 'hidden' : '' }} space-y-2">
                <div class="relative">
                    <input type="text" id="buscador_activo_input" 
                           placeholder="Escriba Nro LIA (ej. LIA-042) o Nro UNSJ..." 
                           autocomplete="off"
                           class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-3 text-sm font-semibold text-gray-900 focus:bg-white focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition-all">
                    <div id="sugerencias_activo" class="hidden absolute z-30 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-60 overflow-y-auto divide-y divide-gray-100">
                    </div>
                </div>
                <p class="text-xs text-gray-400">Escriba al menos 2 caracteres para desplegar sugerencias interactivas.</p>
            </div>
        </div>

        <!-- SECCIÓN 2: DETALLES DEL PROBLEMA -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
            <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-6 h-6 bg-gray-100 text-gray-700 rounded-lg flex items-center justify-center text-xs font-black">2</span>
                    Descripción de la Falla y Síntomas
                    <span class="text-rose-500 font-bold">*</span>
                </h2>
                <span class="text-xs text-gray-400">Detalle el problema observado</span>
            </div>

            <div>
                <textarea name="comentario" rows="4" required
                          placeholder="Describa el comportamiento anómalo detectado: reinicios espontáneos, código de error en pantalla, capacitor inflado, artefactos visuales, daño físico, ruido en ventilador, etc."
                          class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition-all">{{ old('comentario') }}</textarea>
            </div>
        </div>

        <!-- SECCIÓN 3: INFORMANTE Y UBICACIÓN -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">
            <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-6 h-6 bg-gray-100 text-gray-700 rounded-lg flex items-center justify-center text-xs font-black">3</span>
                    Informante y Asignación
                </h2>
                <span class="text-xs text-gray-400">Trazabilidad de la persona que reporta</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Informante / Solicitante -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Informante / Solicitante <span class="text-rose-500 font-bold">*</span>
                    </label>
                    @if($isTec)
                        <input type="text" name="solicitante" value="{{ old('solicitante', $informante) }}" required
                               class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-gray-900 focus:bg-white focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                        <span class="text-[11px] text-gray-400 mt-1 block">Como técnico, puede editar o especificar un informante externo.</span>
                    @else
                        <input type="text" value="{{ $informante }}" readonly disabled
                               class="w-full bg-gray-100 border border-gray-200 text-gray-500 rounded-xl px-3.5 py-2.5 text-sm font-semibold cursor-not-allowed">
                        <input type="hidden" name="solicitante" value="{{ $informante }}">
                        <span class="text-[11px] text-gray-400 mt-1 block">Autocompletado con su identidad institucional registrada (bloqueado para rol Revisor).</span>
                    @endif
                </div>

                <!-- Ubicación Física / Taller -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Ubicación Física Actual / Taller
                    </label>
                    <select name="ubicacion_id" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-gray-900 focus:bg-white focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                        <option value="">Heredar ubicación actual del activo</option>
                        @foreach($ubicaciones as $ub)
                            <option value="{{ $ub->id }}" {{ old('ubicacion_id', $elemento?->ultimoMovimiento?->ubicacion_id) == $ub->id ? 'selected' : '' }}>
                                {{ $ub->nombre }}
                            </option>
                        @endforeach
                    </select>
                    <span class="text-[11px] text-gray-400 mt-1 block">Por defecto permanece en su sala o se traslada a Taller de Hardware.</span>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 4: ADJUNTOS FOTOGRÁFICOS -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
            <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-6 h-6 bg-gray-100 text-gray-700 rounded-lg flex items-center justify-center text-xs font-black">4</span>
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
                    <span class="text-xs text-gray-400 mt-1">PNG, JPG, WebP hasta 10MB por imagen. Puede seleccionar varias fotos.</span>
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
            <button type="submit" class="inline-flex items-center justify-center gap-2 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold px-6 py-2.5 rounded-xl text-sm transition-all shadow-md w-full sm:w-auto text-center">
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
                            sugerenciasBox.innerHTML = '<div class="p-3 text-xs text-gray-400 italic">No se encontraron activos coincidentes.</div>';
                            sugerenciasBox.classList.remove('hidden');
                            return;
                        }

                        let html = '';
                        items.forEach(el => {
                            html += `
                                <div onclick="seleccionarActivo(${el.id}, '${el.nro_lia || ''}', '${el.nro_unsj || ''}', '${el.tipo || ''}', '${el.marca || ''}', '${el.modelo || ''}', '${el.ubicacion || 'Sin sala'}') "
                                     class="p-3 hover:bg-amber-50 cursor-pointer flex items-center justify-between transition-colors">
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-gray-900 text-sm">${el.nro_lia || 'Sin LIA'}</span>
                                            ${el.nro_unsj ? `<span class="text-[11px] font-semibold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">UNSJ: ${el.nro_unsj}</span>` : ''}
                                            <span class="text-xs text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full font-semibold">${el.tipo}</span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">${el.marca || ''} ${el.modelo || ''} • Sala: ${el.ubicacion || 'Sin sala'}</div>
                                    </div>
                                    <span class="text-xs font-bold text-amber-600">Seleccionar →</span>
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
        newCard.className = 'bg-amber-50/70 border border-amber-300 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3';
        newCard.innerHTML = `
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white rounded-lg border border-amber-200 flex items-center justify-center font-black text-amber-700 text-sm shadow-2xs">
                    ${tipo.substring(0, 3).toUpperCase()}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-black text-gray-900 text-base">${lia || 'Sin Nro LIA'}</span>
                        ${unsj ? `<span class="text-xs font-bold text-gray-600 bg-white px-2 py-0.5 rounded border border-gray-200">UNSJ: ${unsj}</span>` : ''}
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-200/80 text-amber-900">${tipo}</span>
                    </div>
                    <div class="text-xs text-gray-600 mt-0.5">
                        <span class="font-bold">${marca}</span> ${modelo} • Ubicación: <span class="font-semibold text-gray-800">${ubicacion}</span>
                    </div>
                </div>
            </div>
            <button type="button" onclick="cambiarElemento()" class="text-xs font-bold text-amber-800 hover:text-amber-950 underline self-start sm:self-auto">
                Cambiar equipo
            </button>
        `;

        container.parentNode.insertBefore(newCard, container);
        container.classList.add('hidden');
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
