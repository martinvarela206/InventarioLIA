@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Centro de Control Operativo LIA</h1>
            <p class="text-sm text-gray-600 mt-1">Métricas en tiempo real, trazabilidad de salas y préstamos activos</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('movimientos.rapido') }}" class="inline-flex items-center gap-2 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold px-4 py-2.5 rounded-xl shadow-sm transition-all text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Nuevo Movimiento / Préstamo
            </a>
            <a href="{{ route('reportes.index') }}" class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-4 py-2.5 rounded-xl shadow-sm transition-all text-sm">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Hub de Reportes
            </a>
        </div>
    </div>

    <!-- 1. 5 KPI CARDS -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <!-- Total Activos -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm relative overflow-hidden">
            <div class="absolute right-3 top-3 text-gray-100 -z-0">
                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block relative z-10">Total Activos</span>
            <div class="text-3xl font-black text-gray-900 mt-2 relative z-10">{{ number_format($totalElementos) }}</div>
            <span class="text-xs text-gray-400 mt-1 block relative z-10">Relevamiento ODS</span>
        </div>

        <!-- Operativos -->
        <div class="bg-white rounded-2xl p-5 border border-emerald-200/80 shadow-sm relative overflow-hidden bg-gradient-to-b from-white to-emerald-50/20">
            <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider block">Operativos en Salas</span>
            <div class="text-3xl font-black text-emerald-700 mt-2">{{ number_format($totalOperativos) }}</div>
            <span class="text-xs text-emerald-600 mt-1 block">En uso y disponibles</span>
        </div>

        <!-- Prestados Activos -->
        <div class="bg-white rounded-2xl p-5 border border-amber-300 shadow-sm relative overflow-hidden bg-gradient-to-b from-white to-amber-50/40">
            <span class="text-xs font-bold text-amber-900 uppercase tracking-wider block">Prestados a Cátedras</span>
            <div class="text-3xl font-black text-amber-600 mt-2">{{ number_format($totalPrestados) }}</div>
            <span class="text-xs text-amber-700 mt-1 block">Fuera del laboratorio</span>
        </div>

        <!-- En Reparación -->
        <div class="bg-white rounded-2xl p-5 border border-sky-200 shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold text-sky-800 uppercase tracking-wider block">En Verificación / Taller</span>
            <div class="text-3xl font-black text-sky-700 mt-2">{{ number_format($totalEnReparacion) }}</div>
            <span class="text-xs text-sky-600 mt-1 block">Intervención técnica</span>
        </div>

        <!-- Obsoletos / Bajas -->
        <div class="bg-white rounded-2xl p-5 border border-rose-200 shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold text-rose-800 uppercase tracking-wider block">Bajas y Basura Tec.</span>
            <div class="text-3xl font-black text-rose-700 mt-2">{{ number_format($totalObsoletos) }}</div>
            <span class="text-xs text-rose-600 mt-1 block">Descarte institucional</span>
        </div>
    </div>

    <!-- 2. WIDGET DE PRÉSTAMOS ACTIVOS CON DEVOLUCIÓN DIRECTA -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-amber-50/50">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-amber-500 animate-pulse"></span>
                <h2 class="font-bold text-gray-900 text-lg">Préstamos Externos Activos</h2>
                <span class="text-xs bg-amber-200 text-amber-900 font-bold px-2 py-0.5 rounded-full">
                    {{ $prestamosActivos->count() }} equipos fuera
                </span>
            </div>
            <a href="{{ route('reportes.show', 'concepto-fechas') }}" class="text-xs text-amber-800 hover:text-black font-semibold">
                Ver historial de préstamos →
            </a>
        </div>

        @if($prestamosActivos->isEmpty())
            <div class="py-12 text-center text-gray-400 text-sm">
                No hay equipos en préstamo externo en este momento. Todos los activos se encuentran dentro de las salas del LIA.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                            <th class="py-3 px-4">Activo</th>
                            <th class="py-3 px-4">Dispositivo</th>
                            <th class="py-3 px-4">Docente / Cátedra Solicitante</th>
                            <th class="py-3 px-4">Fecha Salida</th>
                            <th class="py-3 px-4">Tiempo Prestado</th>
                            <th class="py-3 px-4 text-right">Acción Rápida</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @foreach($prestamosActivos as $p)
                            @php
                                $dias = $p->diasPrestado();
                                $alerta = $dias > 30;
                            @endphp
                            <tr class="hover:bg-amber-50/40 transition-colors">
                                <td class="py-3 px-4 whitespace-nowrap font-mono font-bold">
                                    <a href="{{ route('elementos.show', $p->elemento_id) }}" class="text-amber-800 hover:underline">
                                        {{ $p->elemento?->nro_lia ?: 'LIA: -' }}
                                    </a>
                                    <span class="text-xs text-gray-400 block">UNSJ: {{ $p->elemento?->nro_unsj ?: '-' }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-gray-900">{{ strtoupper($p->elemento?->tipo?->nombre ?? '') }}</span>
                                    <span class="text-xs text-gray-500 block">{{ $p->elemento?->marca }} {{ $p->elemento?->modelo }}</span>
                                </td>
                                <td class="py-3 px-4 font-semibold text-gray-900">
                                    {{ $p->solicitante ?: 'No especificado' }}
                                </td>
                                <td class="py-3 px-4 text-xs whitespace-nowrap">
                                    {{ $p->fecha_prestamo ? $p->fecha_prestamo->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $alerta ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $dias }} días
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <a href="{{ route('movimientos.rapido', ['elemento_id' => $p->elemento_id]) }}" class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-1.5 px-3 rounded-lg shadow-2xs transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Registrar Devolución
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- 3. DISTRIBUCIÓN POR LABORATORIOS Y SALAS -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="font-bold text-gray-900 text-lg">Distribución de Activos por Salas Físicas</h2>
                <p class="text-xs text-gray-500 mt-0.5">Mapa y ocupación de los 12 espacios físicos del LIA</p>
            </div>
            <a href="{{ route('reportes.index') }}" class="text-xs text-amber-800 font-semibold hover:underline">Modo Auditoría →</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($distribucionSalas as $sala)
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200/80 hover:border-amber-400 transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-gray-900 text-sm">{{ $sala['nombre'] }}</h3>
                            <span class="text-xs font-extrabold bg-white border border-gray-200 px-2 py-0.5 rounded text-amber-900">
                                {{ $sala['total_activos'] }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 line-clamp-1">{{ $sala['descripcion'] }}</p>
                    </div>
                    <div class="mt-4 pt-2 border-t border-gray-200/60 flex items-center justify-between text-xs">
                        <a href="{{ route('elementos.index', ['ubicacion' => $sala['id']]) }}" class="text-gray-600 hover:text-black font-semibold">Ver equipos</a>
                        <a href="{{ route('reportes.auditoria', $sala['id']) }}" class="text-amber-700 hover:text-amber-900 font-bold flex items-center gap-0.5" title="Imprimir auditoría en A4">
                            <span>Auditar A4</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 4. ALERTAS DE EQUIPOS CRÍTICOS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Garantías Vencidas o por Vencer -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-bold text-gray-900 text-base flex items-center gap-2 mb-3">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Alertas de Garantías Vencidas
            </h3>
            @if($alertasGarantiaVencida->isEmpty())
                <p class="text-xs text-gray-400 italic py-4">No se registran equipos con garantías vencidas pendientes.</p>
            @else
                <div class="divide-y divide-gray-100 text-xs">
                    @foreach($alertasGarantiaVencida as $alerta)
                        <div class="py-2.5 flex items-center justify-between">
                            <div>
                                <a href="{{ route('elementos.show', $alerta->id) }}" class="font-bold text-gray-900 hover:underline">
                                    {{ $alerta->presenter()->identificadorCompleto() }}
                                </a>
                                <span class="text-gray-500 block">{{ $alerta->marca }} {{ $alerta->modelo }}</span>
                            </div>
                            <div class="text-right">
                                {!! $alerta->presenter()->garantiaBadge() !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Equipos que Requieren Verificación -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-bold text-gray-900 text-base flex items-center gap-2 mb-3">
                <svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Equipos en Estado "Verificar Funcionamiento"
            </h3>
            @if($alertasVerificar->isEmpty())
                <p class="text-xs text-gray-400 italic py-4">No hay equipos reportados pendientes de verificación técnica.</p>
            @else
                <div class="divide-y divide-gray-100 text-xs">
                    @foreach($alertasVerificar as $itemVerif)
                        <div class="py-2.5 flex items-center justify-between">
                            <div>
                                <a href="{{ route('elementos.show', $itemVerif->id) }}" class="font-bold text-gray-900 hover:underline">
                                    {{ $itemVerif->presenter()->identificadorCompleto() }}
                                </a>
                                <span class="text-gray-500 block">{{ $itemVerif->tipo?->nombre }} - {{ $itemVerif->marca }} {{ $itemVerif->modelo }}</span>
                            </div>
                            <a href="{{ route('movimientos.rapido', ['elemento_id' => $itemVerif->id]) }}" class="px-2.5 py-1 bg-sky-100 text-sky-800 font-bold rounded hover:bg-sky-200">
                                Diagnosticar
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
