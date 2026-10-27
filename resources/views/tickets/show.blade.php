@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                <a href="{{ route('dashboard.index') }}" class="hover:text-amber-600 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('tickets.index') }}" class="hover:text-amber-600 transition-colors">Tickets</a>
                <span>/</span>
                <span class="text-gray-900">Ticket #{{ $ticket->id }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                    <span>Ticket #{{ $ticket->id }}</span>
                </h1>
                
                @if($ticket->estado_ticket === 'resuelto')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        Resuelto / Finalizado
                    </span>
                @elseif($ticket->estado_ticket === 'baja')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                        Declarado Irreparable (Baja Patrimonial)
                    </span>
                @elseif($ticket->estado_ticket === 'solucionado')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">
                        Solución Técnica Registrada (Pendiente de Cierre)
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 animate-pulse">
                        Abierto / En Taller de Reparaciones
                    </span>
                @endif
            </div>
        </div>
        <div>
            <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-bold px-4 py-2 rounded-xl text-sm transition-all shadow-sm">
                ← Volver a Bandeja
            </a>
        </div>
    </div>

    <!-- Dual Pane Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Columna Izquierda: Activo, Falla y Fotos (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Ficha del Activo -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Activo Involucrado</h2>
                    <a href="{{ route('elementos.show', $ticket->elemento_id) }}" class="text-xs font-bold text-amber-600 hover:text-amber-800 flex items-center gap-1">
                        Ver Ficha Completa →
                    </a>
                </div>

                <div class="flex items-start gap-3">
                    <div class="w-12 h-12 bg-amber-100 text-amber-800 rounded-xl flex items-center justify-center font-black text-sm shrink-0">
                        {{ strtoupper(substr($ticket->elemento?->tipo?->nombre ?? 'LIA', 0, 3)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-black text-gray-900">{{ $ticket->elemento?->nro_lia ?: 'Sin Nro LIA' }}</span>
                            @if($ticket->elemento?->nro_unsj)
                                <span class="text-xs font-bold text-gray-600 bg-gray-100 px-2 py-0.5 rounded">UNSJ: {{ $ticket->elemento->nro_unsj }}</span>
                            @endif
                        </div>
                        <div class="text-xs font-semibold text-gray-500 mt-0.5">
                            {{ ucfirst($ticket->elemento?->tipo?->nombre ?? 'N/A') }} • <span class="text-gray-900">{{ $ticket->elemento?->marca }}</span> {{ $ticket->elemento?->modelo }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-100 text-xs">
                    <div>
                        <span class="text-gray-400 block font-medium">Ubicación Actual</span>
                        <span class="font-bold text-gray-800">{{ $ticket->ubicacion?->nombre ?? 'Depósito' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block font-medium">Puesto</span>
                        <span class="font-bold text-gray-800">{{ $ticket->puesto ?: ($ticket->elemento?->puesto ?: 'S/P') }}</span>
                    </div>
                </div>
            </div>

            <!-- Falla Reportada -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-3">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-2">Diagnóstico Inicial de la Falla</h2>
                <div class="bg-amber-50/50 border border-amber-200/60 rounded-xl p-4 text-sm text-gray-900 leading-relaxed font-medium">
                    {{ $ticket->comentario }}
                </div>
                <div class="flex items-center justify-between text-xs text-gray-500 pt-1">
                    <span>Informante: <strong class="text-gray-800">{{ $ticket->solicitante ?: ($ticket->usuario?->nombre ?? 'Sistema') }}</strong></span>
                    <span>Fecha: <strong>{{ $ticket->fecha ? $ticket->fecha->format('d/m/Y H:i') : '-' }}</strong></span>
                </div>
            </div>

            <!-- Áreas Multidisciplinarias Involucradas (Acumulativas) -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                    <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        <span>Áreas Involucradas (Multidisciplinario)</span>
                    </h2>
                </div>

                @if($ticket->areas->isEmpty())
                    <p class="text-xs text-gray-400 italic">No hay áreas específicas vinculadas a este ticket.</p>
                @else
                    <div class="flex flex-wrap items-center gap-1.5">
                        @foreach($ticket->areas as $ar)
                            <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-black border shadow-2xs"
                                 style="background-color: {{ $ar->color ? $ar->color . '20' : '#f3f4f6' }}; color: {{ $ar->color ?: '#374151' }}; border-color: {{ $ar->color ? $ar->color . '60' : '#d1d5db' }};">
                                <span>{{ $ar->nombre }}</span>
                                @if($ar->pivot->created_at)
                                    <span class="text-[10px] opacity-75 font-normal">({{ \Carbon\Carbon::parse($ar->pivot->created_at)->format('d/m H:i') }})</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Vincular Área Adicional (Acumulativa) -->
                @if($ticket->esTicketAbierto())
                    <form action="{{ route('tickets.agregarArea', $ticket->id) }}" method="POST" class="pt-2 border-t border-gray-100 flex items-center gap-2">
                        @csrf
                        <select name="area_id" required class="flex-1 bg-gray-50 border border-gray-300 rounded-xl px-2.5 py-1.5 text-xs font-semibold text-gray-800 focus:bg-white focus:ring-2 focus:ring-amber-400">
                            <option value="">+ Sumar Área...</option>
                            @foreach($areas as $ar)
                                @if(!$ticket->areas->contains('id', $ar->id))
                                    <option value="{{ $ar->id }}">{{ $ar->nombre }}</option>
                                @endif
                            @endforeach
                        </select>
                        <button type="submit" class="bg-gray-900 hover:bg-black text-white px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0">
                            Sumar
                        </button>
                    </form>
                @endif
            </div>

            <!-- Evidencia Fotográfica -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                    <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Evidencia Fotográfica ({{ $ticket->adjuntos->count() }})</h2>
                </div>

                @if($ticket->adjuntos->isEmpty())
                    <p class="text-xs text-gray-400 italic">No se adjuntaron fotos al momento del reporte.</p>
                @else
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($ticket->adjuntos as $adj)
                            <a href="{{ $adj->url }}" target="_blank" title="Ver foto en tamaño original" class="group relative rounded-xl overflow-hidden border border-gray-200 aspect-video bg-gray-100 block shadow-2xs hover:shadow-md transition-all">
                                <img src="{{ $adj->url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold">
                                    Ampliar ↗
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Columna Derecha: Contexto y Resolución (7 cols) -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Contexto Operativo: Sala previa y Último Préstamo -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-4">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-2">Trazabilidad Operativa Previa</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <!-- Sala Previa al Ingreso -->
                    <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-200">
                        <span class="text-gray-500 font-bold block mb-1">Sala Previa al Ingreso a Taller</span>
                        @if($movPrevio)
                            <p class="font-black text-gray-900 text-sm">{{ $movPrevio->ubicacion?->nombre ?? 'Sin asignar' }}</p>
                            <p class="text-gray-600 mt-0.5">Puesto: <strong class="text-gray-800">{{ $movPrevio->puesto ?: 'S/P' }}</strong></p>
                            <p class="text-gray-400 text-[11px] mt-1">{{ $movPrevio->fecha ? $movPrevio->fecha->format('d/m/Y') : '' }} ({{ $movPrevio->comentario ?: 'Movimiento regular' }})</p>
                        @else
                            <p class="text-gray-500 italic">No hay registros de movimientos previos.</p>
                        @endif
                    </div>

                    <!-- Último Préstamo Externo -->
                    <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-200">
                        <span class="text-gray-500 font-bold block mb-1">Último Préstamo Registrado</span>
                        @if($ultimoPrestamo)
                            <p class="font-black text-gray-900 text-sm">{{ $ultimoPrestamo->solicitante ?: 'Docente externo' }}</p>
                            <p class="text-gray-600 mt-0.5">
                                Salida: {{ $ultimoPrestamo->fecha_prestamo ? Carbon\Carbon::parse($ultimoPrestamo->fecha_prestamo)->format('d/m/Y') : '-' }} • 
                                @if($ultimoPrestamo->fecha_devolucion)
                                    Retorno: {{ Carbon\Carbon::parse($ultimoPrestamo->fecha_devolucion)->format('d/m/Y') }}
                                @else
                                    <span class="text-amber-700 font-bold">Activo</span>
                                @endif
                            </p>
                        @else
                            <p class="text-gray-500 italic">El equipo nunca ha sido cedido en préstamo.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Resoluciones Previas si existen -->
            @if($ticket->resoluciones->isNotEmpty())
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-4">
                    <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-2">Resolución Técnica Consolidada</h2>
                    @foreach($ticket->resoluciones as $res)
                        <div class="p-4 rounded-xl {{ $res->tipo_movimiento === 'baja' ? 'bg-rose-50 border border-rose-200' : 'bg-emerald-50 border border-emerald-200' }} space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-sm {{ $res->tipo_movimiento === 'baja' ? 'text-rose-900' : 'text-emerald-900' }}">
                                    {{ $res->tipo_movimiento === 'baja' ? 'Declarado Irreparable (Baja Patrimonial)' : 'Ticket Resuelto y Equipo Reasignado' }}
                                </span>
                                <span class="text-xs text-gray-500 font-medium">{{ $res->fecha ? $res->fecha->format('d/m/Y H:i') : '' }}</span>
                            </div>
                            <p class="text-xs text-gray-800 leading-relaxed font-medium">
                                {{ $res->comentario }}
                            </p>
                            <div class="text-xs text-gray-600 pt-1 flex flex-wrap items-center gap-3">
                                <span>Técnico Responsable: <strong class="text-gray-900">{{ $res->usuario?->nombre ?: 'Técnico LIA' }}</strong></span>
                                <span>•</span>
                                <span>Ubicación Asignada: <strong class="text-gray-900">{{ $res->ubicacion?->nombre ?? 'N/A' }} ({{ $res->puesto ?: 'S/P' }})</strong></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Bitácora de Discusión y Seguimiento del Ticket -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                    <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Bitácora y Mensajes de Seguimiento ({{ $ticket->comentarios->count() }})</span>
                    </h2>
                </div>

                @if($ticket->comentarios->isEmpty())
                    <p class="text-xs text-gray-400 italic py-2">No se han registrado mensajes ni notas técnicas intermedias en este ticket.</p>
                @else
                    <div class="space-y-3">
                        @foreach($ticket->comentarios as $cmt)
                            <div class="p-3.5 rounded-xl border text-xs {{ $cmt->tipo === 'solucion_propuesta' ? 'bg-blue-50/70 border-blue-200' : 'bg-gray-50 border-gray-200' }} space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-gray-900">{{ $cmt->usuario?->nombre ?: ($cmt->usuario?->email ?? 'Usuario') }}</span>
                                        @if($cmt->tipo === 'solucion_propuesta')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-100 text-blue-800 border border-blue-300">Diagnóstico / Solución</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-200 text-gray-700">Comentario</span>
                                        @endif
                                    </div>
                                    <span class="text-gray-400 text-[11px]">{{ $cmt->created_at ? $cmt->created_at->format('d/m/Y H:i') : '' }}</span>
                                </div>
                                <p class="text-gray-800 leading-relaxed font-medium whitespace-pre-line">{{ $cmt->mensaje }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Formulario de Respuesta / Comentario para todos los roles autorizados -->
                <form action="{{ route('tickets.comentar', $ticket->id) }}" method="POST" class="pt-2 border-t border-gray-100 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1">
                            Responder o Agregar Observación al Hilo
                        </label>
                        <textarea name="mensaje" rows="2" required placeholder="Escriba un comentario, aclaración de falla o respuesta técnica..."
                                  class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2 text-xs text-gray-900 focus:bg-white focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition-all"></textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-bold text-gray-500 whitespace-nowrap">+ Área adicional:</span>
                            <select name="nueva_area_id" class="bg-gray-50 border border-gray-200 rounded-lg px-2 py-1 text-xs text-gray-700">
                                <option value="">Sin nueva área</option>
                                @foreach($areas as $ar)
                                    @if(!$ticket->areas->contains('id', $ar->id))
                                        <option value="{{ $ar->id }}">{{ $ar->nombre }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="inline-flex items-center gap-1.5 bg-gray-900 hover:bg-black text-white font-bold text-xs py-2 px-4 rounded-xl transition-all shadow-sm self-end">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            <span>Enviar Respuesta</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Panel de Solución Técnica para SubTécnico (si el usuario es SubTec y no puede cerrar) -->
            @if($ticket->esTicketAbierto() && Auth::user()->canSolveTickets() && !Auth::user()->canCloseTickets())
                <div class="bg-white rounded-2xl border border-blue-300 shadow-sm p-6 space-y-4">
                    <div class="border-b border-gray-100 pb-3">
                        <h2 class="text-base font-black text-gray-900 flex items-center gap-2">
                            <span class="w-6 h-6 bg-blue-500 text-white rounded-lg flex items-center justify-center text-xs font-black">⚙</span>
                            Diagnóstico y Solución Técnica (SubTécnico)
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">Como SubTécnico, registre las tareas técnicas efectuadas. El cierre formal y la asignación física de sala serán concluidos por un Técnico.</p>
                    </div>

                    <form action="{{ route('tickets.solucionar', $ticket->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Solución Técnica Aplicada y Pruebas Efectuadas <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <textarea name="solucion" rows="3" required
                                      placeholder="Detalle el trabajo realizado: mantenimiento, reemplazo de componente, comprobación de booteo, etc."
                                      class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-all"></textarea>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-gray-600">+ Área involucrada:</span>
                                <select name="nueva_area_id" class="bg-gray-50 border border-gray-300 rounded-xl px-3 py-1.5 text-xs text-gray-700 font-medium">
                                    <option value="">Mantener áreas actuales</option>
                                    @foreach($areas as $ar)
                                        @if(!$ticket->areas->contains('id', $ar->id))
                                            <option value="{{ $ar->id }}">{{ $ar->nombre }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-extrabold px-5 py-2.5 rounded-xl text-sm transition-all shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Registrar Solución Técnica</span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Formulario de Resolución Formal (solo si usuario posee permisos de cierre: Técnico) -->
            @if($ticket->esTicketAbierto() && Auth::user()->canCloseTickets())
                <div class="bg-white rounded-2xl border border-amber-300 shadow-sm p-6 space-y-5">
                    <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-black text-gray-900 flex items-center gap-2">
                                <span class="w-6 h-6 bg-amber-500 text-black rounded-lg flex items-center justify-center text-xs font-black">✓</span>
                                Resolución Técnica del Ticket (Cierre Formal y Reasignación)
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">Registre la intervención técnica, confirme el destino físico o declare la baja del equipo.</p>
                        </div>
                    </div>

                    <form action="{{ route('tickets.resolver', $ticket->id) }}" method="POST" id="form_resolver_ticket" class="space-y-5">
                        @csrf
                        <input type="hidden" name="accion" id="accion_input" value="resolver">

                        <!-- Solución Técnica / Pruebas Realizadas -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Descripción de la Solución y Pruebas Técnicas Realizadas <span class="text-rose-500 font-bold">*</span>
                            </label>
                            <textarea name="solucion" rows="4" required
                                      placeholder="Detalle la solución aplicada: cambio de pasta térmica, reemplazo de fuente ATX de 500W, formateo y clonación a SSD, pruebas de memoria con MemTest (0 errores), etc."
                                      class="w-full bg-gray-50 border border-gray-300 rounded-xl px-4 py-3 text-sm text-gray-900 focus:bg-white focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition-all">{{ old('solucion') }}</textarea>
                            <span class="text-[11px] text-gray-400 mt-1 block">Este registro quedará asentado de forma inmutable en el historial cronológico del inventario.</span>
                        </div>

                        <!-- Confirmación de Ubicación Física -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Sala de Destino (Reincorporación)
                                </label>
                                <select name="ubicacion_id" class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-gray-900 focus:bg-white focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                                    @php
                                        $ubicacionSugerida = $movPrevio?->ubicacion_id ?: $ticket->ubicacion_id;
                                    @endphp
                                    @foreach($ubicaciones as $ub)
                                        <option value="{{ $ub->id }}" {{ old('ubicacion_id', $ubicacionSugerida) == $ub->id ? 'selected' : '' }}>
                                             {{ $ub->nombre }} {{ $movPrevio && $movPrevio->ubicacion_id == $ub->id ? '(Sala Previa)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-[11px] text-gray-400 mt-1 block">Heredada por defecto del movimiento previo al ingreso.</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Puesto de Destino
                                </label>
                                @php
                                    $puestoSugerido = $movPrevio?->puesto ?: ($ticket->puesto ?: $ticket->elemento?->puesto);
                                @endphp
                                <input type="text" name="puesto" value="{{ old('puesto', $puestoSugerido) }}" placeholder="Ej. Puesto 08, Rack 01..."
                                       class="w-full bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-gray-900 focus:bg-white focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                                <span class="text-[11px] text-gray-400 mt-1 block">Puesto o mesa donde el activo quedará prestando servicio.</span>
                            </div>
                        </div>

                        <!-- Botones de Acción Doble -->
                        <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                            <button type="button" onclick="confirmarBaja()" 
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white hover:bg-rose-50 text-rose-700 border border-rose-300 hover:border-rose-500 font-bold px-4 py-2.5 rounded-xl text-sm transition-all shadow-2xs">
                                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                Declarar Irreparable (Baja / Basura Tec.)
                            </button>

                            <button type="button" onclick="confirmarResolucion()"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold px-6 py-2.5 rounded-xl text-sm transition-all shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                Resolver Ticket y Asignar a Sala
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Notificación informativa para rol Revisor -->
            @if($ticket->esTicketAbierto() && !Auth::user()->canSolveTickets())
                <div class="bg-gray-50 rounded-2xl border border-gray-200 p-5 space-y-2">
                    <div class="flex items-center gap-2 text-xs font-bold text-gray-700 uppercase">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Perfil Revisor</span>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Su perfil le permite participar en la bitácora de mensajes y aportar detalles sobre la falla reportada. La solución técnica y el cierre formal con movimientos de inventario corresponden a los roles SubTécnico y Técnico.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function confirmarResolucion() {
        const form = document.getElementById('form_resolver_ticket');
        const solucion = form.querySelector('textarea[name="solucion"]').value.trim();

        if (solucion.length < 5) {
            alert('Por favor detalle la solución técnica y pruebas realizadas antes de resolver el ticket.');
            form.querySelector('textarea[name="solucion"]').focus();
            return;
        }

        document.getElementById('accion_input').value = 'resolver';
        if (confirm('¿Confirmar la resolución del ticket y el reingreso del activo a sala?')) {
            form.submit();
        }
    }

    function confirmarBaja() {
        const form = document.getElementById('form_resolver_ticket');
        const solucion = form.querySelector('textarea[name="solucion"]').value.trim();

        if (solucion.length < 5) {
            alert('Por favor detalle el motivo técnico por el cual el activo resulta irreparable.');
            form.querySelector('textarea[name="solucion"]').focus();
            return;
        }

        document.getElementById('accion_input').value = 'baja';
        if (confirm('ATENCIÓN: ¿Está seguro de declarar este equipo como IRREPARABLE? El activo pasará a estado de BAJA / BASURA TECNOLÓGICA en el inventario institucional.')) {
            form.submit();
        }
    }
</script>
@endsection
