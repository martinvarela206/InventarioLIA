@extends('layouts.app')

@section('content')
<div class="max-w-[1300px] mx-auto px-2 print:p-0 print:m-0 print:max-w-none">
    
    <!-- BARRA SUPERIOR DE ACCIONES (Oculta al imprimir) -->
    <div class="print:hidden flex flex-wrap items-center justify-between gap-4 mb-6 bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
        <div class="flex items-center gap-2">
            <a href="{{ route('reportes.index') }}" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm transition-all">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver a Reportes</span>
            </a>
            <span class="text-xs bg-amber-100 text-amber-900 font-extrabold px-2.5 py-1 rounded-xl border border-amber-300">
                {{ count($filas) }} registros
            </span>
            <span id="badge-columnas-ocultas" class="hidden text-xs bg-rose-100 text-rose-800 font-bold px-2 py-0.5 rounded-full border border-rose-300">
                <span id="count-columnas-ocultas">0</span> columnas ocultas
            </span>
        </div>

        <div class="flex items-center gap-3">
            <!-- Botón Restaurar Columnas (Visible si hay ocultas) -->
            <button type="button" id="btn-restaurar-columnas" onclick="restaurarTodasLasColumnas()" class="hidden text-xs text-amber-700 hover:text-amber-900 font-bold underline px-2 py-1">
                Restaurar todas las columnas
            </button>

            <!-- Botón Imprimir / PDF -->
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-4 py-2 rounded-xl text-xs shadow-sm transition-all">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimir / PDF</span>
            </button>

            <!-- Botón Exportar CSV -->
            <a id="btn-export-csv" href="{{ route('reportes.exportCsv', array_merge(['tipo' => $tipoReporte], request()->all())) }}" class="inline-flex items-center gap-1.5 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold px-4 py-2 rounded-xl text-xs shadow-sm transition-all">
                <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Exportar CSV</span>
            </a>
        </div>
    </div>

    <!-- DOCUMENTO OFICIAL PARA PANTALLA E IMPRESIÓN -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 lg:p-8 print:p-0 print:border-none print:shadow-none">
        
        <!-- MEMBRETE INSTITUCIONAL OFICIAL (Visible siempre en papel) -->
        <div class="border-b-2 border-gray-800 pb-4 mb-6 text-center">
            <h2 class="text-xs font-bold uppercase tracking-widest text-gray-500">
                UNIVERSIDAD NACIONAL DE SAN JUAN — FACULTAD DE CIENCIAS EXACTAS, FÍSICAS Y NATURALES
            </h2>
            <h1 class="text-xl font-black text-gray-900 mt-1 uppercase">
                Laboratorio de Informática Aplicada (LIA) — Nexo LIA
            </h1>
            <div class="mt-2 text-sm font-bold text-gray-800 bg-gray-50 py-1.5 px-4 rounded border border-gray-200 inline-block">
                {{ $titulo }}
            </div>
            @if(isset($subtitulo))
                <p class="text-xs text-gray-600 mt-1">{{ $subtitulo }}</p>
            @endif
            <div class="flex justify-between items-center text-[11px] text-gray-500 mt-3 px-2">
                <span>Emitido por: <strong>{{ Auth::user()->nombre ?? 'Sistema LIA' }}</strong> ({{ Auth::user()->roles->pluck('rol')->implode(', ') }})</span>
                <span>Fecha y hora de generación: <strong>{{ date('d/m/Y H:i:s') }}</strong></span>
                <span>Total de Registros: <strong>{{ count($filas) }}</strong></span>
            </div>
        </div>

        <!-- RESALTE DE UBICACIÓN ACTUAL (Para reporte de Ubicación y Trazabilidad) -->
        @if(isset($ubicacionActual))
            <div class="mb-6 bg-gradient-to-r from-emerald-50 to-teal-50 border-2 border-emerald-400 rounded-xl p-4 flex flex-wrap items-center justify-between gap-4 shadow-xs">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <span class="text-[11px] uppercase font-black text-emerald-800 tracking-wider block">📍 Ubicación Física Actual en el LIA</span>
                        <div class="text-lg font-black text-gray-900">{{ $ubicacionActual }}</div>
                    </div>
                </div>
                @if(isset($situacionBadge))
                    <div class="flex items-center gap-2 bg-white/80 py-1.5 px-3 rounded-lg border border-emerald-200">
                        <span class="text-xs font-bold text-gray-600">Situación Patrimonial:</span>
                        {!! $situacionBadge !!}
                    </div>
                @endif
            </div>
        @endif

        <!-- TABLA COMPLETA CON GESTIÓN DE COLUMNAS -->
        <div class="overflow-x-auto relative">
            
            <!-- MENÚ DESPLEGABLE FLOTANTE PARA GESTIONAR COLUMNAS (Control "...") -->
            <div id="dropdown-columnas" class="hidden absolute left-12 top-10 z-50 bg-white rounded-xl shadow-xl border border-gray-300 p-3 min-w-[220px] text-xs print:hidden">
                <div class="flex items-center justify-between pb-2 mb-2 border-b border-gray-100 font-bold text-gray-800">
                    <span>Columnas Visibles</span>
                    <button type="button" onclick="cerrarDropdownColumnas()" class="text-gray-400 hover:text-black">✕</button>
                </div>
                <div class="space-y-1.5 max-h-60 overflow-y-auto">
                    @foreach($columnas as $cIdx => $col)
                        <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 p-1 rounded font-medium text-gray-700">
                            <input type="checkbox" checked id="chk-col-{{ $cIdx }}" onchange="toggleVisibilidadColumna({{ $cIdx }}, '{{ addslashes($col) }}', this.checked)" class="rounded text-amber-500 focus:ring-amber-400">
                            <span>{{ $col }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="mt-2 pt-2 border-t border-gray-100 text-right">
                    <button type="button" onclick="restaurarTodasLasColumnas()" class="text-[11px] font-bold text-amber-700 hover:underline">
                        Marcar todas
                    </button>
                </div>
            </div>

            <table class="w-full text-left border-collapse text-[11px] print:text-[8pt] border border-gray-300" id="tabla-reporte">
                <thead>
                    <tr class="bg-gray-100 border-b border-gray-300 text-gray-800 uppercase font-extrabold print:bg-gray-200">
                        <th class="py-1.5 px-2 print:py-1 print:px-1.5 border border-gray-300 w-8 text-center print:text-[8pt]">#</th>
                        @foreach($columnas as $cIdx => $col)
                            @php
                                $esColumnaIdentificadora = (str_contains(mb_strtolower($col), 'lia') || $cIdx === 0);
                            @endphp
                            <th data-col-index="{{ $cIdx }}" data-col-name="{{ $col }}" class="py-1.5 px-2 print:py-1 print:px-1.5 border border-gray-300 print:text-[8pt] whitespace-nowrap">
                                <div class="flex items-center justify-between gap-1">
                                    <div class="flex items-center gap-1.5">
                                        @if($esColumnaIdentificadora)
                                            <!-- Botón "..." en el header de Nro LIA / Identificador para abrir el desplegable de columnas -->
                                            <button type="button" onclick="abrirDropdownColumnas(event)" class="print:hidden p-1 bg-amber-100 hover:bg-amber-200 text-amber-900 rounded font-bold text-[10px] leading-none transition-colors" title="Gestionar columnas visibles (...)">
                                                •••
                                            </button>
                                        @endif
                                        <span>{{ $col }}</span>
                                    </div>
                                    <!-- Botón 'X' a la derecha para ocultar directamente la columna -->
                                    <button type="button" onclick="ocultarColumna({{ $cIdx }}, '{{ addslashes($col) }}', event)" class="print:hidden p-0.5 text-gray-400 hover:text-red-600 hover:bg-gray-200 rounded transition-colors" title="Ocultar columna '{{ $col }}'">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-gray-800">
                    @forelse($filas as $idx => $fila)
                        @php
                            $esActual = (isset($ubicacionActual) && $idx === 0);
                        @endphp
                        <tr class="{{ $esActual ? 'bg-emerald-50/70 font-semibold' : 'odd:bg-white even:bg-gray-50/60' }} print:odd:bg-white print:even:bg-gray-100">
                            <td class="py-1 px-2 print:py-1 print:px-1.5 border border-gray-300 text-center font-mono text-gray-400 font-semibold print:text-[8pt]">
                                {{ $idx + 1 }}
                            </td>
                            @foreach($fila as $cIdx => $celda)
                                <td data-col-index="{{ $cIdx }}" class="py-1 px-2 print:py-1 print:px-1.5 border border-gray-300 font-medium print:text-[8pt] break-words">
                                    {{ $celda }}
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columnas) + 1 }}" class="py-10 text-center text-gray-400 italic">
                                No se encontraron registros para los parámetros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- RECUADRO INFERIOR DE FIRMAS Y CONFORMIDAD (Para resguardo físico en bibliorato) -->
        <div class="mt-8 pt-4 border-t border-gray-300 hidden print:grid grid-cols-2 gap-8 text-[9pt] text-gray-700">
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
    const columnasOcultas = new Set();
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
