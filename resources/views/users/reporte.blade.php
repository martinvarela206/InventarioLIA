@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Breadcrumb & Barra Superior (no-print) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4 no-print">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                <a href="{{ route('dashboard.index') }}" class="hover:text-amber-600 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('users.index') }}" class="hover:text-amber-600 transition-colors">Usuarios</a>
                <span>/</span>
                <span class="text-gray-900">Informe de Actividad</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-blue-100 text-blue-700 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                <span>Informe de Auditoría y Trazabilidad: {{ $user->nombre }}</span>
            </h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-bold px-4 py-2 rounded-xl text-xs transition-all shadow-sm">
                ← Volver a Usuarios
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold px-4 py-2 rounded-xl text-xs transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
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
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4 print:border-none print:shadow-none print:p-0">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100 pb-4">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-400 to-amber-200 text-black font-black text-xl flex items-center justify-center shrink-0 shadow-2xs">
                    {{ strtoupper(substr($user->nombre, 0, 2)) }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-black text-gray-900">{{ $user->nombre }}</h2>
                        @if($user->esAnonimizado())
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-900 border border-purple-200">
                                Huella Anonimizada (Ley 25.326)
                            </span>
                        @elseif($user->isExPersonalMayor6Anios())
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                Plazo Legal Cumplido (> 6 años)
                            </span>
                        @elseif($user->activo)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Cuenta Activa
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                Cuenta Inactiva
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $user->email }}</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mr-1">Roles:</span>
                @forelse($user->roles as $rol)
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-gray-100 border border-gray-200 text-gray-800">
                        {{ $rol->rol }}
                    </span>
                @empty
                    <span class="text-xs text-gray-400 italic">Sin roles activos asignados</span>
                @endforelse
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200">
                <span class="text-gray-400 block font-medium">Cargo Actual</span>
                <span class="font-bold text-gray-900">{{ $user->cargo_actual ?: 'Sin cargo registrado' }}</span>
            </div>
            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200">
                <span class="text-gray-400 block font-medium">Carrera / Área</span>
                <span class="font-bold text-gray-900">{{ $user->carrera ?: 'Sin carrera' }}</span>
            </div>
            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200">
                <span class="text-gray-400 block font-medium">DNI</span>
                <span class="font-mono font-bold text-gray-900">{{ $user->dni ?: 'No informado / Anonimizado' }}</span>
            </div>
            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200">
                <span class="text-gray-400 block font-medium">Vínculos Históricos (Trayectorias)</span>
                <span class="font-bold text-gray-900">{{ $user->trayectorias->count() }} periodos registrados</span>
            </div>
        </div>
    </div>

    <!-- Métricas Resumen de Participación -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Apartado 1: Tickets</span>
                <span class="text-2xl font-black text-gray-900 mt-1 block">{{ $stats['total_tickets'] }}</span>
                <span class="text-xs text-gray-500">Aperturas, bitácora y soluciones</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">
                🎫
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Apartado 2: Reportes</span>
                <span class="text-2xl font-black text-gray-900 mt-1 block">{{ $stats['total_reportes'] }}</span>
                <span class="text-xs text-gray-500">Reportes creados y configurados</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                📊
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Apartado 3: Movimientos</span>
                <span class="text-2xl font-black text-gray-900 mt-1 block">{{ $stats['total_movimientos'] }}</span>
                <span class="text-xs text-gray-500">Operaciones físicas registradas</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg">
                🔄
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- APARTADO 1: TICKETS Y GESTIÓN TÉCNICA      -->
    <!-- ========================================== -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4 print:border-none print:shadow-none print:p-0">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-base font-black text-gray-900 flex items-center gap-2">
                <span class="w-6 h-6 bg-amber-500 text-black rounded-lg flex items-center justify-center text-xs font-black">1</span>
                <span>Apartado de Tickets y Soporte Técnico</span>
            </h3>
            <span class="text-xs font-bold text-gray-500">{{ $stats['total_tickets'] }} participaciones registradas</span>
        </div>

        @if($stats['total_tickets'] === 0)
            <div class="p-6 text-center text-xs text-gray-400 italic bg-gray-50 rounded-xl border border-dashed border-gray-200">
                El usuario no posee participaciones registradas en tickets de soporte técnico (sin aperturas, intervenciones diagnósticas ni resoluciones).
            </div>
        @else
            <!-- 1.A Tickets Iniciados / Solicitados -->
            @if($ticketsCreados->isNotEmpty())
                <div class="space-y-2">
                    <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Tickets Iniciados / Reportados ({{ $ticketsCreados->count() }})</h4>
                    <div class="overflow-x-auto border border-gray-200 rounded-xl">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 font-bold text-gray-500 uppercase">
                                    <th class="py-2.5 px-3">Ticket</th>
                                    <th class="py-2.5 px-3">Activo</th>
                                    <th class="py-2.5 px-3">Falla / Motivo</th>
                                    <th class="py-2.5 px-3">Estado</th>
                                    <th class="py-2.5 px-3">Ubicación</th>
                                    <th class="py-2.5 px-3">Fecha</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-800">
                                @foreach($ticketsCreados as $tc)
                                    <tr class="hover:bg-amber-50/40">
                                        <td class="py-2.5 px-3 font-mono font-bold text-gray-900">#{{ $tc->id }}</td>
                                        <td class="py-2.5 px-3">
                                            <a href="{{ route('tickets.show', $tc->id) }}" class="font-bold text-amber-600 hover:text-amber-800">
                                                {{ $tc->elemento?->nro_lia ?: 'Sin LIA' }}
                                            </a>
                                            <div class="text-[11px] text-gray-500">{{ $tc->elemento?->tipo?->nombre }} • {{ $tc->elemento?->marca }}</div>
                                        </td>
                                        <td class="py-2.5 px-3 max-w-xs truncate">{{ $tc->comentario }}</td>
                                        <td class="py-2.5 px-3 whitespace-nowrap">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                                {{ $tc->estado_ticket === 'resuelto' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                                {{ $tc->estado_ticket === 'solucionado' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $tc->estado_ticket === 'baja' ? 'bg-rose-100 text-rose-800' : '' }}
                                                {{ in_array($tc->estado_ticket, ['abierto', null]) ? 'bg-amber-100 text-amber-800' : '' }}">
                                                {{ $tc->estado_ticket ?: 'Abierto' }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3">{{ $tc->ubicacion?->nombre ?? 'Depósito' }} ({{ $tc->puesto ?: 'S/P' }})</td>
                                        <td class="py-2.5 px-3 font-mono whitespace-nowrap">{{ $tc->fecha ? $tc->fecha->format('d/m/Y H:i') : '-' }}</td>
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
                    <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Intervenciones y Respuestas Técnicas ({{ $ticketComentarios->count() }})</h4>
                    <div class="overflow-x-auto border border-gray-200 rounded-xl">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 font-bold text-gray-500 uppercase">
                                    <th class="py-2.5 px-3">Ticket #</th>
                                    <th class="py-2.5 px-3">Activo</th>
                                    <th class="py-2.5 px-3">Tipo de Intervención</th>
                                    <th class="py-2.5 px-3">Mensaje / Diagnóstico Asentado</th>
                                    <th class="py-2.5 px-3">Fecha y Hora</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-800">
                                @foreach($ticketComentarios as $tcom)
                                    <tr class="hover:bg-blue-50/40">
                                        <td class="py-2.5 px-3 font-mono font-bold">
                                            <a href="{{ route('tickets.show', $tcom->ticket_id) }}" class="text-blue-600 hover:text-blue-800">
                                                #{{ $tcom->ticket_id }}
                                            </a>
                                        </td>
                                        <td class="py-2.5 px-3 font-semibold">{{ $tcom->ticket?->elemento?->nro_lia ?: 'S/LIA' }} ({{ $tcom->ticket?->elemento?->tipo?->nombre }})</td>
                                        <td class="py-2.5 px-3 whitespace-nowrap">
                                            @if($tcom->tipo === 'solucion_propuesta')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-blue-100 text-blue-800 border border-blue-200">Solución Técnica</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700">Comentario / Nota</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-3 max-w-sm truncate">{{ $tcom->mensaje }}</td>
                                        <td class="py-2.5 px-3 font-mono whitespace-nowrap">{{ $tcom->created_at ? $tcom->created_at->format('d/m/Y H:i') : '-' }}</td>
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
                    <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Cierres Técnicos y Reasignaciones de Sala ({{ $ticketResoluciones->count() }})</h4>
                    <div class="overflow-x-auto border border-gray-200 rounded-xl">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 font-bold text-gray-500 uppercase">
                                    <th class="py-2.5 px-3">Ticket #</th>
                                    <th class="py-2.5 px-3">Activo</th>
                                    <th class="py-2.5 px-3">Acción Conclusiva</th>
                                    <th class="py-2.5 px-3">Destino Asignado</th>
                                    <th class="py-2.5 px-3">Detalle Técnico</th>
                                    <th class="py-2.5 px-3">Fecha</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-800">
                                @foreach($ticketResoluciones as $res)
                                    <tr class="hover:bg-emerald-50/40">
                                        <td class="py-2.5 px-3 font-mono font-bold">#{{ $res->ticket_id }}</td>
                                        <td class="py-2.5 px-3 font-bold">{{ $res->elemento?->nro_lia ?: 'S/LIA' }}</td>
                                        <td class="py-2.5 px-3 whitespace-nowrap">
                                            @if($res->tipo_movimiento === 'baja')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Baja Patrimonial</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Resuelto / Reasignado</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-3">{{ $res->ubicacion?->nombre ?? 'N/A' }} ({{ $res->puesto ?: 'S/P' }})</td>
                                        <td class="py-2.5 px-3 max-w-xs truncate">{{ $res->comentario }}</td>
                                        <td class="py-2.5 px-3 font-mono whitespace-nowrap">{{ $res->fecha ? $res->fecha->format('d/m/Y H:i') : '-' }}</td>
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
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4 print:border-none print:shadow-none print:p-0">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-base font-black text-gray-900 flex items-center gap-2">
                <span class="w-6 h-6 bg-blue-500 text-white rounded-lg flex items-center justify-center text-xs font-black">2</span>
                <span>Apartado de Configuración y Emisión de Reportes</span>
            </h3>
            <span class="text-xs font-bold text-gray-500">{{ $stats['total_reportes'] }} reportes configurados</span>
        </div>

        @if($reportesConfigurados->isEmpty())
            <div class="p-6 text-center text-xs text-gray-400 italic bg-gray-50 rounded-xl border border-dashed border-gray-200">
                El usuario no registra reportes personalizados ni configuraciones de catálogos asignadas a su autoría.
            </div>
        @else
            <div class="overflow-x-auto border border-gray-200 rounded-xl">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 font-bold text-gray-500 uppercase">
                            <th class="py-2.5 px-3">Reporte Configurado</th>
                            <th class="py-2.5 px-3">Categoría</th>
                            <th class="py-2.5 px-3">Slug / Identificador</th>
                            <th class="py-2.5 px-3">Estado</th>
                            <th class="py-2.5 px-3">Última Actualización</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-800">
                        @foreach($reportesConfigurados as $rep)
                            <tr class="hover:bg-blue-50/40">
                                <td class="py-2.5 px-3">
                                    <div class="font-bold text-gray-900">{{ $rep->titulo }}</div>
                                    <div class="text-[11px] text-gray-500">{{ $rep->subtitulo ?: $rep->descripcion }}</div>
                                </td>
                                <td class="py-2.5 px-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700 uppercase">{{ $rep->categoria ?: 'General' }}</span>
                                </td>
                                <td class="py-2.5 px-3 font-mono text-[11px] text-gray-600">{{ $rep->slug }}</td>
                                <td class="py-2.5 px-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $rep->activo ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $rep->activo ? 'Activo en Catálogo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 font-mono whitespace-nowrap">{{ $rep->updated_at ? $rep->updated_at->format('d/m/Y') : '-' }}</td>
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
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4 print:border-none print:shadow-none print:p-0">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <h3 class="text-base font-black text-gray-900 flex items-center gap-2">
                <span class="w-6 h-6 bg-emerald-600 text-white rounded-lg flex items-center justify-center text-xs font-black">3</span>
                <span>Apartado de Movimientos de Inventario y Operatoria Física</span>
            </h3>
            <span class="text-xs font-bold text-gray-500">{{ $stats['total_movimientos'] }} operaciones registradas</span>
        </div>

        @if($movimientos->isEmpty())
            <div class="p-6 text-center text-xs text-gray-400 italic bg-gray-50 rounded-xl border border-dashed border-gray-200">
                El usuario no posee registros históricos de movimientos en inventario (sin altas, traslados, préstamos ni devoluciones registradas o solicitadas).
            </div>
        @else
            <div class="overflow-x-auto border border-gray-200 rounded-xl">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 font-bold text-gray-500 uppercase">
                            <th class="py-2.5 px-3">Mov #</th>
                            <th class="py-2.5 px-3">Tipo de Operación</th>
                            <th class="py-2.5 px-3">Activo Involucrado</th>
                            <th class="py-2.5 px-3">Ubicación y Puesto</th>
                            <th class="py-2.5 px-3">Fecha</th>
                            <th class="py-2.5 px-3">Solicitante</th>
                            <th class="py-2.5 px-3">Comentarios / Observaciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-800">
                        @foreach($movimientos as $mov)
                            <tr class="hover:bg-emerald-50/40">
                                <td class="py-2.5 px-3 font-mono font-bold text-gray-900">#{{ $mov->id }}</td>
                                <td class="py-2.5 px-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                        {{ $mov->tipo_movimiento === 'externo_prestamo' ? 'bg-amber-100 text-amber-900' : '' }}
                                        {{ $mov->tipo_movimiento === 'interno' ? 'bg-blue-100 text-blue-900' : '' }}
                                        {{ $mov->tipo_movimiento === 'reparacion' ? 'bg-purple-100 text-purple-900' : '' }}
                                        {{ $mov->tipo_movimiento === 'baja' ? 'bg-rose-100 text-rose-900' : '' }}
                                        {{ !in_array($mov->tipo_movimiento, ['externo_prestamo', 'interno', 'reparacion', 'baja']) ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ str_replace('_', ' ', $mov->tipo_movimiento) }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3">
                                    <div class="font-bold text-gray-900">{{ $mov->elemento?->nro_lia ?: 'S/LIA' }}</div>
                                    <div class="text-[11px] text-gray-500">{{ $mov->elemento?->tipo?->nombre }} • {{ $mov->elemento?->marca }}</div>
                                </td>
                                <td class="py-2.5 px-3 font-medium">{{ $mov->ubicacion?->nombre ?? 'N/A' }} ({{ $mov->puesto ?: 'S/P' }})</td>
                                <td class="py-2.5 px-3 font-mono whitespace-nowrap">{{ $mov->fecha ? $mov->fecha->format('d/m/Y') : '-' }}</td>
                                <td class="py-2.5 px-3">{{ $mov->solicitante ?: '-' }}</td>
                                <td class="py-2.5 px-3 max-w-xs truncate">{{ $mov->comentario ?: 'Sin observaciones' }}</td>
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
