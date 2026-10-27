@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('app_admin.index') }}" class="hover:underline">Gestión App</a>
                <span>/</span>
                <span class="text-gray-900 font-bold">Personal y Autoridades</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Figuras y Autoridades del LIA</span>
                <span class="text-xs bg-amber-100 text-amber-900 font-extrabold px-2.5 py-0.5 rounded-full border border-amber-300">
                    {{ $personal->count() }} miembros
                </span>
            </h1>
            <p class="text-sm text-gray-600 mt-0.5">
                Designa a los usuarios del sistema que figuran como referentes institucionales en la portada. Sus nombres, cargos y fotos oficiales se sincronizan en vivo desde sus perfiles.
            </p>
        </div>
        <div>
            <a href="{{ url('/#personal-lia') }}" target="_blank" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm transition-all">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Ver en Landing Page</span>
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
        <div class="bg-rose-50 border border-rose-300 text-rose-900 text-sm font-semibold px-4 py-3 rounded-xl shadow-sm">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Formulario Alta de Personal -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sticky top-20">
                <h2 class="font-bold text-gray-900 text-base mb-1">Designar Autoridad / Referente</h2>
                <p class="text-xs text-gray-500 mb-4">Selecciona un usuario registrado del LIA para destacarlo en la portada.</p>

                <form action="{{ route('app_admin.personal.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Selector de Usuario del Sistema -->
                    <div class="bg-amber-50/70 p-3.5 rounded-xl border border-amber-200">
                        <label class="block text-amber-950 text-xs font-black uppercase mb-1 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Usuario del Sistema *</span>
                        </label>
                        <select name="user_id" id="user_id_select" required class="w-full border border-amber-300 rounded-xl px-3 py-2 text-xs font-bold text-gray-900 bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                            <option value="">-- Seleccionar usuario activo --</option>
                            @foreach($usuarios as $u)
                                <option value="{{ $u->id }}"
                                    data-nombre="{{ $u->nombre }}"
                                    data-cargo="{{ $u->cargo_actual ?: ($u->cargo ? $u->cargo->nombre : 'Sin cargo oficial') }}"
                                    data-email="{{ $u->email }}"
                                    data-foto="{{ $u->foto_url }}">
                                    {{ $u->nombre }} ({{ $u->cargo_actual ?: ($u->cargo ? $u->cargo->nombre : 'Sin cargo') }}) - {{ $u->email }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Preview visual del usuario seleccionado -->
                        <div id="selected_user_preview" class="hidden mt-3 p-3 bg-white rounded-xl border border-amber-300 flex items-center gap-3 shadow-xs">
                            <img id="selected_user_preview_img" src="/assets/placeholder-user.png" alt="Avatar" class="w-10 h-10 rounded-full object-cover border-2 border-amber-400 flex-shrink-0">
                            <div class="min-w-0 flex-1">
                                <p id="selected_user_preview_name" class="text-xs font-black text-gray-900 truncate"></p>
                                <p id="selected_user_preview_cargo" class="text-[11px] font-bold text-amber-900 truncate"></p>
                                <span class="text-[10px] text-emerald-700 font-bold block mt-0.5">✓ Sincronizado en tiempo real</span>
                            </div>
                        </div>

                        <p class="text-[11px] text-amber-900 mt-2 leading-snug">
                            Solo figuran usuarios activos con huella digital vigente que aún no hayan sido designados.
                        </p>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Comentario Institucional / Horario de Consulta</label>
                        <textarea name="comentario" rows="3" placeholder="Ej: Dirección general, coordinación de investigación, atención martes y jueves..." class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400"></textarea>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Orden de Jerarquía</label>
                        <input type="number" name="orden" value="{{ $personal->count() + 1 }}" class="w-24 border border-gray-300 rounded-xl px-3 py-1.5 text-sm font-mono text-gray-800">
                    </div>

                    <button type="submit" class="w-full bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold py-2.5 px-4 rounded-xl shadow text-sm transition-colors mt-2 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        <span>Designar como Autoridad</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Listado de Personal -->
        <div class="lg:col-span-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($personal as $p)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-14 h-14 rounded-full border-2 border-amber-300 overflow-hidden bg-gray-100 flex-shrink-0 flex items-center justify-center shadow-sm">
                                    <img src="{{ $p->foto_url }}" alt="{{ $p->nombre }}" class="w-full h-full object-cover" onerror="this.src='/assets/placeholder-user.png'">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-bold text-gray-900 text-base truncate">{{ $p->nombre }}</h3>
                                    <span class="inline-block text-xs font-bold text-amber-900 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded">
                                        {{ $p->cargo }}
                                    </span>
                                </div>
                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded font-mono font-bold">#{{ $p->orden }}</span>
                            </div>

                            <div class="mb-2">
                                <span class="inline-flex items-center gap-1 text-[11px] bg-emerald-50 text-emerald-800 border border-emerald-200 px-2 py-0.5 rounded-full font-bold">
                                    <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    <span>Usuario del Sistema Sincronizado</span>
                                </span>
                            </div>

                            <p class="text-xs text-gray-600 italic bg-gray-50 p-3 rounded-xl border border-gray-100 line-clamp-3">
                                "{{ $p->comentario ?: 'Sin comentario institucional cargado.' }}"
                            </p>

                            @if($p->email)
                                <span class="text-[11px] text-gray-500 font-mono mt-2 block">{{ $p->email }}</span>
                            @endif
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-end">
                            <form action="{{ route('app_admin.personal.destroy', $p->id) }}" method="POST" onsubmit="return confirm('¿Quitar a este referente de las autoridades institucionales?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 font-bold rounded-xl px-3 py-1 text-xs transition-colors">
                                    Quitar Autoridad
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 bg-white rounded-2xl p-10 text-center text-gray-400 text-xs italic">
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
