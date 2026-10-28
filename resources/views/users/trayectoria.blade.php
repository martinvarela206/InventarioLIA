@extends('layouts.app')

@section('content')
<div class="max-w-[850px] mx-auto py-4 space-y-4">
    <!-- Header Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('users.index') }}" class="hover:text-figma-brand transition-colors">Gestión de Usuarios</a>
                <span>/</span>
                <span class="text-figma-text-primary">{{ $user->nombre }}</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <span>Historial de Trayectoria: {{ $user->nombre }}</span>
            </h1>
            <p class="text-xs text-figma-text-secondary mt-0.5">Registro cronológico de rotación de alumnos, pasantes, becarios y docentes en el LIA</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[11px] font-mono text-figma-text-secondary bg-figma-bg-deep px-2.5 py-1 rounded border border-figma-border-subtle">
                {{ $user->email }}
            </span>
            <a href="{{ route('users.index') }}" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver</span>
            </a>
        </div>
    </div>

    <!-- Formulario para Añadir Periodo de Trayectoria -->
    <div class="figma-panel p-5 space-y-3.5">
        <h3 class="text-xs font-bold text-figma-text-primary uppercase tracking-wider">Registrar Nuevo Periodo</h3>
        <form action="{{ route('users.trayectoria.store', $user->id) }}" method="POST" class="space-y-3">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Cargo / Función Desempeñada <span class="text-rose-400">*</span></label>
                    <input type="text" name="cargo" list="lista-cargos-trayectoria" placeholder="Seleccione o escriba..." class="figma-input w-full px-2.5 py-1.5 text-xs font-semibold" required>
                    <datalist id="lista-cargos-trayectoria">
                        @foreach($cargos as $cargo)
                            <option value="{{ is_string($cargo) ? $cargo : $cargo->nombre }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Fecha de Inicio <span class="text-rose-400">*</span></label>
                    <input type="date" name="fecha_inicio" value="{{ date('Y-m-d') }}" class="figma-input w-full px-2.5 py-1.5 text-xs font-mono" required>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Fecha de Fin (Opcional)</label>
                    <input type="date" name="fecha_fin" class="figma-input w-full px-2.5 py-1.5 text-xs font-mono">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Resolución / Notas / Observaciones</label>
                <input type="text" name="notas" placeholder="Resolución de designación N° 124/2026-FCEFN..." class="figma-input w-full px-2.5 py-1.5 text-xs">
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="figma-btn-primary text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Agregar Periodo</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Línea de Tiempo de Trayectoria -->
    <div class="figma-panel p-5 space-y-3">
        <h3 class="text-xs font-bold text-figma-text-primary uppercase tracking-wider mb-2">Periodos Registrados</h3>
        <div class="divide-y divide-figma-border-subtle">
            @forelse($user->trayectorias as $t)
                <div class="py-3 flex items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-figma-text-primary text-xs">{{ $t->cargo }}</h4>
                            @if(!$t->fecha_fin)
                                <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 text-[10px] font-mono font-bold px-1.5 py-0.5 rounded">En funciones</span>
                            @endif
                        </div>
                        <p class="text-xs text-figma-text-tertiary mt-0.5 font-mono">
                            Desde {{ $t->fecha_inicio ? $t->fecha_inicio->format('d/m/Y') : '-' }}
                            hasta {{ $t->fecha_fin ? $t->fecha_fin->format('d/m/Y') : 'la actualidad' }}
                        </p>
                        @if($t->notas)
                            <p class="text-xs text-figma-text-secondary mt-1 italic">"{{ $t->notas }}"</p>
                        @endif
                    </div>
                    <form action="{{ route('trayectorias.destroy', $t->id) }}" method="POST" onsubmit="return confirm('¿Seguro de eliminar este periodo?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-semibold px-2 py-1 rounded transition-colors">
                            Eliminar
                        </button>
                    </form>
                </div>
            @empty
                <div class="py-8 text-center text-figma-text-tertiary text-xs italic">
                    No hay periodos de trayectoria registrados para este usuario.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
