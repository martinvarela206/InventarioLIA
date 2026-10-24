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
                <span>🎓 Carreras para Registro de Usuarios</span>
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
                ← Volver al Panel
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

                    <button type="submit" class="w-full bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold py-2.5 px-4 rounded-xl shadow text-sm transition-colors mt-2">
                        + Registrar Carrera
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
                                <button type="button" onclick="editarCarrera({{ $carrera->id }}, '{{ addslashes($carrera->nombre) }}', '{{ addslashes($carrera->codigo ?? '') }}', {{ $carrera->activa ? 'true' : 'false' }})" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold px-2.5 py-1.5 rounded-lg transition-colors">
                                    ✏️
                                </button>

                                <!-- Eliminar (con resguardo histórico) -->
                                <form action="{{ route('app_admin.carreras.destroy', $carrera->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar o archivar esta carrera? Si posee usuarios históricos, se desactivará automáticamente para preservar sus registros.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 hover:text-red-800 hover:bg-red-50 p-1.5 rounded-lg transition-colors" title="Eliminar / Archivar">
                                        🗑️
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
                <button type="button" onclick="cerrarModalEditar()" class="text-xs font-bold text-gray-600 hover:text-black px-4 py-2">Cancelar</button>
                <button type="submit" class="bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold px-4 py-2 rounded-xl text-xs shadow">Guardar Cambios</button>
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
