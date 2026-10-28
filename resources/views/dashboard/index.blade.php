@extends('layouts.app')

@section('content')
<div class="max-w-[1320px] mx-auto px-2 space-y-6">
    <!-- Header Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wider text-figma-text-tertiary mb-0.5">
                <span>Espacio de Trabajo</span>
                <span>/</span>
                <span class="text-figma-text-primary">Visión General</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary tracking-tight flex items-center gap-2">
                <svg class="w-5 h-5 text-figma-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span>Dashboard de Control LIA</span>
            </h1>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('tickets.create') }}" class="figma-btn-primary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Iniciar Ticket</span>
            </a>
            @if(Auth::user()->canManageMovimientos())
                <a href="{{ route('movimientos.rapido') }}" class="figma-btn-secondary">
                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Nuevo Movimiento</span>
                </a>
            @endif
            <a href="{{ route('reportes.index') }}" class="figma-btn-secondary">
                <svg class="w-3.5 h-3.5 text-figma-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Reportes</span>
            </a>
        </div>
    </div>

    @if(Auth::user()->hasRole('user_admin'))
        @include('users.widget-huella-alerta')
    @endif

    <!-- 1. 5 KPI METRIC CARDS (Figma Studio Artboard Style) -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
        <!-- Total Activos -->
        <a href="{{ route('reportes.show', 'inventario-completo') }}" title="Ver inventario completo" class="figma-card p-4 block group cursor-pointer hover:-translate-y-0.5">
            <div class="flex items-center justify-between text-figma-text-secondary mb-2">
                <span class="text-[11px] font-semibold uppercase tracking-wider">Total Activos</span>
                <span class="w-2 h-2 rounded-full bg-figma-brand"></span>
            </div>
            <div class="text-2xl font-bold font-mono text-figma-text-primary group-hover:text-figma-brand transition-colors">
                {{ number_format($totalElementos) }}
            </div>
            <div class="flex items-center justify-between mt-2 pt-2 border-t border-figma-border-subtle text-[11px] text-figma-text-tertiary">
                <span>Relevamiento ODS</span>
                <span class="group-hover:translate-x-0.5 transition-transform text-figma-brand">→</span>
            </div>
        </a>

        <!-- Operativos -->
        <a href="{{ route('reportes.show', 'operativos') }}" title="Ver activos operativos" class="figma-card p-4 block group cursor-pointer hover:-translate-y-0.5">
            <div class="flex items-center justify-between text-figma-text-secondary mb-2">
                <span class="text-[11px] font-semibold uppercase tracking-wider">Operativos</span>
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            </div>
            <div class="text-2xl font-bold font-mono text-emerald-500">
                {{ number_format($totalOperativos) }}
            </div>
            <div class="flex items-center justify-between mt-2 pt-2 border-t border-figma-border-subtle text-[11px] text-figma-text-tertiary">
                <span>En salas LIA</span>
                <span class="group-hover:translate-x-0.5 transition-transform text-emerald-500">→</span>
            </div>
        </a>

        <!-- Prestados Activos -->
        <a href="{{ route('reportes.show', 'prestados') }}" title="Ver activos prestados" class="figma-card p-4 block group cursor-pointer hover:-translate-y-0.5">
            <div class="flex items-center justify-between text-figma-text-secondary mb-2">
                <span class="text-[11px] font-semibold uppercase tracking-wider">Prestados</span>
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            </div>
            <div class="text-2xl font-bold font-mono text-amber-500">
                {{ number_format($totalPrestados) }}
            </div>
            <div class="flex items-center justify-between mt-2 pt-2 border-t border-figma-border-subtle text-[11px] text-figma-text-tertiary">
                <span>Fuera de salas</span>
                <span class="group-hover:translate-x-0.5 transition-transform text-amber-500">→</span>
            </div>
        </a>

        <!-- En Reparación -->
        <a href="{{ Auth::user()->hasRole('tec') ? route('tickets.index') : route('reportes.show', 'historial-reparaciones') }}" title="Ver tickets técnicos" class="figma-card p-4 block group cursor-pointer hover:-translate-y-0.5">
            <div class="flex items-center justify-between text-figma-text-secondary mb-2">
                <span class="text-[11px] font-semibold uppercase tracking-wider">En Reparación</span>
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
            </div>
            <div class="text-2xl font-bold font-mono text-indigo-400">
                {{ number_format($totalEnReparacion) }}
            </div>
            <div class="flex items-center justify-between mt-2 pt-2 border-t border-figma-border-subtle text-[11px] text-figma-text-tertiary">
                <span>Taller técnico</span>
                <span class="group-hover:translate-x-0.5 transition-transform text-indigo-400">→</span>
            </div>
        </a>

        <!-- Bajas / Obsoletos -->
        <a href="{{ route('reportes.show', 'bajas-basura') }}" title="Ver bajas institucionales" class="figma-card p-4 block group cursor-pointer hover:-translate-y-0.5">
            <div class="flex items-center justify-between text-figma-text-secondary mb-2">
                <span class="text-[11px] font-semibold uppercase tracking-wider">Bajas / Descarte</span>
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
            </div>
            <div class="text-2xl font-bold font-mono text-rose-500">
                {{ number_format($totalObsoletos) }}
            </div>
            <div class="flex items-center justify-between mt-2 pt-2 border-t border-figma-border-subtle text-[11px] text-figma-text-tertiary">
                <span>Descarte ODS</span>
                <span class="group-hover:translate-x-0.5 transition-transform text-rose-500">→</span>
            </div>
        </a>
    </div>

    <!-- 2. WIDGET DE PRÉSTAMOS ACTIVOS CON DEVOLUCIÓN DIRECTA -->
    <div class="figma-panel overflow-hidden">
        <div class="p-3.5 border-b border-figma-border-subtle flex items-center justify-between bg-figma-card/50">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                <h2 class="font-bold text-figma-text-primary text-sm">Préstamos Externos Activos</h2>
                <span class="font-mono text-[11px] bg-amber-500/10 text-amber-500 border border-amber-500/20 font-bold px-2 py-0.5 rounded">
                    {{ $prestamosActivos->count() }} equipos fuera
                </span>
            </div>
            <a href="{{ route('reportes.show', 'concepto-fechas') }}" class="text-xs text-figma-text-secondary hover:text-figma-brand font-medium flex items-center gap-1 transition-colors">
                <span>Ver historial</span>
                <span>→</span>
            </a>
        </div>

        @if($prestamosActivos->isEmpty())
            <div class="py-10 text-center text-figma-text-tertiary text-xs">
                No hay equipos en préstamo externo en este momento. Todos los activos se encuentran dentro de las salas del LIA.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-figma-card/80 border-b border-figma-border-subtle text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider">
                            <th class="py-2.5 px-4">Activo</th>
                            <th class="py-2.5 px-4">Dispositivo</th>
                            <th class="py-2.5 px-4">Docente / Cátedra Solicitante</th>
                            <th class="py-2.5 px-4">Fecha Salida</th>
                            <th class="py-2.5 px-4">Tiempo Prestado</th>
                            <th class="py-2.5 px-4 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-figma-border-subtle text-figma-text-secondary">
                        @foreach($prestamosActivos as $p)
                            @php
                                $dias = $p->diasPrestado();
                                $alerta = $dias > 30;
                            @endphp
                            <tr class="hover:bg-figma-hover/50 transition-colors">
                                <td class="py-2.5 px-4 whitespace-nowrap font-mono font-medium">
                                    <a href="{{ route('elementos.show', $p->elemento_id) }}" class="text-figma-brand hover:underline font-bold">
                                        {{ $p->elemento?->nro_lia ?: 'LIA: -' }}
                                    </a>
                                    <span class="text-[10px] text-figma-text-tertiary block">UNSJ: {{ $p->elemento?->nro_unsj ?: '-' }}</span>
                                </td>
                                <td class="py-2.5 px-4">
                                    <span class="font-semibold text-figma-text-primary">{{ strtoupper($p->elemento?->tipo?->nombre ?? '') }}</span>
                                    <span class="text-[11px] text-figma-text-tertiary block">{{ $p->elemento?->marca }} {{ $p->elemento?->modelo }}</span>
                                </td>
                                <td class="py-2.5 px-4 font-medium text-figma-text-primary">
                                    {{ $p->solicitante ?: 'No especificado' }}
                                </td>
                                <td class="py-2.5 px-4 font-mono text-[11px] text-figma-text-tertiary whitespace-nowrap">
                                    {{ $p->fecha_prestamo ? $p->fecha_prestamo->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-2.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded font-mono text-[10px] font-bold {{ $alerta ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-amber-500/15 text-amber-400 border border-amber-500/30' }}">
                                        {{ $dias }} días
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 text-right whitespace-nowrap">
                                    @if(Auth::user()->canManageMovimientos())
                                        <a href="{{ route('movimientos.rapido', ['elemento_id' => $p->elemento_id]) }}" class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-[11px] py-1 px-2.5 rounded shadow-xs transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Devolución
                                        </a>
                                    @else
                                        <span class="text-[11px] text-figma-text-dim italic">En préstamo activo</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- 3. DISTRIBUCIÓN POR LABORATORIOS Y SALAS -->
    <div class="figma-panel p-4 sm:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4 pb-3 border-b border-figma-border-subtle">
            <div>
                <h2 class="font-bold text-figma-text-primary text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-figma-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Distribución de Activos por Salas Físicas</span>
                </h2>
                <p class="text-[11px] text-figma-text-tertiary mt-0.5">Ocupación en tiempo real de los {{ count($distribucionSalas) }} espacios físicos del LIA</p>
            </div>
            <div class="flex items-center gap-2">
                @if(Auth::user()->hasRole('item_admin'))
                    <a href="{{ route('ubicaciones.index') }}" class="figma-btn-secondary text-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        Gestionar Salas
                    </a>
                @endif
                <a href="{{ route('reportes.index') }}" class="text-xs text-figma-text-secondary hover:text-figma-brand font-medium">Modo Auditoría →</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach($distribucionSalas as $sala)
                <div class="figma-card p-3.5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="font-bold text-figma-text-primary text-xs truncate">{{ $sala['nombre'] }}</h3>
                            <span class="font-mono text-xs font-bold px-1.5 py-0.5 rounded bg-figma-bg-deep text-figma-brand border border-figma-border-subtle">
                                {{ $sala['total_activos'] }}
                            </span>
                        </div>
                        <p class="text-[11px] text-figma-text-tertiary mt-1 line-clamp-1">{{ $sala['descripcion'] }}</p>
                    </div>
                    <div class="mt-3 pt-2 border-t border-figma-border-subtle flex items-center justify-between text-[11px]">
                        <a href="{{ route('elementos.index', ['ubicacion' => $sala['id']]) }}" class="text-figma-text-secondary hover:text-figma-text-primary font-medium">Ver activos</a>
                        <a href="{{ route('reportes.auditoria', $sala['id']) }}" class="text-figma-brand hover:underline font-semibold flex items-center gap-1" title="Imprimir auditoría en A4">
                            <span>Auditar A4</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 4. ALERTAS DE EQUIPOS CRÍTICOS -->
    <div class="w-full">
        <!-- Garantías Vencidas o por Vencer -->
        <div class="figma-panel p-4 sm:p-5">
            <h3 class="font-bold text-figma-text-primary text-sm flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Alertas de Garantías Vencidas o Críticas</span>
            </h3>
            @if($alertasGarantiaVencida->isEmpty())
                <p class="text-xs text-figma-text-tertiary italic py-3">No se registran equipos con garantías vencidas pendientes.</p>
            @else
                <div class="divide-y divide-figma-border-subtle text-xs">
                    @foreach($alertasGarantiaVencida as $alerta)
                        <div class="py-2.5 flex items-center justify-between gap-4">
                            <div>
                                <a href="{{ route('elementos.show', $alerta->id) }}" class="font-mono font-bold text-figma-brand hover:underline">
                                    {{ $alerta->presenter()->identificadorCompleto() }}
                                </a>
                                <span class="text-figma-text-tertiary block text-[11px]">{{ $alerta->marca }} {{ $alerta->modelo }}</span>
                            </div>
                            <div class="text-right">
                                {!! $alerta->presenter()->garantiaBadge() !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
