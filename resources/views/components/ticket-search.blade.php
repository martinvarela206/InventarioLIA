@props([
    'id' => 'ticket-search-' . uniqid(),
    'placeholder' => 'Buscar por LIA, UNSJ, problema, solicitante o #ID...',
    'value' => '',
    'inputName' => 'search',
    'searchUrl' => route('tickets.buscar-rapido'),
    'dropdownId' => null,
])

<div id="{{ $id }}-container" class="relative w-full sm:w-80">
    <div class="relative flex items-center">
        <!-- Icono de Búsqueda -->
        <div class="absolute left-3 pointer-events-none text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <!-- Input de Búsqueda -->
        <input type="text"
               id="{{ $id }}-input"
               name="{{ $inputName }}"
               value="{{ $value }}"
               placeholder="{{ $placeholder }}"
               autocomplete="off"
               class="w-full bg-white border border-gray-300 rounded-xl pl-9 pr-9 py-2 text-xs font-semibold text-gray-900 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition-all shadow-2xs">

        <!-- Indicador de Carga (Spinner) -->
        <div id="{{ $id }}-spinner" class="hidden absolute right-3 flex items-center pointer-events-none">
            <svg class="animate-spin h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
        </div>
    </div>

    <!-- Desplegable Flotante de Resultados (Hasta 5 coincidencias) -->
    <div id="{{ $dropdownId ?? ($id . '-dropdown') }}"
         class="hidden absolute z-50 left-0 right-0 mt-1.5 bg-white border border-amber-200/90 rounded-2xl shadow-xl max-h-80 overflow-y-auto divide-y divide-gray-100">
    </div>
</div>

<script>
(function() {
    const componentId = '{{ $id }}';
    const dropdownElementId = '{{ $dropdownId ?? ($id . '-dropdown') }}';
    const searchUrl = '{{ $searchUrl }}';

    const input = document.getElementById(componentId + '-input');
    const spinner = document.getElementById(componentId + '-spinner');
    const dropdown = document.getElementById(dropdownElementId);
    const container = document.getElementById(componentId + '-container');

    let debounceTimer = null;
    let currentResults = [];
    let highlightedIndex = -1;

    if (!input || !dropdown) return;

    input.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(debounceTimer);
        highlightedIndex = -1;

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
                    if (!res.ok) throw new Error('Error al consultar incidencias');
                    return res.json();
                })
                .then(items => {
                    spinner.classList.add('hidden');
                    currentResults = (items || []).slice(0, 5);

                    if (currentResults.length === 0) {
                        dropdown.innerHTML = `
                            <div class="p-3.5 text-center text-xs text-gray-500 italic">
                                No se encontraron tickets con el código, activo o problema indicado.
                            </div>
                        `;
                        dropdown.classList.remove('hidden');
                        return;
                    }

                    let html = '';
                    currentResults.forEach((tck, idx) => {
                        let badgeEstado = '';
                        if (tck.estado === 'resuelto') {
                            badgeEstado = '<span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">Resuelto</span>';
                        } else if (tck.estado === 'baja') {
                            badgeEstado = '<span class="px-2 py-0.5 rounded text-[10px] font-black bg-rose-100 text-rose-800 border border-rose-300">Baja</span>';
                        } else if (tck.estado === 'solucionado') {
                            badgeEstado = '<span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-500 text-black">Solución Propuesta</span>';
                        } else {
                            badgeEstado = '<span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-100 text-amber-900 border border-[#fbc101]">Abierto</span>';
                        }

                        const tipoBadge = tck.es_general
                            ? '<span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-gray-100 text-gray-700 border border-gray-200">Infraestructura</span>'
                            : '<span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">Activo</span>';

                        const detalleExtra = tck.es_general
                            ? `<span class="font-semibold text-gray-700">Ámbito: ${tck.ambito_nombre}</span>`
                            : `<span class="font-semibold text-gray-700">${tck.activo_info || 'Equipo asignado'}</span>`;

                        html += `
                            <div class="ticket-search-item-${componentId} p-3 hover:bg-amber-50/80 cursor-pointer flex flex-col gap-1 text-xs transition-colors" data-index="${idx}">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono font-bold text-gray-900 text-xs sm:text-sm">${tck.codigo}</span>
                                        ${tipoBadge}
                                    </div>
                                    <div class="shrink-0">
                                        ${badgeEstado}
                                    </div>
                                </div>
                                <div class="text-xs text-gray-900 font-semibold line-clamp-1">
                                    ${tck.asunto}
                                </div>
                                <div class="flex items-center gap-1.5 text-[11px] text-gray-500 truncate">
                                    <span>Solicitante: <strong class="text-gray-700 font-normal">${tck.solicitante}</strong></span>
                                    <span>•</span>
                                    <span>${detalleExtra}</span>
                                </div>
                            </div>
                        `;
                    });

                    dropdown.innerHTML = html;
                    dropdown.classList.remove('hidden');

                    // Listener para clic en items
                    dropdown.querySelectorAll(`.ticket-search-item-${componentId}`).forEach(el => {
                        el.addEventListener('click', function() {
                            const idx = parseInt(this.getAttribute('data-index'), 10);
                            const selected = currentResults[idx];
                            if (selected && selected.url) {
                                window.location.href = selected.url;
                            }
                        });
                    });
                })
                .catch(() => {
                    spinner.classList.add('hidden');
                });
        }, 250);
    });

    // Navegación con teclado
    input.addEventListener('keydown', function(e) {
        const items = dropdown.querySelectorAll(`.ticket-search-item-${componentId}`);
        if (!items || items.length === 0 || dropdown.classList.contains('hidden')) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlightedIndex = (highlightedIndex + 1) % items.length;
            updateHighlight(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlightedIndex = (highlightedIndex - 1 + items.length) % items.length;
            updateHighlight(items);
        } else if (e.key === 'Enter') {
            if (highlightedIndex >= 0 && highlightedIndex < currentResults.length) {
                e.preventDefault();
                const selected = currentResults[highlightedIndex];
                if (selected && selected.url) {
                    window.location.href = selected.url;
                }
            }
        } else if (e.key === 'Escape') {
            dropdown.classList.add('hidden');
        }
    });

    function updateHighlight(items) {
        items.forEach((item, i) => {
            if (i === highlightedIndex) {
                item.classList.add('bg-amber-100/80');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('bg-amber-100/80');
            }
        });
    }

    // Ocultar al hacer clic fuera del componente
    document.addEventListener('click', function(e) {
        if (!container.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
})();
</script>
