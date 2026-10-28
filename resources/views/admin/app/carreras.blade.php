@extends('layouts.app')

@section('content')
<div class="max-w-[1320px] mx-auto px-2 space-y-5">
    <!-- Header Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('app_admin.index') }}" class="hover:text-figma-brand transition-colors">Gestión App</a>
                <span>/</span>
                <span class="text-figma-text-primary">Carreras Universitarias</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <svg class="w-5 h-5 text-figma-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                <span>Carreras para Registro de Usuarios</span>
                <span class="font-mono text-xs font-bold bg-figma-bg-deep text-figma-brand px-2 py-0.5 rounded border border-figma-border-subtle">
                    {{ $carreras->count() }} registradas
                </span>
            </h1>
        </div>
        <div>
            <a href="{{ route('app_admin.index') }}" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver a Gestión</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="figma-card p-3 border-emerald-500/40 text-emerald-400 flex items-center gap-2 text-xs font-semibold">
            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="figma-card p-3 border-rose-500/40 text-rose-400 text-xs">
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Formulario Alta de Carrera -->
        <div class="lg:col-span-1">
            <div class="figma-panel p-4 sm:p-5 space-y-3 sticky top-16">
                <div>
                    <h2 class="font-bold text-figma-text-primary text-xs uppercase tracking-wider">Nueva Carrera</h2>
                    <p class="text-[11px] text-figma-text-tertiary mt-0.5">Añadir una carrera al catálogo de la facultad.</p>
                </div>

                <form action="{{ route('app_admin.carreras.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1" for="nombre">Nombre Oficial <span class="text-rose-400">*</span></label>
                        <input type="text" name="nombre" id="nombre" required placeholder="Ej. Licenciatura en Ciencias de la Computación" class="figma-input w-full px-3 py-1.5 text-xs font-medium">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1" for="codigo">Código / Sigla</label>
                        <input type="text" name="codigo" id="codigo" placeholder="Ej. LCC" class="figma-input w-full px-3 py-1.5 text-xs font-mono">
                    </div>

                    <div class="flex items-center gap-2 pt-1 text-figma-text-secondary">
                        <input type="checkbox" name="activa" id="activa" value="1" checked class="rounded text-figma-brand focus:ring-figma-brand">
                        <label for="activa" class="text-[11px] font-medium cursor-pointer">Disponible en registro</label>
                    </div>

                    <div class="pt-1">
                        <button type="submit" class="figma-btn-primary w-full justify-center py-2 text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Registrar Carrera</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Listado de Carreras -->
        <div class="lg:col-span-2">
            <div class="figma-panel overflow-hidden">
                <div class="p-3 border-b border-figma-border-subtle bg-figma-card/50 flex items-center justify-between text-xs">
                    <span class="font-bold text-figma-text-primary uppercase tracking-wider text-[11px]">Catálogo Institucional</span>
                    <span class="font-mono text-[10px] text-figma-text-tertiary">Historial Inmutable Activo</span>
                </div>

                <div class="divide-y divide-figma-border-subtle text-xs">
                    @forelse($carreras as $carrera)
                        <div class="p-3 sm:p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-figma-hover/50 transition-colors">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-figma-text-primary text-xs">{{ $carrera->nombre }}</h3>
                                    @if($carrera->codigo)
                                        <span class="font-mono text-[10px] bg-figma-bg-deep text-figma-text-secondary border border-figma-border-subtle px-1.5 py-0.2 rounded font-semibold">
                                            {{ $carrera->codigo }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 text-[11px] text-figma-text-tertiary mt-1">
                                    <span>Usuarios: <strong class="text-figma-text-secondary">{{ $carrera->usuarios_count }}</strong></span>
                                    <span>•</span>
                                    @if($carrera->activa)
                                        <span class="inline-flex items-center gap-1 text-emerald-400 font-semibold font-mono text-[10px]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-amber-400 font-semibold font-mono text-[10px]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Archivada
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 self-end sm:self-center">
                                <form action="{{ route('app_admin.carreras.toggle', $carrera->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="figma-btn-secondary text-[11px] py-1 px-2.5">
                                        {{ $carrera->activa ? 'Archivar' : 'Reactivar' }}
                                    </button>
                                </form>

                                <button type="button" onclick="editarCarrera({{ $carrera->id }}, '{{ addslashes($carrera->nombre) }}', '{{ addslashes($carrera->codigo ?? '') }}', {{ $carrera->activa ? 'true' : 'false' }})" class="figma-btn-secondary text-[11px] py-1 px-2" title="Editar">
                                    <svg class="w-3.5 h-3.5 text-figma-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                <form action="{{ route('app_admin.carreras.destroy', $carrera->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar o archivar esta carrera?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-figma-text-tertiary hover:text-rose-400 rounded transition-colors" title="Eliminar / Archivar">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-figma-text-tertiary text-xs italic">
                            No hay carreras registradas.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edición Carrera (Figma Studio Dialog) -->
<div id="modal-editar-carrera" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="figma-panel max-w-md w-full p-5 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between pb-2 border-b border-figma-border-subtle">
            <h3 class="font-bold text-figma-text-primary text-sm">Editar Carrera</h3>
            <button type="button" onclick="cerrarModalEditar()" class="text-figma-text-tertiary hover:text-figma-text-primary text-sm font-bold">✕</button>
        </div>

        <form id="form-editar-carrera" method="POST" class="space-y-3.5 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Nombre Oficial <span class="text-rose-400">*</span></label>
                <input type="text" name="nombre" id="edit-nombre" required class="figma-input w-full px-3 py-1.5 text-xs font-medium">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Código / Sigla</label>
                <input type="text" name="codigo" id="edit-codigo" class="figma-input w-full px-3 py-1.5 text-xs font-mono">
            </div>

            <div class="flex items-center gap-2 text-figma-text-secondary">
                <input type="checkbox" name="activa" id="edit-activa" value="1" class="rounded text-figma-brand focus:ring-figma-brand">
                <label for="edit-activa" class="text-[11px] font-medium cursor-pointer">Carrera activa en registro</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-figma-border-subtle">
                <button type="button" onclick="cerrarModalEditar()" class="figma-btn-secondary">Cancelar</button>
                <button type="submit" class="figma-btn-primary">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
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
