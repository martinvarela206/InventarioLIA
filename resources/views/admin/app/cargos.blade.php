@extends('layouts.app')

@section('content')
<div class="max-w-[1320px] mx-auto px-2 space-y-5">
    <!-- Header Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('app_admin.index') }}" class="hover:text-figma-brand transition-colors">Gestión App</a>
                <span>/</span>
                <span class="text-figma-text-primary">Cargos Institucionales</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span>Cargos y Funciones en el Laboratorio</span>
                <span class="font-mono text-xs font-bold bg-figma-bg-deep text-indigo-400 px-2 py-0.5 rounded border border-figma-border-subtle">
                    {{ $cargos->count() }} registrados
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
        <!-- Formulario Alta de Cargo -->
        <div class="lg:col-span-1">
            <div class="figma-panel p-4 sm:p-5 space-y-3 sticky top-16">
                <div>
                    <h2 class="font-bold text-figma-text-primary text-xs uppercase tracking-wider">Nuevo Cargo</h2>
                    <p class="text-[11px] text-figma-text-tertiary mt-0.5">Añadir un cargo o rol para miembros del laboratorio.</p>
                </div>

                <form action="{{ route('app_admin.cargos.store') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1" for="nombre">Nombre del Cargo <span class="text-rose-400">*</span></label>
                        <input type="text" name="nombre" id="nombre" required placeholder="Ej. Investigador Adjunto" class="figma-input w-full px-3 py-1.5 text-xs font-medium">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1" for="descripcion">Descripción / Función</label>
                        <input type="text" name="descripcion" id="descripcion" placeholder="Ej. Proyectos de I+D" class="figma-input w-full px-3 py-1.5 text-xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1" for="orden">Orden de Prioridad</label>
                        <input type="number" name="orden" id="orden" value="0" min="0" class="figma-input w-full px-3 py-1.5 text-xs font-mono">
                    </div>

                    <div class="flex items-center gap-2 pt-1 text-figma-text-secondary">
                        <input type="checkbox" name="activo" id="activo" value="1" checked class="rounded text-figma-brand focus:ring-figma-brand">
                        <label for="activo" class="text-[11px] font-medium cursor-pointer">Disponible en perfiles</label>
                    </div>

                    <div class="pt-1">
                        <button type="submit" class="figma-btn-primary w-full justify-center py-2 text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Registrar Cargo</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Listado de Cargos -->
        <div class="lg:col-span-2">
            <div class="figma-panel overflow-hidden">
                <div class="p-3 border-b border-figma-border-subtle bg-figma-card/50 flex items-center justify-between text-xs">
                    <span class="font-bold text-figma-text-primary uppercase tracking-wider text-[11px]">Catálogo Institucional de Cargos</span>
                    <span class="font-mono text-[10px] text-figma-text-tertiary">Gestión de Perfil y Registro</span>
                </div>

                <div class="divide-y divide-figma-border-subtle text-xs">
                    @forelse($cargos as $cargo)
                        <div class="p-3 sm:p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-figma-hover/50 transition-colors">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-figma-text-primary text-xs">{{ $cargo->nombre }}</h3>
                                    @if($cargo->orden > 0)
                                        <span class="font-mono text-[10px] bg-figma-bg-deep text-figma-text-secondary border border-figma-border-subtle px-1.5 py-0.2 rounded font-semibold">
                                            Orden: {{ $cargo->orden }}
                                        </span>
                                    @endif
                                </div>
                                @if($cargo->descripcion)
                                    <p class="text-[11px] text-figma-text-tertiary">{{ $cargo->descripcion }}</p>
                                @endif
                                <div class="flex items-center gap-2 text-[11px] text-figma-text-tertiary">
                                    <span>Usuarios: <strong class="text-figma-text-secondary">{{ $cargo->usuarios_count }}</strong></span>
                                    <span>•</span>
                                    @if($cargo->activo)
                                        <span class="inline-flex items-center gap-1 text-emerald-400 font-semibold font-mono text-[10px]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-amber-400 font-semibold font-mono text-[10px]">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Archivado
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0 self-end sm:self-center">
                                <form action="{{ route('app_admin.cargos.toggle', $cargo->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="figma-btn-secondary text-[11px] py-1 px-2.5">
                                        {{ $cargo->activo ? 'Archivar' : 'Reactivar' }}
                                    </button>
                                </form>

                                <button type="button" onclick="openEditCargoModal({{ json_encode($cargo) }})" class="figma-btn-secondary text-[11px] py-1 px-2" title="Editar">
                                    <svg class="w-3.5 h-3.5 text-figma-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                <form action="{{ route('app_admin.cargos.destroy', $cargo->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este cargo?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-figma-text-tertiary hover:text-rose-400 rounded transition-colors" title="Eliminar cargo">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-figma-text-tertiary text-xs italic">
                            No hay cargos registrados en el catálogo institucional.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edición de Cargo (Figma Studio Dialog) -->
<div id="editCargoModal" class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 hidden">
    <div class="figma-panel max-w-md w-full p-5 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between pb-2 border-b border-figma-border-subtle">
            <h3 class="font-bold text-figma-text-primary text-sm">Editar Cargo</h3>
            <button type="button" onclick="closeEditCargoModal()" class="text-figma-text-tertiary hover:text-figma-text-primary text-sm font-bold">✕</button>
        </div>

        <form id="editCargoForm" method="POST" class="space-y-3.5 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1" for="modal_nombre">Nombre del Cargo <span class="text-rose-400">*</span></label>
                <input type="text" name="nombre" id="modal_nombre" required class="figma-input w-full px-3 py-1.5 text-xs font-medium">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1" for="modal_descripcion">Descripción / Función</label>
                <input type="text" name="descripcion" id="modal_descripcion" class="figma-input w-full px-3 py-1.5 text-xs">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1" for="modal_orden">Orden</label>
                <input type="number" name="orden" id="modal_orden" min="0" class="figma-input w-full px-3 py-1.5 text-xs font-mono">
            </div>

            <div class="flex items-center gap-2 text-figma-text-secondary">
                <input type="checkbox" name="activo" id="modal_activo" value="1" class="rounded text-figma-brand focus:ring-figma-brand">
                <label for="modal_activo" class="text-[11px] font-medium cursor-pointer">Disponible para selección</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-figma-border-subtle">
                <button type="button" onclick="closeEditCargoModal()" class="figma-btn-secondary">
                    Cancelar
                </button>
                <button type="submit" class="figma-btn-primary">
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
