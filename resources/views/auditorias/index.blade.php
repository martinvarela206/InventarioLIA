@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Header Quiet Chrome -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-figma-border-subtle pb-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-figma-text-tertiary uppercase tracking-wider mb-1">
                <a href="{{ route('dashboard.index') }}" class="hover:text-figma-text-primary transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-figma-text-primary font-bold">Auditorías</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary tracking-tight flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-figma-brand/15 text-figma-brand flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </span>
                <span>Archivo Histórico de Auditorías Físicas</span>
            </h1>
            <p class="text-xs text-figma-text-tertiary mt-0.5">
                Consulta y trazabilidad de relevamientos de inventario in situ en laboratorios y salas del LIA.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('auditorias.create') }}" class="figma-btn-primary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nueva Auditoría de Ubicación</span>
            </a>
        </div>
    </div>

    <!-- Barra de Filtros Avanzada -->
    <div class="figma-panel p-4">
        <form method="GET" action="{{ route('auditorias.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-figma-text-secondary uppercase mb-1">Sala / Laboratorio</label>
                <select name="ubicacion_id" class="figma-input w-full px-2.5 py-1.5 text-xs font-medium">
                    <option value="">Todas las salas</option>
                    @foreach($ubicaciones as $ub)
                        <option value="{{ $ub->id }}" {{ request('ubicacion_id') == $ub->id ? 'selected' : '' }}>
                            {{ $ub->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-figma-text-secondary uppercase mb-1">Auditor Responsable</label>
                <select name="user_id" class="figma-input w-full px-2.5 py-1.5 text-xs font-medium">
                    <option value="">Todos los auditores</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-figma-text-secondary uppercase mb-1">Estado</label>
                <select name="estado" class="figma-input w-full px-2.5 py-1.5 text-xs font-medium">
                    <option value="">Todos los estados</option>
                    <option value="en_progreso" {{ request('estado') === 'en_progreso' ? 'selected' : '' }}>En Progreso</option>
                    <option value="finalizada" {{ request('estado') === 'finalizada' ? 'selected' : '' }}>Finalizada</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-figma-text-secondary uppercase mb-1">Fecha Desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="figma-input w-full px-2.5 py-1.5 text-xs font-medium font-mono">
            </div>

            <div>
                <label class="block text-[10px] font-bold text-figma-text-secondary uppercase mb-1">Fecha Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="figma-input w-full px-2.5 py-1.5 text-xs font-medium font-mono">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="figma-btn-primary flex-1 justify-center py-2 text-xs">
                    Filtrar
                </button>
                <a href="{{ route('auditorias.index') }}" class="figma-btn-secondary px-3 py-2 text-xs" title="Limpiar filtros">
                    Limpiar
                </a>
            </div>
            
            <div class="col-span-full pt-1">
                <label class="inline-flex items-center gap-2 cursor-pointer text-xs text-figma-text-secondary">
                    <input type="checkbox" name="con_inconsistencias" value="1" {{ request('con_inconsistencias') == '1' ? 'checked' : '' }} class="rounded text-figma-brand focus:ring-figma-brand">
                    <span class="font-semibold text-rose-400">Mostrar únicamente auditorías con incidencias o inconsistencias detectadas</span>
                </label>
            </div>
        </form>
    </div>

    <!-- Listado de Auditorías -->
    <div class="figma-panel overflow-hidden">
        @if($auditorias->isEmpty())
            <div class="p-16 text-center space-y-3">
                <div class="w-10 h-10 rounded-full bg-figma-bg-deep text-figma-text-tertiary mx-auto flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xs font-bold text-figma-text-primary">No se encontraron auditorías</h3>
                <p class="text-xs text-figma-text-tertiary max-w-sm mx-auto">
                    No hay registros que coincidan con los criterios seleccionados o aún no se ha iniciado ningún relevamiento físico.
                </p>
                <div class="pt-2">
                    <a href="{{ route('auditorias.create') }}" class="text-xs text-figma-brand font-semibold hover:underline">
                        + Comenzar una nueva auditoría ahora
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-figma-bg-deep text-[10px] font-bold text-figma-text-secondary uppercase tracking-wider border-b border-figma-border-subtle">
                        <tr>
                            <th class="py-3 px-4">Folio / Fecha</th>
                            <th class="py-3 px-4">Sala / Laboratorio</th>
                            <th class="py-3 px-4">Auditor Actuante</th>
                            <th class="py-3 px-4 text-center">Estado</th>
                            <th class="py-3 px-4 text-center">Relevamiento</th>
                            <th class="py-3 px-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-figma-border-subtle">
                        @foreach($auditorias as $aud)
                            <tr class="hover:bg-figma-bg-deep/50 transition-colors">
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="font-mono font-bold text-figma-brand">#AUD-{{ str_pad($aud->id, 4, '0', STR_PAD_LEFT) }}</span>
                                    <div class="text-[11px] text-figma-text-tertiary font-mono">
                                        {{ $aud->fecha ? $aud->fecha->format('d/m/Y H:i') : '-' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-figma-text-primary">{{ $aud->ubicacion?->nombre ?? 'Sin Asignar' }}</div>
                                    <div class="text-[11px] text-figma-text-tertiary">{{ $aud->ubicacion?->descripcion ?? 'Área de Laboratorio' }}</div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="font-semibold text-figma-text-primary">{{ $aud->usuario?->nombre ?? 'Usuario LIA' }}</div>
                                    <div class="text-[11px] text-figma-text-tertiary font-mono">{{ $aud->usuario?->email ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    @if($aud->estado === 'finalizada')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            FINALIZADA
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30 animate-pulse">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                            EN PROGRESO
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5 font-mono text-[11px]">
                                        <span class="px-2 py-0.5 rounded bg-figma-bg-deep text-figma-text-secondary border border-figma-border-subtle" title="Total auditados">
                                            {{ $aud->total_auditados }} total
                                        </span>
                                        <span class="px-2 py-0.5 rounded bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 font-bold" title="Conformes">
                                            ✓ {{ $aud->total_ok }}
                                        </span>
                                        @if($aud->total_inconsistencias > 0)
                                            <span class="px-2 py-0.5 rounded bg-rose-500/15 text-rose-400 border border-rose-500/30 font-bold" title="Inconsistencias">
                                                ⚠ {{ $aud->total_inconsistencias }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('auditorias.show', $aud->id) }}" class="figma-btn-secondary py-1 px-2.5 text-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span>Ver Informe</span>
                                        </a>

                                        @if($aud->estado === 'en_progreso')
                                            <a href="{{ route('auditorias.create', ['ubicacion_id' => $aud->ubicacion_id, 'auditoria_id' => $aud->id]) }}" class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-500/20 text-amber-400 border border-amber-500/30 hover:bg-amber-500 hover:text-black rounded-lg text-xs font-semibold transition-colors">
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
                <div class="p-3 border-t border-figma-border-subtle">
                    {{ $auditorias->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
