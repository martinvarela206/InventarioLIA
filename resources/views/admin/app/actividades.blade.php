@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('app_admin.index') }}" class="hover:underline">Gestión App</a>
                <span>/</span>
                <span class="text-gray-900 font-bold">Actividades y Novedades</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                <span class="inline-flex items-center gap-2">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                    Posteos y Actividades del LIA (Feed)
                </span>
                <span class="text-xs bg-amber-100 text-amber-900 font-extrabold px-2.5 py-0.5 rounded-full border border-amber-300">
                    {{ $actividades->count() }} publicaciones
                </span>
            </h1>
            <p class="text-sm text-gray-600 mt-0.5">
                Realiza posteos tipo Instagram con fotografías o comunicados breves para anunciar eventos, talleres y novedades del laboratorio.
            </p>
        </div>
        <div>
            <a href="{{ url('/#actividades-lia') }}" target="_blank" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm transition-all">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                Ver Feed en Portada
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
        <!-- Formulario Crear Publicación -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sticky top-20">
                <h2 class="font-bold text-gray-900 text-base mb-1">Nuevo Posteo / Anuncio</h2>
                <p class="text-xs text-gray-500 mb-4">Publicar novedad al feed institucional del LIA.</p>

                <form action="{{ route('app_admin.actividades.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Título de la Actividad *</label>
                        <input type="text" name="titulo" required placeholder="Ej. Taller de Hardware y Limpieza Preventiva" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 font-bold">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Mensaje / Contenido del Post *</label>
                        <textarea name="contenido" rows="5" required placeholder="Escribe el anuncio o mensaje para los estudiantes y docentes..." class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 leading-relaxed"></textarea>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Subir Foto para el Post (Opcional)</label>
                        <input type="file" name="foto" accept="image/*" class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-100 file:text-amber-900 hover:file:bg-amber-200 cursor-pointer">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">O Ruta / URL de Imagen</label>
                        <input type="text" name="imagen_url" placeholder="/assets/notebook-wide.png" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs font-mono text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-xs font-bold uppercase mb-1">Fecha de la Publicación</label>
                        <input type="datetime-local" name="fecha_publicacion" value="{{ now()->format('Y-m-d\TH:i') }}" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>

                    <button type="submit" class="w-full bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold py-2.5 px-4 rounded-xl shadow text-sm transition-colors mt-2 inline-flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Publicar en el Feed
                    </button>
                </form>
            </div>
        </div>

        <!-- Listado de Publicaciones del Feed -->
        <div class="lg:col-span-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($actividades as $act)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col justify-between">
                        <!-- Cabecera estilo post -->
                        <div class="p-3.5 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-amber-400 text-black font-black text-[10px] flex items-center justify-center">LIA</span>
                                <div>
                                    <span class="text-xs font-bold text-gray-900 block leading-tight">nexo.lia.unsj</span>
                                    <span class="text-[10px] text-gray-400">{{ $act->fecha_publicacion?->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                            <span class="text-[10px] bg-emerald-50 text-emerald-800 border border-emerald-200 px-2 py-0.5 rounded font-bold">Publicado</span>
                        </div>

                        <!-- Imagen si posee -->
                        @if($act->imagen_path)
                            <div class="h-44 bg-gray-100 overflow-hidden">
                                <img src="{{ $act->imagen_path }}" alt="{{ $act->titulo }}" class="w-full h-full object-cover" onerror="this.src='/assets/notebook-wide.png'">
                            </div>
                        @endif

                        <!-- Contenido -->
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm mb-1.5">{{ $act->titulo }}</h3>
                                <p class="text-xs text-gray-600 whitespace-pre-line line-clamp-4">{{ $act->contenido }}</p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-[11px]">
                                <span class="text-gray-400">Por: <strong>{{ $act->autor?->nombre ?? 'Admin' }}</strong></span>
                                <form action="{{ route('app_admin.actividades.destroy', $act->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta publicación del feed?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 font-bold rounded-xl px-3 py-1 text-xs transition-colors">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 bg-white rounded-2xl p-10 text-center text-gray-400 text-xs italic">
                        No hay actividades publicadas en el feed aún.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
