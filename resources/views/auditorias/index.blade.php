@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                <a href="{{ route('dashboard.index') }}" class="hover:text-amber-600 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-gray-900">Auditorías</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-indigo-100 text-indigo-700 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </span>
                <span>Archivo Histórico de Auditorías Físicas</span>
            </h1>
            <p class="text-xs text-gray-500 mt-1">
                Consulta y trazabilidad legal de relevamientos de inventario in situ en laboratorios y salas del LIA.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('auditorias.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all hover:shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nueva Auditoría de Ubicación</span>
            </a>
        </div>
    </div>

    <!-- Barra de Filtros Avanzada -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
        <form method="GET" action="{{ route('auditorias.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Sala / Laboratorio</label>
                <select name="ubicacion_id" class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                    <option value="">Todas las salas</option>
                    @foreach($ubicaciones as $ub)
                        <option value="{{ $ub->id }}" {{ request('ubicacion_id') == $ub->id ? 'selected' : '' }}>
                            {{ $ub->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Auditor Responsable</label>
                <select name="user_id" class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                    <option value="">Todos los auditores</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Estado</label>
                <select name="estado" class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                    <option value="">Todos los estados</option>
                    <option value="en_progreso" {{ request('estado') === 'en_progreso' ? 'selected' : '' }}>En Progreso</option>
                    <option value="finalizada" {{ request('estado') === 'finalizada' ? 'selected' : '' }}>Finalizada</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Fecha Desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-gray-600 uppercase mb-1">Fecha Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-gray-900 hover:bg-black text-white text-xs font-bold py-2 px-3 rounded-lg transition-colors">
                    Filtrar
                </button>
                <a href="{{ route('auditorias.index') }}" class="p-2 border border-gray-300 hover:bg-gray-100 rounded-lg text-gray-600 text-xs text-center" title="Limpiar filtros">
                    Limpiar
                </a>
            </div>
            
            <div class="col-span-full pt-1">
                <label class="inline-flex items-center gap-2 cursor-pointer text-xs text-gray-700">
                    <input type="checkbox" name="con_inconsistencias" value="1" {{ request('con_inconsistencias') == '1' ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="font-semibold text-rose-700">Mostrar únicamente auditorías con incidencias o inconsistencias detectadas</span>
                </label>
            </div>
        </form>
    </div>

    <!-- Listado de Auditorías -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        @if($auditorias->isEmpty())
            <div class="p-16 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-gray-800">No se encontraron auditorías</h3>
                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                    No hay registros de auditoría que coincidan con los criterios seleccionados o aún no se ha iniciado ningún relevamiento físico.
                </p>
                <div class="mt-4">
                    <a href="{{ route('auditorias.create') }}" class="text-xs text-indigo-600 font-bold hover:underline">
                        + Comenzar una nueva auditoría ahora
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-700">
                    <thead class="bg-gray-50/80 text-[11px] font-black text-gray-500 uppercase tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="py-3.5 px-4">Folio / Fecha</th>
                            <th class="py-3.5 px-4">Sala / Laboratorio</th>
                            <th class="py-3.5 px-4">Auditor Actuante</th>
                            <th class="py-3.5 px-4 text-center">Estado</th>
                            <th class="py-3.5 px-4 text-center">Relevamiento</th>
                            <th class="py-3.5 px-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        @foreach($auditorias as $aud)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-mono font-bold text-indigo-700">#AUD-{{ str_pad($aud->id, 4, '0', STR_PAD_LEFT) }}</span>
                                    <div class="text-[11px] text-gray-500">
                                        {{ $aud->fecha ? $aud->fecha->format('d/m/Y H:i') : '-' }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-gray-900">{{ $aud->ubicacion?->nombre ?? 'Sin Asignar' }}</div>
                                    <div class="text-[11px] text-gray-500">{{ $aud->ubicacion?->descripcion ?? 'Área de Laboratorio' }}</div>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-semibold text-gray-800">{{ $aud->usuario?->nombre ?? 'Usuario LIA' }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $aud->usuario?->email ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if($aud->estado === 'finalizada')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            FINALIZADA
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200 animate-pulse">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            EN PROGRESO
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 font-bold text-[11px]" title="Total auditados">
                                            {{ $aud->total_auditados }} total
                                        </span>
                                        <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold text-[11px]" title="Conformes">
                                            ✓ {{ $aud->total_ok }}
                                        </span>
                                        @if($aud->total_inconsistencias > 0)
                                            <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-700 font-bold text-[11px]" title="Inconsistencias">
                                                ⚠ {{ $aud->total_inconsistencias }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('auditorias.show', $aud->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-indigo-50 hover:text-indigo-700 text-gray-700 rounded-lg text-xs font-bold transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span>Ver Informe</span>
                                        </a>

                                        @if($aud->estado === 'en_progreso')
                                            <a href="{{ route('auditorias.create', ['ubicacion_id' => $aud->ubicacion_id, 'auditoria_id' => $aud->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold transition-colors shadow-sm">
                                                <span>Continuar</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($auditorias->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $auditorias->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
