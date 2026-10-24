@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-8">
    <!-- Header -->
    <div class="border-b border-gray-200 pb-5">
        <span class="text-xs font-bold uppercase tracking-wider text-amber-700 bg-amber-100 px-2.5 py-1 rounded-md">
            Módulo Institucional Oficial
        </span>
        <h1 class="text-3xl font-extrabold text-gray-900 mt-2">Sistema Universal de Reportes y Auditoría</h1>
        <p class="text-sm text-gray-600 mt-1">
            Generación y exportación de los 8 reportes oficiales basados en Solicitud de Reportes 01.docx, listados completos sin truncar y resguardo físico para bibliorato.
        </p>
    </div>

    <!-- The 8 Official Reports Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Reporte 1: Listado de Inventario Completo -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-800 font-extrabold flex items-center justify-center text-sm">1</span>
                    <span class="text-[11px] font-bold uppercase bg-gray-100 text-gray-700 px-2 py-0.5 rounded">Universo Total</span>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Listado de Inventario Completo</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Catálogo exhaustivo de los ~1.150 activos del LIA con Nro LIA, Nro UNSJ, características de hardware, estado y localización física.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-2">
                <a href="{{ route('reportes.show', 'inventario-completo') }}" class="flex-1 text-center py-2 px-3 bg-amber-400 hover:bg-amber-500 font-bold text-black rounded-lg text-xs transition-colors">
                    Ver Reporte Digital
                </a>
                <a href="{{ route('reportes.exportCsv', 'inventario-completo') }}" class="p-2 border border-gray-300 hover:bg-gray-50 rounded-lg text-gray-700 text-xs font-bold" title="Descargar CSV">
                    CSV
                </a>
            </div>
        </div>

        <!-- Reporte 2: Historial de Reparación y Mantenimiento -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="w-8 h-8 rounded-lg bg-sky-100 text-sky-800 font-extrabold flex items-center justify-center text-sm">2</span>
                    <span class="text-[11px] font-bold uppercase bg-sky-50 text-sky-800 px-2 py-0.5 rounded">Taller / Service</span>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Historial de Reparación por Elemento</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Cronología completa de fallas reportadas, ingresos a taller técnico, diagnósticos y altas operativas de un activo específico.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100">
                <form action="{{ route('reportes.show', 'historial-reparaciones') }}" method="GET" class="flex gap-2">
                    <input type="number" name="elemento_id" placeholder="ID Técnico de Activo..." class="w-full text-xs px-2.5 py-1.5 border rounded-lg bg-gray-50" required>
                    <button type="submit" class="px-3 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-bold hover:bg-black">
                        Ver
                    </button>
                </form>
            </div>
        </div>

        <!-- Reporte 3: Ubicación y Trazabilidad de un Elemento -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-800 font-extrabold flex items-center justify-center text-sm">3</span>
                    <span class="text-[11px] font-bold uppercase bg-purple-50 text-purple-800 px-2 py-0.5 rounded">Línea de Tiempo</span>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Ubicación y Trazabilidad Histórica</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Ubicación actual exacta (sala + puesto o armario) y línea de tiempo completa de traslados y préstamos desde su adquisición.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100">
                <form action="{{ route('reportes.show', 'ubicacion-trazabilidad') }}" method="GET" class="flex gap-2">
                    <input type="number" name="elemento_id" placeholder="ID Técnico de Activo..." class="w-full text-xs px-2.5 py-1.5 border rounded-lg bg-gray-50" required>
                    <button type="submit" class="px-3 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-bold hover:bg-black">
                        Ver
                    </button>
                </form>
            </div>
        </div>

        <!-- Reporte 4: Elementos por Localización (Modo Auditoría de Sala / Armario) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 font-extrabold flex items-center justify-center text-sm">4</span>
                    <span class="text-[11px] font-bold uppercase bg-emerald-50 text-emerald-800 px-2 py-0.5 rounded">Auditoría A4</span>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Auditoría Física por Sala / Armario</h3>
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
                    <button type="submit" class="px-3 py-1.5 bg-emerald-700 text-white rounded-lg text-xs font-bold hover:bg-emerald-800">
                        Auditar
                    </button>
                </form>
            </div>
        </div>

        <!-- Reporte 5: Detalle según Concepto entre Fechas (Préstamos / Bajas) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-800 font-extrabold flex items-center justify-center text-sm">5</span>
                    <span class="text-[11px] font-bold uppercase bg-amber-50 text-amber-800 px-2 py-0.5 rounded">Salidas y Bajas</span>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Detalle por Concepto entre Fechas</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Seguimiento detallado de activos prestados a cátedras, bajas patrimoniales, transferencias o donaciones en un rango temporal.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-2">
                <a href="{{ route('reportes.show', 'concepto-fechas') }}" class="flex-1 text-center py-2 px-3 bg-amber-400 hover:bg-amber-500 font-bold text-black rounded-lg text-xs transition-colors">
                    Ver Informe
                </a>
                <a href="{{ route('reportes.exportCsv', 'concepto-fechas') }}" class="p-2 border border-gray-300 hover:bg-gray-50 rounded-lg text-gray-700 text-xs font-bold" title="Descargar CSV">
                    CSV
                </a>
            </div>
        </div>

        <!-- Reporte 6: Informe de Altas con Fechas -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-800 font-extrabold flex items-center justify-center text-sm">6</span>
                    <span class="text-[11px] font-bold uppercase bg-indigo-50 text-indigo-800 px-2 py-0.5 rounded">Altas y Compras</span>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Informe de Altas de Elementos</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Registro de compras institucionales, donaciones recibidas e incorporaciones patrimoniales ordenadas cronológicamente.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-2">
                <a href="{{ route('reportes.show', 'altas-fechas') }}" class="flex-1 text-center py-2 px-3 bg-indigo-600 hover:bg-indigo-700 font-bold text-white rounded-lg text-xs transition-colors">
                    Ver Altas
                </a>
                <a href="{{ route('reportes.exportCsv', 'altas-fechas') }}" class="p-2 border border-gray-300 hover:bg-gray-50 rounded-lg text-gray-700 text-xs font-bold" title="Descargar CSV">
                    CSV
                </a>
            </div>
        </div>

        <!-- Reporte 7: Detalle de Movimientos entre Fechas -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="w-8 h-8 rounded-lg bg-teal-100 text-teal-800 font-extrabold flex items-center justify-center text-sm">7</span>
                    <span class="text-[11px] font-bold uppercase bg-teal-50 text-teal-800 px-2 py-0.5 rounded">Bitácora Global</span>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Detalle de Movimientos entre Fechas</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Bitácora histórica integral de todos los traslados internos y préstamos ejecutados por los técnicos en un periodo seleccionado.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-2">
                <a href="{{ route('reportes.show', 'movimientos-fechas') }}" class="flex-1 text-center py-2 px-3 bg-teal-600 hover:bg-teal-700 font-bold text-white rounded-lg text-xs transition-colors">
                    Ver Bitácora
                </a>
                <a href="{{ route('reportes.exportCsv', 'movimientos-fechas') }}" class="p-2 border border-gray-300 hover:bg-gray-50 rounded-lg text-gray-700 text-xs font-bold" title="Descargar CSV">
                    CSV
                </a>
            </div>
        </div>

        <!-- Reporte 8: Listado Detallado Parametrizable Multi-Criterio -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 transition-all">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="w-8 h-8 rounded-lg bg-orange-100 text-orange-800 font-extrabold flex items-center justify-center text-sm">8</span>
                    <span class="text-[11px] font-bold uppercase bg-orange-50 text-orange-800 px-2 py-0.5 rounded">Parametrizable</span>
                </div>
                <h3 class="font-bold text-gray-900 text-base">Listado Parametrizable Multi-Criterio</h3>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Filtro cruzado multidimensional por tipo de periférico, sala física, estado operativo y estado de garantía vigente o vencida.
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-2">
                <a href="{{ route('reportes.show', 'parametrizable') }}" class="flex-1 text-center py-2 px-3 bg-amber-400 hover:bg-amber-500 font-bold text-black rounded-lg text-xs transition-colors">
                    Configurar y Ver
                </a>
                <a href="{{ route('reportes.exportCsv', 'parametrizable') }}" class="p-2 border border-gray-300 hover:bg-gray-50 rounded-lg text-gray-700 text-xs font-bold" title="Descargar CSV">
                    CSV
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
