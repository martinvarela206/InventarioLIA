@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Breadcrumb & Barra Superior (no-print) Quiet Chrome -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-figma-border-subtle pb-4 no-print">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-figma-text-tertiary uppercase tracking-wider mb-1">
                <a href="{{ route('dashboard.index') }}" class="hover:text-figma-text-primary transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('users.index') }}" class="hover:text-figma-text-primary transition-colors">Usuarios</a>
                <span>/</span>
                <span class="text-figma-text-primary font-bold">Informe de Actividad</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary tracking-tight flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-figma-brand/15 text-figma-brand flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                <span>Informe de Auditoría y Trazabilidad: {{ $user->nombre }}</span>
            </h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('users.index') }}" class="figma-btn-secondary">
                ← Volver a Usuarios
            </a>
            <button onclick="window.print()" class="figma-btn-primary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Imprimir Informe Oficial</span>
            </button>
        </div>
    </div>

    <!-- Membrete Institucional exclusivo para Impresión -->
    <div class="hidden print:block border-b-2 border-gray-900 pb-4 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-black uppercase text-gray-900 tracking-tight">Laboratorio de Informática Aplicada (LIA)</h1>
                <p class="text-xs font-bold text-gray-600">Facultad de Ciencias Exactas, Físicas y Naturales — UNSJ</p>
                <p class="text-[11px] text-gray-500">Informe de Trazabilidad, Auditoría y Participación Individual de Usuario</p>
            </div>
            <div class="text-right text-xs">
                <p class="font-bold text-gray-900">Fecha de Emisión: {{ now()->format('d/m/Y H:i') }}</p>
                <p class="text-gray-500">Generado por: {{ Auth::user()->nombre ?: Auth::user()->email }}</p>
            </div>
        </div>
    </div>

    <!-- Ficha de Identidad del Usuario -->
    <div class="figma-panel p-6 space-y-4 print:border-none print:shadow-none print:p-0">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-figma-border-subtle pb-4">
            <div class="flex items-start gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-figma-brand text-white font-mono font-bold text-lg flex items-center justify-center shrink-0 shadow-sm">
                    {{ strtoupper(substr($user->nombre, 0, 2)) }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-lg font-bold text-figma-text-primary">{{ $user->nombre }}</h2>
                        @if($user->esAnonimizado())
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-purple-500/15 text-purple-400 border border-purple-500/30">
                                Huella Anonimizada (Ley 25.326)
                            </span>
                        @elseif($user->isExPersonalMayor6Anios())
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                Plazo Legal Cumplido (> 6 años)
                            </span>
                        @elseif($user->activo)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                Cuenta Activa
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30">
                                Cuenta Inactiva
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-figma-text-tertiary font-mono mt-0.5">{{ $user->email }}</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-[11px] font-bold text-figma-text-tertiary uppercase tracking-wider mr-1">Roles:</span>
                @forelse($user->roles as $rol)
                    <span class="px-2 py-0.5 rounded text-xs font-mono font-semibold bg-figma-bg-deep border border-figma-border-subtle text-figma-text-primary">
                        {{ $rol->rol }}
                    </span>
                @empty
                    <span class="text-xs text-figma-text-tertiary italic">Sin roles activos</span>
                @endforelse
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
            <div class="figma-card p-3">
                <span class="text-[10px] text-figma-text-tertiary block uppercase font-bold">Cargo Actual</span>
                <span class="font-semibold text-figma-text-primary">{{ $user->cargo_actual ?: 'Sin cargo registrado' }}</span>
            </div>
            <div class="figma-card p-3">
                <span class="text-[10px] text-figma-text-tertiary block uppercase font-bold">Carrera / Área</span>
                <span class="font-semibold text-figma-text-primary">{{ $user->carrera ?: 'Sin carrera' }}</span>
            </div>
            <div class="figma-card p-3">
                <span class="text-[10px] text-figma-text-tertiary block uppercase font-bold">DNI</span>
                <span class="font-mono font-semibold text-figma-text-primary">{{ $user->dni ?: 'No informado' }}</span>
            </div>
            <div class="figma-card p-3">
                <span class="text-[10px] text-figma-text-tertiary block uppercase font-bold">Trayectorias</span>
                <span class="font-semibold text-figma-text-primary">{{ $user->trayectorias->count() }} periodos</span>
            </div>
        </div>
    </div>

    <!-- Métricas Resumen de Participación -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="figma-panel p-4 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-figma-text-tertiary uppercase tracking-wider block">Apartado 1: Tickets</span>
                <span class="text-2xl font-mono font-bold text-figma-text-primary mt-0.5 block">{{ $stats['total_tickets'] }}</span>
                <span class="text-[11px] text-figma-text-tertiary">Aperturas y soluciones</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-500/15 text-amber-400 border border-amber-500/30 flex items-center justify-center font-bold text-base">
                🎫
            </div>
        </div>

        <div class="figma-panel p-4 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-figma-text-tertiary uppercase tracking-wider block">Apartado 2: Reportes</span>
                <span class="text-2xl font-mono font-bold text-figma-text-primary mt-0.5 block">{{ $stats['total_reportes'] }}</span>
                <span class="text-[11px] text-figma-text-tertiary">Catálogos configurados</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-blue-500/15 text-blue-400 border border-blue-500/30 flex items-center justify-center font-bold text-base">
                📊
            </div>
        </div>

        <div class="figma-panel p-4 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-figma-text-tertiary uppercase tracking-wider block">Apartado 3: Movimientos</span>
                <span class="text-2xl font-mono font-bold text-figma-text-primary mt-0.5 block">{{ $stats['total_movimientos'] }}</span>
                <span class="text-[11px] text-figma-text-tertiary">Operaciones físicas</span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold text-base">
                🔄
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- APARTADO 1: TICKETS Y GESTIÓN TÉCNICA      -->
    <!-- ========================================== -->
    <div class="figma-panel p-5 space-y-4 print:border-none print:shadow-none print:p-0">
        <div class="flex items-center justify-between border-b border-figma-border-subtle pb-3">
            <h3 class="text-xs font-bold text-figma-text-primary uppercase tracking-wider flex items-center gap-2">
                <span class="w-5 h-5 bg-amber-500/20 text-amber-400 rounded-md flex items-center justify-center text-xs font-bold">1</span>
                <span>Apartado de Tickets y Soporte Técnico</span>
            </h3>
            <span class="text-xs font-mono text-figma-text-tertiary">{{ $stats['total_tickets'] }} participaciones</span>
        </div>

        @if($stats['total_tickets'] === 0)
            <div class="p-6 text-center text-xs text-figma-text-tertiary italic figma-card border-dashed">
                El usuario no posee participaciones registradas en tickets de soporte técnico.
            </div>
        @else
            <!-- 1.A Tickets Iniciados / Solicitados -->
            @if($ticketsCreados->isNotEmpty())
                <div class="space-y-2">
                    <h4 class="text-[11px] font-bold text-figma-text-tertiary uppercase tracking-wider">Tickets Iniciados / Reportados ({{ $ticketsCreados->count() }})</h4>
                    <div class="overflow-x-auto figma-panel border border-figma-border-subtle">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-figma-bg-deep border-b border-figma-border-subtle text-[10px] font-bold text-figma-text-secondary uppercase">
                                    <th class="py-2.5 px-3">Ticket</th>
                                    <th class="py-2.5 px-3">Activo</th>
                                    <th class="py-2.5 px-3">Falla / Motivo</th>
                                    <th class="py-2.5 px-3">Estado</th>
                                    <th class="py-2.5 px-3">Ubicación</th>
                                    <th class="py-2.5 px-3">Fecha</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-figma-border-subtle text-figma-text-secondary">
                                @foreach($ticketsCreados as $tc)
                                    <tr class="hover:bg-figma-bg-deep/40 transition-colors">
                                        <td class="py-2 px-3 font-mono font-bold text-figma-brand">#{{ $tc->id }}</td>
                                        <td class="py-2 px-3">
                                            <a href="{{ route('tickets.show', $tc->id) }}" class="font-mono font-bold text-figma-brand hover:underline">
                                                {{ $tc->elemento?->nro_lia ?: 'Sin LIA' }}
                                            </a>
                                            <div class="text-[10px] text-figma-text-tertiary">{{ $tc->elemento?->tipo?->nombre }} • {{ $tc->elemento?->marca }}</div>
                                        </td>
                                        <td class="py-2 px-3 max-w-xs truncate">{{ $tc->comentario }}</td>
                                        <td class="py-2 px-3 whitespace-nowrap">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase
                                                {{ $tc->estado_ticket === 'resuelto' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : '' }}
                                                {{ $tc->estado_ticket === 'solucionado' ? 'bg-blue-500/15 text-blue-400 border border-blue-500/30' : '' }}
                                                {{ $tc->estado_ticket === 'baja' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : '' }}
                                                {{ in_array($tc->estado_ticket, ['abierto', null]) ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : '' }}">
                                                {{ $tc->estado_ticket ?: 'Abierto' }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-3">{{ $tc->ubicacion?->nombre ?? 'Depósito' }} ({{ $tc->puesto ?: 'S/P' }})</td>
                                        <td class="py-2 px-3 font-mono text-[11px] whitespace-nowrap">{{ $tc->fecha ? $tc->fecha->format('d/m/Y H:i') : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- 1.B Intervenciones en Bitácora y Respuestas -->
            @if($ticketComentarios->isNotEmpty())
                <div class="space-y-2 pt-2">
                    <h4 class="text-[11px] font-bold text-figma-text-tertiary uppercase tracking-wider">Intervenciones y Respuestas Técnicas ({{ $ticketComentarios->count() }})</h4>
                    <div class="overflow-x-auto figma-panel border border-figma-border-subtle">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-figma-bg-deep border-b border-figma-border-subtle text-[10px] font-bold text-figma-text-secondary uppercase">
                                    <th class="py-2.5 px-3">Ticket #</th>
                                    <th class="py-2.5 px-3">Activo</th>
                                    <th class="py-2.5 px-3">Tipo de Intervención</th>
                                    <th class="py-2.5 px-3">Mensaje / Diagnóstico Asentado</th>
                                    <th class="py-2.5 px-3">Fecha y Hora</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-figma-border-subtle text-figma-text-secondary">
                                @foreach($ticketComentarios as $tcom)
                                    <tr class="hover:bg-figma-bg-deep/40 transition-colors">
                                        <td class="py-2 px-3 font-mono font-bold">
                                            <a href="{{ route('tickets.show', $tcom->ticket_id) }}" class="text-figma-brand hover:underline">
                                                #{{ $tcom->ticket_id }}
                                            </a>
                                        </td>
                                        <td class="py-2 px-3 font-medium">{{ $tcom->ticket?->elemento?->nro_lia ?: 'S/LIA' }} ({{ $tcom->ticket?->elemento?->tipo?->nombre }})</td>
                                        <td class="py-2 px-3 whitespace-nowrap">
                                            @if($tcom->tipo === 'solucion_propuesta')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-blue-500/15 text-blue-400 border border-blue-500/30">Solución Técnica</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-medium bg-figma-bg-deep text-figma-text-tertiary border border-figma-border-subtle">Comentario</span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-3 max-w-sm truncate">{{ $tcom->mensaje }}</td>
                                        <td class="py-2 px-3 font-mono text-[11px] whitespace-nowrap">{{ $tcom->created_at ? $tcom->created_at->format('d/m/Y H:i') : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- 1.C Cierres Formales y Resoluciones de Tickets -->
            @if($ticketResoluciones->isNotEmpty())
                <div class="space-y-2 pt-2">
                    <h4 class="text-[11px] font-bold text-figma-text-tertiary uppercase tracking-wider">Cierres Técnicos y Reasignaciones de Sala ({{ $ticketResoluciones->count() }})</h4>
                    <div class="overflow-x-auto figma-panel border border-figma-border-subtle">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-figma-bg-deep border-b border-figma-border-subtle text-[10px] font-bold text-figma-text-secondary uppercase">
                                    <th class="py-2.5 px-3">Ticket #</th>
                                    <th class="py-2.5 px-3">Activo</th>
                                    <th class="py-2.5 px-3">Acción Conclusiva</th>
                                    <th class="py-2.5 px-3">Destino Asignado</th>
                                    <th class="py-2.5 px-3">Detalle Técnico</th>
                                    <th class="py-2.5 px-3">Fecha</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-figma-border-subtle text-figma-text-secondary">
                                @foreach($ticketResoluciones as $res)
                                    <tr class="hover:bg-figma-bg-deep/40 transition-colors">
                                        <td class="py-2 px-3 font-mono font-bold text-figma-brand">#{{ $res->ticket_id }}</td>
                                        <td class="py-2 px-3 font-mono font-bold">{{ $res->elemento?->nro_lia ?: 'S/LIA' }}</td>
                                        <td class="py-2 px-3 whitespace-nowrap">
                                            @if($res->tipo_movimiento === 'baja')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30">Baja Patrimonial</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">Resuelto / Reasignado</span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-3">{{ $res->ubicacion?->nombre ?? 'N/A' }} ({{ $res->puesto ?: 'S/P' }})</td>
                                        <td class="py-2 px-3 max-w-xs truncate">{{ $res->comentario }}</td>
                                        <td class="py-2 px-3 font-mono text-[11px] whitespace-nowrap">{{ $res->fecha ? $res->fecha->format('d/m/Y H:i') : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif
    </div>

    <!-- ========================================== -->
    <!-- APARTADO 2: CATÁLOGO Y REPORTES            -->
    <!-- ========================================== -->
    <div class="figma-panel p-5 space-y-4 print:border-none print:shadow-none print:p-0">
        <div class="flex items-center justify-between border-b border-figma-border-subtle pb-3">
            <h3 class="text-xs font-bold text-figma-text-primary uppercase tracking-wider flex items-center gap-2">
                <span class="w-5 h-5 bg-blue-500/20 text-blue-400 rounded-md flex items-center justify-center text-xs font-bold">2</span>
                <span>Apartado de Configuración y Emisión de Reportes</span>
            </h3>
            <span class="text-xs font-mono text-figma-text-tertiary">{{ $stats['total_reportes'] }} reportes</span>
        </div>

        @if($reportesConfigurados->isEmpty())
            <div class="p-6 text-center text-xs text-figma-text-tertiary italic figma-card border-dashed">
                El usuario no registra reportes personalizados ni configuraciones de catálogos asignadas a su autoría.
            </div>
        @else
            <div class="overflow-x-auto figma-panel border border-figma-border-subtle">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-figma-bg-deep border-b border-figma-border-subtle text-[10px] font-bold text-figma-text-secondary uppercase">
                            <th class="py-2.5 px-3">Reporte Configurado</th>
                            <th class="py-2.5 px-3">Categoría</th>
                            <th class="py-2.5 px-3">Slug / Identificador</th>
                            <th class="py-2.5 px-3">Estado</th>
                            <th class="py-2.5 px-3">Última Actualización</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-figma-border-subtle text-figma-text-secondary">
                        @foreach($reportesConfigurados as $rep)
                            <tr class="hover:bg-figma-bg-deep/40 transition-colors">
                                <td class="py-2 px-3">
                                    <div class="font-bold text-figma-text-primary">{{ $rep->titulo }}</div>
                                    <div class="text-[10px] text-figma-text-tertiary">{{ $rep->subtitulo ?: $rep->descripcion }}</div>
                                </td>
                                <td class="py-2 px-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-medium bg-figma-bg-deep text-figma-text-tertiary border border-figma-border-subtle uppercase">{{ $rep->categoria ?: 'General' }}</span>
                                </td>
                                <td class="py-2 px-3 font-mono text-[11px] text-figma-text-tertiary">{{ $rep->slug }}</td>
                                <td class="py-2 px-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold {{ $rep->activo ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-figma-bg-deep text-figma-text-tertiary' }}">
                                        {{ $rep->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="py-2 px-3 font-mono text-[11px] whitespace-nowrap">{{ $rep->updated_at ? $rep->updated_at->format('d/m/Y') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- ========================================== -->
    <!-- APARTADO 3: MOVIMIENTOS DE INVENTARIO      -->
    <!-- ========================================== -->
    <div class="figma-panel p-5 space-y-4 print:border-none print:shadow-none print:p-0">
        <div class="flex items-center justify-between border-b border-figma-border-subtle pb-3">
            <h3 class="text-xs font-bold text-figma-text-primary uppercase tracking-wider flex items-center gap-2">
                <span class="w-5 h-5 bg-emerald-500/20 text-emerald-400 rounded-md flex items-center justify-center text-xs font-bold">3</span>
                <span>Apartado de Movimientos de Inventario y Operatoria Física</span>
            </h3>
            <span class="text-xs font-mono text-figma-text-tertiary">{{ $stats['total_movimientos'] }} operaciones</span>
        </div>

        @if($movimientos->isEmpty())
            <div class="p-6 text-center text-xs text-figma-text-tertiary italic figma-card border-dashed">
                El usuario no posee registros históricos de movimientos en inventario.
            </div>
        @else
            <div class="overflow-x-auto figma-panel border border-figma-border-subtle">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-figma-bg-deep border-b border-figma-border-subtle text-[10px] font-bold text-figma-text-secondary uppercase">
                            <th class="py-2.5 px-3">Mov #</th>
                            <th class="py-2.5 px-3">Tipo de Operación</th>
                            <th class="py-2.5 px-3">Activo Involucrado</th>
                            <th class="py-2.5 px-3">Ubicación y Puesto</th>
                            <th class="py-2.5 px-3">Fecha</th>
                            <th class="py-2.5 px-3">Solicitante</th>
                            <th class="py-2.5 px-3">Comentarios / Observaciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-figma-border-subtle text-figma-text-secondary">
                        @foreach($movimientos as $mov)
                            <tr class="hover:bg-figma-bg-deep/40 transition-colors">
                                <td class="py-2 px-3 font-mono font-bold text-figma-brand">#{{ $mov->id }}</td>
                                <td class="py-2 px-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase
                                        {{ $mov->tipo_movimiento === 'externo_prestamo' ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : '' }}
                                        {{ $mov->tipo_movimiento === 'interno' ? 'bg-blue-500/15 text-blue-400 border border-blue-500/30' : '' }}
                                        {{ $mov->tipo_movimiento === 'reparacion' ? 'bg-purple-500/15 text-purple-400 border border-purple-500/30' : '' }}
                                        {{ $mov->tipo_movimiento === 'baja' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : '' }}
                                        {{ !in_array($mov->tipo_movimiento, ['externo_prestamo', 'interno', 'reparacion', 'baja']) ? 'bg-figma-bg-deep text-figma-text-tertiary border border-figma-border-subtle' : '' }}">
                                        {{ str_replace('_', ' ', $mov->tipo_movimiento) }}
                                    </span>
                                </td>
                                <td class="py-2 px-3">
                                    <div class="font-mono font-bold text-figma-text-primary">{{ $mov->elemento?->nro_lia ?: 'S/LIA' }}</div>
                                    <div class="text-[10px] text-figma-text-tertiary">{{ $mov->elemento?->tipo?->nombre }} • {{ $mov->elemento?->marca }}</div>
                                </td>
                                <td class="py-2 px-3 font-medium">{{ $mov->ubicacion?->nombre ?? 'N/A' }} ({{ $mov->puesto ?: 'S/P' }})</td>
                                <td class="py-2 px-3 font-mono text-[11px] whitespace-nowrap">{{ $mov->fecha ? $mov->fecha->format('d/m/Y') : '-' }}</td>
                                <td class="py-2 px-3">{{ $mov->solicitante ?: '-' }}</td>
                                <td class="py-2 px-3 max-w-xs truncate">{{ $mov->comentario ?: 'Sin observaciones' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Pie de Firma Institucional para Impresión -->
    <div class="hidden print:block pt-12 mt-12 border-t border-gray-300">
        <div class="grid grid-cols-2 gap-12 text-center text-xs">
            <div>
                <div class="border-b border-gray-400 pb-1 mb-2"></div>
                <p class="font-bold text-gray-900">Firma del Responsable de Auditoría / User Admin</p>
                <p class="text-[11px] text-gray-500">Laboratorio de Informática Aplicada</p>
            </div>
            <div>
                <div class="border-b border-gray-400 pb-1 mb-2"></div>
                <p class="font-bold text-gray-900">Sello de Control y Conformidad Patrimonial</p>
                <p class="text-[11px] text-gray-500">Exactas — UNSJ</p>
            </div>
        </div>
    </div>
</div>
@endsection
