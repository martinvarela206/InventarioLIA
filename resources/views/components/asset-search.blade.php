@props([
    'id' => 'asset-search-' . uniqid(),
    'mode' => 'select', // 'select' o 'filter'
    'placeholder' => 'Buscar por LIA, UNSJ, marca, modelo...',
    'value' => '',
    'inputName' => 'search',
    'hiddenName' => 'elemento_id',
    'hiddenValue' => '',
    'onSelect' => 'onAssetSelected',
    'dropdownId' => null,
    'searchUrl' => route('movimientos.buscar-rapido'),
])

<div id="{{ $id }}-container" class="relative w-full">
    @if($mode === 'select')
        <input type="hidden" name="{{ $hiddenName }}" id="{{ $id }}-hidden" value="{{ $hiddenValue }}">
    @endif

    <div class="relative flex items-center">
        <!-- Icono de Búsqueda -->
        <div class="absolute left-3.5 pointer-events-none text-gray-400">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <!-- Input de Búsqueda -->
        <input type="text"
               id="{{ $id }}-input"
               @if($mode === 'filter') name="{{ $inputName }}" @endif
               value="{{ $value }}"
               placeholder="{{ $placeholder }}"
               autocomplete="off"
               class="w-full bg-white border border-gray-300 rounded-xl pl-10 pr-11 py-2.5 text-sm font-semibold text-gray-900 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition-all shadow-xs">

        <!-- Indicador de Carga (Spinner) -->
        <div id="{{ $id }}-spinner" class="hidden absolute right-3.5 flex items-center pointer-events-none">
            <svg class="animate-spin h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
        </div>
    </div>

    <!-- Desplegable Flotante de Resultados -->
    <div id="{{ $dropdownId ?? ($id . '-dropdown') }}" 
         class="hidden absolute z-50 left-0 right-0 mt-1.5 bg-white border border-amber-200/90 rounded-2xl shadow-xl max-h-72 overflow-y-auto divide-y divide-gray-100">
    </div>
</div>

<script>
(function() {
    const componentId = '{{ $id }}';
    const dropdownElementId = '{{ $dropdownId ?? ($id . '-dropdown') }}';
    const mode = '{{ $mode }}';
    const searchUrl = '{{ $searchUrl }}';
    const onSelectName = '{{ $onSelect }}';

    const input = document.getElementById(componentId + '-input');
    const spinner = document.getElementById(componentId + '-spinner');
    const dropdown = document.getElementById(dropdownElementId);
    const hiddenInput = document.getElementById(componentId + '-hidden');

    let debounceTimer = null;
    let currentResults = [];

    if (!input || !dropdown) return;

    input.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(debounceTimer);

        if (query.length < 2) {
            dropdown.classList.add('hidden');
            dropdown.innerHTML = '';
            spinner.classList.add('hidden');
            currentResults = [];
            return;
        }

        spinner.classList.remove('hidden');

        debounceTimer = setTimeout(() => {
            fetch(`${searchUrl}?q=${encodeURIComponent(query)}`)
                .then(res => {
                    if (!res.ok) throw new Error('Error de conexión');
                    return res.json();
                })
                .then(items => {
                    spinner.classList.add('hidden');
                    currentResults = items || [];

                    if (currentResults.length === 0) {
                        dropdown.innerHTML = `
                            <div class="p-3.5 text-center text-xs text-gray-500 italic">
                                No se encontraron activos con el código, modelo o puesto indicado.
                            </div>
                        `;
                        dropdown.classList.remove('hidden');
                        return;
                    }

                    let html = '';
                    currentResults.forEach((item, index) => {
                        const esPrestamo = !!item.es_prestamo_activo;
                        const badgeClase = esPrestamo ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800';
                        const badgeTexto = esPrestamo ? 'Prestado' : 'En Sala';
                        const nroLia = item.nro_lia && item.nro_lia !== 'S/N' ? item.nro_lia : 'Sin LIA';
                        const nroUnsj = item.nro_unsj && item.nro_unsj !== 'S/N' ? item.nro_unsj : null;
                        const sala = item.ubicacion_actual || 'Sin sala registrada';
                        const puesto = item.puesto ? ` (${item.puesto})` : '';

                        html += `
                            <div class="asset-search-item-${componentId} p-3 hover:bg-amber-50/80 cursor-pointer flex items-center justify-between text-sm transition-colors" data-index="${index}">
                                <div class="min-w-0 pr-3">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-mono font-bold text-gray-900 text-xs sm:text-sm">${nroLia}</span>
                                        ${nroUnsj ? `<span class="text-[11px] font-mono font-semibold text-gray-600 bg-gray-100 px-1.5 py-0.2 rounded border border-gray-200">UNSJ: ${nroUnsj}</span>` : ''}
                                        <span class="text-[11px] font-semibold px-2 py-0.2 rounded-full bg-amber-100 text-amber-900 uppercase tracking-wider">${item.tipo || 'Activo'}</span>
                                    </div>
                                    <div class="text-xs text-gray-600 mt-0.5 truncate">
                                        <span class="font-semibold text-gray-800">${item.marca || ''} ${item.modelo || ''}</span>
                                        <span class="text-gray-400">•</span>
                                        <span>Sala: <strong class="text-gray-700">${sala}${puesto}</strong></span>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold ${badgeClase}">
                                        ${badgeTexto}
                                    </span>
                                </div>
                            </div>
                        `;
                    });

                    dropdown.innerHTML = html;
                    dropdown.classList.remove('hidden');

                    // Asignar listeners de clic
                    dropdown.querySelectorAll(`.asset-search-item-${componentId}`).forEach(el => {
                        el.addEventListener('click', function() {
                            const idx = parseInt(this.getAttribute('data-index'));
                            const selected = currentResults[idx];
                            if (!selected) return;

                            if (mode === 'filter') {
                                // En modo filtro de catálogo, clic navega a la ficha del equipo
                                window.location.href = `/elementos/${selected.id}`;
                            } else {
                                // En modo selección, rellena el hidden y dispara el callback
                                if (hiddenInput) hiddenInput.value = selected.id;
                                dropdown.classList.add('hidden');
                                input.value = '';

                                if (typeof window[onSelectName] === 'function') {
                                    window[onSelectName](selected);
                                } else {
                                    window.dispatchEvent(new CustomEvent('asset-selected', { detail: selected }));
                                }
                            }
                        });
                    });
                })
                .catch(() => {
                    spinner.classList.add('hidden');
                    dropdown.innerHTML = '<div class="p-3.5 text-center text-xs text-rose-500 italic">Error al consultar los activos. Intente nuevamente.</div>';
                    dropdown.classList.remove('hidden');
                });
        }, 250);
    });

    // Cerrar al presionar Escape o al hacer clic fuera
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            dropdown.classList.add('hidden');
        }
    });

    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target) && e.target !== input) {
            dropdown.classList.add('hidden');
        }
    });
})();
</script>
