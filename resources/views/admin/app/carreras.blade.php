@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('app_admin.index') }}" class="hover:underline">Gestión App</a>
                <span>/</span>
                <span class="text-gray-900 font-bold">Carreras Universitarias</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                <span>Carreras para Registro de Usuarios</span>
                <span class="text-xs bg-amber-100 text-amber-900 font-extrabold px-2.5 py-0.5 rounded-full border border-amber-300">
                    {{ $carreras->count() }} registradas
                </span>
            </h1>
            <p class="text-sm text-gray-600 mt-0.5">
                Configura la lista de opciones para nuevos registros. Las carreras archivadas no se ofrecen a nuevos alumnos, pero conservan de por vida el historial de los usuarios preexistentes.
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
        <!-- Formulario Alta de Carrera -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sticky top-20">
                <h2 class="font-bold text-gray-900 text-base mb-1">Nueva Carrera</h2>
                <p class="text-xs text-gray-500 mb-4">Añadir una carrera al catálogo de la facultad.</p>

                <form action="{{ route('app_admin.carreras.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="nombre">Nombre Oficial *</label>
                        <input type="text" name="nombre" id="nombre" required placeholder="Ej. Licenciatura en Inteligencia Artificial" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 font-medium">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1" for="codigo">Código / Sigla (Opcional)</label>
                        <input type="text" name="codigo" id="codigo" placeholder="Ej. LIA-DEG" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 font-mono">
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="activa" id="activa" value="1" checked class="w-4 h-4 text-amber-500 rounded focus:ring-amber-400">
                        <label for="activa" class="text-xs font-bold text-gray-700">Disponible de inmediato para registro</label>
                    </div>

                    <button type="submit" class="w-full bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold py-2.5 px-4 rounded-xl shadow-sm text-sm transition-all inline-flex items-center justify-center gap-2 mt-2">
                        <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Registrar Carrera</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Listado de Carreras -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Catálogo Institucional</span>
                    <span class="text-xs text-gray-500">Historial Inmutable Activo</span>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($carreras as $carrera)
                        <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-amber-50/40 transition-colors">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-gray-900 text-sm">{{ $carrera->nombre }}</h3>
                                    @if($carrera->codigo)
                                        <span class="text-[10px] bg-gray-100 text-gray-700 font-mono font-bold px-2 py-0.5 rounded border border-gray-200">
                                            {{ $carrera->codigo }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3 text-xs text-gray-500 mt-1">
                                    <span>
                                        Alumnos/Usuarios vinculados: 
                                        <strong class="text-gray-900">{{ $carrera->usuarios_count }}</strong>
                                    </span>
                                    <span>•</span>
                                    @if($carrera->activa)
                                        <span class="inline-flex items-center gap-1 text-emerald-700 font-bold">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Activa en Registro
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-amber-800 font-bold bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                            <span class="w-2 h-2 rounded-full bg-amber-500"></span> Archivada (Histórica)
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2 self-end sm:self-center">
                                <!-- Alternar Activa/Inactiva -->
                                <form action="{{ route('app_admin.carreras.toggle', $carrera->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-lg border {{ $carrera->activa ? 'border-amber-300 text-amber-800 hover:bg-amber-100' : 'border-emerald-300 text-emerald-800 hover:bg-emerald-100' }} transition-colors" title="{{ $carrera->activa ? 'Archivar carrera (dejará de mostrarse a nuevos registros)' : 'Activar carrera para nuevos registros' }}">
                                        {{ $carrera->activa ? 'Desactivar / Archivar' : 'Reactivar' }}
                                    </button>
                                </form>

                                <!-- Botón Editar -->
                                <button type="button" onclick="editarCarrera({{ $carrera->id }}, '{{ addslashes($carrera->nombre) }}', '{{ addslashes($carrera->codigo ?? '') }}', {{ $carrera->activa ? 'true' : 'false' }})" class="p-1.5 text-gray-600 hover:text-black hover:bg-gray-100 rounded-lg transition-colors" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                <!-- Eliminar (con resguardo histórico) -->
                                <form action="{{ route('app_admin.carreras.destroy', $carrera->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar o archivar esta carrera? Si posee usuarios históricos, se desactivará automáticamente para preservar sus registros.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar / Archivar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-400 text-xs italic">
                            No hay carreras registradas.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edición Carrera -->
<div id="modal-editar-carrera" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-200">
        <h3 class="font-bold text-gray-900 text-lg mb-1">Editar Carrera</h3>
        <p class="text-xs text-gray-500 mb-4">Modificar datos oficiales de la carrera universitaria.</p>

        <form id="form-editar-carrera" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Nombre Oficial *</label>
                <input type="text" name="nombre" id="edit-nombre" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 font-medium focus:ring-2 focus:ring-amber-400">
            </div>

            <div>
                <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Código / Sigla</label>
                <input type="text" name="codigo" id="edit-codigo" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 font-mono focus:ring-2 focus:ring-amber-400">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="activa" id="edit-activa" value="1" class="w-4 h-4 text-amber-500 rounded focus:ring-amber-400">
                <label for="edit-activa" class="text-xs font-bold text-gray-700">Carrera activa en formulario de registro</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t">
                <button type="button" onclick="cerrarModalEditar()" class="px-4 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 font-bold rounded-xl text-xs shadow-sm transition-all">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold rounded-xl text-xs shadow-sm transition-all inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Guardar Cambios</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function editarCarrera(id, nombre, codigo, activa) {
        document.getElementById('form-editar-carrera').action = `/app-admin/carreras/${id}`;
        document.getElementById('edit-nombre').value = nombre;
        document.getElementById('edit-codigo').value = codigo;
        document.getElementById('edit-activa').checked = activa;
        document.getElementById('modal-editar-carrera').classList.remove('hidden');
    }

    function cerrarModalEditar() {
        document.getElementById('modal-editar-carrera').classList.add('hidden');
    }
</script>
@endsection
