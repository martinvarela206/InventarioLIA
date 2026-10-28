@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto py-4 space-y-4">
    <!-- Header Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('app_admin.index') }}" class="hover:text-figma-brand transition-colors">Gestión App</a>
                <span>/</span>
                <span class="text-figma-text-primary">Personal y Autoridades</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <span>Figuras y Autoridades del LIA</span>
                <span class="text-[10px] bg-figma-bg-deep text-figma-brand font-mono font-bold px-2 py-0.5 rounded border border-figma-border-subtle">
                    {{ $personal->count() }} miembros
                </span>
            </h1>
            <p class="text-xs text-figma-text-secondary mt-0.5">
                Designa a los usuarios del sistema que figuran como referentes institucionales en la portada.
            </p>
        </div>
        <div>
            <a href="{{ url('/#personal-lia') }}" target="_blank" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Ver en Landing Page</span>
            </a>
        </div>
    </div>

    <!-- Mensajes -->
    @if(session('success'))
        <div class="figma-card p-3 border-emerald-500/40 text-emerald-400 text-xs">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="figma-card p-3 border-rose-500/40 text-rose-400 text-xs">
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Formulario Alta de Personal -->
        <div class="lg:col-span-1">
            <div class="figma-panel p-5 sticky top-20 space-y-3.5">
                <h2 class="font-bold text-figma-text-primary text-sm">Designar Autoridad / Referente</h2>
                <p class="text-xs text-figma-text-tertiary">Selecciona un usuario registrado del LIA para destacarlo en la portada.</p>

                <form action="{{ route('app_admin.personal.store') }}" method="POST" class="space-y-3.5">
                    @csrf

                    <!-- Selector de Usuario del Sistema -->
                    <div class="figma-card p-3 space-y-2">
                        <label class="block text-figma-text-secondary text-[11px] font-bold uppercase">
                            Usuario del Sistema <span class="text-rose-400">*</span>
                        </label>
                        <select name="user_id" id="user_id_select" required class="figma-input w-full px-2.5 py-1.5 text-xs font-semibold">
                            <option value="">-- Seleccionar usuario activo --</option>
                            @foreach($usuarios as $u)
                                <option value="{{ $u->id }}"
                                    data-nombre="{{ $u->nombre }}"
                                    data-cargo="{{ $u->cargo_actual ?: ($u->cargo ? $u->cargo->nombre : 'Sin cargo oficial') }}"
                                    data-email="{{ $u->email }}"
                                    data-foto="{{ $u->foto_url }}">
                                    {{ $u->nombre }} ({{ $u->cargo_actual ?: ($u->cargo ? $u->cargo->nombre : 'Sin cargo') }})
                                </option>
                            @endforeach
                        </select>

                        <!-- Preview visual del usuario seleccionado -->
                        <div id="selected_user_preview" class="hidden p-2 rounded bg-figma-bg-deep border border-figma-border-subtle flex items-center gap-2.5">
                            <img id="selected_user_preview_img" src="/assets/placeholder-user.png" alt="Avatar" class="w-8 h-8 rounded-full object-cover border border-figma-border-subtle flex-shrink-0">
                            <div class="min-w-0 flex-1">
                                <p id="selected_user_preview_name" class="text-xs font-bold text-figma-text-primary truncate"></p>
                                <p id="selected_user_preview_cargo" class="text-[10px] text-figma-text-tertiary truncate"></p>
                            </div>
                        </div>

                        <p class="text-[10px] text-figma-text-tertiary leading-tight">
                            Solo figuran usuarios activos con huella digital vigente no designados.
                        </p>
                    </div>

                    <div>
                        <label class="block text-figma-text-secondary text-[11px] font-bold uppercase mb-1">Comentario Institucional</label>
                        <textarea name="comentario" rows="3" placeholder="Ej: Dirección general, coordinación de investigación..." class="figma-input w-full px-2.5 py-1.5 text-xs"></textarea>
                    </div>

                    <div>
                        <label class="block text-figma-text-secondary text-[11px] font-bold uppercase mb-1">Orden de Jerarquía</label>
                        <input type="number" name="orden" value="{{ $personal->count() + 1 }}" class="figma-input w-24 px-2.5 py-1.5 text-xs font-mono">
                    </div>

                    <button type="submit" class="figma-btn-primary w-full justify-center text-xs py-2 mt-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        <span>Designar como Autoridad</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Listado de Personal -->
        <div class="lg:col-span-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                @forelse($personal as $p)
                    <div class="figma-card p-4 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-12 h-12 rounded-full border border-figma-border-subtle overflow-hidden bg-figma-bg-deep flex-shrink-0 flex items-center justify-center">
                                    <img src="{{ $p->foto_url }}" alt="{{ $p->nombre }}" class="w-full h-full object-cover" onerror="this.src='/assets/placeholder-user.png'">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-bold text-figma-text-primary text-sm truncate">{{ $p->nombre }}</h3>
                                    <span class="inline-block text-[10px] font-bold text-figma-brand bg-figma-bg-deep border border-figma-border-subtle px-1.5 py-0.5 rounded">
                                        {{ $p->cargo }}
                                    </span>
                                </div>
                                <span class="text-[10px] bg-figma-bg-deep text-figma-text-tertiary px-1.5 py-0.5 rounded font-mono font-bold">#{{ $p->orden }}</span>
                            </div>

                            <p class="text-xs text-figma-text-secondary italic p-2 rounded bg-figma-bg-deep border border-figma-border-subtle line-clamp-3">
                                "{{ $p->comentario ?: 'Sin comentario institucional cargado.' }}"
                            </p>

                            @if($p->email)
                                <span class="text-[10px] text-figma-text-tertiary font-mono mt-2 block">{{ $p->email }}</span>
                            @endif
                        </div>

                        <div class="mt-3 pt-2.5 border-t border-figma-border-subtle flex items-center justify-end">
                            <form action="{{ route('app_admin.personal.destroy', $p->id) }}" method="POST" onsubmit="return confirm('¿Quitar a este referente de las autoridades institucionales?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-semibold px-2 py-1 rounded transition-colors">
                                    Quitar Autoridad
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 figma-card p-8 text-center text-figma-text-tertiary text-xs italic">
                        No hay autoridades o referentes registrados actualmente.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const userSelect = document.getElementById('user_id_select');
    const previewContainer = document.getElementById('selected_user_preview');
    const previewImg = document.getElementById('selected_user_preview_img');
    const previewName = document.getElementById('selected_user_preview_name');
    const previewCargo = document.getElementById('selected_user_preview_cargo');

    if (!userSelect) return;

    userSelect.addEventListener('change', function () {
        const selectedOpt = userSelect.options[userSelect.selectedIndex];

        if (userSelect.value && previewContainer && previewImg && previewName && previewCargo) {
            previewImg.src = selectedOpt.dataset.foto || '/assets/placeholder-user.png';
            previewName.textContent = selectedOpt.dataset.nombre || '';
            previewCargo.textContent = selectedOpt.dataset.cargo || '';
            previewContainer.classList.remove('hidden');
        } else if (previewContainer) {
            previewContainer.classList.add('hidden');
        }
    });
});
</script>
@endsection
