@extends('layouts.app')

@section('content')
<div class="max-w-[1360px] mx-auto px-2 space-y-4">
    <!-- Header Toolbar -->
    <div class="flex flex-col md:flex-row items-center justify-between gap-4 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('dashboard.index') }}" class="hover:text-figma-brand transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-figma-text-primary">Activos Patrimoniales</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <svg class="w-5 h-5 text-figma-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>Inventario y Catálogo de Activos</span>
                <span class="font-mono text-xs font-bold bg-figma-bg-deep text-figma-brand px-2 py-0.5 rounded border border-figma-border-subtle">
                    {{ $elementos->total() }} registros
                </span>
            </h1>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <!-- Botón Exportar CSV -->
            <a href="{{ route('elementos.exportCsv', request()->all()) }}" class="figma-btn-secondary" title="Descargar vista filtrada en CSV">
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Exportar CSV</span>
            </a>

            <!-- Botón Imprimir / PDF -->
            <a href="{{ route('elementos.imprimir', request()->all()) }}" target="_blank" class="figma-btn-secondary" title="Generar informe imprimible / PDF">
                <svg class="w-3.5 h-3.5 text-figma-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimir / PDF</span>
            </a>

            @if(Auth::user()->isAppAdmin())
                <!-- Botón Guardar Filtros como Reporte (app_admin) -->
                <button type="button" onclick="abrirModalGuardarReporte()" class="figma-btn-secondary" title="Guardar filtros activos como nuevo Reporte">
                    <svg class="w-3.5 h-3.5 text-figma-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    <span>Guardar Reporte</span>
                </button>
            @endif

            @if(Auth::user()->canManageElementos())
                <a href="{{ route('elementos.create') }}" class="figma-btn-primary">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Añadir Activo</span>
                </a>
            @endif
            @if(Auth::user()->canManageMovimientos())
                <a href="{{ route('movimientos.rapido') }}" class="figma-btn-secondary">
                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Movimiento Rápido</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Search & Multi-Filter Bar (Figma Studio Inspector Style) -->
    <div class="figma-panel p-3.5 space-y-3">
        <form action="{{ route('elementos.index') }}" method="GET" class="space-y-2.5">
            <div class="flex gap-2 items-center">
                <div class="relative flex-1">
                    <input type="text" name="search" placeholder="Buscar por LIA, UNSJ, marca, modelo, Nro de serie, puesto..." 
                        value="{{ request('search') }}" 
                        class="figma-input w-full pl-9 pr-3 py-2 text-xs font-medium" />
                    <svg class="w-4 h-4 text-figma-text-tertiary absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button type="submit" class="figma-btn-primary">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Buscar</span>
                </button>
                @if(request()->hasAny(['search', 'tipo', 'ubicacion', 'puesto', 'situacion', 'anio', 'gb', 'garantia']))
                    <a href="{{ route('elementos.index') }}" class="figma-btn-secondary" title="Limpiar todos los filtros">
                        <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Limpiar</span>
                    </a>
                @endif
            </div>

            <!-- Filters Pill Row -->
            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-figma-border-subtle text-xs">
                <span class="font-bold text-figma-text-tertiary text-[11px] uppercase tracking-wider mr-1">Filtros:</span>

                <!-- Filter Situación -->
                <select name="situacion" onchange="this.form.submit()" class="figma-input px-2.5 py-1 text-xs cursor-pointer">
                    <option value="">Situación: Todas</option>
                    <option value="operativo" {{ request('situacion') === 'operativo' ? 'selected' : '' }}>En Sala / Operativo</option>
                    <option value="prestado" {{ request('situacion') === 'prestado' ? 'selected' : '' }}>Prestados Activos</option>
                    <option value="reparacion" {{ request('situacion') === 'reparacion' ? 'selected' : '' }}>En Reparación / Ticket</option>
                    <option value="baja" {{ request('situacion') === 'baja' ? 'selected' : '' }}>Basura Tecnológica / De Baja</option>
                </select>

                <!-- Filter Tipo -->
                <select name="tipo" onchange="this.form.submit()" class="figma-input px-2.5 py-1 text-xs cursor-pointer">
                    <option value="">Todos los Tipos</option>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo->nombre }}" {{ request('tipo') === $tipo->nombre ? 'selected' : '' }}>
                            {{ ucfirst($tipo->nombre) }}
                        </option>
                    @endforeach
                </select>

                <!-- Filter Ubicación -->
                <select name="ubicacion" onchange="this.form.submit()" class="figma-input px-2.5 py-1 text-xs cursor-pointer">
                    <option value="">Todas las Salas</option>
                    <option value="prestamo_externo" {{ request('ubicacion') === 'prestamo_externo' ? 'selected' : '' }}>Préstamo Externo</option>
                    @foreach($ubicaciones as $ub)
                        <option value="{{ $ub->id }}" {{ request('ubicacion') == $ub->id ? 'selected' : '' }}>
                            {{ $ub->nombre }}
                        </option>
                    @endforeach
                </select>

                <!-- Filter Puesto -->
                <select name="puesto" onchange="this.form.submit()" class="figma-input px-2.5 py-1 text-xs cursor-pointer">
                    <option value="">Todos los Puestos</option>
                    @foreach($puestos as $p)
                        <option value="{{ $p }}" {{ request('puesto') === $p ? 'selected' : '' }}>
                            {{ $p }}
                        </option>
                    @endforeach
                </select>

                <!-- Filter Año Compra -->
                <select name="anio" onchange="this.form.submit()" class="figma-input px-2.5 py-1 text-xs cursor-pointer">
                    <option value="">Año de Compra: Todos</option>
                    @foreach($anios as $a)
                        <option value="{{ $a }}" {{ request('anio') == $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>

                <!-- Filter Garantía -->
                <select name="garantia" onchange="this.form.submit()" class="figma-input px-2.5 py-1 text-xs cursor-pointer">
                    <option value="">Garantía: Todas</option>
                    <option value="vigente" {{ request('garantia') === 'vigente' ? 'selected' : '' }}>Garantía Vigente</option>
                    <option value="vencida" {{ request('garantia') === 'vencida' ? 'selected' : '' }}>Garantía Vencida</option>
                    <option value="sin_datos" {{ request('garantia') === 'sin_datos' ? 'selected' : '' }}>Sin Datos</option>
                </select>

                <!-- Filter RAM / Capacidad -->
                <select name="gb" onchange="this.form.submit()" class="figma-input px-2.5 py-1 text-xs cursor-pointer">
                    <option value="">RAM / Capacidad: Todas</option>
                    <option value="4" {{ request('gb') == '4' ? 'selected' : '' }}>4 GB</option>
                    <option value="8" {{ request('gb') == '8' ? 'selected' : '' }}>8 GB</option>
                    <option value="16" {{ request('gb') == '16' ? 'selected' : '' }}>16 GB</option>
                    <option value="32" {{ request('gb') == '32' ? 'selected' : '' }}>32 GB</option>
                    <option value="120" {{ request('gb') == '120' ? 'selected' : '' }}>120 / 128 GB</option>
                    <option value="240" {{ request('gb') == '240' ? 'selected' : '' }}>240 / 256 GB</option>
                    <option value="480" {{ request('gb') == '480' ? 'selected' : '' }}>480 / 512 GB</option>
                    <option value="1000" {{ request('gb') == '1000' ? 'selected' : '' }}>1 TB (1000 GB)</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Assets Table (Figma Layers / Canvas Density) -->
    <div class="figma-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-figma-card/80 border-b border-figma-border-subtle text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider">
                        <th class="py-2.5 px-3.5">Identificadores</th>
                        <th class="py-2.5 px-3.5">Tipo / Categoría</th>
                        <th class="py-2.5 px-3.5">Marca y Modelo</th>
                        <th class="py-2.5 px-3.5">Especificaciones</th>
                        <th class="py-2.5 px-3.5">Ubicación y Puesto</th>
                        <th class="py-2.5 px-3.5">Situación</th>
                        <th class="py-2.5 px-3.5">Garantía / Adq.</th>
                        <th class="py-2.5 px-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-figma-border-subtle text-figma-text-secondary">
                    @forelse($elementos as $el)
                        <tr onclick="window.location='{{ route('elementos.show', $el->id) }}'" class="hover:bg-figma-hover/50 transition-colors cursor-pointer group">
                            <td class="py-2.5 px-3.5 whitespace-nowrap">
                                <span class="font-mono font-bold text-figma-brand hover:underline block">
                                    {{ $el->nro_lia ?: 'LIA: -' }}
                                </span>
                                <span class="font-mono text-[10px] text-figma-text-tertiary">
                                    UNSJ: {{ $el->nro_unsj ?: '-' }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3.5">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-figma-bg-deep text-figma-text-secondary border border-figma-border-subtle">
                                    {{ strtoupper($el->tipo?->nombre ?? 'Activo') }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3.5">
                                <div class="font-semibold text-figma-text-primary">{{ $el->marca ?: '-' }}</div>
                                <div class="text-[11px] text-figma-text-tertiary">{{ $el->modelo ?: '-' }}</div>
                            </td>
                            <td class="py-2.5 px-3.5">
                                <div class="max-w-xs text-[11px] text-figma-text-secondary">
                                    {!! $el->presenter()->especificacionesHtml() !!}
                                </div>
                            </td>
                            <td class="py-2.5 px-3.5">
                                <div class="font-medium text-figma-text-primary">{{ $el->presenter()->puestoUbicacion() }}</div>
                            </td>
                            <td class="py-2.5 px-3.5 whitespace-nowrap">
                                {!! $el->presenter()->statusBadge() !!}
                            </td>
                            <td class="py-2.5 px-3.5 whitespace-nowrap text-[11px]">
                                {!! $el->presenter()->garantiaBadge() !!}
                            </td>
                            <td class="py-2.5 px-3.5 text-right whitespace-nowrap" onclick="event.stopPropagation()">
                                <div class="flex items-center justify-end gap-1">
                                    @if(Auth::user()->canManageMovimientos())
                                        <a href="{{ route('movimientos.rapido', ['elemento_id' => $el->id]) }}" class="p-1 text-amber-500 hover:text-amber-400 hover:bg-figma-hover rounded transition-colors" title="Mover o prestar">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        </a>
                                    @endif
                                    @if(Auth::user()->canManageElementos())
                                        <a href="{{ route('elementos.edit', $el->id) }}" class="p-1 text-figma-brand hover:text-figma-brand-hover hover:bg-figma-hover rounded transition-colors" title="Editar ficha">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-figma-text-tertiary text-xs">
                                No se encontraron activos que coincidan con los criterios de búsqueda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($elementos->hasPages())
            <div class="p-3 border-t border-figma-border-subtle bg-figma-card/50 flex items-center justify-between">
                {{ $elementos->links() }}
            </div>
        @endif
    </div>
</div>

@if(Auth::user()->isAppAdmin())
    <!-- Modal Guardar como Reporte (Exclusivo app_admin) -->
    <div id="modal-guardar-reporte" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="figma-panel max-w-lg w-full p-5 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-figma-border-subtle">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-figma-brand/15 text-figma-brand flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    <div>
                        <h3 class="font-bold text-figma-text-primary text-sm">Guardar Vista como Reporte</h3>
                        <p class="text-[11px] text-figma-text-tertiary">Registra un nuevo reporte reutilizable en el Hub de Reportes</p>
                    </div>
                </div>
                <button type="button" onclick="cerrarModalGuardarReporte()" class="text-figma-text-tertiary hover:text-figma-text-primary text-sm font-bold">✕</button>
            </div>

            <form action="{{ route('app_admin.reportes.guardarDesdeInventario') }}" method="POST" class="space-y-3.5 text-xs">
                @csrf
                
                <div>
                    <label class="block font-bold text-figma-text-secondary uppercase tracking-wider text-[10px] mb-1">Título del Reporte <span class="text-rose-500">*</span></label>
                    <input type="text" name="titulo" required placeholder="Ej: Auditoría de Laptops en Sala 1" class="figma-input w-full px-3 py-2 text-xs font-semibold">
                </div>

                <div>
                    <label class="block font-bold text-figma-text-secondary uppercase tracking-wider text-[10px] mb-1">Subtítulo / Breve Descripción</label>
                    <input type="text" name="descripcion" placeholder="Ej: Control de activos filtrados del catálogo" class="figma-input w-full px-3 py-2 text-xs">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-figma-text-secondary uppercase tracking-wider text-[10px] mb-1">Categoría</label>
                        <select name="categoria" class="figma-input w-full px-3 py-2 text-xs">
                            <option value="inventario">Inventario General</option>
                            <option value="auditoria">Auditoría de Sala</option>
                            <option value="prestamos">Préstamos</option>
                            <option value="mantenimiento">Mantenimiento</option>
                            <option value="patrimonio">Patrimonio</option>
                            <option value="personalizado" selected>Personalizado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-figma-text-secondary uppercase tracking-wider text-[10px] mb-1">Icono</label>
                        <select name="icono" class="figma-input w-full px-3 py-2 text-xs">
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

                <!-- Resumen de filtros fijos capturados -->
                <div class="figma-card p-3">
                    <span class="block font-bold text-figma-text-primary text-[11px] mb-1.5">📌 Filtros Activos Capturados:</span>
                    <div class="flex flex-wrap gap-1.5 text-[10px]">
                        @if(request('search'))
                            <span class="bg-figma-bg-deep px-2 py-0.5 rounded border border-figma-border-subtle text-figma-text-secondary">Texto: <strong>{{ request('search') }}</strong></span>
                            <input type="hidden" name="filtros_fijos[search]" value="{{ request('search') }}">
                        @endif
                        @if(request('situacion'))
                            <span class="bg-figma-bg-deep px-2 py-0.5 rounded border border-figma-border-subtle text-figma-text-secondary">Situación: <strong>{{ request('situacion') }}</strong></span>
                            <input type="hidden" name="filtros_fijos[situacion]" value="{{ request('situacion') }}">
                        @endif
                        @if(request('tipo'))
                            <span class="bg-figma-bg-deep px-2 py-0.5 rounded border border-figma-border-subtle text-figma-text-secondary">Tipo: <strong>{{ request('tipo') }}</strong></span>
                            <input type="hidden" name="filtros_fijos[tipo]" value="{{ request('tipo') }}">
                        @endif
                        @if(request('ubicacion'))
                            <span class="bg-figma-bg-deep px-2 py-0.5 rounded border border-figma-border-subtle text-figma-text-secondary">Sala: <strong>{{ request('ubicacion') }}</strong></span>
                            <input type="hidden" name="filtros_fijos[ubicacion]" value="{{ request('ubicacion') }}">
                        @endif
                        @if(request('puesto'))
                            <span class="bg-figma-bg-deep px-2 py-0.5 rounded border border-figma-border-subtle text-figma-text-secondary">Puesto: <strong>{{ request('puesto') }}</strong></span>
                            <input type="hidden" name="filtros_fijos[puesto]" value="{{ request('puesto') }}">
                        @endif
                        @if(request('anio'))
                            <span class="bg-figma-bg-deep px-2 py-0.5 rounded border border-figma-border-subtle text-figma-text-secondary">Año: <strong>{{ request('anio') }}</strong></span>
                            <input type="hidden" name="filtros_fijos[anio]" value="{{ request('anio') }}">
                        @endif
                        @if(!request()->hasAny(['search', 'situacion', 'tipo', 'ubicacion', 'puesto', 'anio']))
                            <span class="text-figma-text-tertiary italic">Sin filtros específicos aplicados (abarca todo el inventario).</span>
                        @endif
                    </div>
                </div>

                <!-- Variables de Entrada Dinámicas -->
                <div class="figma-card p-3 space-y-1.5">
                    <span class="block font-bold text-figma-text-primary text-[11px] mb-1">⚙️ Variables de entrada requeridas al emitir:</span>
                    <p class="text-[10px] text-figma-text-tertiary mb-2">Marca si deseas que el usuario deba seleccionar alguna de estas variables:</p>
                    <div class="space-y-1 text-figma-text-secondary">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="variables_requeridas[ubicacion_id]" value="1" class="rounded text-figma-brand focus:ring-figma-brand">
                            <span>El usuario seleccionará la <strong>Sala / Armario</strong></span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="variables_requeridas[puesto]" value="1" class="rounded text-figma-brand focus:ring-figma-brand">
                            <span>El usuario seleccionará el <strong>Puesto de Trabajo</strong></span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="variables_requeridas[tipo_id]" value="1" class="rounded text-figma-brand focus:ring-figma-brand">
                            <span>El usuario seleccionará el <strong>Tipo de Activo</strong></span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="variables_requeridas[nro_lia]" value="1" class="rounded text-figma-brand focus:ring-figma-brand">
                            <span>El usuario ingresará un <strong>N° LIA / Activo específico</strong></span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="variables_requeridas[fechas]" value="1" class="rounded text-figma-brand focus:ring-figma-brand">
                            <span>El usuario definirá un <strong>Rango de Fechas (Desde / Hasta)</strong></span>
                        </label>
                    </div>
                </div>

                <div class="pt-3 border-t border-figma-border-subtle flex items-center justify-end gap-2">
                    <button type="button" onclick="cerrarModalGuardarReporte()" class="figma-btn-secondary">
                        Cancelar
                    </button>
                    <button type="submit" class="figma-btn-primary">
                        Guardar Reporte
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModalGuardarReporte() {
            document.getElementById('modal-guardar-reporte').classList.remove('hidden');
        }
        function cerrarModalGuardarReporte() {
            document.getElementById('modal-guardar-reporte').classList.add('hidden');
        }
    </script>
@endif
@endsection
