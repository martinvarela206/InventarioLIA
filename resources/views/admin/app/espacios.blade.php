@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('app_admin.index') }}" class="hover:underline">Gestión App</a>
                <span>/</span>
                <span class="text-gray-900 font-bold">Espacios del LIA</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                <span>🏢 Espacios y Fotos de Laboratorios</span>
                <span class="text-xs bg-amber-100 text-amber-900 font-extrabold px-2.5 py-0.5 rounded-full border border-amber-300">
                    {{ $espacios->count() }} salas
                </span>
            </h1>
            <p class="text-sm text-gray-600 mt-0.5">
                Publica fotografías, nombres y descripciones de las salas y laboratorios para la sección pública de la landing page.
            </p>
        </div>
        <div>
            <a href="{{ url('/#espacios-lia') }}" target="_blank" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm transition-all">
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
        <!-- Formulario Alta de Espacio -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sticky top-20">
                <h2 class="font-bold text-gray-900 text-base mb-1">Nuevo Espacio / Foto</h2>
                <p class="text-xs text-gray-500 mb-4">Añadir una sala con fotografía a la portada.</p>

                <form action="{{ route('app_admin.espacios.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Nombre / Título de la Sala *</label>
                        <input type="text" name="titulo" required placeholder="Ej. Laboratorio Federico Sánchez" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 font-medium">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Descripción del Espacio</label>
                        <textarea name="descripcion" rows="3" placeholder="Capacidad, puestos equipados, proyectores, finalidad académica..." class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400"></textarea>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Subir Fotografía (JPG, PNG)</label>
                        <input type="file" name="foto" accept="image/*" class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-100 file:text-amber-900 hover:file:bg-amber-200 cursor-pointer">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">O Ruta / URL de Imagen</label>
                        <input type="text" name="imagen_url" placeholder="/assets/notebook-wide.png" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs font-mono text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Orden de Visualización</label>
                        <input type="number" name="orden" value="{{ $espacios->count() + 1 }}" class="w-24 border border-gray-300 rounded-xl px-3 py-1.5 text-sm font-mono text-gray-800">
                    </div>

                    <button type="submit" class="w-full bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold py-2.5 px-4 rounded-xl shadow text-sm transition-colors mt-2">
                        📸 Publicar Espacio
                    </button>
                </form>
            </div>
        </div>

        <!-- Listado de Espacios -->
        <div class="lg:col-span-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($espacios as $espacio)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col justify-between">
                        <div class="h-40 bg-gray-100 relative overflow-hidden">
                            <img src="{{ $espacio->imagen_path ?: '/assets/notebook-wide.png' }}" alt="{{ $espacio->titulo }}" class="w-full h-full object-cover" onerror="this.src='/assets/notebook-wide.png'">
                            <span class="absolute top-2 left-2 bg-black/70 backdrop-blur-sm text-white text-[10px] font-bold px-2 py-0.5 rounded">
                                Orden: {{ $espacio->orden }}
                            </span>
                            @if($espacio->activo)
                                <span class="absolute top-2 right-2 bg-emerald-500 text-white text-[10px] font-bold px-2 py-0.5 rounded">
                                    Visible
                                </span>
                            @else
                                <span class="absolute top-2 right-2 bg-gray-500 text-white text-[10px] font-bold px-2 py-0.5 rounded">
                                    Oculto
                                </span>
                            @endif
                        </div>

                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-gray-900 text-base mb-1">{{ $espacio->titulo }}</h3>
                                <p class="text-xs text-gray-600 line-clamp-3">{{ $espacio->descripcion ?: 'Sin descripción cargada.' }}</p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                                <span class="text-[11px] text-gray-400 font-mono truncate max-w-[150px]">{{ $espacio->imagen_path }}</span>
                                <form action="{{ route('app_admin.espacios.destroy', $espacio->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este espacio del catálogo?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-bold hover:underline">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 bg-white rounded-2xl p-10 text-center text-gray-400 text-xs italic">
                        No hay espacios cargados aún.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
