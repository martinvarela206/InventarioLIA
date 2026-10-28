@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto py-4 space-y-4">
    <!-- Header Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('app_admin.index') }}" class="hover:text-figma-brand transition-colors">Gestión App</a>
                <span>/</span>
                <span class="text-figma-text-primary">Gestión de Reportes</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <span>Catálogo y Configuración de Reportes</span>
                <span class="text-[10px] bg-figma-bg-deep text-figma-brand font-mono font-bold px-2 py-0.5 rounded border border-figma-border-subtle">
                    {{ $reportes->where('activo', true)->count() }} activos
                </span>
            </h1>
            <p class="text-xs text-figma-text-secondary mt-0.5">
                Administra los reportes oficiales del LIA y crea nuevos reportes dinámicos basados en filtros del inventario.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reportes.index') }}" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span>Hub de Reportes</span>
            </a>
            <button onclick="abrirModalCrearReporte()" class="figma-btn-primary text-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nuevo Reporte</span>
            </button>
        </div>
    </div>

    <!-- Mensajes de feedback -->
    @if(session('success'))
        <div class="figma-card p-3 border-emerald-500/40 text-emerald-400 text-xs">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="figma-card p-3 border-rose-500/40 text-rose-400 text-xs">
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tabla de Reportes -->
    <div class="figma-panel overflow-hidden">
        <div class="p-3 border-b border-figma-border-subtle bg-figma-bg-deep flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <span class="font-bold text-figma-text-primary text-xs">
                Catálogo de Reportes Registrados ({{ $reportes->count() }})
            </span>
            <div class="flex items-center gap-3 text-[11px] font-mono text-figma-text-tertiary">
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-figma-brand inline-block"></span> Sistema: {{ $reportes->where('es_sistema', true)->count() }}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span> Personalizados: {{ $reportes->where('es_sistema', false)->count() }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-figma-bg-deep text-figma-text-secondary uppercase font-bold text-[10px] tracking-wider border-b border-figma-border-subtle">
                    <tr>
                        <th class="py-2.5 px-3 w-12 text-center font-mono">Ord</th>
                        <th class="py-2.5 px-3">Reporte / Identificador</th>
                        <th class="py-2.5 px-3">Categoría</th>
                        <th class="py-2.5 px-3">Variables Requeridas</th>
                        <th class="py-2.5 px-3">Filtros Fijos</th>
                        <th class="py-2.5 px-3 text-center">Estado</th>
                        <th class="py-2.5 px-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-figma-border-subtle">
                    @forelse($reportes as $rep)
                        <tr class="hover:bg-figma-bg-deep/50 transition-colors {{ !$rep->activo ? 'opacity-60' : '' }}">
                            <td class="py-2.5 px-3 text-center font-mono text-figma-text-tertiary">
                                {{ $rep->orden }}
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="flex items-start gap-2.5">
                                    <div class="w-7 h-7 rounded bg-figma-bg-deep border border-figma-border-subtle text-figma-text-primary flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
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
                                        <a href="{{ route('reportes.show', $rep->slug) }}" class="font-bold text-figma-text-primary hover:text-figma-brand transition-colors text-xs block">
                                            {{ $rep->titulo }}
                                        </a>
                                        <span class="text-[11px] text-figma-text-tertiary line-clamp-1">{{ $rep->subtitulo ?: $rep->descripcion }}</span>
                                        <span class="text-[10px] font-mono text-figma-text-tertiary">slug: {{ $rep->slug }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="space-y-1">
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-figma-bg-deep text-figma-text-secondary border border-figma-border-subtle">
                                        {{ $rep->categoria }}
                                    </span>
                                    <div>
                                        @if($rep->es_sistema)
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-figma-brand">
                                                🔒 Sistema
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-400">
                                                ⭐ Personalizado
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="flex flex-wrap gap-1">
                                    @if($rep->requiereUbicacion())
                                        <span class="px-1.5 py-0.5 bg-figma-bg-deep text-figma-text-secondary border border-figma-border-subtle rounded text-[10px]">🏢 Sala</span>
                                    @endif
                                    @if($rep->requiereTipo())
                                        <span class="px-1.5 py-0.5 bg-figma-bg-deep text-figma-text-secondary border border-figma-border-subtle rounded text-[10px]">🏷️ Tipo</span>
                                    @endif
                                    @if($rep->requiereNroLia())
                                        <span class="px-1.5 py-0.5 bg-figma-bg-deep text-figma-text-secondary border border-figma-border-subtle rounded text-[10px] font-mono">🔢 N° LIA</span>
                                    @endif
                                    @if($rep->requiereRangoFechas())
                                        <span class="px-1.5 py-0.5 bg-figma-bg-deep text-figma-text-secondary border border-figma-border-subtle rounded text-[10px]">📅 Fechas</span>
                                    @endif
                                    @if(!$rep->requiereUbicacion() && !$rep->requiereTipo() && !$rep->requiereNroLia() && !$rep->requiereRangoFechas())
                                        <span class="text-figma-text-tertiary italic text-[11px]">Directo</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-2.5 px-3">
                                @if(!empty($rep->filtros_fijos))
                                    <div class="space-y-0.5">
                                        @foreach($rep->filtros_fijos as $k => $v)
                                            @if(!empty($v))
                                                <div class="text-[10px] font-mono text-figma-text-secondary bg-figma-bg-deep px-1.5 py-0.5 rounded border border-figma-border-subtle inline-block">
                                                    <strong>{{ $k }}:</strong> {{ is_array($v) ? implode(',', $v) : $v }}
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-figma-text-tertiary italic text-[11px]">-</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <form action="{{ route('app_admin.reportes.toggle', $rep->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="cursor-pointer inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold transition-all {{ $rep->activo ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-figma-bg-deep text-figma-text-tertiary border border-figma-border-subtle' }}" title="Clic para alternar">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $rep->activo ? 'bg-emerald-400' : 'bg-gray-500' }}"></span>
                                        {{ $rep->activo ? 'Activo' : 'Inactivo' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-2.5 px-3 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('reportes.show', $rep->slug) }}" class="p-1 text-figma-text-secondary hover:text-figma-brand rounded transition-colors" title="Ver Reporte">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <button type="button" onclick="editarReporte({{ json_encode($rep) }})" class="p-1 text-figma-brand hover:text-figma-brand-hover rounded transition-colors" title="Editar">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    @if(!$rep->es_sistema)
                                        <form action="{{ route('app_admin.reportes.destroy', $rep->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar definitivamente este reporte personalizado?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 text-rose-400 hover:text-rose-300 rounded transition-colors" title="Eliminar">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-figma-text-tertiary italic text-xs">
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
    <div class="figma-panel max-w-2xl w-full p-5 space-y-3.5 max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="flex items-center justify-between pb-2 border-b border-figma-border-subtle">
            <h3 class="font-bold text-figma-text-primary text-sm flex items-center gap-2">
                <span>📊</span>
                <span>Nuevo Reporte Configurado</span>
            </h3>
            <button type="button" onclick="cerrarModalCrearReporte()" class="text-figma-text-tertiary hover:text-figma-text-primary leading-none text-base">✕</button>
        </div>

        <form action="{{ route('app_admin.reportes.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                <div class="md:col-span-2">
                    <label class="block font-bold text-figma-text-secondary uppercase text-[10px] mb-1">Título del Reporte <span class="text-rose-400">*</span></label>
                    <input type="text" name="titulo" required placeholder="Ej. Computadoras en Sala A" class="figma-input w-full px-2.5 py-1.5 text-xs font-semibold">
                </div>
                <div>
                    <label class="block font-bold text-figma-text-secondary uppercase text-[10px] mb-1">Subtítulo / Bajada</label>
                    <input type="text" name="subtitulo" placeholder="Ej. Listado oficial de puestos..." class="figma-input w-full px-2.5 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block font-bold text-figma-text-secondary uppercase text-[10px] mb-1">Orden en el Menú</label>
                    <input type="number" name="orden" value="50" class="figma-input w-full px-2.5 py-1.5 text-xs font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                <div>
                    <label class="block font-bold text-figma-text-secondary uppercase text-[10px] mb-1">Categoría</label>
                    <select name="categoria" class="figma-input w-full px-2 py-1.5 text-xs">
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
                    <label class="block font-bold text-figma-text-secondary uppercase text-[10px] mb-1">Icono</label>
                    <select name="icono" class="figma-input w-full px-2 py-1.5 text-xs">
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
                <label class="block font-bold text-figma-text-secondary uppercase text-[10px] mb-1">Descripción Detallada</label>
                <textarea name="descripcion" rows="2" placeholder="Explica la finalidad del reporte..." class="figma-input w-full px-2.5 py-1.5 text-xs"></textarea>
            </div>

            <!-- Filtros Fijos -->
            <div class="figma-card p-3 space-y-2">
                <span class="block font-bold text-figma-text-primary text-[11px]">📌 Filtros Fijos Predefinidos (Opcional):</span>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                    <div>
                        <label class="block text-figma-text-tertiary text-[10px] font-semibold mb-0.5">Tipo de Activo:</label>
                        <select name="filtros_fijos[tipo_id]" class="figma-input w-full px-2 py-1 text-xs">
                            <option value="">-- Todos --</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo->id }}">{{ $tipo->tipo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-figma-text-tertiary text-[10px] font-semibold mb-0.5">Sala / Ubicación:</label>
                        <select name="filtros_fijos[ubicacion_id]" class="figma-input w-full px-2 py-1 text-xs">
                            <option value="">-- Todas --</option>
                            @foreach($ubicaciones as $ub)
                                <option value="{{ $ub->id }}">{{ $ub->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-figma-text-tertiary text-[10px] font-semibold mb-0.5">Puesto:</label>
                        <input type="text" name="filtros_fijos[puesto]" placeholder="Ej. Puesto 04" class="figma-input w-full px-2 py-1 text-xs">
                    </div>
                    <div>
                        <label class="block text-figma-text-tertiary text-[10px] font-semibold mb-0.5">Situación:</label>
                        <select name="filtros_fijos[situacion]" class="figma-input w-full px-2 py-1 text-xs">
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
            <div class="figma-card p-3 space-y-2">
                <span class="block font-bold text-figma-text-primary text-[11px]">⚙️ Variables dinámicas que el usuario elegirá:</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-figma-text-secondary">
                    <label class="flex items-center gap-2 cursor-pointer font-medium">
                        <input type="checkbox" name="variables_requeridas[ubicacion_id]" value="1" class="rounded text-figma-brand">
                        <span>Selección de <strong>Sala / Armario</strong></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium">
                        <input type="checkbox" name="variables_requeridas[puesto]" value="1" class="rounded text-figma-brand">
                        <span>Selección de <strong>Puesto de Trabajo</strong></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium">
                        <input type="checkbox" name="variables_requeridas[tipo_id]" value="1" class="rounded text-figma-brand">
                        <span>Selección de <strong>Tipo de Activo</strong></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium">
                        <input type="checkbox" name="variables_requeridas[nro_lia]" value="1" class="rounded text-figma-brand">
                        <span>Ingreso de <strong>N° LIA / Activo</strong></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium">
                        <input type="checkbox" name="variables_requeridas[fechas]" value="1" class="rounded text-figma-brand">
                        <span>Rango de <strong>Fechas (Desde/Hasta)</strong></span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="activo" id="crear_activo" value="1" checked class="rounded text-figma-brand">
                <label for="crear_activo" class="font-bold text-figma-text-primary">Activo (visible en el Catálogo de Reportes)</label>
            </div>

            <div class="pt-3 border-t border-figma-border-subtle flex items-center justify-end gap-2">
                <button type="button" onclick="cerrarModalCrearReporte()" class="figma-btn-secondary text-xs">
                    Cancelar
                </button>
                <button type="submit" class="figma-btn-primary text-xs">
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
    <div class="figma-panel max-w-2xl w-full p-5 space-y-3.5 max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="flex items-center justify-between pb-2 border-b border-figma-border-subtle">
            <h3 class="font-bold text-figma-text-primary text-sm flex items-center gap-2">
                <span>✏️</span>
                <span>Editar Configuración de Reporte</span>
            </h3>
            <button type="button" onclick="cerrarModalEditarReporte()" class="text-figma-text-tertiary hover:text-figma-text-primary leading-none text-base">✕</button>
        </div>

        <form id="form-editar-reporte" action="" method="POST" class="space-y-3 text-xs">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                <div class="md:col-span-2">
                    <label class="block font-bold text-figma-text-secondary uppercase text-[10px] mb-1">Título del Reporte <span class="text-rose-400">*</span></label>
                    <input type="text" name="titulo" id="edit_titulo" required class="figma-input w-full px-2.5 py-1.5 text-xs font-semibold">
                </div>
                <div>
                    <label class="block font-bold text-figma-text-secondary uppercase text-[10px] mb-1">Subtítulo / Bajada</label>
                    <input type="text" name="subtitulo" id="edit_subtitulo" class="figma-input w-full px-2.5 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block font-bold text-figma-text-secondary uppercase text-[10px] mb-1">Orden en el Menú</label>
                    <input type="number" name="orden" id="edit_orden" class="figma-input w-full px-2.5 py-1.5 text-xs font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                <div>
                    <label class="block font-bold text-figma-text-secondary uppercase text-[10px] mb-1">Categoría</label>
                    <select name="categoria" id="edit_categoria" class="figma-input w-full px-2 py-1.5 text-xs">
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
                    <label class="block font-bold text-figma-text-secondary uppercase text-[10px] mb-1">Icono</label>
                    <select name="icono" id="edit_icono" class="figma-input w-full px-2 py-1.5 text-xs">
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
                <label class="block font-bold text-figma-text-secondary uppercase text-[10px] mb-1">Descripción</label>
                <textarea name="descripcion" id="edit_descripcion" rows="2" class="figma-input w-full px-2.5 py-1.5 text-xs"></textarea>
            </div>

            <!-- Variables Requeridas -->
            <div class="figma-card p-3 space-y-2">
                <span class="block font-bold text-figma-text-primary text-[11px]">⚙️ Variables dinámicas requeridas:</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-figma-text-secondary">
                    <label class="flex items-center gap-2 cursor-pointer font-medium">
                        <input type="checkbox" name="variables_requeridas[ubicacion_id]" id="edit_var_ubicacion" value="1" class="rounded text-figma-brand">
                        <span>Selección de <strong>Sala / Armario</strong></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium">
                        <input type="checkbox" name="variables_requeridas[puesto]" id="edit_var_puesto" value="1" class="rounded text-figma-brand">
                        <span>Selección de <strong>Puesto de Trabajo</strong></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium">
                        <input type="checkbox" name="variables_requeridas[tipo_id]" id="edit_var_tipo" value="1" class="rounded text-figma-brand">
                        <span>Selección de <strong>Tipo de Activo</strong></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium">
                        <input type="checkbox" name="variables_requeridas[nro_lia]" id="edit_var_nro_lia" value="1" class="rounded text-figma-brand">
                        <span>Ingreso de <strong>N° LIA / Activo</strong></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium">
                        <input type="checkbox" name="variables_requeridas[fechas]" id="edit_var_fechas" value="1" class="rounded text-figma-brand">
                        <span>Rango de <strong>Fechas (Desde/Hasta)</strong></span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="activo" id="edit_activo" value="1" class="rounded text-figma-brand">
                <label for="edit_activo" class="font-bold text-figma-text-primary">Activo (visible en el Catálogo)</label>
            </div>

            <div class="pt-3 border-t border-figma-border-subtle flex items-center justify-end gap-2">
                <button type="button" onclick="cerrarModalEditarReporte()" class="figma-btn-secondary text-xs">
                    Cancelar
                </button>
                <button type="submit" class="figma-btn-primary text-xs">
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
