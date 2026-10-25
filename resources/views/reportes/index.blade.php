@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Header -->
    <div class="border-b border-gray-200 pb-4">
        <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2.5">
            <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Reportes de Inventario</span>
        </h1>
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
                    Catálogo completo de todos los activos del LIA.
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

        <!-- 2. Inventario por ubicación -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 hover:shadow-md transition-all">
            <div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Inventario por ubicación</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Listado de activos por ubicación.
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

        <!-- 3. Ubicación e Historial de Movimientos -->
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
                    Busca elementos por su Nro LIA y obtén su ubicación exacta e historial de movimiento.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100">
                <form action="{{ route('reportes.show', 'ubicacion-trazabilidad') }}" method="GET" class="space-y-2">
                    <div class="flex gap-2">
                        <input type="text" name="nro_lia" placeholder="N° LIA (ej: SRV-001)..." class="w-full text-xs px-2.5 py-2 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400" required>
                        <button type="submit" class="px-3.5 py-2 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold rounded-xl text-xs transition-colors whitespace-nowrap inline-flex items-center gap-1 shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 4. Historial de Préstamos -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 hover:shadow-md transition-all">
            <div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Historial de Préstamos</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Obtén el historial de elementos prestados.
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

        <!-- 5. Auditoría de movimientos entre fechas -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 hover:shadow-md transition-all md:col-span-2 lg:col-span-2">
            <div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Auditoría de movimientos entre fechas</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Informe de todos los movimientos realizados entre la fecha inicial y la fecha final.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100">
                <form action="{{ route('reportes.show', 'movimientos-fechas') }}" method="GET" class="flex flex-wrap items-end gap-3">
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-[11px] font-bold text-gray-600 mb-1">Fecha Inicial:</label>
                        <input type="date" name="fecha_desde" value="{{ now()->subMonth()->format('Y-m-d') }}" class="w-full text-xs px-2.5 py-2 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 font-medium" required>
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-[11px] font-bold text-gray-600 mb-1">Fecha Final:</label>
                        <input type="date" name="fecha_hasta" value="{{ now()->format('Y-m-d') }}" class="w-full text-xs px-2.5 py-2 border border-gray-300 rounded-xl bg-gray-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400 font-medium" required>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="px-4 py-2 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold rounded-xl text-xs transition-colors inline-flex items-center gap-1.5 shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Ver Informe
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
