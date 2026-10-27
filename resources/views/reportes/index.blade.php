@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Header -->
    <div class="border-b border-gray-200 pb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2.5">
                <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Centro de Reportes y Auditorías — Nexo LIA</span>
            </h1>
            <p class="text-xs text-gray-500 mt-1">Todos los reportes están disponibles para visualización en pantalla, impresión formal en A4/PDF y exportación a CSV con BOM UTF-8.</p>
        </div>
        @if(auth()->check() && (auth()->user()->isAppAdmin() || auth()->user()->hasRole('app_admin')))
            <div>
                <a href="{{ route('app_admin.reportes') }}" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-3.5 py-2 rounded-xl text-xs shadow-xs transition-all">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Configurar Catálogo de Reportes</span>
                </a>
            </div>
        @endif
    </div>

    @if(isset($reportesPersonalizados) && $reportesPersonalizados->isNotEmpty())
        <!-- Sección de Reportes Personalizados y Guardados -->
        <div class="space-y-3 pt-2">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-black text-gray-900 flex items-center gap-2">
                    <span class="text-lg">⭐</span>
                    <span>Reportes Personalizados y Guardados ({{ $reportesPersonalizados->count() }})</span>
                </h2>
                <span class="text-xs text-amber-800 font-semibold bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">
                    Definidos por Administrador
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($reportesPersonalizados as $rep)
                    <div class="bg-gradient-to-br from-amber-50/40 via-white to-white rounded-2xl shadow-sm border border-amber-200 p-6 flex flex-col justify-between hover:border-amber-400 hover:shadow-md transition-all">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-900 flex items-center justify-center font-bold text-lg">
                                    @if($rep->icono == 'cubes') 📦
                                    @elseif($rep->icono == 'building') 🏢
                                    @elseif($rep->icono == 'location') 📍
                                    @elseif($rep->icono == 'wrench') 🔧
                                    @elseif($rep->icono == 'arrows-right-left') 🔄
                                    @elseif($rep->icono == 'shield') 🛡️
                                    @elseif($rep->icono == 'calendar-plus') 📅
                                    @elseif($rep->icono == 'trash') 🗑️
                                    @else 📊
                                    @endif
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300">
                                    {{ $rep->categoria ?: 'Personalizado' }}
                                </span>
                            </div>
                            <h3 class="font-bold text-gray-900 text-base">{{ $rep->titulo }}</h3>
                            <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                                {{ $rep->subtitulo ?: $rep->descripcion }}
                            </p>
                        </div>

                        <div class="mt-5 pt-4 border-t border-amber-100">
                            @if($rep->requiereUbicacion() || $rep->requierePuesto() || $rep->requiereTipo() || $rep->requiereNroLia() || $rep->requiereRangoFechas())
                                <form action="{{ route('reportes.show', $rep->slug) }}" method="GET" class="space-y-2">
                                    @if($rep->requiereUbicacion())
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Sala / Armario:</label>
                                            <select name="ubicacion_id" class="w-full text-xs px-2.5 py-1.5 border border-gray-300 rounded-xl bg-gray-50 font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400" {{ $rep->slug === 'auditoria-sala' ? 'required' : '' }}>
                                                @if($rep->slug !== 'auditoria-sala')
                                                    <option value="">-- Todas las salas y armarios --</option>
                                                @endif
                                                @foreach($ubicaciones as $ub)
                                                    <option value="{{ $ub->id }}">{{ $ub->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    @if($rep->requierePuesto())
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Puesto de Trabajo:</label>
                                            <input type="text" name="puesto" placeholder="Ej. Puesto 04" class="w-full text-xs px-2.5 py-1.5 border border-gray-300 rounded-xl bg-gray-50 font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                                        </div>
                                    @endif

                                    @if($rep->requiereTipo())
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Tipo de Activo:</label>
                                            <select name="tipo_id" class="w-full text-xs px-2.5 py-1.5 border border-gray-300 rounded-xl bg-gray-50 font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400" required>
                                                @foreach($tipos as $tp)
                                                    <option value="{{ $tp->id }}">{{ $tp->tipo }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    @if($rep->requiereNroLia())
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-600 mb-0.5">N° LIA / Activo:</label>
                                            <input type="text" name="nro_lia" placeholder="Ej. LIA-042" class="w-full text-xs px-2.5 py-1.5 border border-gray-300 rounded-xl bg-gray-50 font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400" required>
                                        </div>
                                    @endif

                                    @if($rep->requiereRangoFechas())
                                        <div class="grid grid-cols-2 gap-1.5">
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Desde:</label>
                                                <input type="date" name="fecha_desde" value="{{ \Carbon\Carbon::now()->subMonths(6)->toDateString() }}" class="w-full text-xs px-2 py-1.5 border border-gray-300 rounded-xl bg-gray-50 font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Hasta:</label>
                                                <input type="date" name="fecha_hasta" value="{{ \Carbon\Carbon::now()->toDateString() }}" class="w-full text-xs px-2 py-1.5 border border-gray-300 rounded-xl bg-gray-50 font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                                            </div>
                                        </div>
                                    @endif

                                    <div class="pt-1 flex items-center gap-2">
                                        <button type="submit" class="flex-1 text-center py-2 px-3 bg-[#fbc101] hover:bg-amber-400 font-extrabold text-black rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Ver Reporte</span>
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('reportes.show', $rep->slug) }}" class="flex-1 text-center py-2 px-3 bg-[#fbc101] hover:bg-amber-400 font-extrabold text-black rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>Ver Reporte</span>
                                    </a>
                                    <a href="{{ route('reportes.exportCsv', $rep->slug) }}" class="p-2 border border-gray-300 hover:bg-gray-50 rounded-xl text-gray-700 text-xs font-bold inline-flex items-center gap-1 shadow-xs" title="Descargar CSV">
                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        CSV
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-gray-200 my-4"></div>
    @endif

    <div class="flex items-center justify-between pt-1">
        <h2 class="text-base font-black text-gray-900 flex items-center gap-2">
            <span class="text-lg">🔒</span>
            <span>Reportes Oficiales del Sistema</span>
        </h2>
    </div>

    <!-- Reports Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- 1. Listado completo del Inventario -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 hover:shadow-md transition-all">
            <div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Listado completo del Inventario</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Catálogo exhaustivo de todos los activos del LIA (operativos, prestados y bajas).
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-2">
                <a href="{{ route('reportes.show', 'inventario-completo') }}" class="flex-1 text-center py-2 px-3 bg-[#fbc101] hover:bg-amber-400 font-extrabold text-black rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Ver Reporte</span>
                </a>
                <a href="{{ route('reportes.exportCsv', 'inventario-completo') }}" class="p-2 border border-gray-300 hover:bg-gray-50 rounded-xl text-gray-700 text-xs font-bold inline-flex items-center gap-1 shadow-xs" title="Descargar CSV">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    CSV
                </a>
            </div>
        </div>

        <!-- 2. Inventario por ubicación (Auditoría de Sala) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 hover:shadow-md transition-all">
            <div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Inventario por ubicación</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Auditoría in situ de equipos asignados a cada laboratorio o armario.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100">
                <form action="{{ route('reportes.show', 'auditoria-sala') }}" method="GET" class="flex gap-2">
                    <select name="ubicacion_id" class="w-full text-xs px-2.5 py-2 border border-gray-300 rounded-xl bg-gray-50 font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400" required>
                        @foreach($ubicaciones as $ub)
                            <option value="{{ $ub->id }}">{{ $ub->nombre }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-3.5 py-2 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold rounded-xl text-xs transition-colors whitespace-nowrap inline-flex items-center gap-1 shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Ver
                    </button>
                </form>
            </div>
        </div>

        <!-- 3. Ubicación e Historial de Movimientos (Con sugerencias automáticas) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 hover:shadow-md transition-all">
            <div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Ubicación e Historial de Movimientos</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Escribe el Nro LIA para recibir sugerencias y consultar la ubicación actual resaltada y su historial.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100">
                <form action="{{ route('reportes.show', 'ubicacion-trazabilidad') }}" method="GET" class="space-y-2">
                    <div class="flex gap-2 relative">
                        <div class="relative flex-1">
                            <input type="text" id="input-reporte-nro-lia" name="nro_lia" autocomplete="off" placeholder="N° LIA (ej: SRV-001)..." class="w-full text-xs px-2.5 py-2 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 font-medium" required>
                            <!-- Contenedor flotante de sugerencias AJAX -->
                            <div id="sugerencias-lia" class="hidden absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl z-50 max-h-48 overflow-y-auto text-xs divide-y divide-gray-100"></div>
                        </div>
                        <button type="submit" class="px-3.5 py-2 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold rounded-xl text-xs transition-colors whitespace-nowrap inline-flex items-center gap-1 shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 4. Historial de Tickets y Reparaciones -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 hover:shadow-md transition-all">
            <div>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Historial de Tickets y Reparaciones</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Consulta por Nro LIA o UNSJ el consolidado de intervenciones técnicas, bajas, sala previa y último préstamo.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100 space-y-2">
                <form action="{{ route('reportes.show', 'historial-reparaciones') }}" method="GET" class="space-y-2">
                    <div class="flex gap-2 relative">
                        <div class="relative flex-1">
                            <input type="text" id="input-reporte-ticket-lia" name="nro_lia" autocomplete="off" placeholder="N° LIA / UNSJ (o vacío para todos)..." class="w-full text-xs px-2.5 py-2 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 font-medium">
                            <div id="sugerencias-ticket-lia" class="hidden absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl z-50 max-h-48 overflow-y-auto text-xs divide-y divide-gray-100"></div>
                        </div>
                        <button type="submit" class="px-3.5 py-2 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold rounded-xl text-xs transition-colors whitespace-nowrap inline-flex items-center gap-1 shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Buscar
                        </button>
                    </div>
                </form>
                <div class="flex items-center gap-2 pt-1">
                    <a href="{{ route('reportes.show', 'historial-reparaciones') }}" class="flex-1 text-center py-1.5 px-2 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold rounded-xl text-xs transition-colors">
                        Ver Todos
                    </a>
                    <a href="{{ route('reportes.exportCsv', 'historial-reparaciones') }}" class="p-1.5 border border-gray-300 hover:bg-gray-50 rounded-xl text-gray-700 text-xs font-bold inline-flex items-center gap-1" title="Descargar CSV">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- 5. Historial de Préstamos -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 hover:shadow-md transition-all">
            <div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Historial de Préstamos</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Auditoría de equipos cedidos a cátedras o autoridades con cálculo de días.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-2">
                <a href="{{ route('reportes.show', 'prestados') }}" class="flex-1 text-center py-2 px-3 bg-[#fbc101] hover:bg-amber-400 font-extrabold text-black rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Ver Préstamos</span>
                </a>
                <a href="{{ route('reportes.exportCsv', 'prestados') }}" class="p-2 border border-gray-300 hover:bg-gray-50 rounded-xl text-gray-700 text-xs font-bold inline-flex items-center gap-1 shadow-xs" title="Descargar CSV">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    CSV
                </a>
            </div>
        </div>

        <!-- 5. Reporte de Activos Donados entre Fechas -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 hover:shadow-md transition-all">
            <div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Activos Donados entre Fechas</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Auditoría de bienes ingresados o transferidos por donación dentro de un rango temporal.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100">
                <form action="{{ route('reportes.show', 'donados-fechas') }}" method="GET" class="space-y-2">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Desde:</label>
                            <input type="date" name="fecha_desde" value="{{ now()->subYears(2)->format('Y-m-d') }}" class="w-full text-xs px-2 py-1.5 border border-gray-300 rounded-lg bg-gray-50 focus:bg-white" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Hasta:</label>
                            <input type="date" name="fecha_hasta" value="{{ now()->format('Y-m-d') }}" class="w-full text-xs px-2 py-1.5 border border-gray-300 rounded-lg bg-gray-50 focus:bg-white" required>
                        </div>
                    </div>
                    <button type="submit" class="w-full mt-2 py-2 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold rounded-xl text-xs transition-colors inline-flex items-center justify-center gap-1.5 shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Ver Donaciones</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- 6. Informe de Altas de Elementos con Fechas -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 hover:shadow-md transition-all">
            <div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Altas de Elementos con Fechas</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Registro patrimonial de nuevas incorporaciones e ingresos adquiridos entre fechas.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100">
                <form action="{{ route('reportes.show', 'altas-fechas') }}" method="GET" class="space-y-2">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Desde:</label>
                            <input type="date" name="fecha_desde" value="{{ now()->subYears(2)->format('Y-m-d') }}" class="w-full text-xs px-2 py-1.5 border border-gray-300 rounded-lg bg-gray-50 focus:bg-white" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Hasta:</label>
                            <input type="date" name="fecha_hasta" value="{{ now()->format('Y-m-d') }}" class="w-full text-xs px-2 py-1.5 border border-gray-300 rounded-lg bg-gray-50 focus:bg-white" required>
                        </div>
                    </div>
                    <button type="submit" class="w-full mt-2 py-2 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold rounded-xl text-xs transition-colors inline-flex items-center justify-center gap-1.5 shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Ver Altas</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- 7. Estado de Activos según Fecha de Ingreso al LIA -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 hover:shadow-md transition-all">
            <div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Estado por Fecha de Ingreso</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Evalúa la situación y ubicación actual de los equipos incorporados en un período.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100">
                <form action="{{ route('reportes.show', 'ingreso-estado-fechas') }}" method="GET" class="space-y-2">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Desde:</label>
                            <input type="date" name="fecha_desde" value="{{ now()->subYears(2)->format('Y-m-d') }}" class="w-full text-xs px-2 py-1.5 border border-gray-300 rounded-lg bg-gray-50 focus:bg-white" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Hasta:</label>
                            <input type="date" name="fecha_hasta" value="{{ now()->format('Y-m-d') }}" class="w-full text-xs px-2 py-1.5 border border-gray-300 rounded-lg bg-gray-50 focus:bg-white" required>
                        </div>
                    </div>
                    <button type="submit" class="w-full mt-2 py-2 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold rounded-xl text-xs transition-colors inline-flex items-center justify-center gap-1.5 shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Ver Estado de Activos</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- 8. Reporte de Estado de Garantías entre Fechas -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 hover:shadow-md transition-all">
            <div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Estado de Garantías entre Fechas</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Audita vencimientos de garantía próximos o caducados según el rango ingresado.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100">
                <form action="{{ route('reportes.show', 'garantias-fechas') }}" method="GET" class="space-y-2">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Desde:</label>
                            <input type="date" name="fecha_desde" value="{{ now()->subMonths(6)->format('Y-m-d') }}" class="w-full text-xs px-2 py-1.5 border border-gray-300 rounded-lg bg-gray-50 focus:bg-white" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Hasta:</label>
                            <input type="date" name="fecha_hasta" value="{{ now()->addYear()->format('Y-m-d') }}" class="w-full text-xs px-2 py-1.5 border border-gray-300 rounded-lg bg-gray-50 focus:bg-white" required>
                        </div>
                    </div>
                    <button type="submit" class="w-full mt-2 py-2 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold rounded-xl text-xs transition-colors inline-flex items-center justify-center gap-1.5 shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Ver Garantías</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- 9. Auditoría de movimientos entre fechas -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 hover:shadow-md transition-all">
            <div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Auditoría de Movimientos</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Historial de traslados, asignaciones y cambios operativos entre fechas.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100">
                <form action="{{ route('reportes.show', 'movimientos-fechas') }}" method="GET" class="space-y-2">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Desde:</label>
                            <input type="date" name="fecha_desde" value="{{ now()->subMonth()->format('Y-m-d') }}" class="w-full text-xs px-2 py-1.5 border border-gray-300 rounded-lg bg-gray-50 focus:bg-white" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Hasta:</label>
                            <input type="date" name="fecha_hasta" value="{{ now()->format('Y-m-d') }}" class="w-full text-xs px-2 py-1.5 border border-gray-300 rounded-lg bg-gray-50 focus:bg-white" required>
                        </div>
                    </div>
                    <button type="submit" class="w-full mt-2 py-2 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold rounded-xl text-xs transition-colors inline-flex items-center justify-center gap-1.5 shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>Ver Movimientos</span>
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    // Autocompletado dinámico en tiempo real para Nro LIA en tarjeta de reporte
    const inputLia = document.getElementById('input-reporte-nro-lia');
    const boxSug = document.getElementById('sugerencias-lia');
    let debounceTimer;

    if (inputLia && boxSug) {
        inputLia.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const q = this.value.trim();
            if (q.length < 1) {
                boxSug.innerHTML = '';
                boxSug.classList.add('hidden');
                return;
            }
            debounceTimer = setTimeout(() => {
                fetch(`{{ route('movimientos.buscar-rapido') }}?q=${encodeURIComponent(q)}`)
                    .then(res => res.json())
                    .then(items => {
                        if (items.length === 0) {
                            boxSug.innerHTML = '<div class="p-2.5 text-gray-400 italic">No se encontraron sugerencias</div>';
                            boxSug.classList.remove('hidden');
                            return;
                        }
                        boxSug.innerHTML = items.map(it => `
                            <div class="p-2 hover:bg-amber-50 cursor-pointer flex justify-between items-center transition-colors" onclick="seleccionarSugerenciaLia('${it.nro_lia}')">
                                <div>
                                    <span class="font-bold text-gray-900">${it.nro_lia}</span>
                                    <span class="text-gray-500 text-[10px] ml-1">(${it.tipo} ${it.marca || ''})</span>
                                </div>
                                <span class="text-[10px] bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded font-medium">${it.ubicacion_actual}</span>
                            </div>
                        `).join('');
                        boxSug.classList.remove('hidden');
                    })
                    .catch(() => {
                        boxSug.classList.add('hidden');
                    });
            }, 180);
        });

        document.addEventListener('click', function(e) {
            if (!inputLia.contains(e.target) && !boxSug.contains(e.target)) {
                boxSug.classList.add('hidden');
            }
        });
    }

    function seleccionarSugerenciaLia(lia) {
        if (inputLia) {
            inputLia.value = lia;
            boxSug.classList.add('hidden');
            inputLia.form.submit();
        }
    }

    // Autocompletado para Historial de Tickets
    const inputTicketLia = document.getElementById('input-reporte-ticket-lia');
    const boxTicketSug = document.getElementById('sugerencias-ticket-lia');
    let debounceTicketTimer = null;

    if (inputTicketLia && boxTicketSug) {
        inputTicketLia.addEventListener('input', function() {
            clearTimeout(debounceTicketTimer);
            const q = this.value.trim();
            if (q.length < 1) {
                boxTicketSug.innerHTML = '';
                boxTicketSug.classList.add('hidden');
                return;
            }
            debounceTicketTimer = setTimeout(() => {
                fetch(`{{ route('movimientos.buscar-rapido') }}?q=${encodeURIComponent(q)}`)
                    .then(res => res.json())
                    .then(items => {
                        if (items.length === 0) {
                            boxTicketSug.innerHTML = '<div class="p-2.5 text-gray-400 italic">No se encontraron sugerencias</div>';
                            boxTicketSug.classList.remove('hidden');
                            return;
                        }
                        boxTicketSug.innerHTML = items.map(it => `
                            <div class="p-2 hover:bg-amber-50 cursor-pointer flex justify-between items-center transition-colors" onclick="seleccionarSugerenciaTicketLia('${it.nro_lia}')">
                                <div>
                                    <span class="font-bold text-gray-900">${it.nro_lia}</span>
                                    <span class="text-gray-500 text-[10px] ml-1">(${it.tipo} ${it.marca || ''})</span>
                                </div>
                                <span class="text-[10px] bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded font-medium">${it.ubicacion_actual}</span>
                            </div>
                        `).join('');
                        boxTicketSug.classList.remove('hidden');
                    })
                    .catch(() => {
                        boxTicketSug.classList.add('hidden');
                    });
            }, 180);
        });

        document.addEventListener('click', function(e) {
            if (!inputTicketLia.contains(e.target) && !boxTicketSug.contains(e.target)) {
                boxTicketSug.classList.add('hidden');
            }
        });
    }

    function seleccionarSugerenciaTicketLia(lia) {
        if (inputTicketLia) {
            inputTicketLia.value = lia;
            boxTicketSug.classList.add('hidden');
            inputTicketLia.form.submit();
        }
    }
</script>
@endsection
