@extends('layouts.app')

@section('content')
<div class="max-w-[850px] mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('users.index') }}" class="text-sm font-semibold text-gray-500 hover:text-black">← Volver a usuarios</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Historial de Trayectoria: {{ $user->nombre }}</h1>
            <p class="text-xs text-gray-500">Registro cronológico de rotación de alumnos, pasantes, becarios y docentes en el LIA</p>
        </div>
        <span class="text-xs font-mono bg-gray-100 text-gray-700 px-3 py-1 rounded-md">
            {{ $user->email }}
        </span>
    </div>

    <!-- Formulario para Añadir Periodo de Trayectoria -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-3">Registrar Nuevo Periodo</h3>
        <form action="{{ route('users.trayectoria.store', $user->id) }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Cargo / Función Desempeñada *</label>
                    <input type="text" name="cargo" placeholder="Ej. Becario de Investigación, JTP Redes" class="w-full px-3 py-2 border rounded-lg text-sm bg-white font-medium" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Fecha de Inicio *</label>
                    <input type="date" name="fecha_inicio" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border rounded-lg text-sm bg-white font-medium" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Fecha de Fin (Dejar vacío si está activo)</label>
                    <input type="date" name="fecha_fin" class="w-full px-3 py-2 border rounded-lg text-sm bg-white font-medium">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Resolución / Notas / Observaciones</label>
                <input type="text" name="notas" placeholder="Resolución de designación N° 124/2026-FCEFN..." class="w-full px-3 py-2 border rounded-lg text-sm bg-white">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2 bg-amber-400 hover:bg-amber-500 text-black font-bold rounded-xl text-xs shadow-sm transition-all">
                    + Agregar Periodo
                </button>
            </div>
        </form>
    </div>

    <!-- Línea de Tiempo de Trayectoria -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Periodos Registrados</h3>
        <div class="divide-y divide-gray-100">
            @forelse($user->trayectorias as $t)
                <div class="py-3.5 flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-gray-900 text-sm">{{ $t->cargo }}</h4>
                            @if(!$t->fecha_fin)
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-full">Actualmente en funciones</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Desde {{ $t->fecha_inicio ? $t->fecha_inicio->format('d/m/Y') : '-' }}
                            hasta {{ $t->fecha_fin ? $t->fecha_fin->format('d/m/Y') : 'la actualidad' }}
                        </p>
                        @if($t->notas)
                            <p class="text-xs text-gray-700 mt-1 italic">"{{ $t->notas }}"</p>
                        @endif
                    </div>
                    <form action="{{ route('trayectorias.destroy', $t->id) }}" method="POST" onsubmit="return confirm('¿Seguro de eliminar este periodo?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-semibold p-1">
                            Eliminar
                        </button>
                    </form>
                </div>
            @empty
                <div class="py-8 text-center text-gray-400 text-sm">
                    No hay periodos de trayectoria registrados para este usuario.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
