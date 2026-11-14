@extends('layouts.app')

@section('content')
<div class="max-w-[850px] mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-200 pb-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                <a href="{{ route('dashboard.index') }}" class="hover:text-amber-600 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('elementos.index') }}" class="hover:text-amber-600 transition-colors">Inventario</a>
                <span>/</span>
                <span class="text-gray-900">Movimiento Rápido</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-amber-100 text-amber-800 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </span>
                <span>Asistente de Movimiento y Préstamo</span>
            </h1>
        </div>
        <div>
            <a href="{{ route('elementos.index') }}" class="inline-flex items-center gap-1.5 bg-white hover:bg-amber-50/50 border border-gray-300 text-gray-700 font-bold px-4 py-2 rounded-xl text-xs shadow-2xs transition-all">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver al Catálogo</span>
            </a>
        </div>
    </div>

    <!-- SECCIÓN 1: BÚSQUEDA Y SELECCIÓN DEL ACTIVO -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
        <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-400 text-black rounded-lg flex items-center justify-center text-xs font-black">1</span>
                <span>Identificación del Activo</span>
                <span class="text-rose-500 font-bold">*</span>
            </h2>
            <span class="text-xs text-gray-400">Escanee o busque por Nro LIA, UNSJ o modelo</span>
        </div>

        <!-- Tarjeta del equipo seleccionado -->
        <x-asset-selected-card 
            id="movimiento-asset-card"
            :elemento="$elementoPreseleccionado"
            onReset="cambiarActivoMovimiento"
        />

        <!-- Buscador interactivo -->
        <div id="movimiento-buscador-container" class="{{ $elementoPreseleccionado ? 'hidden' : '' }} space-y-2">
            <x-asset-search 
                id="movimiento-asset-search"
                mode="select"
                dropdownId="resultados-dropdown"
                placeholder="Escriba Nro LIA (ej. LIA-FS-01), Nro UNSJ o modelo..."
                hiddenName="elemento_id"
                hiddenValue="{{ $elementoPreseleccionado?->id }}"
                onSelect="seleccionarActivoMovimiento"
            />
            <p class="text-xs text-gray-400">Escriba al menos 2 caracteres para desplegar sugerencias con ubicación en vivo.</p>
        </div>
    </div>

    <!-- MODO DEVOLUCIÓN AUTOMÁTICA (Si el activo tiene préstamo activo) -->
    <div id="seccion-devolucion" class="{{ $elementoPreseleccionado && $elementoPreseleccionado->ultimoMovimiento?->esPrestamo() && !$elementoPreseleccionado->ultimoMovimiento?->fecha_devolucion ? '' : 'hidden' }} bg-white rounded-2xl border border-amber-300 shadow-sm p-6 space-y-4">
        <div class="border-b border-amber-200 pb-3 flex items-center justify-between">
            <h2 class="text-base font-bold text-amber-950 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-400 text-black rounded-lg flex items-center justify-center text-xs font-black">2</span>
                <span>Flujo de Devolución Rápida (Equipo actualmente prestado)</span>
            </h2>
            <span class="text-xs bg-amber-100 text-amber-900 font-bold px-2 py-0.5 rounded-full">Préstamo Activo</span>
        </div>

        <form id="form-devolucion" method="POST" action="{{ $elementoPreseleccionado && $elementoPreseleccionado->ultimoMovimiento ? route('movimientos.devolucion', $elementoPreseleccionado->ultimoMovimiento->id) : '#' }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Fecha de Devolución</label>
                <input type="date" name="fecha_devolucion" value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white font-medium text-sm">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Sala / Laboratorio de Reingreso <span class="text-rose-500 font-bold">*</span></label>
                    <select name="ubicacion_id" class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white font-semibold text-sm" required>
                        @foreach($ubicaciones as $ub)
                            <option value="{{ $ub->id }}">{{ $ub->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Puesto / Mesa de Reingreso</label>
                    <input type="text" name="puesto" placeholder="Ej. Puesto 04, Mesa 2" class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Observaciones de Entrega</label>
                <input type="text" name="comentario" placeholder="Equipo recibido en conformidad con sus accesorios..." class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white text-sm">
            </div>

            <button type="submit" class="w-full py-3.5 px-6 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold text-sm rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>Registrar Devolución y Reingreso al LIA</span>
            </button>
        </form>
    </div>

    <!-- FORMULARIO ESTÁNDAR: TRASLADO INTERNO vs PRÉSTAMO EXTERNO -->
    <div id="seccion-formulario-normal" class="{{ $elementoPreseleccionado && $elementoPreseleccionado->ultimoMovimiento?->esPrestamo() && !$elementoPreseleccionado->ultimoMovimiento?->fecha_devolucion ? 'hidden' : '' }} bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-6">
        
        <form id="form-movimiento" method="POST" action="{{ route('movimientos.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="elemento_id" id="form-elemento-id" value="{{ $elementoPreseleccionado?->id }}">

            <!-- Selector de Tipo de Movimiento -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    <span class="w-5 h-5 bg-amber-400 text-black rounded inline-flex items-center justify-center text-xs font-black mr-1">2</span>
                    Tipo de Operación Patrimonial
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="tipo-btn flex items-center justify-center gap-2 p-3.5 border-2 rounded-xl cursor-pointer font-bold text-sm transition-all border-amber-500 bg-amber-50 text-amber-900" id="btn-tipo-interno">
                        <input type="radio" name="tipo_movimiento" value="interno" class="hidden" checked onchange="toggleTipoAccion('interno')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>Traslado Interno</span>
                    </label>
                    <label class="tipo-btn flex items-center justify-center gap-2 p-3.5 border-2 rounded-xl cursor-pointer font-bold text-sm transition-all border-gray-200 bg-white text-gray-700" id="btn-tipo-prestamo">
                        <input type="radio" name="tipo_movimiento" value="externo_prestamo" class="hidden" onchange="toggleTipoAccion('externo_prestamo')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Préstamo Externo</span>
                    </label>
                </div>
            </div>

            <!-- Campos de Traslado Interno -->
            <div id="campos-interno" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Sala / Laboratorio Destino <span class="text-rose-500 font-bold">*</span></label>
                        <select name="ubicacion_id" class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white font-semibold text-sm">
                            @foreach($ubicaciones as $ub)
                                <option value="{{ $ub->id }}">{{ $ub->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Puesto / Mesa de Destino</label>
                        <input type="text" name="puesto" placeholder="Ej. Puesto 03, Mesa 12" class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white text-sm">
                    </div>
                </div>
            </div>

            <!-- Campos de Préstamo Externo -->
            <div id="campos-prestamo" class="space-y-4 hidden">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Docente / Investigador / Cátedra Solicitante <span class="text-rose-500 font-bold">*</span></label>
                    <input type="text" name="solicitante" id="input-solicitante" list="lista-solicitantes-rapido" placeholder="Ej. Dr. Juan Pérez (Cátedra Algoritmos)" class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white font-semibold text-sm">
                    <datalist id="lista-solicitantes-rapido">
                        @foreach($solicitantesFrecuentes ?? [] as $sol)
                            <option value="{{ $sol }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Fecha de Salida</label>
                    <input type="date" name="fecha_prestamo" value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white font-medium text-sm">
                </div>
            </div>

            <!-- Comentario Común -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Observaciones / Motivo</label>
                <textarea name="comentario" rows="2" placeholder="Notas sobre el traslado o préstamo..." class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white text-sm"></textarea>
            </div>

            <div class="pt-2">
                <button type="submit" id="btn-submit-movimiento" class="w-full py-3.5 px-6 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold rounded-xl shadow-sm transition-all text-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>Confirmar y Guardar Movimiento</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    window.seleccionarActivoMovimiento = function(item) {
        document.getElementById('form-elemento-id').value = item.id;

        const card = document.getElementById('movimiento-asset-card');
        const buscadorContainer = document.getElementById('movimiento-buscador-container');

        const lia = item.nro_lia && item.nro_lia !== 'S/N' ? item.nro_lia : 'Sin Nro LIA';
        const unsj = item.nro_unsj && item.nro_unsj !== 'S/N' ? item.nro_unsj : '';
        const tipo = item.tipo || 'Activo';
        const marcaModelo = `${item.marca || ''} ${item.modelo || ''}`.trim() || 'Activo sin marca';
        const sala = item.ubicacion_actual || 'Sin sala registrada';
        const puesto = item.puesto ? ` (${item.puesto})` : '';
        const ubicacionCompleta = `${sala}${puesto}`;

        document.getElementById('movimiento-asset-card-avatar').innerText = tipo.substring(0, 3).toUpperCase();
        document.getElementById('movimiento-asset-card-lia').innerText = lia;
        
        const unsjEl = document.getElementById('movimiento-asset-card-unsj');
        if (unsj) {
            unsjEl.innerText = `UNSJ: ${unsj}`;
            unsjEl.classList.remove('hidden');
        } else {
            unsjEl.classList.add('hidden');
        }

        document.getElementById('movimiento-asset-card-tipo').innerText = tipo.toUpperCase();
        document.getElementById('movimiento-asset-card-modelo').innerText = marcaModelo;
        document.getElementById('movimiento-asset-card-ubicacion').innerText = ubicacionCompleta;

        const badgeContainer = document.getElementById('movimiento-asset-card-badge-container');
        const badgeClase = item.es_prestamo_activo ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800';
        const badgeTexto = item.es_prestamo_activo ? 'Prestado' : 'En Sala';
        badgeContainer.innerHTML = `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold ${badgeClase}">${badgeTexto}</span>`;

        card.classList.remove('hidden');
        buscadorContainer.classList.add('hidden');

        // Si tiene préstamo activo, activar modo devolución
        if (item.es_prestamo_activo) {
            document.getElementById('seccion-devolucion').classList.remove('hidden');
            document.getElementById('seccion-formulario-normal').classList.add('hidden');
            document.getElementById('form-devolucion').action = `/movimientos/${item.prestamo_id}/devolucion`;
        } else {
            document.getElementById('seccion-devolucion').classList.add('hidden');
            document.getElementById('seccion-formulario-normal').classList.remove('hidden');
        }
    };

    window.cambiarActivoMovimiento = function() {
        document.getElementById('form-elemento-id').value = '';
        const card = document.getElementById('movimiento-asset-card');
        const buscadorContainer = document.getElementById('movimiento-buscador-container');
        if (card) card.classList.add('hidden');
        if (buscadorContainer) {
            buscadorContainer.classList.remove('hidden');
            const input = document.getElementById('movimiento-asset-search-input');
            if (input) {
                input.value = '';
                input.focus();
            }
        }
        document.getElementById('seccion-devolucion').classList.add('hidden');
        document.getElementById('seccion-formulario-normal').classList.remove('hidden');
    };

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
            const input = document.getElementById('movimiento-asset-search-input');
            if (input) input.focus();
        }
    });
</script>
@endsection
