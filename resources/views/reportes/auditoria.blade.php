@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 print:p-0 print:m-0 print:max-w-none">
    
    <!-- Barra superior de control de auditoría -->
    <div class="print:hidden flex items-center justify-between gap-4 mb-6 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
        <div>
            <a href="{{ route('reportes.index') }}" class="text-sm font-semibold text-gray-600 hover:text-black">
                ← Volver al Hub de Reportes
            </a>
            <h1 class="text-xl font-bold text-gray-900 mt-1">Planilla de Auditoría Física de Ubicación: {{ $ubicacion->nombre }}</h1>
            <p class="text-xs text-gray-500">{{ $ubicacion->descripcion }} — {{ $elementos->count() }} activos asignados</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold px-4 py-2 rounded-lg text-xs shadow transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimir Planilla A4
            </button>
            <a href="{{ route('reportes.exportCsv', ['tipo' => 'auditoria-sala', 'ubicacion_id' => $ubicacion->id]) }}" class="px-3.5 py-2 border border-gray-300 font-bold text-gray-700 rounded-lg text-xs hover:bg-gray-50">
                Descargar CSV
            </a>
        </div>
    </div>

    <!-- DOCUMENTO A4 DE AUDITORÍA -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 print:p-0 print:border-none print:shadow-none">
        
        <!-- MEMBRETE OFICIAL UNSJ -->
        <div class="border-b-2 border-gray-900 pb-3 mb-5 text-center">
            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500 block">
                UNIVERSIDAD NACIONAL DE SAN JUAN — FACULTAD DE CIENCIAS EXACTAS, FÍSICAS Y NATURALES
            </span>
            <span class="text-lg font-black uppercase text-gray-900 block mt-0.5">
                LABORATORIO DE INFORMÁTICA APLICADA (LIA)
            </span>
            <div class="mt-1.5 inline-block bg-gray-100 border border-gray-300 px-3 py-1 rounded text-xs font-black uppercase text-gray-800">
                PLANILLA DE RELEVAMIENTO Y AUDITORÍA FÍSICA IN SITU: {{ strtoupper($ubicacion->nombre) }}
            </div>
            <div class="flex justify-between items-center text-[10px] text-gray-600 mt-2">
                <span>Auditor Responsable: <strong>{{ $usuarioEmisor }}</strong></span>
                <span>Fecha de Relevamiento: <strong>{{ $fechaEmision }}</strong></span>
                <span>Total de Ítems en Sala: <strong>{{ $elementos->count() }}</strong></span>
            </div>
        </div>

        <!-- TABLA DE VERIFICACIÓN FÍSICA -->
        <table class="w-full text-left border-collapse text-[11px] border border-gray-400">
            <thead>
                <tr class="bg-gray-100 border-b border-gray-400 uppercase font-black text-gray-900 print:bg-gray-200">
                    <th class="py-1.5 px-2 border border-gray-400 w-6 text-center">#</th>
                    <th class="py-1.5 px-2 border border-gray-400">Puesto / Mesa</th>
                    <th class="py-1.5 px-2 border border-gray-400">Identificadores</th>
                    <th class="py-1.5 px-2 border border-gray-400">Tipo</th>
                    <th class="py-1.5 px-2 border border-gray-400">Marca / Modelo / Serie</th>
                    <th class="py-1.5 px-2 border border-gray-400">Estado Sistema</th>
                    <th class="py-1.5 px-2 border border-gray-400 w-36 text-center">Verificación Física</th>
                    <th class="py-1.5 px-2 border border-gray-400 w-44">Observaciones in situ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300 text-gray-900">
                @forelse($elementos as $idx => $el)
                    @php $u = $el->ultimoMovimiento; @endphp
                    <tr class="odd:bg-white even:bg-gray-50/50 print:odd:bg-white print:even:bg-gray-100">
                        <td class="py-1.5 px-2 border border-gray-400 text-center font-mono">{{ $idx + 1 }}</td>
                        <td class="py-1.5 px-2 border border-gray-400 font-bold">
                            {{ $u?->puesto ?: ($el->puesto ?: 'General') }}
                        </td>
                        <td class="py-1.5 px-2 border border-gray-400 font-mono">
                            <span class="block">LIA: <strong>{{ $el->nro_lia ?: '-' }}</strong></span>
                            <span class="block text-gray-500">UNSJ: {{ $el->nro_unsj ?: '-' }}</span>
                        </td>
                        <td class="py-1.5 px-2 border border-gray-400 font-semibold uppercase text-[10px]">
                            {{ $el->tipo?->nombre }}
                        </td>
                        <td class="py-1.5 px-2 border border-gray-400">
                            <strong>{{ $el->marca }}</strong> {{ $el->modelo }}
                            @if($el->nro_serie)
                                <span class="block text-[10px] text-gray-500 font-mono">S/N: {{ $el->nro_serie }}</span>
                            @endif
                        </td>
                        <td class="py-1.5 px-2 border border-gray-400 font-medium">
                            {{ ucfirst($u?->estado?->nombre ?? 'N/A') }}
                        </td>
                        <td class="py-1.5 px-2 border border-gray-400 text-center whitespace-nowrap">
                            <span class="inline-block mr-2">[ &nbsp; ] OK</span>
                            <span class="inline-block mr-2">[ &nbsp; ] Daño</span>
                            <span class="inline-block">[ &nbsp; ] Falta</span>
                        </td>
                        <td class="py-1.5 px-2 border border-gray-400 border-dotted">
                            &nbsp;
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-10 text-center text-gray-400 italic">
                            No se registran activos asignados a esta sala o armario.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- RECUADRO DE CONFORMIDAD -->
        <div class="mt-8 pt-4 border-t-2 border-gray-800 grid grid-cols-2 gap-8 text-xs text-gray-800">
            <div class="border-t border-gray-800 pt-2 text-center mt-10">
                <span class="block font-bold">Firma del Revisor / Auditor de Sala</span>
                <span class="block text-gray-500 text-[10px]">Aclaración y DNI</span>
            </div>
            <div class="border-t border-gray-800 pt-2 text-center mt-10">
                <span class="block font-bold">Firma de Conformidad del Responsable LIA</span>
                <span class="block text-gray-500 text-[10px]">Aclaración y Cargo</span>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body {
        background-color: #fff !important;
        font-size: 9pt;
    }
    header, footer, .print\:hidden {
        display: none !important;
    }
    table {
        page-break-inside: auto;
    }
    tr {
        page-break-inside: avoid;
    }
}
</style>
@endsection
