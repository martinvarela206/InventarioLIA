@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">

    <!-- Header del Panel -->
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 pb-5">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-800 bg-amber-100 px-2.5 py-1 rounded-md">
                    Gestión de Espacios Físicos
                </span>
                <span class="text-xs text-gray-500 font-semibold">item_admin</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 mt-1">Salas, Laboratorios y Depósitos del LIA</h1>
            <p class="text-xs text-gray-500 mt-0.5">
                Administra los espacios físicos donde se localizan y trasladan los activos patrimoniales.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.index') }}" class="px-3.5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold rounded-xl text-xs shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver al Dashboard
            </a>
            <a href="{{ route('ubicaciones.create') }}" class="px-4 py-2 bg-amber-400 hover:bg-amber-500 text-black font-extrabold rounded-xl text-xs shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo Espacio / Sala
            </a>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl font-bold flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 text-xs rounded-xl font-bold flex items-center gap-2">
            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 text-xs rounded-xl font-medium">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Grilla de Espacios Físicos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($ubicaciones as $ub)
            <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm hover:border-amber-400 hover:shadow-md transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ $ub->total_activos > 0 ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                            <h3 class="font-extrabold text-gray-900 text-base leading-tight">{{ $ub->nombre }}</h3>
                        </div>
                        <span class="text-xs font-black px-2.5 py-1 rounded-full {{ $ub->total_activos > 0 ? 'bg-amber-100 text-amber-900' : 'bg-gray-100 text-gray-600' }}" title="Activos localizados aquí">
                            {{ $ub->total_activos }} {{ $ub->total_activos === 1 ? 'activo' : 'activos' }}
                        </span>
                    </div>

                    <p class="text-xs text-gray-600 mb-4 min-h-[32px] line-clamp-2">
                        {{ $ub->descripcion ?: 'Sin descripción detallada registrada para este espacio.' }}
                    </p>
                </div>

                <div class="pt-4 border-t border-gray-100 space-y-3">
                    <!-- Acciones Operativas y Auditoría -->
                    <div class="flex items-center justify-between text-xs">
                        <a href="{{ route('elementos.index', ['ubicacion_id' => $ub->id]) }}" class="text-amber-800 hover:text-black font-bold flex items-center gap-1">
                            <span>Ver equipos</span>
                            <span>→</span>
                        </a>
                        <a href="{{ route('reportes.auditoria', $ub->id) }}" class="text-gray-500 hover:text-gray-900 font-semibold flex items-center gap-1" title="Planilla de cotejo e inventario físico en papel">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Auditar A4
                        </a>
                    </div>

                    <!-- Botones de Gestión (Editar / Eliminar) -->
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-50">
                        <a href="{{ route('ubicaciones.edit', $ub->id) }}" class="px-2.5 py-1 text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Editar
                        </a>

                        @if($ub->total_activos === 0)
                            <form action="{{ route('ubicaciones.destroy', $ub->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar el espacio {{ $ub->nombre }}? Esta acción no se puede deshacer.');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2.5 py-1 text-xs font-bold text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-colors flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Eliminar
                                </button>
                            </form>
                        @else
                            <button type="button" disabled title="No se puede eliminar porque contiene activos asignados" class="px-2.5 py-1 text-xs font-medium text-gray-400 bg-gray-50 rounded-lg cursor-not-allowed">
                                Con Activos
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-gray-500 bg-white rounded-2xl border border-gray-200">
                <p class="font-bold text-base text-gray-700">No hay espacios físicos registrados.</p>
                <p class="text-xs text-gray-400 mt-1">Crea el primer laboratorio o sala física del LIA.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
