@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('app_admin.index') }}" class="hover:underline">Gestión App</a>
                <span>/</span>
                <span class="text-gray-900 font-bold">Gestión de Reportes</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2.5">
                <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Catálogo y Configuración de Reportes</span>
                <span class="text-xs bg-emerald-100 text-emerald-900 font-extrabold px-2.5 py-0.5 rounded-full border border-emerald-300">
                    {{ $reportes->where('activo', true)->count() }} activos
                </span>
            </h1>
            <p class="text-sm text-gray-600 mt-0.5">
                Administra los reportes oficiales del LIA y crea nuevos reportes dinámicos basados en filtros del inventario con variables de emisión configurables.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reportes.index') }}" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm transition-all">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span>Ver Hub de Reportes</span>
            </a>
            <button onclick="abrirModalCrearReporte()" class="inline-flex items-center gap-1.5 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold px-4 py-2 rounded-xl text-xs shadow-sm transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nuevo Reporte</span>
            </button>
        </div>
    </div>

    <!-- Mensajes de feedback -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-300 text-emerald-900 text-sm font-semibold px-4 py-3 rounded-xl shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-900 text-sm font-semibold px-4 py-3 rounded-xl shadow-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tabla de Reportes -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <span class="font-bold text-gray-800 text-sm">
                Catálogo de Reportes Registrados ({{ $reportes->count() }})
            </span>
            <div class="flex items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-1 text-gray-600">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span> Sistema: {{ $reportes->where('es_sistema', true)->count() }}
                </span>
                <span class="text-gray-300">|</span>
                <span class="inline-flex items-center gap-1 text-gray-600">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block"></span> Personalizados: {{ $reportes->where('es_sistema', false)->count() }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-700">
                <thead class="bg-gray-100 text-gray-600 uppercase font-black tracking-wider text-[11px] border-b border-gray-200">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">Ord</th>
                        <th class="py-3 px-4">Reporte / Identificador</th>
                        <th class="py-3 px-4">Categoría / Tipo</th>
                        <th class="py-3 px-4">Variables Requeridas</th>
                        <th class="py-3 px-4">Filtros Fijos</th>
                        <th class="py-3 px-4 text-center">Estado</th>
                        <th class="py-3 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reportes as $rep)
                        <tr class="hover:bg-amber-50/40 transition-colors {{ !$rep->activo ? 'opacity-60 bg-gray-50/50' : '' }}">
                            <td class="py-3.5 px-4 text-center font-bold text-gray-500">
                                {{ $rep->orden }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-900 flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5">
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
                                    <div>
                                        <a href="{{ route('reportes.show', $rep->slug) }}" class="font-bold text-gray-900 hover:text-amber-800 text-sm block">
                                            {{ $rep->titulo }}
                                        </a>
                                        <span class="text-[11px] text-gray-500 line-clamp-1">{{ $rep->subtitulo ?: $rep->descripcion }}</span>
                                        <span class="text-[10px] font-mono text-gray-400">slug: {{ $rep->slug }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="space-y-1">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-800 border border-gray-200">
                                        {{ $rep->categoria }}
                                    </span>
                                    <div>
                                        @if($rep->es_sistema)
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200">
                                                🔒 Sistema LIA
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-800 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                                                ⭐ Personalizado
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1">
                                    @if($rep->requiereUbicacion())
                                        <span class="px-2 py-0.5 bg-sky-50 text-sky-800 border border-sky-200 rounded font-semibold text-[10px]">🏢 Sala</span>
                                    @endif
                                    @if($rep->requiereTipo())
                                        <span class="px-2 py-0.5 bg-indigo-50 text-indigo-800 border border-indigo-200 rounded font-semibold text-[10px]">🏷️ Tipo</span>
                                    @endif
                                    @if($rep->requiereNroLia())
                                        <span class="px-2 py-0.5 bg-purple-50 text-purple-800 border border-purple-200 rounded font-semibold text-[10px]">🔢 N° LIA</span>
                                    @endif
                                    @if($rep->requiereRangoFechas())
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded font-semibold text-[10px]">📅 Rango Fechas</span>
                                    @endif
                                    @if(!$rep->requiereUbicacion() && !$rep->requiereTipo() && !$rep->requiereNroLia() && !$rep->requiereRangoFechas())
                                        <span class="text-gray-400 italic text-[11px]">Ninguna (Directo)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if(!empty($rep->filtros_fijos))
                                    <div class="space-y-0.5">
                                        @foreach($rep->filtros_fijos as $k => $v)
                                            @if(!empty($v))
                                                <div class="text-[10px] font-mono text-gray-600 bg-gray-50 px-1.5 py-0.5 rounded inline-block">
                                                    <strong>{{ $k }}:</strong> {{ is_array($v) ? implode(',', $v) : $v }}
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 italic text-[11px]">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <form action="{{ route('app_admin.reportes.toggle', $rep->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="cursor-pointer inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold transition-all {{ $rep->activo ? 'bg-emerald-100 text-emerald-900 border border-emerald-300 hover:bg-emerald-200' : 'bg-gray-200 text-gray-700 border border-gray-300 hover:bg-gray-300' }}" title="Clic para alternar estado">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $rep->activo ? 'bg-emerald-600' : 'bg-gray-500' }}"></span>
                                        {{ $rep->activo ? 'Activo' : 'Inactivo' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('reportes.show', $rep->slug) }}" class="p-1.5 text-gray-600 hover:text-amber-800 hover:bg-amber-100 rounded-lg transition-colors" title="Ver / Ejecutar Reporte">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <button type="button" onclick="editarReporte({{ json_encode($rep) }})" class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer" title="Editar Configuración">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    @if(!$rep->es_sistema)
                                        <form action="{{ route('app_admin.reportes.destroy', $rep->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar definitivamente este reporte personalizado?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors cursor-pointer" title="Eliminar Reporte">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-500 italic">
                                No se encontraron reportes configurados en el sistema.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: CREAR NUEVO REPORTE                               -->
<!-- ======================================================== -->
<div id="modal-crear-reporte" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 max-w-2xl w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <span class="text-xl">📊</span>
                <h3 class="font-black text-gray-900 text-lg">Nuevo Reporte Configurado</h3>
            </div>
            <button type="button" onclick="cerrarModalCrearReporte()" class="text-gray-400 hover:text-gray-600 rounded-lg p-1.5">✕</button>
        </div>

        <form action="{{ route('app_admin.reportes.store') }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="md:col-span-2">
                    <label class="block font-bold text-gray-700 uppercase tracking-wider text-[10px] mb-1">Título del Reporte *</label>
                    <input type="text" name="titulo" required placeholder="Ej. Computadoras en Sala A" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-400 font-semibold text-gray-900">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 uppercase tracking-wider text-[10px] mb-1">Subtítulo / Bajada</label>
                    <input type="text" name="subtitulo" placeholder="Ej. Listado oficial de puestos de trabajo" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-400 font-medium">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 uppercase tracking-wider text-[10px] mb-1">Orden en el Menú</label>
                    <input type="number" name="orden" value="50" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-400 font-semibold">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 uppercase tracking-wider text-[10px] mb-1">Categoría</label>
                    <select name="categoria" class="w-full px-3 py-2 border border-gray-300 rounded-xl bg-gray-50 font-medium">
                        <option value="general">General</option>
                        <option value="inventario">Inventario</option>
                        <option value="activos">Activos</option>
                        <option value="movimientos">Movimientos</option>
                        <option value="soporte">Soporte / Taller</option>
                        <option value="patrimonio">Patrimonio</option>
                        <option value="personalizado" selected>Personalizado</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-gray-700 uppercase tracking-wider text-[10px] mb-1">Icono</label>
                    <select name="icono" class="w-full px-3 py-2 border border-gray-300 rounded-xl bg-gray-50 font-medium">
                        <option value="cubes">📦 Inventario / Cubos</option>
                        <option value="building">🏢 Edificio / Sala</option>
                        <option value="location">📍 Ubicación / Trazabilidad</option>
                        <option value="wrench">🔧 Reparación / Taller</option>
                        <option value="arrows-right-left">🔄 Préstamos</option>
                        <option value="shield">🛡️ Garantías</option>
                        <option value="calendar-plus">📅 Calendario / Fechas</option>
                        <option value="trash">🗑️ Bajas / Basura</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase tracking-wider text-[10px] mb-1">Descripción Detallada</label>
                <textarea name="descripcion" rows="2" placeholder="Explica la finalidad del reporte para los usuarios del LIA..." class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-400 font-medium"></textarea>
            </div>

            <!-- Filtros Fijos -->
            <div class="border border-amber-200 rounded-2xl p-3 bg-amber-50/50 space-y-2">
                <span class="block font-bold text-amber-950 text-[11px]">📌 Filtros Fijos Predefinidos (Opcional):</span>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                    <div>
                        <label class="block text-gray-600 text-[10px] font-semibold mb-0.5">Tipo de Activo:</label>
                        <select name="filtros_fijos[tipo_id]" class="w-full px-2 py-1.5 border border-amber-300 rounded-lg bg-white">
                            <option value="">-- Todos --</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo->id }}">{{ $tipo->tipo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-[10px] font-semibold mb-0.5">Sala / Ubicación:</label>
                        <select name="filtros_fijos[ubicacion_id]" class="w-full px-2 py-1.5 border border-amber-300 rounded-lg bg-white">
                            <option value="">-- Todas --</option>
                            @foreach($ubicaciones as $ub)
                                <option value="{{ $ub->id }}">{{ $ub->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-[10px] font-semibold mb-0.5">Puesto de Trabajo:</label>
                        <input type="text" name="filtros_fijos[puesto]" placeholder="Ej. Puesto 04" class="w-full px-2 py-1.5 border border-amber-300 rounded-lg bg-white text-xs">
                    </div>
                    <div>
                        <label class="block text-gray-600 text-[10px] font-semibold mb-0.5">Situación:</label>
                        <select name="filtros_fijos[situacion]" class="w-full px-2 py-1.5 border border-amber-300 rounded-lg bg-white">
                            <option value="">-- Todas --</option>
                            <option value="operativo">Operativo</option>
                            <option value="prestado">Prestado</option>
                            <option value="en_reparacion">En Reparación</option>
                            <option value="obsoleto">Obsoleto</option>
                            <option value="baja">Baja</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Variables Requeridas -->
            <div class="border border-gray-200 rounded-2xl p-3 bg-gray-50 space-y-2">
                <span class="block font-bold text-gray-800 text-[11px]">⚙️ Variables dinámicas que el usuario elegirá al emitir:</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 cursor-pointer font-medium text-gray-700">
                        <input type="checkbox" name="variables_requeridas[ubicacion_id]" value="1" class="rounded text-amber-500 focus:ring-amber-400">
                        <span>Selección de <strong>Sala / Armario</strong></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium text-gray-700">
                        <input type="checkbox" name="variables_requeridas[puesto]" value="1" class="rounded text-amber-500 focus:ring-amber-400">
                        <span>Selección de <strong>Puesto de Trabajo</strong></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium text-gray-700">
                        <input type="checkbox" name="variables_requeridas[tipo_id]" value="1" class="rounded text-amber-500 focus:ring-amber-400">
                        <span>Selección de <strong>Tipo de Activo</strong></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium text-gray-700">
                        <input type="checkbox" name="variables_requeridas[nro_lia]" value="1" class="rounded text-amber-500 focus:ring-amber-400">
                        <span>Ingreso de <strong>N° LIA / Activo</strong></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium text-gray-700">
                        <input type="checkbox" name="variables_requeridas[fechas]" value="1" class="rounded text-amber-500 focus:ring-amber-400">
                        <span>Rango de <strong>Fechas (Desde/Hasta)</strong></span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="activo" id="crear_activo" value="1" checked class="rounded text-amber-500 focus:ring-amber-400">
                <label for="crear_activo" class="font-bold text-gray-800">Activo (visible en el Catálogo de Reportes)</label>
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                <button type="button" onclick="cerrarModalCrearReporte()" class="px-4 py-2 border border-gray-300 rounded-xl font-bold text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold rounded-xl shadow-xs transition-colors">
                    Crear Reporte
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: EDITAR REPORTE EXISTENTE                          -->
<!-- ======================================================== -->
<div id="modal-editar-reporte" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 hidden">
    <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 max-w-2xl w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <span class="text-xl">✏️</span>
                <h3 class="font-black text-gray-900 text-lg">Editar Configuración de Reporte</h3>
            </div>
            <button type="button" onclick="cerrarModalEditarReporte()" class="text-gray-400 hover:text-gray-600 rounded-lg p-1.5">✕</button>
        </div>

        <form id="form-editar-reporte" action="" method="POST" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="md:col-span-2">
                    <label class="block font-bold text-gray-700 uppercase tracking-wider text-[10px] mb-1">Título del Reporte *</label>
                    <input type="text" name="titulo" id="edit_titulo" required class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-400 font-semibold text-gray-900">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 uppercase tracking-wider text-[10px] mb-1">Subtítulo / Bajada</label>
                    <input type="text" name="subtitulo" id="edit_subtitulo" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-400 font-medium">
                </div>
                <div>
                    <label class="block font-bold text-gray-700 uppercase tracking-wider text-[10px] mb-1">Orden en el Menú</label>
                    <input type="number" name="orden" id="edit_orden" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-400 font-semibold">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-gray-700 uppercase tracking-wider text-[10px] mb-1">Categoría</label>
                    <select name="categoria" id="edit_categoria" class="w-full px-3 py-2 border border-gray-300 rounded-xl bg-gray-50 font-medium">
                        <option value="general">General</option>
                        <option value="inventario">Inventario</option>
                        <option value="activos">Activos</option>
                        <option value="movimientos">Movimientos</option>
                        <option value="soporte">Soporte / Taller</option>
                        <option value="patrimonio">Patrimonio</option>
                        <option value="personalizado">Personalizado</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-gray-700 uppercase tracking-wider text-[10px] mb-1">Icono</label>
                    <select name="icono" id="edit_icono" class="w-full px-3 py-2 border border-gray-300 rounded-xl bg-gray-50 font-medium">
                        <option value="cubes">📦 Inventario / Cubos</option>
                        <option value="building">🏢 Edificio / Sala</option>
                        <option value="location">📍 Ubicación / Trazabilidad</option>
                        <option value="wrench">🔧 Reparación / Taller</option>
                        <option value="arrows-right-left">🔄 Préstamos</option>
                        <option value="shield">🛡️ Garantías</option>
                        <option value="calendar-plus">📅 Calendario / Fechas</option>
                        <option value="trash">🗑️ Bajas / Basura</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase tracking-wider text-[10px] mb-1">Descripción</label>
                <textarea name="descripcion" id="edit_descripcion" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-400 font-medium"></textarea>
            </div>

            <!-- Variables Requeridas -->
            <div class="border border-gray-200 rounded-2xl p-3 bg-gray-50 space-y-2">
                <span class="block font-bold text-gray-800 text-[11px]">⚙️ Variables dinámicas requeridas:</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 cursor-pointer font-medium text-gray-700">
                        <input type="checkbox" name="variables_requeridas[ubicacion_id]" id="edit_var_ubicacion" value="1" class="rounded text-amber-500 focus:ring-amber-400">
                        <span>Selección de <strong>Sala / Armario</strong></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium text-gray-700">
                        <input type="checkbox" name="variables_requeridas[puesto]" id="edit_var_puesto" value="1" class="rounded text-amber-500 focus:ring-amber-400">
                        <span>Selección de <strong>Puesto de Trabajo</strong></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium text-gray-700">
                        <input type="checkbox" name="variables_requeridas[tipo_id]" id="edit_var_tipo" value="1" class="rounded text-amber-500 focus:ring-amber-400">
                        <span>Selección de <strong>Tipo de Activo</strong></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium text-gray-700">
                        <input type="checkbox" name="variables_requeridas[nro_lia]" id="edit_var_nro_lia" value="1" class="rounded text-amber-500 focus:ring-amber-400">
                        <span>Ingreso de <strong>N° LIA / Activo</strong></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium text-gray-700">
                        <input type="checkbox" name="variables_requeridas[fechas]" id="edit_var_fechas" value="1" class="rounded text-amber-500 focus:ring-amber-400">
                        <span>Rango de <strong>Fechas (Desde/Hasta)</strong></span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="activo" id="edit_activo" value="1" class="rounded text-amber-500 focus:ring-amber-400">
                <label for="edit_activo" class="font-bold text-gray-800">Activo (visible en el Catálogo)</label>
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                <button type="button" onclick="cerrarModalEditarReporte()" class="px-4 py-2 border border-gray-300 rounded-xl font-bold text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold rounded-xl shadow-xs transition-colors">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModalCrearReporte() {
        document.getElementById('modal-crear-reporte').classList.remove('hidden');
    }
    function cerrarModalCrearReporte() {
        document.getElementById('modal-crear-reporte').classList.add('hidden');
    }

    function editarReporte(rep) {
        document.getElementById('form-editar-reporte').action = '/app-admin/reportes/' + rep.id;
        document.getElementById('edit_titulo').value = rep.titulo || '';
        document.getElementById('edit_subtitulo').value = rep.subtitulo || '';
        document.getElementById('edit_descripcion').value = rep.descripcion || '';
        document.getElementById('edit_orden').value = rep.orden || 50;
        document.getElementById('edit_categoria').value = rep.categoria || 'general';
        document.getElementById('edit_icono').value = rep.icono || 'cubes';
        document.getElementById('edit_activo').checked = rep.activo ? true : false;

        const vars = rep.variables_requeridas || {};
        document.getElementById('edit_var_ubicacion').checked = !!vars.ubicacion_id;
        document.getElementById('edit_var_puesto').checked = !!vars.puesto;
        document.getElementById('edit_var_tipo').checked = !!vars.tipo_id;
        document.getElementById('edit_var_nro_lia').checked = !!vars.nro_lia;
        document.getElementById('edit_var_fechas').checked = !!vars.fechas;

        document.getElementById('modal-editar-reporte').classList.remove('hidden');
    }
    function cerrarModalEditarReporte() {
        document.getElementById('modal-editar-reporte').classList.add('hidden');
    }
</script>
@endsection
