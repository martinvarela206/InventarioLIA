@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Dashboard</span>
            </h1>
        </div>
        <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full sm:w-auto">
            <a href="{{ route('tickets.create') }}" class="inline-flex items-center gap-2 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold px-4 py-2.5 rounded-xl shadow-xs transition-all text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                <span>Iniciar Ticket</span>
            </a>
            @if(Auth::user()->canManageMovimientos())
                <a href="{{ route('movimientos.rapido') }}" class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 border border-slate-300 text-slate-800 font-bold px-4 py-2.5 rounded-xl shadow-xs transition-all text-sm">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Nuevo Movimiento</span>
                </a>
            @endif
            <a href="{{ route('reportes.index') }}" class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 border border-slate-300 text-slate-800 font-bold px-4 py-2.5 rounded-xl shadow-xs transition-all text-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Reportes</span>
            </a>
        </div>
    </div>

    @if(Auth::user()->hasRole('user_admin'))
        @include('users.widget-huella-alerta')
    @endif

    <!-- 1. 5 KPI CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
        <!-- Total Activos -->
        <a href="{{ route('reportes.show', 'inventario-completo') }}" title="Ver reporte digital de todos los activos" class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm relative overflow-hidden block hover:-translate-y-1 hover:shadow-md hover:border-gray-400 transition-all group cursor-pointer">
            <div class="absolute right-3 top-3 text-gray-100 group-hover:text-amber-100 transition-colors -z-0">
                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div class="flex items-center justify-between relative z-10">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Total Activos</span>
                <span class="text-xs font-semibold text-gray-400 group-hover:text-amber-600 transition-colors flex items-center gap-0.5">Reporte <span class="group-hover:translate-x-0.5 transition-transform">→</span></span>
            </div>
            <div class="text-3xl font-black text-gray-900 mt-2 relative z-10">{{ number_format($totalElementos) }}</div>
            <span class="text-xs text-gray-400 mt-1 block relative z-10">Relevamiento ODS</span>
        </a>

        <!-- Operativos -->
        <a href="{{ route('reportes.show', 'operativos') }}" title="Ver reporte digital de activos operativos en salas" class="bg-white rounded-2xl p-5 border border-emerald-200/80 shadow-sm relative overflow-hidden bg-gradient-to-b from-white to-emerald-50/20 block hover:-translate-y-1 hover:shadow-md hover:border-emerald-400 transition-all group cursor-pointer">
            <div class="absolute right-3 top-3 text-emerald-100/60 group-hover:text-emerald-200/60 transition-colors -z-0">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="flex items-center justify-between relative z-10">
                <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider block">Operativos en Salas</span>
                <span class="text-xs font-semibold text-emerald-600 group-hover:text-emerald-800 transition-colors flex items-center gap-0.5">Reporte <span class="group-hover:translate-x-0.5 transition-transform">→</span></span>
            </div>
            <div class="text-3xl font-black text-emerald-700 mt-2 relative z-10">{{ number_format($totalOperativos) }}</div>
            <span class="text-xs text-emerald-600 mt-1 block relative z-10">En uso y disponibles</span>
        </a>

        <!-- Prestados Activos -->
        <a href="{{ route('reportes.show', 'prestados') }}" title="Ver reporte digital de equipos prestados a cátedras" class="bg-white rounded-2xl p-5 border border-amber-300 shadow-sm relative overflow-hidden bg-gradient-to-b from-white to-amber-50/40 block hover:-translate-y-1 hover:shadow-md hover:border-amber-400 transition-all group cursor-pointer">
            <div class="absolute right-3 top-3 text-amber-100/80 group-hover:text-amber-200/80 transition-colors -z-0">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </div>
            <div class="flex items-center justify-between relative z-10">
                <span class="text-xs font-bold text-amber-900 uppercase tracking-wider block">Prestados a Cátedras</span>
                <span class="text-xs font-semibold text-amber-700 group-hover:text-amber-900 transition-colors flex items-center gap-0.5">Reporte <span class="group-hover:translate-x-0.5 transition-transform">→</span></span>
            </div>
            <div class="text-3xl font-black text-amber-600 mt-2 relative z-10">{{ number_format($totalPrestados) }}</div>
            <span class="text-xs text-amber-700 mt-1 block relative z-10">Fuera del laboratorio</span>
        </a>

        <!-- En Reparación / Tickets -->
        <a href="{{ Auth::user()->hasRole('tec') ? route('tickets.index') : route('reportes.show', 'historial-reparaciones') }}" title="Ver tickets y reparaciones técnicas" class="bg-white rounded-2xl p-5 border border-indigo-200 shadow-sm relative overflow-hidden block hover:-translate-y-1 hover:shadow-md hover:border-indigo-400 transition-all group cursor-pointer">
            <div class="absolute right-3 top-3 text-indigo-100 group-hover:text-indigo-200 transition-colors -z-0">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
            <div class="flex items-center justify-between relative z-10">
                <span class="text-xs font-bold text-indigo-900 uppercase tracking-wider block">En Reparación</span>
                <span class="text-xs font-semibold text-indigo-600 group-hover:text-indigo-900 transition-colors flex items-center gap-0.5">Tickets <span class="group-hover:translate-x-0.5 transition-transform">→</span></span>
            </div>
            <div class="text-3xl font-black text-indigo-700 mt-2 relative z-10">{{ number_format($totalEnReparacion) }}</div>
            <span class="text-xs text-indigo-600 mt-1 block relative z-10">Taller y diagnóstico</span>
        </a>

        <!-- Obsoletos / Bajas -->
        <a href="{{ route('reportes.show', 'bajas-basura') }}" title="Ver reporte digital de bajas y basura tecnológica" class="bg-white rounded-2xl p-5 border border-rose-200 shadow-sm relative overflow-hidden block hover:-translate-y-1 hover:shadow-md hover:border-rose-400 transition-all group cursor-pointer">
            <div class="absolute right-3 top-3 text-rose-100/70 group-hover:text-rose-200/70 transition-colors -z-0">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <div class="flex items-center justify-between relative z-10">
                <span class="text-xs font-bold text-rose-800 uppercase tracking-wider block">Bajas y Basura Tec.</span>
                <span class="text-xs font-semibold text-rose-600 group-hover:text-rose-800 transition-colors flex items-center gap-0.5">Reporte <span class="group-hover:translate-x-0.5 transition-transform">→</span></span>
            </div>
            <div class="text-3xl font-black text-rose-700 mt-2 relative z-10">{{ number_format($totalObsoletos) }}</div>
            <span class="text-xs text-rose-600 mt-1 block relative z-10">Descarte institucional</span>
        </a>
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
                                    @if(Auth::user()->canManageMovimientos())
                                        <a href="{{ route('movimientos.rapido', ['elemento_id' => $p->elemento_id]) }}" class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-1.5 px-3 rounded-lg shadow-2xs transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Registrar Devolución
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400 font-medium italic">En préstamo activo</span>
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
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <div>
                <h2 class="font-bold text-gray-900 text-lg">Distribución de Activos por Salas Físicas</h2>
                <p class="text-xs text-gray-500 mt-0.5">Mapa y ocupación de los {{ count($distribucionSalas) }} espacios físicos del LIA</p>
            </div>
            <div class="flex items-center gap-3">
                @if(Auth::user()->hasRole('item_admin'))
                    <a href="{{ route('ubicaciones.index') }}" class="text-xs bg-[#fbc101] hover:bg-amber-400 font-extrabold px-3 py-1.5 rounded-lg text-black transition-colors flex items-center gap-1 shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        Gestionar Salas
                    </a>
                @endif
                <a href="{{ route('reportes.index') }}" class="text-xs text-amber-800 font-semibold hover:underline">Modo Auditoría →</a>
            </div>
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
    <div class="w-full">
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
    </div>
</div>
@endsection
