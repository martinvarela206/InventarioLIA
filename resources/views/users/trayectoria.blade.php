@extends('layouts.app')

@section('content')
<div class="max-w-[850px] mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Historial de Trayectoria: {{ $user->nombre }}</h1>
            <p class="text-xs text-gray-500 mt-0.5">Registro cronológico de rotación de alumnos, pasantes, becarios y docentes en el LIA</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-mono bg-gray-100 text-gray-700 px-3 py-1.5 rounded-xl border border-gray-200">
                {{ $user->email }}
            </span>
            <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm transition-all">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver</span>
            </a>
        </div>
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
            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold rounded-xl text-sm shadow-sm transition-all inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Agregar Periodo</span>
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
                        <button type="submit" class="bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 font-bold rounded-xl px-3 py-1.5 text-xs transition-colors">
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
