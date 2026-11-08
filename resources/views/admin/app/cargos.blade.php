@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('app_admin.index') }}" class="hover:underline">Gestión App</a>
                <span>/</span>
                <span class="text-gray-900 font-bold">Cargos del Laboratorio</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span>Cargos y Funciones en el Laboratorio</span>
                <span class="text-xs bg-amber-100 text-amber-900 font-extrabold px-2.5 py-0.5 rounded-full border border-amber-300">
                    {{ $cargos->count() }} registrados
                </span>
            </h1>
            <p class="text-sm text-gray-600 mt-0.5">
                Configura los cargos oficiales del LIA para el perfil de usuario y el registro institucional. Los cargos archivados no se ofrecen a nuevos usuarios pero conservan el historial de quienes ya los tienen asignados.
            </p>
        </div>
        <div>
            <a href="{{ route('app_admin.index') }}" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm transition-all">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver al Panel</span>
            </a>
        </div>
    </div>

    <!-- Mensajes -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-300 text-emerald-900 text-sm font-semibold px-4 py-3 rounded-xl shadow-sm">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-900 text-sm font-semibold px-4 py-3 rounded-xl shadow-sm">
            <ul>
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Formulario Alta de Cargo -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sticky top-20">
                <h2 class="font-bold text-gray-900 text-base mb-1">Nuevo Cargo</h2>
                <p class="text-xs text-gray-500 mb-4">Añadir un cargo o rol oficial para los miembros del laboratorio.</p>

                <form action="{{ route('app_admin.cargos.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="nombre">Nombre del Cargo *</label>
                        <input type="text" name="nombre" id="nombre" required placeholder="Ej. Investigador Adjunto" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 font-medium">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="descripcion">Descripción / Función (Opcional)</label>
                        <input type="text" name="descripcion" id="descripcion" placeholder="Ej. Proyectos de I+D en redes" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="orden">Orden de Prioridad (Opcional)</label>
                        <input type="number" name="orden" id="orden" value="0" min="0" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="activo" id="activo" value="1" checked class="w-4 h-4 text-amber-500 rounded focus:ring-amber-400">
                        <label for="activo" class="text-xs font-bold text-gray-700">Disponible para selección de usuarios</label>
                    </div>

                    <button type="submit" class="w-full bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold py-2.5 px-4 rounded-xl shadow-sm text-sm transition-all inline-flex items-center justify-center gap-2 mt-2">
                        <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Registrar Cargo</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Listado de Cargos -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Catálogo Institucional de Cargos</span>
                    <span class="text-xs text-gray-500">Gestión de Perfil y Registro</span>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($cargos as $cargo)
                        <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-amber-50/40 transition-colors">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-gray-900 text-sm">{{ $cargo->nombre }}</h3>
                                    @if($cargo->orden > 0)
                                        <span class="text-[10px] bg-gray-100 text-gray-600 font-bold px-1.5 py-0.5 rounded border border-gray-200">
                                            Orden: {{ $cargo->orden }}
                                        </span>
                                    @endif
                                </div>
                                @if($cargo->descripcion)
                                    <p class="text-xs text-gray-600">{{ $cargo->descripcion }}</p>
                                @endif
                                <div class="flex items-center gap-3 text-xs text-gray-500">
                                    <span class="inline-flex items-center gap-1 font-medium">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        {{ $cargo->usuarios_count }} {{ Str::plural('usuario', $cargo->usuarios_count) }}
                                    </span>
                                    <span>&bull;</span>
                                    @if($cargo->activo)
                                        <span class="text-emerald-700 font-bold inline-flex items-center gap-1">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Activo
                                        </span>
                                    @else
                                        <span class="text-gray-400 font-bold inline-flex items-center gap-1">
                                            <span class="w-2 h-2 rounded-full bg-gray-400"></span> Archivada / Oculta
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="flex items-center gap-2 shrink-0">
                                <!-- Botón Toggle Activo/Inactivo -->
                                <form action="{{ route('app_admin.cargos.toggle', $cargo->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg text-xs font-bold border transition-all {{ $cargo->activo ? 'border-amber-200 text-amber-900 hover:bg-amber-100' : 'border-emerald-200 text-emerald-800 hover:bg-emerald-50' }}" title="{{ $cargo->activo ? 'Deshabilitar para nuevos registros' : 'Habilitar para nuevos registros' }}">
                                        {{ $cargo->activo ? 'Archivar' : 'Reactivar' }}
                                    </button>
                                </form>

                                <!-- Botón Editar -->
                                <button type="button" onclick="openEditCargoModal({{ json_encode($cargo) }})" class="p-1.5 text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors" title="Editar datos del cargo">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                <!-- Botón Eliminar con Confirmación -->
                                <form action="{{ route('app_admin.cargos.destroy', $cargo->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este cargo? Si cuenta con usuarios asociados, se archivará automáticamente para resguardar el historial.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition-colors" title="Eliminar cargo">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500 text-sm">
                            No hay cargos registrados en el catálogo institucional.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edición de Cargo -->
<div id="editCargoModal" class="fixed inset-0 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-200">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
            <h3 class="font-black text-gray-900 text-lg">Editar Cargo</h3>
            <button type="button" onclick="closeEditCargoModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="editCargoForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="modal_nombre">Nombre del Cargo *</label>
                <input type="text" name="nombre" id="modal_nombre" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 font-medium">
            </div>

            <div>
                <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="modal_descripcion">Descripción / Función</label>
                <input type="text" name="descripcion" id="modal_descripcion" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <div>
                <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="modal_orden">Orden</label>
                <input type="number" name="orden" id="modal_orden" min="0" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="activo" id="modal_activo" value="1" class="w-4 h-4 text-amber-500 rounded focus:ring-amber-400">
                <label for="modal_activo" class="text-xs font-bold text-gray-700">Disponible para selección</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeEditCargoModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-100 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold px-4 py-2 rounded-xl text-xs shadow-sm transition-all">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditCargoModal(cargo) {
        const form = document.getElementById('editCargoForm');
        form.action = `/app-admin/cargos/${cargo.id}`;
        document.getElementById('modal_nombre').value = cargo.nombre;
        document.getElementById('modal_descripcion').value = cargo.descripcion || '';
        document.getElementById('modal_orden').value = cargo.orden || 0;
        document.getElementById('modal_activo').checked = !!cargo.activo;
        document.getElementById('editCargoModal').classList.remove('hidden');
    }

    function closeEditCargoModal() {
        document.getElementById('editCargoModal').classList.add('hidden');
    }
</script>
@endsection
