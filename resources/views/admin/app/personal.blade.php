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
                <span>👥 Figuras y Autoridades del LIA</span>
                <span class="text-xs bg-amber-100 text-amber-900 font-extrabold px-2.5 py-0.5 rounded-full border border-amber-300">
                    {{ $personal->count() }} miembros
                </span>
            </h1>
            <p class="text-sm text-gray-600 mt-0.5">
                Configura a los referentes principales del LIA (Dirección, Coordinadores, Seguridad ante emergencias) y sus comentarios institucionales para la comunidad.
            </p>
        </div>
        <div>
            <a href="{{ url('/#personal-lia') }}" target="_blank" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm transition-all">
                🌐 Ver en Landing Page
            </a>
        </div>
    </div>

    <!-- Mensajes -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-300 text-emerald-900 text-sm font-semibold px-4 py-3 rounded-xl shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Formulario Alta de Personal -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sticky top-20">
                <h2 class="font-bold text-gray-900 text-base mb-1">Nueva Autoridad / Referente</h2>
                <p class="text-xs text-gray-500 mb-4">Añadir una figura institucional con rol y comentario.</p>

                <form action="{{ route('app_admin.personal.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Nombre y Apellido *</label>
                        <input type="text" name="nombre" required placeholder="Ej. Dra. María Elena Fernández" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 font-medium">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Cargo / Rol en el LIA *</label>
                        <input type="text" name="cargo" required placeholder="Ej. Directora del LIA / Coordinador de Seguridad" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 font-medium">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Comentario / Responsabilidades</label>
                        <textarea name="comentario" rows="3" placeholder="Descripción de funciones, horarios de consulta, responsabilidades..." class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400"></textarea>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Correo de Contacto Institucional</label>
                        <input type="email" name="email" placeholder="referente@unsj.edu.ar" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 font-mono">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Subir Foto / Avatar</label>
                        <input type="file" name="foto" accept="image/*" class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-100 file:text-amber-900 hover:file:bg-amber-200 cursor-pointer">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">O Ruta / URL de Foto</label>
                        <input type="text" name="foto_url" placeholder="/assets/flavia.png" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs font-mono text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Orden de Jerarquía</label>
                        <input type="number" name="orden" value="{{ $personal->count() + 1 }}" class="w-24 border border-gray-300 rounded-xl px-3 py-1.5 text-sm font-mono text-gray-800">
                    </div>

                    <button type="submit" class="w-full bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold py-2.5 px-4 rounded-xl shadow text-sm transition-colors mt-2">
                        👤 Registrar Autoridad
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
                                <div class="w-12 h-12 rounded-full border border-amber-300 overflow-hidden bg-gray-100 flex-shrink-0 flex items-center justify-center">
                                    <img src="{{ $p->foto_path ?: '/assets/placeholder-user.png' }}" alt="{{ $p->nombre }}" class="w-full h-full object-cover" onerror="this.src='/assets/placeholder-user.png'">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-bold text-gray-900 text-sm truncate">{{ $p->nombre }}</h3>
                                    <span class="inline-block text-[11px] font-bold text-amber-900 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded">
                                        {{ $p->cargo }}
                                    </span>
                                </div>
                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded font-mono font-bold">#{{ $p->orden }}</span>
                            </div>

                            <p class="text-xs text-gray-600 italic bg-gray-50 p-3 rounded-xl border border-gray-100 line-clamp-3">
                                "{{ $p->comentario ?: 'Sin comentario cargado.' }}"
                            </p>

                            @if($p->email)
                                <span class="text-[11px] text-gray-500 font-mono mt-2 block">{{ $p->email }}</span>
                            @endif
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-end">
                            <form action="{{ route('app_admin.personal.destroy', $p->id) }}" method="POST" onsubmit="return confirm('¿Eliminar a este referente del personal?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-bold hover:underline">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 bg-white rounded-2xl p-10 text-center text-gray-400 text-xs italic">
                        No hay autoridades registradas aún.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
