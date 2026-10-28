@extends('layouts.app')

@section('content')
<div class="max-w-[800px] mx-auto py-4 space-y-4">
    <!-- Header Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('dashboard.index') }}" class="hover:text-figma-brand transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-figma-text-primary">Operaciones</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Movimiento Rápido de Activo</span>
            </h1>
        </div>
        <div>
            <a href="{{ route('dashboard.index') }}" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver al Dashboard</span>
            </a>
        </div>
    </div>

    <!-- Main Wizard Panel (Figma Studio Inspector) -->
    <div class="figma-panel p-5 sm:p-6 space-y-5 shadow-sm">
        
        <!-- PASO 1: Búsqueda en vivo de Activo -->
        <div class="space-y-3">
            <label for="buscar-input" class="block text-xs font-bold text-figma-text-primary uppercase tracking-wider">
                1. Escanear o Tipear Código LIA o UNSJ
            </label>
            <div class="relative">
                <input type="text" id="buscar-input" autocomplete="off"
                    value="{{ $elementoPreseleccionado ? ($elementoPreseleccionado->nro_lia ?: $elementoPreseleccionado->nro_unsj) : '' }}"
                    placeholder="Ej. LIA-FS-01, 02-05950, Bangho..."
                    class="figma-input w-full text-base font-mono font-bold px-4 py-3">
                <div id="buscar-spinner" class="hidden absolute right-3.5 top-3.5">
                    <svg class="animate-spin h-5 w-5 text-figma-brand" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                </div>
                <!-- Resultados en Vivo Flotante -->
                <div id="resultados-dropdown" class="hidden absolute left-0 right-0 mt-1 figma-panel bg-figma-card shadow-2xl border-figma-border-strong max-h-64 overflow-y-auto divide-y divide-figma-border-subtle z-50">
                </div>
            </div>

            <!-- Ficha del Activo Seleccionado -->
            <div id="activo-seleccionado-card" class="{{ $elementoPreseleccionado ? '' : 'hidden' }} figma-card p-4 border-figma-border-strong">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span id="card-tipo" class="font-mono text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-figma-bg-deep text-figma-brand border border-figma-border-subtle">
                            {{ strtoupper($elementoPreseleccionado?->tipo?->nombre ?? 'ACTIVO') }}
                        </span>
                        <h3 id="card-titulo" class="text-sm font-bold text-figma-text-primary mt-1.5">
                            {{ $elementoPreseleccionado?->marca }} {{ $elementoPreseleccionado?->modelo }}
                        </h3>
                        <p id="card-identificadores" class="text-xs font-mono font-semibold text-figma-text-secondary mt-0.5">
                            LIA: {{ $elementoPreseleccionado?->nro_lia ?: '-' }} | UNSJ: {{ $elementoPreseleccionado?->nro_unsj ?: '-' }}
                        </p>
                        <p id="card-ubicacion" class="text-[11px] text-figma-text-tertiary mt-1">
                            Ubicación actual: <strong class="text-figma-text-secondary">{{ $elementoPreseleccionado?->presenter()->puestoUbicacion() }}</strong>
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

        <!-- MODO DEVOLUCIÓN AUTOMÁTICA -->
        <div id="seccion-devolucion" class="{{ $elementoPreseleccionado && $elementoPreseleccionado->ultimoMovimiento?->esPrestamo() && !$elementoPreseleccionado->ultimoMovimiento?->fecha_devolucion ? '' : 'hidden' }} figma-card p-4 space-y-3.5 border-amber-500/40">
            <div class="flex items-center gap-2 text-amber-500 font-bold text-xs">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                <span>Flujo de Devolución Rápida (Equipo actualmente en préstamo externo)</span>
            </div>
            <form id="form-devolucion" method="POST" action="{{ $elementoPreseleccionado && $elementoPreseleccionado->ultimoMovimiento ? route('movimientos.devolucion', $elementoPreseleccionado->ultimoMovimiento->id) : '#' }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Fecha de Devolución</label>
                    <input type="date" name="fecha_devolucion" value="{{ date('Y-m-d') }}" class="figma-input w-full px-3 py-1.5 text-xs font-mono">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Sala de Reingreso <span class="text-rose-400">*</span></label>
                        <select name="ubicacion_id" class="figma-input w-full px-3 py-1.5 text-xs cursor-pointer" required>
                            @foreach($ubicaciones as $ub)
                                <option value="{{ $ub->id }}">{{ $ub->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Puesto / Mesa (Opcional)</label>
                        <input type="text" name="puesto" placeholder="Ej. Puesto 04" class="figma-input w-full px-3 py-1.5 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Observaciones de Entrega</label>
                    <input type="text" name="comentario" placeholder="Equipo recibido en conformidad con sus accesorios..." class="figma-input w-full px-3 py-1.5 text-xs">
                </div>

                <div class="pt-2">
                    <button type="submit" class="figma-btn-primary w-full justify-center py-2.5 text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Registrar Devolución y Reingreso</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- FORMULARIO ESTÁNDAR: TRASLADO INTERNO vs PRÉSTAMO EXTERNO -->
        <div id="seccion-formulario-normal" class="{{ $elementoPreseleccionado && $elementoPreseleccionado->ultimoMovimiento?->esPrestamo() && !$elementoPreseleccionado->ultimoMovimiento?->fecha_devolucion ? 'hidden' : '' }} space-y-4">
            
            <form id="form-movimiento" method="POST" action="{{ route('movimientos.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="elemento_id" id="form-elemento-id" value="{{ $elementoPreseleccionado?->id }}">

                <!-- Selector de Acción: Figma Pill Segment Ribbon -->
                <div>
                    <label class="block text-xs font-bold text-figma-text-primary uppercase tracking-wider mb-2">2. Acción a Realizar</label>
                    <div class="figma-pill-ribbon w-full flex">
                        <label class="figma-pill-item flex-1 justify-center cursor-pointer active" id="btn-tipo-interno">
                            <input type="radio" name="tipo_movimiento" value="interno" class="hidden" checked onchange="toggleTipoAccion('interno')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            <span>Traslado Interno (Dentro de Salas)</span>
                        </label>
                        <label class="figma-pill-item flex-1 justify-center cursor-pointer" id="btn-tipo-prestamo">
                            <input type="radio" name="tipo_movimiento" value="externo_prestamo" class="hidden" onchange="toggleTipoAccion('externo_prestamo')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span>Préstamo a Cátedra / Externo</span>
                        </label>
                    </div>
                </div>

                <!-- Campos de Traslado Interno -->
                <div id="campos-interno" class="figma-card p-4 space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Sala Destino <span class="text-rose-400">*</span></label>
                            <select name="ubicacion_id" class="figma-input w-full px-3 py-1.5 text-xs cursor-pointer">
                                @foreach($ubicaciones as $ub)
                                    <option value="{{ $ub->id }}">{{ $ub->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Puesto / Mesa de Destino</label>
                            <input type="text" name="puesto" placeholder="Ej. Puesto 03, Mesa 12" class="figma-input w-full px-3 py-1.5 text-xs">
                        </div>
                    </div>
                </div>

                <!-- Campos de Préstamo Externo -->
                <div id="campos-prestamo" class="figma-card p-4 space-y-3 hidden">
                    <div>
                        <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Docente / Investigador / Cátedra <span class="text-rose-400">*</span></label>
                        <input type="text" name="solicitante" id="input-solicitante" list="lista-solicitantes-rapido" placeholder="Ej. Dr. Juan Pérez (Cátedra Algoritmos)" class="figma-input w-full px-3 py-1.5 text-xs font-medium">
                        <datalist id="lista-solicitantes-rapido">
                            @foreach($solicitantesFrecuentes ?? [] as $sol)
                                <option value="{{ $sol }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Fecha de Salida</label>
                        <input type="date" name="fecha_prestamo" value="{{ date('Y-m-d') }}" class="figma-input w-full px-3 py-1.5 text-xs font-mono">
                    </div>
                </div>

                <!-- Comentario Común -->
                <div>
                    <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Observaciones / Motivo</label>
                    <textarea name="comentario" rows="2" placeholder="Notas adicionales sobre el movimiento..." class="figma-input w-full px-3 py-1.5 text-xs"></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" id="btn-submit-movimiento" class="figma-btn-primary w-full justify-center py-2.5 text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Confirmar y Guardar Movimiento</span>
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
                        dropdown.innerHTML = '<div class="p-3 text-center text-figma-text-tertiary text-xs">No se encontraron activos con ese identificador.</div>';
                        dropdown.classList.remove('hidden');
                        return;
                    }

                    let html = '';
                    items.forEach((item, index) => {
                        const badgeClase = item.es_prestamo_activo ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30';
                        const badgeTexto = item.es_prestamo_activo ? 'Prestado' : 'En Sala';
                        html += `
                            <div onclick="seleccionarActivoIndex(${index})" class="p-2.5 hover:bg-figma-hover cursor-pointer flex items-center justify-between text-xs transition-colors">
                                <div>
                                    <span class="font-mono font-bold text-figma-brand">LIA: ${item.nro_lia} | UNSJ: ${item.nro_unsj}</span>
                                    <div class="text-[11px] text-figma-text-tertiary">${item.tipo.toUpperCase()} - ${item.marca || ''} ${item.modelo || ''}</div>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded font-mono ${badgeClase}">
                                        ${badgeTexto}
                                    </span>
                                    <div class="text-[10px] text-figma-text-tertiary mt-0.5">${item.ubicacion_actual}</div>
                                </div>
                            </div>
                        `;
                    });
                    dropdown.innerHTML = html;
                    dropdown.classList.remove('hidden');
                })
                .catch(err => {
                    spinner.classList.add('hidden');
                    dropdown.innerHTML = '<div class="p-3 text-center text-rose-400 text-xs">Error de conexión al servidor.</div>';
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
            btnInterno.classList.add('active');
            btnPrestamo.classList.remove('active');
            camposInterno.classList.remove('hidden');
            camposPrestamo.classList.add('hidden');
        } else {
            btnPrestamo.classList.add('active');
            btnInterno.classList.remove('active');
            camposInterno.classList.add('hidden');
            camposPrestamo.classList.remove('hidden');
        }
    }

    document.getElementById('form-movimiento')?.addEventListener('submit', function(e) {
        const elementoId = document.getElementById('form-elemento-id').value;
        if (!elementoId) {
            e.preventDefault();
            alert('Por favor busca y selecciona un activo antes de guardar el movimiento.');
            document.getElementById('buscar-input').focus();
            return;
        }
    });
</script>
@endsection
