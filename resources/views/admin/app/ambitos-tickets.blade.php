@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('app_admin.index') }}" class="hover:underline">Gestión App</a>
                <span>/</span>
                <span class="text-gray-900 font-bold">Ámbitos de Incidencias</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span>Catálogo de Ámbitos y Espacios para Tickets</span>
                <span class="text-xs bg-amber-50 text-amber-900 font-extrabold px-2.5 py-1 rounded-xl border border-[#fbc101]">
                    {{ $ambitos->count() }} ámbitos
                </span>
            </h1>
            <p class="text-sm text-gray-600 mt-0.5">
                Configura los destinos disponibles en el desplegable de incidencias generales (infraestructura, laboratorios, servicios o situaciones no vinculadas a un activo de inventario).
            </p>
        </div>
        <div>
            <a href="{{ route('app_admin.index') }}" class="inline-flex items-center gap-1.5 bg-white hover:bg-amber-50 border border-amber-300 text-gray-800 font-bold px-3.5 py-2 rounded-xl text-xs shadow-xs transition-all">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver al Panel</span>
            </a>
        </div>
    </div>

    <!-- Mensajes Flash -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-300 text-emerald-900 text-sm font-semibold px-4 py-3 rounded-xl shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('warning'))
        <div class="bg-amber-50 border border-[#fbc101] text-amber-900 text-sm font-semibold px-4 py-3 rounded-xl shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ session('warning') }}</span>
            </div>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-900 text-sm font-semibold px-4 py-3 rounded-xl shadow-xs">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Formulario Alta de Ámbito -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-xs border border-gray-200 p-6 sticky top-20">
                <h2 class="font-extrabold text-gray-900 text-base mb-1">Nuevo Ámbito</h2>
                <p class="text-xs text-gray-500 mb-4">Añadir una opción para reporte de incidencias generales.</p>

                <form action="{{ route('app_admin.ambitos.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="nombre">Nombre del Ámbito *</label>
                        <input type="text" name="nombre" id="nombre" required placeholder="Ej. Redes y Conectividad" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#fbc101] font-medium" value="{{ old('nombre') }}">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="descripcion">Descripción / Alcance (Opcional)</label>
                        <input type="text" name="descripcion" id="descripcion" placeholder="Ej. Racks, cableado, switches o Wi-Fi" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#fbc101]" value="{{ old('descripcion') }}">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="orden">Orden de Presentación (Opcional)</label>
                        <input type="number" name="orden" id="orden" value="{{ old('orden', 0) }}" min="0" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#fbc101]">
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="activo" id="activo" value="1" {{ old('activo', true) ? 'checked' : '' }} class="w-4 h-4 text-amber-500 rounded focus:ring-[#fbc101]">
                        <label for="activo" class="text-xs font-bold text-gray-700">Habilitado para selección al crear tickets</label>
                    </div>

                    <button type="submit" class="w-full bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold py-2.5 px-4 rounded-xl shadow-xs text-sm transition-all inline-flex items-center justify-center gap-2 mt-2">
                        <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Registrar Ámbito</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Listado de Ámbitos -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Opciones Vigentes en Formulario de Tickets</span>
                    <span class="text-xs text-gray-500">Orden y Disponibilidad</span>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($ambitos as $ambito)
                        <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-amber-50/30 transition-colors" id="ambito-row-{{ $ambito->id }}">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-gray-900 text-sm">{{ $ambito->nombre }}</h3>
                                    @if($ambito->activo)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800">
                                            Habilitado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-gray-100 text-gray-600">
                                            Deshabilitado
                                        </span>
                                    @endif
                                    <span class="text-xs bg-amber-50 text-amber-900 font-extrabold px-2 py-0.5 rounded-xl border border-[#fbc101]">
                                        {{ $ambito->tickets_count }} ticket(s)
                                    </span>
                                </div>
                                @if($ambito->descripcion)
                                    <p class="text-xs text-gray-500">{{ $ambito->descripcion }}</p>
                                @endif
                                <div class="text-[11px] text-gray-400">
                                    <span>Prioridad de orden: #{{ $ambito->orden }}</span>
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button" onclick="toggleEdit({{ $ambito->id }})" class="p-1.5 text-gray-500 hover:text-amber-800 hover:bg-amber-100 rounded-lg transition-colors" title="Editar ámbito">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>

                                <form action="{{ route('app_admin.ambitos.toggle', $ambito) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 text-xs font-bold rounded-lg border {{ $ambito->activo ? 'border-amber-300 text-amber-800 hover:bg-amber-100' : 'border-emerald-300 text-emerald-800 hover:bg-emerald-50' }} transition-colors">
                                        {{ $ambito->activo ? 'Deshabilitar' : 'Habilitar' }}
                                    </button>
                                </form>

                                <form action="{{ route('app_admin.ambitos.destroy', $ambito) }}" method="POST" class="inline" onsubmit="return confirm('¿Confirma que desea eliminar este ámbito? Si tiene tickets asociados, se deshabilitará automáticamente para proteger la trazabilidad.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar ámbito">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Panel de Edición Inline Desplegable -->
                        <div id="edit-panel-{{ $ambito->id }}" class="hidden p-4 bg-amber-50/50 border-t border-b border-amber-200">
                            <form action="{{ route('app_admin.ambitos.update', $ambito) }}" method="POST" class="space-y-3">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Nombre</label>
                                        <input type="text" name="nombre" value="{{ $ambito->nombre }}" required class="w-full border border-gray-300 rounded-xl px-3 py-1.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#fbc101]">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Descripción</label>
                                        <input type="text" name="descripcion" value="{{ $ambito->descripcion }}" class="w-full border border-gray-300 rounded-xl px-3 py-1.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#fbc101]">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Orden</label>
                                        <input type="number" name="orden" value="{{ $ambito->orden }}" min="0" class="w-full border border-gray-300 rounded-xl px-3 py-1.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#fbc101]">
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-2">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" name="activo" id="activo_{{ $ambito->id }}" value="1" {{ $ambito->activo ? 'checked' : '' }} class="w-4 h-4 text-amber-500 rounded focus:ring-[#fbc101]">
                                        <label for="activo_{{ $ambito->id }}" class="text-xs font-bold text-gray-700">Habilitado para selección</label>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="toggleEdit({{ $ambito->id }})" class="px-3 py-1.5 text-xs font-bold text-gray-600 hover:text-gray-800 bg-white border border-gray-300 rounded-xl">Cancelar</button>
                                        <button type="submit" class="px-3.5 py-1.5 text-xs font-extrabold text-black bg-[#fbc101] hover:bg-amber-400 rounded-xl shadow-xs">Guardar Cambios</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500 text-sm">
                            No hay ámbitos registrados en el catálogo institucional.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleEdit(id) {
    const panel = document.getElementById('edit-panel-' + id);
    if (panel) {
        panel.classList.toggle('hidden');
    }
}
</script>
@endsection
