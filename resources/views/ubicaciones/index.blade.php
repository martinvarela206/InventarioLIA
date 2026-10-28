@extends('layouts.app')

@section('content')
<div class="max-w-[1320px] mx-auto px-2 space-y-5">

    <!-- Header Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('dashboard.index') }}" class="hover:text-figma-brand transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-figma-text-primary">Administración</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <svg class="w-5 h-5 text-figma-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span>Salas, Laboratorios y Espacios Físicos</span>
            </h1>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('ubicaciones.create') }}" class="figma-btn-primary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nuevo Espacio</span>
            </a>
            <a href="{{ route('dashboard.index') }}" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Dashboard</span>
            </a>
        </div>
    </div>

    <!-- Grilla de Espacios Físicos (Figma Studio Canvas Artboards) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($ubicaciones as $ub)
            <div class="figma-card p-4 sm:p-5 flex flex-col justify-between hover:-translate-y-0.5">
                <div>
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-figma-bg-deep border border-figma-border-subtle flex items-center justify-center flex-shrink-0 text-figma-brand">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-figma-text-primary text-sm leading-tight">{{ $ub->nombre }}</h3>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $ub->total_activos > 0 ? 'bg-emerald-500' : 'bg-figma-text-tertiary' }}"></span>
                                    <span class="text-[11px] text-figma-text-tertiary">{{ $ub->total_activos > 0 ? 'Con activos asignados' : 'Sin activos asignados' }}</span>
                                </div>
                            </div>
                        </div>
                        <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-figma-bg-deep text-figma-brand border border-figma-border-subtle">
                            {{ $ub->total_activos }} {{ $ub->total_activos === 1 ? 'activo' : 'activos' }}
                        </span>
                    </div>

                    <p class="text-[11px] text-figma-text-secondary mb-3 min-h-[30px] line-clamp-2">
                        {{ $ub->descripcion ?: 'Sin descripción detallada registrada para este espacio.' }}
                    </p>
                </div>

                <div class="pt-3 border-t border-figma-border-subtle space-y-2.5">
                    <!-- Acciones Operativas y Auditoría -->
                    <div class="flex items-center justify-between text-xs">
                        <a href="{{ route('elementos.index', ['ubicacion_id' => $ub->id]) }}" class="text-figma-brand hover:underline font-semibold flex items-center gap-1 text-[11px]">
                            <span>Ver equipos</span>
                            <span>→</span>
                        </a>
                        <a href="{{ route('reportes.auditoria', $ub->id) }}" class="text-figma-text-secondary hover:text-figma-text-primary font-medium flex items-center gap-1 text-[11px]" title="Planilla de cotejo e inventario físico en papel">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Auditar A4</span>
                        </a>
                    </div>

                    <!-- Botones de Gestión (Editar / Eliminar) -->
                    <div class="flex items-center justify-end gap-1.5 pt-2 border-t border-figma-border-subtle">
                        <a href="{{ route('ubicaciones.edit', $ub->id) }}" class="figma-btn-secondary text-[11px] py-1 px-2.5">
                            <svg class="w-3 h-3 text-figma-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Editar</span>
                        </a>

                        @if($ub->total_activos === 0)
                            <form action="{{ route('ubicaciones.destroy', $ub->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar el espacio {{ $ub->nombre }}? Esta acción no se puede deshacer.');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-rose-400 hover:text-rose-300 py-1 px-2 rounded hover:bg-rose-500/10 transition-colors flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Eliminar</span>
                                </button>
                            </form>
                        @else
                            <span title="No se puede eliminar porque contiene activos asignados" class="text-[10px] font-mono font-medium text-figma-text-tertiary px-2 py-1">
                                Bloqueado
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-figma-text-tertiary figma-panel text-xs">
                <p class="font-bold text-sm text-figma-text-primary">No hay espacios físicos registrados.</p>
                <p class="mt-1">Crea el primer laboratorio o sala física del LIA.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
