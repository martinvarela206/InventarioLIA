@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-8">
    <!-- Header -->
    <div class="border-b border-gray-200 pb-5">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-800 bg-amber-100 px-2.5 py-1 rounded-md">
                Módulo Institucional Oficial
            </span>
            <span class="text-xs text-gray-500 font-semibold">revisor / técnicos</span>
        </div>
        <h1 class="text-3xl font-extrabold text-gray-900 mt-2 flex items-center gap-3">
            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Sistema Universal de Reportes y Auditoría</span>
        </h1>
        <p class="text-sm text-gray-600 mt-1">
            Generación y exportación de los 8 reportes oficiales basados en Solicitud de Reportes 01.docx, listados completos sin truncar y resguardo físico para bibliorato.
        </p>
    </div>

    <!-- The 8 Official Reports Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Reporte 1: Listado de Inventario Completo -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 hover:shadow-md transition-all group">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-900 font-black flex items-center justify-center text-xs">01</span>
                        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold uppercase bg-gray-100 text-gray-700 px-2 py-0.5 rounded">Universo Total</span>
                </div>
                <h3 class="font-bold text-gray-900 text-base group-hover:text-amber-600 transition-colors">Listado de Inventario Completo</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Catálogo exhaustivo de los ~1.150 activos del LIA con Nro LIA, Nro UNSJ, características de hardware, estado y localización física.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-2">
                <a href="{{ route('reportes.show', 'inventario-completo') }}" class="flex-1 text-center py-2 px-3 bg-amber-400 hover:bg-amber-500 font-bold text-black rounded-lg text-xs transition-colors flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Ver Reporte Digital</span>
                </a>
                <a href="{{ route('reportes.exportCsv', 'inventario-completo') }}" class="p-2 border border-gray-300 hover:bg-gray-50 rounded-lg text-gray-700 text-xs font-bold inline-flex items-center gap-1" title="Descargar CSV">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    CSV
                </a>
            </div>
        </div>

        <!-- Reporte 2: Historial de Reparación y Mantenimiento -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-sky-400 hover:shadow-md transition-all group">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-sky-100 text-sky-900 font-black flex items-center justify-center text-xs">02</span>
                        <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold uppercase bg-sky-50 text-sky-800 px-2 py-0.5 rounded">Taller / Service</span>
                </div>
                <h3 class="font-bold text-gray-900 text-base group-hover:text-sky-600 transition-colors">Historial de Reparación por Elemento</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Cronología completa de fallas reportadas, ingresos a taller técnico, diagnósticos y altas operativas de un activo específico.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100">
                <form action="{{ route('reportes.show', 'historial-reparaciones') }}" method="GET" class="space-y-2">
                    <div class="flex gap-2">
                        <input type="text" name="nro_lia" placeholder="N° LIA (ej: SRV-001, LIA-U-24)..." class="w-full text-xs px-2.5 py-1.5 border border-gray-300 rounded-lg bg-gray-50 focus:bg-white" required>
                        <button type="submit" class="px-3 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-bold hover:bg-black whitespace-nowrap inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Ver
                        </button>
                    </div>
                    <div class="text-[11px] text-gray-500 flex items-center justify-between">
                        <span>Opcional UNSJ:</span>
                        <input type="text" name="nro_unsj" placeholder="02-08246..." class="w-32 text-xs px-2 py-0.5 border border-gray-200 rounded bg-gray-50">
                    </div>
                </form>
            </div>
        </div>

        <!-- Reporte 3: Ubicación y Trazabilidad de un Elemento -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-purple-400 hover:shadow-md transition-all group">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-purple-100 text-purple-900 font-black flex items-center justify-center text-xs">03</span>
                        <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold uppercase bg-purple-50 text-purple-800 px-2 py-0.5 rounded">Línea de Tiempo</span>
                </div>
                <h3 class="font-bold text-gray-900 text-base group-hover:text-purple-600 transition-colors">Ubicación y Trazabilidad Histórica</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Ubicación actual exacta (sala + puesto o armario) y línea de tiempo completa de traslados y préstamos desde su adquisición.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100">
                <form action="{{ route('reportes.show', 'ubicacion-trazabilidad') }}" method="GET" class="space-y-2">
                    <div class="flex gap-2">
                        <input type="text" name="nro_lia" placeholder="N° LIA (ej: SRV-001, LIA-U-24)..." class="w-full text-xs px-2.5 py-1.5 border border-gray-300 rounded-lg bg-gray-50 focus:bg-white" required>
                        <button type="submit" class="px-3 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-bold hover:bg-black whitespace-nowrap inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Ver
                        </button>
                    </div>
                    <div class="text-[11px] text-gray-500 flex items-center justify-between">
                        <span>Opcional UNSJ:</span>
                        <input type="text" name="nro_unsj" placeholder="02-08246..." class="w-32 text-xs px-2 py-0.5 border border-gray-200 rounded bg-gray-50">
                    </div>
                </form>
            </div>
        </div>

        <!-- Reporte 4: Elementos por Localización (Modo Auditoría de Sala / Armario) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-emerald-400 hover:shadow-md transition-all group">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-900 font-black flex items-center justify-center text-xs">04</span>
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold uppercase bg-emerald-50 text-emerald-800 px-2 py-0.5 rounded">Auditoría A4</span>
                </div>
                <h3 class="font-bold text-gray-900 text-base group-hover:text-emerald-600 transition-colors">Auditoría Física por Sala / Armario</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Lista el 100% de los elementos contenidos en una sala o armario para cotejo físico in situ e impresión con recuadro de firma.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100">
                <form action="{{ route('reportes.show', 'auditoria-sala') }}" method="GET" class="flex gap-2">
                    <select name="ubicacion_id" class="w-full text-xs px-2 py-1.5 border rounded-lg bg-gray-50 font-semibold" required>
                        @foreach($ubicaciones as $ub)
                            <option value="{{ $ub->id }}">{{ $ub->nombre }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-3 py-1.5 bg-emerald-700 text-white rounded-lg text-xs font-bold hover:bg-emerald-800 inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Auditar
                    </button>
                </form>
            </div>
        </div>

        <!-- Reporte 5: Detalle según Concepto entre Fechas (Préstamos / Bajas) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 hover:shadow-md transition-all group">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-900 font-black flex items-center justify-center text-xs">05</span>
                        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold uppercase bg-amber-50 text-amber-800 px-2 py-0.5 rounded">Salidas y Bajas</span>
                </div>
                <h3 class="font-bold text-gray-900 text-base group-hover:text-amber-600 transition-colors">Detalle por Concepto entre Fechas</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Seguimiento detallado de activos prestados a cátedras, bajas patrimoniales, transferencias o donaciones en un rango temporal.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-2">
                <a href="{{ route('reportes.show', 'concepto-fechas') }}" class="flex-1 text-center py-2 px-3 bg-amber-400 hover:bg-amber-500 font-bold text-black rounded-lg text-xs transition-colors flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Ver Informe</span>
                </a>
                <a href="{{ route('reportes.exportCsv', 'concepto-fechas') }}" class="p-2 border border-gray-300 hover:bg-gray-50 rounded-lg text-gray-700 text-xs font-bold inline-flex items-center gap-1" title="Descargar CSV">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    CSV
                </a>
            </div>
        </div>

        <!-- Reporte 6: Informe de Altas con Fechas -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-indigo-400 hover:shadow-md transition-all group">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-900 font-black flex items-center justify-center text-xs">06</span>
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold uppercase bg-indigo-50 text-indigo-800 px-2 py-0.5 rounded">Altas y Compras</span>
                </div>
                <h3 class="font-bold text-gray-900 text-base group-hover:text-indigo-600 transition-colors">Informe de Altas de Elementos</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Registro de compras institucionales, donaciones recibidas e incorporaciones patrimoniales ordenadas cronológicamente.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-2">
                <a href="{{ route('reportes.show', 'altas-fechas') }}" class="flex-1 text-center py-2 px-3 bg-indigo-600 hover:bg-indigo-700 font-bold text-white rounded-lg text-xs transition-colors flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Ver Altas</span>
                </a>
                <a href="{{ route('reportes.exportCsv', 'altas-fechas') }}" class="p-2 border border-gray-300 hover:bg-gray-50 rounded-lg text-gray-700 text-xs font-bold inline-flex items-center gap-1" title="Descargar CSV">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    CSV
                </a>
            </div>
        </div>

        <!-- Reporte 7: Detalle de Movimientos entre Fechas -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-teal-400 hover:shadow-md transition-all group">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-teal-100 text-teal-900 font-black flex items-center justify-center text-xs">07</span>
                        <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold uppercase bg-teal-50 text-teal-800 px-2 py-0.5 rounded">Bitácora Global</span>
                </div>
                <h3 class="font-bold text-gray-900 text-base group-hover:text-teal-600 transition-colors">Detalle de Movimientos entre Fechas</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Bitácora histórica integral de todos los traslados internos y préstamos ejecutados por los técnicos en un periodo seleccionado.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-2">
                <a href="{{ route('reportes.show', 'movimientos-fechas') }}" class="flex-1 text-center py-2 px-3 bg-teal-600 hover:bg-teal-700 font-bold text-white rounded-lg text-xs transition-colors flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <span>Ver Bitácora</span>
                </a>
                <a href="{{ route('reportes.exportCsv', 'movimientos-fechas') }}" class="p-2 border border-gray-300 hover:bg-gray-50 rounded-lg text-gray-700 text-xs font-bold inline-flex items-center gap-1" title="Descargar CSV">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    CSV
                </a>
            </div>
        </div>

        <!-- Reporte 8: Listado Detallado Parametrizable Multi-Criterio -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 hover:shadow-md transition-all group">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-orange-100 text-orange-900 font-black flex items-center justify-center text-xs">08</span>
                        <div class="w-8 h-8 rounded-xl bg-orange-50 text-orange-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold uppercase bg-orange-50 text-orange-800 px-2 py-0.5 rounded">Parametrizable</span>
                </div>
                <h3 class="font-bold text-gray-900 text-base group-hover:text-amber-600 transition-colors">Listado Parametrizable Multi-Criterio</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Filtro cruzado multidimensional por tipo de periférico, sala física, estado operativo y estado de garantía vigente o vencida.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-2">
                <a href="{{ route('reportes.show', 'parametrizable') }}" class="flex-1 text-center py-2 px-3 bg-amber-400 hover:bg-amber-500 font-bold text-black rounded-lg text-xs transition-colors flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    <span>Configurar y Ver</span>
                </a>
                <a href="{{ route('reportes.exportCsv', 'parametrizable') }}" class="p-2 border border-gray-300 hover:bg-gray-50 rounded-lg text-gray-700 text-xs font-bold inline-flex items-center gap-1" title="Descargar CSV">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    CSV
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
