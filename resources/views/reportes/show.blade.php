@extends('layouts.app')

@section('content')
<div class="max-w-[1300px] mx-auto px-2 print:p-0 print:m-0 print:max-w-none">
    
    <!-- BARRA SUPERIOR DE ACCIONES (Oculta al imprimir) -->
    <div class="print:hidden flex flex-wrap items-center justify-between gap-4 mb-6 bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
        <div class="flex items-center gap-2">
            <a href="{{ route('reportes.index') }}" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm transition-all">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver al Hub de Reportes</span>
            </a>
            <span class="text-xs bg-amber-100 text-amber-900 font-extrabold px-2.5 py-1 rounded-xl border border-amber-300">
                Sin paginación ({{ count($filas) }} registros)
            </span>
        </div>

        <div class="flex items-center gap-3">
            <!-- Botón Imprimir en Papel A4 -->
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-4 py-2 rounded-xl text-xs shadow-sm transition-all">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimir en Papel A4</span>
            </button>

            <!-- Botón Exportar CSV -->
            <a href="{{ route('reportes.exportCsv', array_merge(['tipo' => $tipoReporte], request()->all())) }}" class="inline-flex items-center gap-1.5 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold px-4 py-2 rounded-xl text-xs shadow-sm transition-all">
                <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Exportar CSV Completo</span>
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

        <!-- TABLA COMPLETA SIN PAGINAR (ESTILO PLANILLA DE CÁLCULO) -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-[11px] print:text-[8pt] border border-gray-300">
                <thead>
                    <tr class="bg-gray-100 border-b border-gray-300 text-gray-800 uppercase font-extrabold print:bg-gray-200">
                        <th class="py-1.5 px-2 print:py-1 print:px-1.5 border border-gray-300 w-8 text-center print:text-[8pt]">#</th>
                        @foreach($columnas as $col)
                            <th class="py-1.5 px-2 print:py-1 print:px-1.5 border border-gray-300 print:text-[8pt]">{{ $col }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-gray-800">
                    @forelse($filas as $idx => $fila)
                        <tr class="odd:bg-white even:bg-gray-50/60 print:odd:bg-white print:even:bg-gray-100">
                            <td class="py-1 px-2 print:py-1 print:px-1.5 border border-gray-300 text-center font-mono text-gray-400 font-semibold print:text-[8pt]">{{ $idx + 1 }}</td>
                            @foreach($fila as $celda)
                                <td class="py-1 px-2 print:py-1 print:px-1.5 border border-gray-300 font-medium print:text-[8pt] break-words">
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
