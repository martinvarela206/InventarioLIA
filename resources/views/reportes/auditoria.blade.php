@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto py-4 space-y-4 print:p-0 print:m-0 print:max-w-none">
    
    <!-- Barra superior de control de auditoría -->
    <div class="print:hidden flex flex-wrap items-center justify-between gap-3 p-3 figma-panel">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('reportes.index') }}" class="hover:text-figma-brand transition-colors">Hub de Reportes</a>
                <span>/</span>
                <span class="text-figma-text-primary">Auditoría de Sala</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary mt-0.5">Planilla de Auditoría: {{ $ubicacion->nombre }}</h1>
            <p class="text-xs text-figma-text-secondary">{{ $ubicacion->descripcion }} — {{ $elementos->count() }} activos asignados</p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="figma-btn-primary text-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimir Planilla A4</span>
            </button>
            <a href="{{ route('reportes.exportCsv', ['tipo' => 'auditoria-sala', 'ubicacion_id' => $ubicacion->id]) }}" class="figma-btn-secondary text-xs">
                <span>Descargar CSV</span>
            </a>
        </div>
    </div>

    <!-- DOCUMENTO A4 DE AUDITORÍA -->
    <div class="figma-panel p-5 lg:p-7 print:p-0 print:border-none print:shadow-none">
        
        <!-- MEMBRETE OFICIAL UNSJ -->
        <div class="border-b border-figma-border-subtle pb-3 mb-5 text-center">
            <span class="text-[10px] font-bold uppercase tracking-widest text-figma-text-tertiary block">
                UNIVERSIDAD NACIONAL DE SAN JUAN — FACULTAD DE CIENCIAS EXACTAS, FÍSICAS Y NATURALES
            </span>
            <span class="text-base font-bold uppercase text-figma-text-primary block mt-0.5">
                LABORATORIO DE INFORMÁTICA APLICADA (LIA)
            </span>
            <div class="mt-1.5 inline-block bg-figma-bg-deep border border-figma-border-subtle px-3 py-1 rounded text-xs font-mono font-bold uppercase text-figma-text-primary">
                PLANILLA DE RELEVAMIENTO FÍSICO IN SITU: {{ strtoupper($ubicacion->nombre) }}
            </div>
            <div class="flex flex-wrap justify-between items-center text-[10px] font-mono text-figma-text-tertiary mt-2 px-1 gap-2">
                <span>Auditor: <strong class="text-figma-text-primary">{{ $usuarioEmisor }}</strong></span>
                <span>Fecha: <strong class="text-figma-text-primary">{{ $fechaEmision }}</strong></span>
                <span>Total Activos: <strong class="text-figma-text-primary">{{ $elementos->count() }}</strong></span>
            </div>
        </div>

        <!-- TABLA DE VERIFICACIÓN FÍSICA -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs print:text-[8pt]">
                <thead>
                    <tr class="bg-figma-bg-deep border-b border-figma-border-subtle uppercase font-bold text-[10px] text-figma-text-secondary tracking-wider print:bg-gray-200">
                        <th class="py-2 px-2.5 border border-figma-border-subtle w-6 text-center font-mono">#</th>
                        <th class="py-2 px-2.5 border border-figma-border-subtle">Puesto</th>
                        <th class="py-2 px-2.5 border border-figma-border-subtle">Identificadores</th>
                        <th class="py-2 px-2.5 border border-figma-border-subtle">Tipo</th>
                        <th class="py-2 px-2.5 border border-figma-border-subtle">Marca / Modelo / Serie</th>
                        <th class="py-2 px-2.5 border border-figma-border-subtle">Situación</th>
                        <th class="py-2 px-2.5 border border-figma-border-subtle w-36 text-center">Verificación</th>
                        <th class="py-2 px-2.5 border border-figma-border-subtle w-44">Observaciones in situ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-figma-border-subtle text-figma-text-primary text-xs">
                    @forelse($elementos as $idx => $el)
                        @php $u = $el->ultimoMovimiento; @endphp
                        <tr class="hover:bg-figma-bg-deep/40 transition-colors print:odd:bg-white print:even:bg-gray-100">
                            <td class="py-1.5 px-2.5 border border-figma-border-subtle text-center font-mono text-figma-text-tertiary text-xs">{{ $idx + 1 }}</td>
                            <td class="py-1.5 px-2.5 border border-figma-border-subtle font-bold">
                                {{ $u?->puesto ?: ($el->puesto ?: 'General') }}
                            </td>
                            <td class="py-1.5 px-2.5 border border-figma-border-subtle font-mono text-xs">
                                <span class="block">LIA: <strong class="text-figma-brand">{{ $el->nro_lia ?: '-' }}</strong></span>
                                <span class="block text-figma-text-tertiary text-[10px]">UNSJ: {{ $el->nro_unsj ?: '-' }}</span>
                            </td>
                            <td class="py-1.5 px-2.5 border border-figma-border-subtle font-semibold uppercase text-[10px] text-figma-text-secondary">
                                {{ $el->tipo?->nombre }}
                            </td>
                            <td class="py-1.5 px-2.5 border border-figma-border-subtle">
                                <strong class="text-figma-text-primary">{{ $el->marca }}</strong> {{ $el->modelo }}
                                @if($el->especificacionesSintetizadas(false) !== '-')
                                    <span class="block text-[10px] text-figma-text-secondary font-mono">{{ $el->especificacionesSintetizadas(false) }}</span>
                                @endif
                                @if($el->nro_serie)
                                    <span class="block text-[10px] text-figma-text-tertiary font-mono">S/N: {{ $el->nro_serie }}</span>
                                @endif
                            </td>
                            <td class="py-1.5 px-2.5 border border-figma-border-subtle font-medium">
                                {{ $u?->esPrestamo() ? 'Prestado' : 'En Sala' }}
                            </td>
                            <td class="py-1.5 px-2.5 border border-figma-border-subtle text-center whitespace-nowrap text-figma-text-secondary">
                                <span class="inline-block mr-2 font-mono">[ &nbsp; ] OK</span>
                                <span class="inline-block mr-2 font-mono">[ &nbsp; ] Daño</span>
                                <span class="inline-block font-mono">[ &nbsp; ] Falta</span>
                            </td>
                            <td class="py-1.5 px-2.5 border border-figma-border-subtle border-dotted text-figma-text-tertiary">
                                &nbsp;
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-figma-text-tertiary italic text-xs">
                                No se registran activos asignados a esta sala o armario.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- RECUADRO DE CONFORMIDAD -->
        <div class="mt-8 pt-4 border-t border-figma-border-subtle grid grid-cols-2 gap-8 text-[9pt] text-figma-text-secondary">
            <div class="border-t border-figma-border-subtle pt-2 text-center mt-10">
                <span class="block font-bold text-figma-text-primary">Firma del Revisor / Auditor de Sala</span>
                <span class="block text-figma-text-tertiary text-[8pt]">Aclaración y DNI</span>
            </div>
            <div class="border-t border-figma-border-subtle pt-2 text-center mt-10">
                <span class="block font-bold text-figma-text-primary">Firma de Conformidad del Responsable LIA</span>
                <span class="block text-figma-text-tertiary text-[8pt]">Aclaración y Cargo</span>
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
        font-size: 8pt !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    header, footer, nav, .print\:hidden {
        display: none !important;
    }
    table {
        page-break-inside: auto;
        width: 100% !important;
        font-size: 8pt !important;
        border-collapse: collapse !important;
    }
    tr {
        page-break-inside: avoid;
    }
    th, td {
        font-size: 8pt !important;
        padding: 2.5px 3.5px !important;
        line-height: 1.15 !important;
    }
}
</style>
@endsection
