@extends('layouts.app')

@section('content')
<style>
@media print {
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

<div class="max-w-[1000px] mx-auto px-4 py-6 print-container space-y-6">
    <!-- Barra Superior de Navegación y Acciones (no-print) -->
    <div class="no-print flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-figma-border-subtle pb-4">
        <div class="flex items-center gap-2 text-xs font-medium text-figma-text-tertiary uppercase tracking-wider">
            <a href="{{ route('auditorias.index') }}" class="hover:text-figma-text-primary transition-colors flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Volver al Archivo</span>
            </a>
            <span>/</span>
            <span class="text-figma-text-primary font-mono font-bold">#AUD-{{ str_pad($auditoria->id, 4, '0', STR_PAD_LEFT) }}</span>
        </div>

        <div class="flex items-center gap-2">
            @if($auditoria->estado === 'en_progreso')
                <a href="{{ route('auditorias.create', ['ubicacion_id' => $auditoria->ubicacion_id, 'auditoria_id' => $auditoria->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500/20 text-amber-400 border border-amber-500/30 hover:bg-amber-500 hover:text-black rounded-lg text-xs font-semibold transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Continuar Relevamiento</span>
                </a>
            @endif

            <button onclick="window.print()" class="figma-btn-primary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Imprimir / PDF</span>
            </button>
        </div>
    </div>

    <!-- Hoja Oficial del Informe (Estilo Documental Formal en Figma Studio) -->
    <div class="figma-panel p-6 sm:p-10 print:border-none print:shadow-none print:p-0 space-y-6">
        <!-- Encabezado Institucional -->
        <div class="border-b border-figma-border-subtle pb-6 flex flex-col sm:flex-row justify-between items-start gap-4">
            <div>
                <div class="text-[10px] uppercase tracking-widest font-mono font-bold text-figma-text-tertiary">
                    SISTEMA DE GESTIÓN PATRIMONIAL Y CONTROL DE INVENTARIO
                </div>
                <h1 class="text-xl font-bold text-figma-text-primary tracking-tight mt-0.5">
                    LABORATORIO DE INFORMÁTICA APLICADA (LIA)
                </h1>
                <div class="text-xs font-semibold text-figma-brand uppercase tracking-wider mt-1">
                    Acta e Informe Oficial de Auditoría Física de Ubicación
                </div>
            </div>
            <div class="text-left sm:text-right">
                <div class="inline-block px-3 py-1 bg-figma-bg-deep border border-figma-border-subtle text-figma-text-primary font-mono font-bold text-xs rounded-lg">
                    #AUD-{{ str_pad($auditoria->id, 4, '0', STR_PAD_LEFT) }}
                </div>
                <div class="text-[11px] text-figma-text-tertiary mt-1 font-mono">
                    Emisión: {{ now()->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>

        <!-- Ficha Técnica de la Auditoría -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-4 border-b border-figma-border-subtle text-xs">
            <div>
                <span class="block text-[10px] font-bold text-figma-text-tertiary uppercase tracking-wider">Sala / Laboratorio</span>
                <span class="font-bold text-figma-text-primary text-xs">{{ $auditoria->ubicacion?->nombre ?? 'Sin Ubicación' }}</span>
                <span class="block text-[11px] text-figma-text-tertiary">{{ $auditoria->ubicacion?->descripcion ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-figma-text-tertiary uppercase tracking-wider">Auditor Actuante</span>
                <span class="font-semibold text-figma-text-primary">{{ $auditoria->usuario?->nombre ?? 'Usuario LIA' }}</span>
                <span class="block text-[11px] text-figma-text-tertiary font-mono">{{ $auditoria->usuario?->email ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-figma-text-tertiary uppercase tracking-wider">Fecha Relevamiento</span>
                <span class="font-mono text-figma-text-primary">{{ $auditoria->fecha ? $auditoria->fecha->format('d/m/Y H:i') : '-' }}</span>
                <span class="block text-[11px] text-figma-text-tertiary">Inspección Presencial</span>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-figma-text-tertiary uppercase tracking-wider">Estado</span>
                @if($auditoria->estado === 'finalizada')
                    <span class="inline-flex items-center gap-1 font-mono text-[11px] font-bold text-emerald-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> FINALIZADA
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 font-mono text-[11px] font-bold text-amber-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> EN PROGRESO
                    </span>
                @endif
                <span class="block text-[11px] text-figma-text-tertiary">Acta registrada</span>
            </div>
        </div>

        <!-- Resumen Ejecutivo (KPIs) -->
        <div class="space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-figma-text-tertiary">1. Resumen Ejecutivo del Relevamiento</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-3 figma-card space-y-0.5">
                    <div class="text-[10px] font-bold text-figma-text-tertiary uppercase">Activos Relevados</div>
                    <div class="text-lg font-mono font-bold text-figma-text-primary">{{ $auditoria->total_auditados }}</div>
                    <div class="text-[10px] text-figma-text-tertiary">Total auditados</div>
                </div>

                <div class="p-3 figma-card border-emerald-500/30 bg-emerald-500/5 space-y-0.5">
                    <div class="text-[10px] font-bold text-emerald-400 uppercase">Conformes (OK)</div>
                    <div class="text-lg font-mono font-bold text-emerald-400">{{ $auditoria->total_ok }}</div>
                    <div class="text-[10px] text-emerald-400/80">En posición y operativos</div>
                </div>

                <div class="p-3 figma-card border-rose-500/30 bg-rose-500/5 space-y-0.5">
                    <div class="text-[10px] font-bold text-rose-400 uppercase">Inconsistencias</div>
                    <div class="text-lg font-mono font-bold text-rose-400">{{ $auditoria->total_inconsistencias }}</div>
                    <div class="text-[10px] text-rose-400/80">Roturas / Faltantes</div>
                </div>

                <div class="p-3 figma-card border-figma-brand/30 bg-figma-brand/5 space-y-0.5">
                    <div class="text-[10px] font-bold text-figma-brand uppercase">Conformidad</div>
                    <div class="text-lg font-mono font-bold text-figma-brand">
                        {{ $auditoria->total_auditados > 0 ? round(($auditoria->total_ok / $auditoria->total_auditados) * 100, 1) : 0 }}%
                    </div>
                    <div class="text-[10px] text-figma-brand/80">Cumplimiento patrimonial</div>
                </div>
            </div>
        </div>

        @if($auditoria->observaciones)
            <div class="p-3.5 figma-card text-xs space-y-1">
                <div class="font-bold text-figma-text-tertiary uppercase text-[10px]">Observaciones Generales de la Auditoría</div>
                <p class="text-figma-text-secondary leading-relaxed">{{ $auditoria->observaciones }}</p>
            </div>
        @endif

        <!-- Detalle de Puestos y Elementos Verificados -->
        <div class="space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-figma-text-tertiary">2. Detalle de Puestos y Activos Auditados</h2>
            @if($auditoria->detalles->isEmpty())
                <div class="p-8 text-center figma-card border-dashed text-xs text-figma-text-tertiary">
                    No se han registrado ítems o estaciones en esta auditoría.
                </div>
            @else
                <div class="overflow-x-auto figma-panel border border-figma-border-subtle">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-figma-bg-deep text-[10px] font-bold uppercase tracking-wider text-figma-text-secondary border-b border-figma-border-subtle">
                            <tr>
                                <th class="py-2.5 px-3">Estación / Puesto</th>
                                <th class="py-2.5 px-3">Activo Relevado</th>
                                <th class="py-2.5 px-3">Serial / Patrimonial</th>
                                <th class="py-2.5 px-3 text-center">Estado</th>
                                <th class="py-2.5 px-3">Observaciones / Incidencia</th>
                                <th class="py-2.5 px-3 text-center">Ticket Derivado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-figma-border-subtle">
                            @foreach($auditoria->detalles as $det)
                                <tr class="hover:bg-figma-bg-deep/40 transition-colors {{ $det->estado === 'inconsistencia' ? 'bg-rose-500/5' : '' }}">
                                    <td class="py-2.5 px-3 font-semibold text-figma-text-primary whitespace-nowrap">
                                        {{ $det->puesto ?: 'General / Mesón' }}
                                    </td>
                                    <td class="py-2.5 px-3">
                                        @if($det->elemento)
                                            <div class="font-medium text-figma-text-primary">{{ $det->elemento->modelo ?? 'Elemento' }}</div>
                                            <div class="text-[10px] text-figma-text-tertiary">{{ $det->elemento->tipo?->nombre }}</div>
                                        @else
                                            <span class="text-figma-text-tertiary italic">Puesto Global</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 font-mono text-[11px] text-figma-text-secondary whitespace-nowrap">
                                        {{ $det->elemento?->nro_serie ?: ($det->elemento?->nro_lia ?: '-') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                        @if($det->estado === 'ok')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                                CONFORME
                                            </span>
                                        @elseif($det->estado === 'inconsistencia')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30 uppercase">
                                                {{ strtoupper($det->tipo_discrepancia ?: 'INCONSISTENCIA') }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-figma-bg-deep text-figma-text-tertiary border border-figma-border-subtle">
                                                {{ strtoupper($det->estado) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-figma-text-secondary text-[11px]">
                                        {{ $det->observaciones ?: '-' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                        @if($det->ticket)
                                            <a href="{{ route('tickets.show', $det->ticket->id) }}" class="inline-flex items-center gap-1 font-mono font-bold text-rose-400 hover:underline">
                                                <span>#{{ $det->ticket->id }}</span>
                                                <span class="text-[9px] uppercase px-1.5 py-0.2 bg-rose-500/15 text-rose-400 rounded">
                                                    {{ $det->ticket->estado_ticket }}
                                                </span>
                                            </a>
                                        @else
                                            <span class="text-figma-text-tertiary text-[10px] font-mono">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Secciones de Firmas y Responsabilidad Legal -->
        <div class="mt-8 pt-6 border-t border-figma-border-subtle">
            <h2 class="text-xs font-bold uppercase tracking-wider text-figma-text-tertiary mb-6">3. Certificación y Conformidad de Relevamiento</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 text-center text-xs">
                <div class="space-y-1">
                    <div class="border-b border-figma-border-subtle w-3/4 mx-auto mb-2 h-12 flex items-end justify-center">
                    </div>
                    <div class="font-bold text-figma-text-primary">{{ $auditoria->usuario?->nombre ?? 'Auditor Responsable' }}</div>
                    <div class="text-[11px] text-figma-text-tertiary">Auditor Relevador LIA</div>
                    <div class="text-[10px] text-figma-text-tertiary font-mono">DNI / Legajo: ___________________</div>
                </div>

                <div class="space-y-1">
                    <div class="border-b border-figma-border-subtle w-3/4 mx-auto mb-2 h-12 flex items-end justify-center">
                    </div>
                    <div class="font-bold text-figma-text-primary">Dirección / Responsable de Laboratorios</div>
                    <div class="text-[11px] text-figma-text-tertiary">Laboratorio de Informática Aplicada (LIA)</div>
                    <div class="text-[10px] text-figma-text-tertiary">Firma y Sello Institucional</div>
                </div>
            </div>

            <div class="mt-6 pt-3 border-t border-figma-border-subtle text-center text-[10px] text-figma-text-tertiary">
                Documento emitido por Nexo LIA. Toda modificación no autorizada anula la validez del presente informe.
            </div>
        </div>
    </div>
</div>
@endsection
