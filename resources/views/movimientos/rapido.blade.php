@extends('layouts.app')

@section('content')
<div class="max-w-[800px] mx-auto">
    <!-- Header -->
    <div class="mb-6 text-center">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-200 text-amber-900 mb-2">
            ⚡ Operación Rápida
        </span>
        <h1 class="text-2xl lg:text-3xl font-extrabold text-gray-900">
            Registro Rápido de Movimientos y Préstamos
        </h1>
        <p class="text-sm text-gray-600 mt-1">
            Optimizado para celulares y lectores de códigos de barra en laboratorios
        </p>
    </div>

    <!-- Main Wizard Card -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden">
        
        <!-- PASO 1: Búsqueda en vivo de Activo -->
        <div class="p-6 bg-gradient-to-b from-gray-50 to-white border-b border-gray-100">
            <label for="buscar-input" class="block text-sm font-bold text-gray-900 mb-2">
                1. Escanear o Tipear Código LIA o UNSJ
            </label>
            <div class="relative">
                <input type="text" id="buscar-input" autocomplete="off"
                    value="{{ $elementoPreseleccionado ? ($elementoPreseleccionado->nro_lia ?: $elementoPreseleccionado->nro_unsj) : '' }}"
                    placeholder="Ej. LIA-FS-01, 02-05950, Bangho..."
                    class="w-full text-lg font-mono font-bold px-4 py-3.5 bg-white border-2 border-amber-400 rounded-xl focus:ring-4 focus:ring-amber-200 focus:border-amber-500 outline-none transition-all shadow-inner">
                <div id="buscar-spinner" class="hidden absolute right-4 top-4">
                    <svg class="animate-spin h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                </div>
            </div>

            <!-- Resultados en Vivo (Dropdown) -->
            <div id="resultados-dropdown" class="hidden mt-2 bg-white rounded-xl shadow-xl border border-gray-200 max-h-60 overflow-y-auto divide-y divide-gray-100 z-30">
            </div>

            <!-- Ficha del Activo Seleccionado -->
            <div id="activo-seleccionado-card" class="{{ $elementoPreseleccionado ? '' : 'hidden' }} mt-4 p-4 rounded-xl border {{ $elementoPreseleccionado && $elementoPreseleccionado->ultimoMovimiento?->esPrestamo() && !$elementoPreseleccionado->ultimoMovimiento?->fecha_devolucion ? 'bg-amber-50 border-amber-300' : 'bg-emerald-50 border-emerald-300' }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span id="card-tipo" class="text-xs font-extrabold uppercase px-2 py-0.5 rounded bg-white text-gray-800 border border-gray-200">
                            {{ strtoupper($elementoPreseleccionado?->tipo?->nombre ?? 'ACTIVO') }}
                        </span>
                        <h3 id="card-titulo" class="text-base font-bold text-gray-900 mt-1">
                            {{ $elementoPreseleccionado?->marca }} {{ $elementoPreseleccionado?->modelo }}
                        </h3>
                        <p id="card-identificadores" class="text-xs font-mono font-semibold text-gray-700">
                            LIA: {{ $elementoPreseleccionado?->nro_lia ?: '-' }} | UNSJ: {{ $elementoPreseleccionado?->nro_unsj ?: '-' }}
                        </p>
                        <p id="card-ubicacion" class="text-xs text-gray-600 mt-1">
                            Ubicación actual: <strong>{{ $elementoPreseleccionado?->presenter()->puestoUbicacion() }}</strong>
                        </p>
                    </div>
                    <div id="card-badge-estado" class="text-right">
                        @if($elementoPreseleccionado)
                            {!! $elementoPreseleccionado->presenter()->statusBadge() !!}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- MODO DEVOLUCIÓN AUTOMÁTICA (Si el activo tiene préstamo activo) -->
        <div id="seccion-devolucion" class="{{ $elementoPreseleccionado && $elementoPreseleccionado->ultimoMovimiento?->esPrestamo() && !$elementoPreseleccionado->ultimoMovimiento?->fecha_devolucion ? '' : 'hidden' }} p-6 bg-amber-50 border-b border-amber-200">
            <div class="flex items-center gap-2 text-amber-900 font-bold mb-3">
                <span class="w-3 h-3 rounded-full bg-amber-600 animate-pulse"></span>
                <span>Flujo de Devolución Rápida (Equipo actualmente prestado)</span>
            </div>
            <form id="form-devolucion" method="POST" action="{{ $elementoPreseleccionado && $elementoPreseleccionado->ultimoMovimiento ? route('movimientos.devolucion', $elementoPreseleccionado->ultimoMovimiento->id) : '#' }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Fecha de Devolución</label>
                        <input type="date" name="fecha_devolucion" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border rounded-lg bg-white font-medium">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Sala / Laboratorio Reingreso</label>
                            <select name="ubicacion_id" class="w-full px-3 py-2 border rounded-lg bg-white font-semibold" required>
                                @foreach($ubicaciones as $ub)
                                    <option value="{{ $ub->id }}">{{ $ub->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Puesto / Mesa (Opcional)</label>
                            <input type="text" name="puesto" placeholder="Ej. Puesto 04" class="w-full px-3 py-2 border rounded-lg bg-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Observaciones de Entrega</label>
                        <input type="text" name="comentario" placeholder="Equipo recibido en conformidad con sus cables..." class="w-full px-3 py-2 border rounded-lg bg-white">
                    </div>

                    <button type="submit" class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow transition-all text-base flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Registrar Devolución y Reingreso
                    </button>
                </div>
            </form>
        </div>

        <!-- FORMULARIO ESTÁNDAR: TRASLADO INTERNO vs PRÉSTAMO EXTERNO -->
        <div id="seccion-formulario-normal" class="{{ $elementoPreseleccionado && $elementoPreseleccionado->ultimoMovimiento?->esPrestamo() && !$elementoPreseleccionado->ultimoMovimiento?->fecha_devolucion ? 'hidden' : '' }} p-6 space-y-6">
            
            <form id="form-movimiento" method="POST" action="{{ route('movimientos.store') }}">
                @csrf
                <input type="hidden" name="elemento_id" id="form-elemento-id" value="{{ $elementoPreseleccionado?->id }}">

                <!-- Selector de Tipo de Movimiento -->
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-900 mb-2">2. Tipo de Acción a Realizar</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="tipo-btn flex items-center justify-center gap-2 p-3.5 border-2 rounded-xl cursor-pointer font-bold text-sm transition-all border-amber-500 bg-amber-50 text-amber-900" id="btn-tipo-interno">
                            <input type="radio" name="tipo_movimiento" value="interno" class="hidden" checked onchange="toggleTipoAccion('interno')">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            Traslado Interno
                        </label>
                        <label class="tipo-btn flex items-center justify-center gap-2 p-3.5 border-2 rounded-xl cursor-pointer font-bold text-sm transition-all border-gray-200 bg-white text-gray-700" id="btn-tipo-prestamo">
                            <input type="radio" name="tipo_movimiento" value="externo_prestamo" class="hidden" onchange="toggleTipoAccion('externo_prestamo')">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Préstamo Externo
                        </label>
                    </div>
                </div>

                <!-- Campos de Traslado Interno -->
                <div id="campos-interno" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Sala / Laboratorio Destino <span class="text-red-500 font-bold">*</span></label>
                            <select name="ubicacion_id" class="w-full px-3.5 py-2.5 border rounded-xl bg-white font-semibold">
                                @foreach($ubicaciones as $ub)
                                    <option value="{{ $ub->id }}">{{ $ub->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Puesto / Mesa de destino</label>
                            <input type="text" name="puesto" placeholder="Ej. Puesto 03, Mesa 12" class="w-full px-3.5 py-2.5 border rounded-xl bg-white">
                        </div>
                    </div>
                </div>

                <!-- Campos de Préstamo Externo -->
                <div id="campos-prestamo" class="space-y-4 hidden">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Docente / Investigador / Cátedra Solicitante <span class="text-red-500 font-bold">*</span></label>
                        <input type="text" name="solicitante" id="input-solicitante" placeholder="Ej. Dr. Juan Pérez (Cátedra Algoritmos)" class="w-full px-3.5 py-2.5 border rounded-xl bg-white font-semibold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Fecha de Salida</label>
                        <input type="date" name="fecha_prestamo" value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 border rounded-xl bg-white font-medium">
                    </div>
                </div>

                <!-- Comentario Común -->
                <div class="mt-4">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Observaciones / Motivo</label>
                    <textarea name="comentario" rows="2" placeholder="Notas sobre el traslado o préstamo..." class="w-full px-3.5 py-2 border rounded-xl bg-white text-sm"></textarea>
                </div>

                <div class="mt-6">
                    <button type="submit" id="btn-submit-movimiento" class="w-full py-4 px-6 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold rounded-xl shadow-md transition-all text-base flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Confirmar y Guardar Movimiento
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
    let timerBusqueda = null;
    window.itemsBusqueda = [];
    const urlBusqueda = '{{ route('movimientos.buscar-rapido') }}';

    document.getElementById('buscar-input')?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (window.itemsBusqueda && window.itemsBusqueda.length > 0) {
                seleccionarActivo(window.itemsBusqueda[0]);
            }
        }
    });

    document.getElementById('buscar-input')?.addEventListener('input', function(e) {
        clearTimeout(timerBusqueda);
        const query = e.target.value.trim();
        const dropdown = document.getElementById('resultados-dropdown');
        const spinner = document.getElementById('buscar-spinner');

        if (query.length < 2) {
            dropdown.classList.add('hidden');
            dropdown.innerHTML = '';
            window.itemsBusqueda = [];
            return;
        }

        spinner.classList.remove('hidden');

        timerBusqueda = setTimeout(() => {
            fetch(`${urlBusqueda}?q=${encodeURIComponent(query)}`)
                .then(res => {
                    if (!res.ok) throw new Error('Error en consulta');
                    return res.json();
                })
                .then(items => {
                    spinner.classList.add('hidden');
                    window.itemsBusqueda = items;

                    if (!items || items.length === 0) {
                        dropdown.innerHTML = '<div class="p-4 text-center text-gray-400 text-xs">No se encontraron activos con ese identificador (código LIA, UNSJ o modelo).</div>';
                        dropdown.classList.remove('hidden');
                        return;
                    }

                    let html = '';
                    items.forEach((item, index) => {
                        const badgeClase = item.es_prestamo_activo ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800';
                        const badgeTexto = item.es_prestamo_activo ? 'Prestado' : 'En Sala';
                        html += `
                            <div onclick="seleccionarActivoIndex(${index})" class="p-3 hover:bg-amber-50 cursor-pointer flex items-center justify-between text-sm transition-colors">
                                <div>
                                    <span class="font-mono font-bold text-gray-900">LIA: ${item.nro_lia} | UNSJ: ${item.nro_unsj}</span>
                                    <div class="text-xs text-gray-600">${item.tipo.toUpperCase()} - ${item.marca || ''} ${item.modelo || ''}</div>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded ${badgeClase}">
                                        ${badgeTexto}
                                    </span>
                                    <div class="text-[11px] text-gray-400">${item.ubicacion_actual}</div>
                                </div>
                            </div>
                        `;
                    });
                    dropdown.innerHTML = html;
                    dropdown.classList.remove('hidden');
                })
                .catch(err => {
                    spinner.classList.add('hidden');
                    dropdown.innerHTML = '<div class="p-4 text-center text-red-400 text-xs">Error al consultar el servidor. Intenta nuevamente.</div>';
                    dropdown.classList.remove('hidden');
                });
        }, 250);
    });

    function seleccionarActivoIndex(index) {
        if (window.itemsBusqueda && window.itemsBusqueda[index]) {
            seleccionarActivo(window.itemsBusqueda[index]);
        }
    }

    function seleccionarActivo(item) {
        document.getElementById('resultados-dropdown').classList.add('hidden');
        document.getElementById('buscar-input').value = item.nro_lia !== 'S/N' ? item.nro_lia : item.nro_unsj;

        document.getElementById('form-elemento-id').value = item.id;
        document.getElementById('card-tipo').innerText = item.tipo.toUpperCase();
        document.getElementById('card-titulo').innerText = `${item.marca || ''} ${item.modelo || ''}`.trim() || 'Activo sin marca';
        document.getElementById('card-identificadores').innerText = `LIA: ${item.nro_lia} | UNSJ: ${item.nro_unsj}`;
        document.getElementById('card-ubicacion').innerHTML = `Ubicación actual: <strong>${item.ubicacion_actual}</strong>`;

        const card = document.getElementById('activo-seleccionado-card');
        card.classList.remove('hidden');

        // Si tiene préstamo activo, mostrar modo de devolución
        if (item.es_prestamo_activo) {
            document.getElementById('seccion-devolucion').classList.remove('hidden');
            document.getElementById('seccion-formulario-normal').classList.add('hidden');
            document.getElementById('form-devolucion').action = `/movimientos/${item.prestamo_id}/devolucion`;
        } else {
            document.getElementById('seccion-devolucion').classList.add('hidden');
            document.getElementById('seccion-formulario-normal').classList.remove('hidden');
        }
    }

    function toggleTipoAccion(tipo) {
        const btnInterno = document.getElementById('btn-tipo-interno');
        const btnPrestamo = document.getElementById('btn-tipo-prestamo');
        const camposInterno = document.getElementById('campos-interno');
        const camposPrestamo = document.getElementById('campos-prestamo');

        if (tipo === 'interno') {
            btnInterno.classList.add('border-amber-500', 'bg-amber-50', 'text-amber-900');
            btnInterno.classList.remove('border-gray-200', 'bg-white', 'text-gray-700');

            btnPrestamo.classList.remove('border-amber-500', 'bg-amber-50', 'text-amber-900');
            btnPrestamo.classList.add('border-gray-200', 'bg-white', 'text-gray-700');

            camposInterno.classList.remove('hidden');
            camposPrestamo.classList.add('hidden');
        } else {
            btnPrestamo.classList.add('border-amber-500', 'bg-amber-50', 'text-amber-900');
            btnPrestamo.classList.remove('border-gray-200', 'bg-white', 'text-gray-700');

            btnInterno.classList.remove('border-amber-500', 'bg-amber-50', 'text-amber-900');
            btnInterno.classList.add('border-gray-200', 'bg-white', 'text-gray-700');

            camposInterno.classList.add('hidden');
            camposPrestamo.classList.remove('hidden');
        }
    }

    // Interceptar submit para verificar elemento seleccionado
    document.getElementById('form-movimiento')?.addEventListener('submit', function(e) {
        const elementoId = document.getElementById('form-elemento-id').value;
        if (!elementoId) {
            e.preventDefault();
            alert('Por favor busca y selecciona un elemento antes de guardar el movimiento.');
            document.getElementById('buscar-input').focus();
            return;
        }
    });
</script>
@endsection
