@extends('layouts.app')

@section('content')
<div class="max-w-[1300px] mx-auto py-4 space-y-4 print:p-0 print:m-0 print:max-w-none">
    
    <!-- BARRA SUPERIOR DE ACCIONES (Oculta al imprimir) -->
    <div class="print:hidden flex flex-wrap items-center justify-between gap-3 p-3 figma-panel">
        <div class="flex items-center gap-2">
            <a href="{{ route('reportes.index') }}" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver a Reportes</span>
            </a>
            <span class="text-[10px] bg-figma-bg-deep text-figma-brand font-mono font-bold px-2 py-0.5 rounded border border-figma-border-subtle">
                {{ count($filas) }} registros
            </span>
            <span id="badge-columnas-ocultas" class="hidden text-[10px] bg-rose-500/10 text-rose-400 font-bold px-2 py-0.5 rounded border border-rose-500/30">
                <span id="count-columnas-ocultas">0</span> columnas ocultas
            </span>
        </div>

        <div class="flex items-center gap-2">
            <!-- Botón Restaurar Columnas (Visible si hay ocultas) -->
            <button type="button" id="btn-restaurar-columnas" onclick="restaurarTodasLasColumnas()" class="hidden text-xs text-figma-brand hover:underline font-bold px-2 py-1">
                Restaurar columnas
            </button>

            <!-- Botón Imprimir / PDF -->
            <button type="button" onclick="window.print()" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimir / PDF</span>
            </button>

            <!-- Botón Exportar CSV -->
            <a id="btn-export-csv" href="{{ route('reportes.exportCsv', array_merge(['tipo' => $tipoReporte], request()->all())) }}" class="figma-btn-primary text-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Exportar CSV</span>
            </a>
        </div>
    </div>

    <!-- DOCUMENTO OFICIAL PARA PANTALLA E IMPRESIÓN -->
    <div class="figma-panel p-5 lg:p-7 print:p-0 print:border-none print:shadow-none">
        
        <!-- MEMBRETE INSTITUCIONAL OFICIAL (Visible siempre en papel) -->
        <div class="border-b border-figma-border-subtle pb-4 mb-5 text-center">
            <h2 class="text-[10px] font-bold uppercase tracking-widest text-figma-text-tertiary">
                UNIVERSIDAD NACIONAL DE SAN JUAN — FACULTAD DE CIENCIAS EXACTAS, FÍSICAS Y NATURALES
            </h2>
            <h1 class="text-lg font-bold text-figma-text-primary mt-1 uppercase">
                Laboratorio de Informática Aplicada (LIA) — Nexo LIA
            </h1>
            <div class="mt-2 text-xs font-bold text-figma-text-primary bg-figma-bg-deep py-1 px-3 rounded border border-figma-border-subtle inline-block">
                {{ $titulo }}
            </div>
            @if(isset($subtitulo))
                <p class="text-xs text-figma-text-secondary mt-1">{{ $subtitulo }}</p>
            @endif
            <div class="flex flex-wrap justify-between items-center text-[10px] font-mono text-figma-text-tertiary mt-3 px-1 gap-2">
                <span>Emitido por: <strong class="text-figma-text-primary">{{ Auth::user()->nombre ?? 'Sistema LIA' }}</strong> ({{ Auth::user()->roles->pluck('rol')->implode(', ') }})</span>
                <span>Fecha: <strong class="text-figma-text-primary">{{ date('d/m/Y H:i:s') }}</strong></span>
                <span>Total: <strong class="text-figma-text-primary">{{ count($filas) }}</strong></span>
            </div>
        </div>

        <!-- RESALTE DE UBICACIÓN ACTUAL -->
        @if(isset($ubicacionActual))
            <div class="mb-5 figma-card p-3.5 flex flex-wrap items-center justify-between gap-3 border-emerald-500/40">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-emerald-400 tracking-wider block">📍 Ubicación Física Actual</span>
                        <div class="text-sm font-bold text-figma-text-primary">{{ $ubicacionActual }}</div>
                    </div>
                </div>
                @if(isset($situacionBadge))
                    <div class="flex items-center gap-2 bg-figma-bg-deep py-1 px-2.5 rounded border border-figma-border-subtle">
                        <span class="text-[11px] font-semibold text-figma-text-secondary">Situación:</span>
                        {!! $situacionBadge !!}
                    </div>
                @endif
            </div>
        @endif

        <!-- TABLA COMPLETA CON GESTIÓN DE COLUMNAS -->
        <div class="overflow-x-auto relative">
            
            <!-- MENÚ DESPLEGABLE FLOTANTE PARA GESTIONAR COLUMNAS (Control "...") -->
            <div id="dropdown-columnas" class="hidden absolute left-12 top-10 z-50 figma-panel p-3 min-w-[220px] text-xs shadow-2xl print:hidden">
                <div class="flex items-center justify-between pb-2 mb-2 border-b border-figma-border-subtle font-bold text-figma-text-primary text-[11px]">
                    <span>Columnas Visibles</span>
                    <button type="button" onclick="cerrarDropdownColumnas()" class="text-figma-text-tertiary hover:text-figma-text-primary leading-none">✕</button>
                </div>
                <div class="space-y-1.5 max-h-60 overflow-y-auto">
                    @foreach($columnas as $cIdx => $col)
                        @php
                            $estaOculta = in_array($col, $columnasOcultasIniciales ?? []);
                        @endphp
                        <label class="flex items-center gap-2 cursor-pointer hover:bg-figma-bg-deep p-1 rounded font-medium text-figma-text-secondary text-xs">
                            <input type="checkbox" {{ $estaOculta ? '' : 'checked' }} id="chk-col-{{ $cIdx }}" onchange="toggleVisibilidadColumna({{ $cIdx }}, '{{ addslashes($col) }}', this.checked)" class="rounded text-figma-brand focus:ring-figma-brand">
                            <span>{{ $col }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="mt-2 pt-2 border-t border-figma-border-subtle text-right">
                    <button type="button" onclick="restaurarTodasLasColumnas()" class="text-[10px] font-bold text-figma-brand hover:underline">
                        Marcar todas
                    </button>
                </div>
            </div>

            <table class="w-full text-left border-collapse text-xs print:text-[8pt]" id="tabla-reporte">
                <thead>
                    <tr class="bg-figma-bg-deep border-b border-figma-border-subtle text-figma-text-secondary uppercase font-bold text-[10px] tracking-wider print:bg-gray-200">
                        <th class="py-2 px-2.5 border border-figma-border-subtle w-8 text-center font-mono">#</th>
                        @foreach($columnas as $cIdx => $col)
                            @php
                                $esColumnaIdentificadora = (str_contains(mb_strtolower($col), 'lia') || $cIdx === 0);
                                $estaOculta = in_array($col, $columnasOcultasIniciales ?? []);
                            @endphp
                            <th data-col-index="{{ $cIdx }}" data-col-name="{{ $col }}" style="{{ $estaOculta ? 'display: none;' : '' }}" class="py-2 px-2.5 border border-figma-border-subtle whitespace-nowrap">
                                <div class="flex items-center justify-between gap-1">
                                    <div class="flex items-center gap-1.5">
                                        @if($esColumnaIdentificadora)
                                            <button type="button" onclick="abrirDropdownColumnas(event)" class="print:hidden p-0.5 px-1 bg-figma-bg-canvas hover:bg-figma-brand hover:text-white rounded font-mono font-bold text-[9px] transition-colors" title="Gestionar columnas (...)">
                                                •••
                                            </button>
                                        @endif
                                        <span>{{ $col }}</span>
                                    </div>
                                    <button type="button" onclick="ocultarColumna({{ $cIdx }}, '{{ addslashes($col) }}', event)" class="print:hidden p-0.5 text-figma-text-tertiary hover:text-rose-400 rounded transition-colors" title="Ocultar columna '{{ $col }}'">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-figma-border-subtle text-figma-text-primary">
                    @forelse($filas as $idx => $fila)
                        @php
                            $esActual = (isset($ubicacionActual) && $idx === 0);
                        @endphp
                        <tr class="{{ $esActual ? 'bg-emerald-500/10 font-semibold' : 'hover:bg-figma-bg-deep/40' }} transition-colors print:odd:bg-white print:even:bg-gray-100">
                            <td class="py-1.5 px-2.5 border border-figma-border-subtle text-center font-mono text-figma-text-tertiary text-xs">
                                {{ $idx + 1 }}
                            </td>
                            @foreach($fila as $cIdx => $celda)
                                @php
                                    $colName = $columnas[$cIdx] ?? '';
                                    $estaOculta = in_array($colName, $columnasOcultasIniciales ?? []);
                                @endphp
                                <td data-col-index="{{ $cIdx }}" style="{{ $estaOculta ? 'display: none;' : '' }}" class="py-1.5 px-2.5 border border-figma-border-subtle text-xs break-words">
                                    {{ $celda }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columnas) + 1 }}" class="py-8 text-center text-figma-text-tertiary italic text-xs">
                                No se encontraron registros para los parámetros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- RECUADRO INFERIOR DE FIRMAS Y CONFORMIDAD (Para resguardo físico en bibliorato) -->
        <div class="mt-8 pt-4 border-t border-figma-border-subtle hidden print:grid grid-cols-2 gap-8 text-[9pt] text-gray-700">
            <div class="border-t border-gray-800 pt-2 text-center mt-10">
                <span class="block font-bold">Firma del Revisor / Auditor</span>
                <span class="block text-gray-500 text-[8pt]">Aclaración y DNI</span>
            </div>
            <div class="border-t border-gray-800 pt-2 text-center mt-10">
                <span class="block font-bold">Firma del Director / Autoridad LIA</span>
                <span class="block text-gray-500 text-[8pt]">Aclaración y Cargo</span>
            </div>
        </div>
    </div>
</div>

<script>
    const columnasOcultas = new Set(@json($columnasOcultasIniciales ?? []));
    const urlBaseExportCsv = "{{ route('reportes.exportCsv', array_merge(['tipo' => $tipoReporte], request()->except('columnas_ocultas')) ) }}";

    function abrirDropdownColumnas(event) {
        event.stopPropagation();
        const dd = document.getElementById('dropdown-columnas');
        dd.classList.toggle('hidden');
    }

    function cerrarDropdownColumnas() {
        document.getElementById('dropdown-columnas').classList.add('hidden');
    }

    document.addEventListener('click', function(e) {
        const dd = document.getElementById('dropdown-columnas');
        if (dd && !dd.contains(e.target)) {
            dd.classList.add('hidden');
        }
    });

    function toggleVisibilidadColumna(idx, nombreColumna, visible) {
        if (!visible) {
            columnasOcultas.add(nombreColumna);
        } else {
            columnasOcultas.delete(nombreColumna);
        }
        aplicarVisibilidadColumna(idx, visible);
        actualizarEstadoGlobal();
    }

    function ocultarColumna(idx, nombreColumna, event) {
        if (event) event.stopPropagation();
        columnasOcultas.add(nombreColumna);
        const chk = document.getElementById('chk-col-' + idx);
        if (chk) chk.checked = false;
        aplicarVisibilidadColumna(idx, false);
        actualizarEstadoGlobal();
    }

    function aplicarVisibilidadColumna(idx, visible) {
        const celdas = document.querySelectorAll(`[data-col-index="${idx}"]`);
        celdas.forEach(el => {
            if (visible) {
                el.style.display = '';
            } else {
                el.style.display = 'none';
            }
        });
    }

    function restaurarTodasLasColumnas() {
        columnasOcultas.clear();
        document.querySelectorAll('#dropdown-columnas input[type="checkbox"]').forEach(chk => chk.checked = true);
        document.querySelectorAll('#tabla-reporte [data-col-index]').forEach(el => el.style.display = '');
        actualizarEstadoGlobal();
        cerrarDropdownColumnas();
    }

    function actualizarEstadoGlobal() {
        const badge = document.getElementById('badge-columnas-ocultas');
        const count = document.getElementById('count-columnas-ocultas');
        const btnRestaurar = document.getElementById('btn-restaurar-columnas');
        const btnCsv = document.getElementById('btn-export-csv');

        const totalOcultas = columnasOcultas.size;
        if (totalOcultas > 0) {
            badge.classList.remove('hidden');
            count.innerText = totalOcultas;
            btnRestaurar.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
            btnRestaurar.classList.add('hidden');
        }

        // Sincronizar enlace de exportación CSV con las columnas ocultadas
        if (btnCsv) {
            const separator = urlBaseExportCsv.includes('?') ? '&' : '?';
            const listaOcultas = encodeURIComponent(Array.from(columnasOcultas).join(','));
            btnCsv.href = totalOcultas > 0 
                ? `${urlBaseExportCsv}${separator}columnas_ocultas=${listaOcultas}`
                : urlBaseExportCsv;
        }
    }

    actualizarEstadoGlobal();
</script>

<style>
@page {
    size: landscape;
    margin: 8mm 6mm;
}

@media print {
    body {
        background-color: #fff !important;
        color: #000 !important;
        font-size: 8pt !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    header, footer, nav, .print\:hidden {
        display: none !important;
    }
    .print\:grid {
        display: grid !important;
    }
    table {
        page-break-inside: auto;
        width: 100% !important;
        font-size: 8pt !important;
        border-collapse: collapse !important;
        table-layout: auto !important;
    }
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    thead {
        display: table-header-group;
    }
    th, td {
        font-size: 8pt !important;
        padding: 2.5px 3.5px !important;
        line-height: 1.15 !important;
        word-break: break-word;
    }
    th {
        font-size: 8pt !important;
        font-weight: 800 !important;
        background-color: #f3f4f6 !important;
        text-transform: uppercase !important;
    }
}
</style>
@endsection
