@extends('layouts.app')

@section('content')
<style>
@media print {
    /* Ocultar elementos de navegación y botones de acción en impresión */
    header, nav, footer, .no-print, #navbar, .navbar {
        display: none !important;
    }
    body {
        background-color: #ffffff !important;
        color: #000000 !important;
        font-size: 11pt !important;
    }
    .print-container {
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        box-shadow: none !important;
        border: none !important;
    }
    .page-break {
        page-break-after: always;
    }
}
</style>

<div class="max-w-[1000px] mx-auto px-4 py-6 print-container space-y-8">
    <!-- Barra Superior de Navegación y Acciones (no-print) -->
    <div class="no-print flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4">
        <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
            <a href="{{ route('auditorias.index') }}" class="hover:text-indigo-600 transition-colors flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Volver al Archivo de Auditorías</span>
            </a>
            <span>/</span>
            <span class="text-gray-900 font-bold">Informe Oficial #AUD-{{ str_pad($auditoria->id, 4, '0', STR_PAD_LEFT) }}</span>
        </div>

        <div class="flex items-center gap-2">
            @if($auditoria->estado === 'en_progreso')
                <a href="{{ route('auditorias.create', ['ubicacion_id' => $auditoria->ubicacion_id, 'auditoria_id' => $auditoria->id]) }}" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Continuar Relevamiento</span>
                </a>
            @endif

            <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Imprimir / Exportar a PDF</span>
            </button>
        </div>
    </div>

    <!-- Hoja Oficial del Informe (Estilo Documental Formal) -->
    <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-8 sm:p-12 print:border-none print:shadow-none print:p-0">
        <!-- Encabezado Institucional -->
        <div class="border-b-2 border-gray-900 pb-6 flex flex-col sm:flex-row justify-between items-start gap-4">
            <div>
                <div class="text-[10px] uppercase tracking-widest font-black text-gray-500">
                    SISTEMA DE GESTIÓN PATRIMONIAL Y CONTROL DE INVENTARIO
                </div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight mt-0.5">
                    LABORATORIO DE INFORMÁTICA APLICADA (LIA)
                </h1>
                <div class="text-xs font-bold text-indigo-700 uppercase tracking-wider mt-1">
                    Acta e Informe Oficial de Auditoría Física de Ubicación
                </div>
            </div>
            <div class="text-left sm:text-right">
                <div class="inline-block px-3 py-1 bg-gray-900 text-white font-mono font-black text-sm rounded-lg">
                    #AUD-{{ str_pad($auditoria->id, 4, '0', STR_PAD_LEFT) }}
                </div>
                <div class="text-[11px] text-gray-500 mt-1 font-medium">
                    Emisión: {{ now()->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>

        <!-- Ficha Técnica de la Auditoría -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-6 border-b border-gray-200 text-xs">
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sala / Laboratorio</span>
                <span class="font-extrabold text-gray-900 text-sm">{{ $auditoria->ubicacion?->nombre ?? 'Sin Ubicación' }}</span>
                <span class="block text-[11px] text-gray-500">{{ $auditoria->ubicacion?->descripcion ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Auditor Actuante</span>
                <span class="font-bold text-gray-900">{{ $auditoria->usuario?->nombre ?? 'Usuario LIA' }}</span>
                <span class="block text-[11px] text-gray-500">{{ $auditoria->usuario?->email ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Fecha Relevamiento</span>
                <span class="font-bold text-gray-900">{{ $auditoria->fecha ? $auditoria->fecha->format('d/m/Y H:i') : '-' }}</span>
                <span class="block text-[11px] text-gray-500">Inspección Presencial</span>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Estado del Relevamiento</span>
                @if($auditoria->estado === 'finalizada')
                    <span class="inline-flex items-center gap-1 font-black text-emerald-800">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> FINALIZADA
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 font-black text-amber-700">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> EN PROGRESO
                    </span>
                @endif
                <span class="block text-[11px] text-gray-500">Acta cerrada</span>
            </div>
        </div>

        <!-- Resumen Ejecutivo (KPIs) -->
        <div class="my-6">
            <h2 class="text-xs font-black uppercase tracking-wider text-gray-400 mb-3">1. Resumen Ejecutivo del Relevamiento</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-3.5 bg-gray-50 rounded-2xl border border-gray-100">
                    <div class="text-[10px] font-bold text-gray-500 uppercase">Activos Relevados</div>
                    <div class="text-xl font-black text-gray-900 mt-0.5">{{ $auditoria->total_auditados }}</div>
                    <div class="text-[10px] text-gray-400">Total auditados</div>
                </div>

                <div class="p-3.5 bg-emerald-50/60 rounded-2xl border border-emerald-100">
                    <div class="text-[10px] font-bold text-emerald-700 uppercase">Conformes (OK)</div>
                    <div class="text-xl font-black text-emerald-800 mt-0.5">{{ $auditoria->total_ok }}</div>
                    <div class="text-[10px] text-emerald-600">En posición y operativos</div>
                </div>

                <div class="p-3.5 bg-rose-50/60 rounded-2xl border border-rose-100">
                    <div class="text-[10px] font-bold text-rose-700 uppercase">Inconsistencias</div>
                    <div class="text-xl font-black text-rose-800 mt-0.5">{{ $auditoria->total_inconsistencias }}</div>
                    <div class="text-[10px] text-rose-600">Roturas / Faltantes / Sobrantes</div>
                </div>

                <div class="p-3.5 bg-indigo-50/60 rounded-2xl border border-indigo-100">
                    <div class="text-[10px] font-bold text-indigo-700 uppercase">Índice Conformidad</div>
                    <div class="text-xl font-black text-indigo-800 mt-0.5">
                        {{ $auditoria->total_auditados > 0 ? round(($auditoria->total_ok / $auditoria->total_auditados) * 100, 1) : 0 }}%
                    </div>
                    <div class="text-[10px] text-indigo-600">Cumplimiento patrimonial</div>
                </div>
            </div>
        </div>

        @if($auditoria->observaciones)
            <div class="my-6 p-4 bg-gray-50 rounded-xl border border-gray-200 text-xs">
                <div class="font-bold text-gray-700 uppercase text-[10px] mb-1">Observaciones Generales de la Auditoría</div>
                <p class="text-gray-800 leading-relaxed">{{ $auditoria->observaciones }}</p>
            </div>
        @endif

        <!-- Detalle de Puestos y Elementos Verificados -->
        <div class="my-6">
            <h2 class="text-xs font-black uppercase tracking-wider text-gray-400 mb-3">2. Detalle de Puestos y Activos Auditados</h2>
            @if($auditoria->detalles->isEmpty())
                <div class="p-8 text-center border border-dashed border-gray-300 rounded-xl text-xs text-gray-500">
                    No se han registrado ítems o estaciones en esta auditoría.
                </div>
            @else
                <div class="overflow-x-auto border border-gray-200 rounded-2xl">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-100/80 text-[10px] font-black uppercase tracking-wider text-gray-600 border-b border-gray-200">
                            <tr>
                                <th class="py-2.5 px-3">Estación / Puesto</th>
                                <th class="py-2.5 px-3">Activo Relevado</th>
                                <th class="py-2.5 px-3">Serial / Patrimonial</th>
                                <th class="py-2.5 px-3 text-center">Estado</th>
                                <th class="py-2.5 px-3">Observaciones / Incidencia</th>
                                <th class="py-2.5 px-3 text-center">Ticket Derivado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @foreach($auditoria->detalles as $det)
                                <tr class="{{ $det->estado === 'inconsistencia' ? 'bg-rose-50/30' : '' }}">
                                    <td class="py-2.5 px-3 font-bold text-gray-900 whitespace-nowrap">
                                        {{ $det->puesto ?: 'General / Mesón' }}
                                    </td>
                                    <td class="py-2.5 px-3">
                                        @if($det->elemento)
                                            <div class="font-semibold text-gray-900">{{ $det->elemento->modelo ?? 'Elemento' }}</div>
                                            <div class="text-[10px] text-gray-500">{{ $det->elemento->tipo?->nombre }}</div>
                                        @else
                                            <span class="text-gray-400 italic">Puesto Global</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 font-mono text-[11px] text-gray-600 whitespace-nowrap">
                                        {{ $det->elemento?->nro_serie ?: ($det->elemento?->nro_lia ?: '-') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                        @if($det->estado === 'ok')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-100 text-emerald-800">
                                                CONFORME (OK)
                                            </span>
                                        @elseif($det->estado === 'inconsistencia')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-rose-100 text-rose-800 uppercase">
                                                {{ strtoupper($det->tipo_discrepancia ?: 'INCONSISTENCIA') }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600">
                                                {{ strtoupper($det->estado) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-gray-600 text-[11px]">
                                        {{ $det->observaciones ?: '-' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                        @if($det->ticket)
                                            <a href="{{ route('tickets.show', $det->ticket->id) }}" class="inline-flex items-center gap-1 font-mono font-bold text-rose-700 hover:underline">
                                                <span>#{{ $det->ticket->id }}</span>
                                                <span class="text-[9px] uppercase px-1.5 py-0.2 bg-rose-100 text-rose-800 rounded font-sans">
                                                    {{ $det->ticket->estado_ticket }}
                                                </span>
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-[10px]">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Secciones de Firmas y Responsabilidad Legal (Ideal para Print) -->
        <div class="mt-12 pt-8 border-t-2 border-gray-300">
            <h2 class="text-xs font-black uppercase tracking-wider text-gray-400 mb-8">3. Certificación y Conformidad de Relevamiento</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-12 text-center text-xs">
                <div class="space-y-1">
                    <div class="border-b border-gray-400 w-3/4 mx-auto mb-2 h-16 flex items-end justify-center">
                        <!-- Línea de Firma -->
                    </div>
                    <div class="font-bold text-gray-900">{{ $auditoria->usuario?->nombre ?? 'Auditor Responsable' }}</div>
                    <div class="text-[11px] text-gray-500">Auditor Relevador LIA</div>
                    <div class="text-[10px] text-gray-400">DNI / Legajo: ___________________</div>
                </div>

                <div class="space-y-1">
                    <div class="border-b border-gray-400 w-3/4 mx-auto mb-2 h-16 flex items-end justify-center">
                        <!-- Línea de Firma -->
                    </div>
                    <div class="font-bold text-gray-900">Dirección / Responsable de Laboratorios</div>
                    <div class="text-[11px] text-gray-500">Laboratorio de Informática Aplicada (LIA)</div>
                    <div class="text-[10px] text-gray-400">Firma y Sello Institucional</div>
                </div>
            </div>

            <div class="mt-8 pt-4 border-t border-gray-100 text-center text-[10px] text-gray-400">
                Documento generado automáticamente por Nexo LIA. Toda modificación no autorizada anula la validez del presente informe.
            </div>
        </div>
    </div>
</div>
@endsection
