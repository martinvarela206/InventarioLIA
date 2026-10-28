@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto py-4 space-y-4">
    <!-- Header Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('app_admin.index') }}" class="hover:text-figma-brand transition-colors">Gestión App</a>
                <span>/</span>
                <span class="text-figma-text-primary">Actividades y Novedades</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <span>Posteos y Actividades del LIA (Feed)</span>
                <span class="text-[10px] bg-figma-bg-deep text-figma-brand font-mono font-bold px-2 py-0.5 rounded border border-figma-border-subtle">
                    {{ $actividades->count() }} publicaciones
                </span>
            </h1>
            <p class="text-xs text-figma-text-secondary mt-0.5">
                Publicaciones para anunciar eventos, talleres y novedades del laboratorio en la portada.
            </p>
        </div>
        <div>
            <a href="{{ url('/#actividades-lia') }}" target="_blank" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Ver Feed en Portada</span>
            </a>
        </div>
    </div>

    <!-- Mensajes -->
    @if(session('success'))
        <div class="figma-card p-3 border-emerald-500/40 text-emerald-400 text-xs">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Formulario Crear Publicación -->
        <div class="lg:col-span-1">
            <div class="figma-panel p-5 sticky top-20 space-y-3.5">
                <h2 class="font-bold text-figma-text-primary text-sm">Nuevo Posteo / Anuncio</h2>
                <p class="text-xs text-figma-text-tertiary">Publicar novedad al feed institucional del LIA.</p>

                <form action="{{ route('app_admin.actividades.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-figma-text-secondary text-[11px] font-bold uppercase mb-1">Título de la Actividad <span class="text-rose-400">*</span></label>
                        <input type="text" name="titulo" required placeholder="Ej. Taller de Hardware y Mantenimiento" class="figma-input w-full px-2.5 py-1.5 text-xs font-semibold">
                    </div>

                    <div>
                        <label class="block text-figma-text-secondary text-[11px] font-bold uppercase mb-1">Mensaje / Contenido del Post <span class="text-rose-400">*</span></label>
                        <textarea name="contenido" rows="4" required placeholder="Escribe el anuncio o mensaje institucional..." class="figma-input w-full px-2.5 py-1.5 text-xs"></textarea>
                    </div>

                    <div>
                        <label class="block text-figma-text-secondary text-[11px] font-bold uppercase mb-1">Subir Foto para el Post (Opcional)</label>
                        <input type="file" name="foto" accept="image/*" class="w-full text-xs text-figma-text-secondary file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-bold file:bg-figma-bg-deep file:text-figma-text-primary hover:file:bg-figma-brand hover:file:text-black cursor-pointer">
                    </div>

                    <div>
                        <label class="block text-figma-text-secondary text-[11px] font-bold uppercase mb-1">O Ruta / URL de Imagen</label>
                        <input type="text" name="imagen_url" placeholder="/assets/notebook-wide.png" class="figma-input w-full px-2.5 py-1.5 text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-figma-text-secondary text-[11px] font-bold uppercase mb-1">Fecha de la Publicación</label>
                        <input type="datetime-local" name="fecha_publicacion" value="{{ now()->format('Y-m-d\TH:i') }}" class="figma-input w-full px-2.5 py-1.5 text-xs font-mono">
                    </div>

                    <button type="submit" class="figma-btn-primary w-full justify-center text-xs py-2 mt-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        <span>Publicar en el Feed</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Listado de Publicaciones del Feed -->
        <div class="lg:col-span-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                @forelse($actividades as $act)
                    <div class="figma-card overflow-hidden flex flex-col justify-between">
                        <!-- Cabecera estilo post -->
                        <div class="p-3 border-b border-figma-border-subtle flex items-center justify-between bg-figma-bg-deep">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded bg-figma-brand text-black font-mono font-extrabold text-[9px] flex items-center justify-center">LIA</span>
                                <div>
                                    <span class="text-xs font-bold text-figma-text-primary block leading-tight">nexo.lia.unsj</span>
                                    <span class="text-[10px] text-figma-text-tertiary font-mono">{{ $act->fecha_publicacion?->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                            <span class="text-[10px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-1.5 py-0.5 rounded font-bold">Publicado</span>
                        </div>

                        <!-- Imagen si posee -->
                        @if($act->imagen_path)
                            <div class="h-40 bg-figma-bg-deep overflow-hidden">
                                <img src="{{ $act->imagen_path }}" alt="{{ $act->titulo }}" class="w-full h-full object-cover" onerror="this.src='/assets/notebook-wide.png'">
                            </div>
                        @endif

                        <!-- Contenido -->
                        <div class="p-3.5 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-figma-text-primary text-xs mb-1">{{ $act->titulo }}</h3>
                                <p class="text-xs text-figma-text-secondary whitespace-pre-line line-clamp-4 leading-relaxed">{{ $act->contenido }}</p>
                            </div>

                            <div class="mt-3 pt-2.5 border-t border-figma-border-subtle flex items-center justify-between text-[10px]">
                                <span class="text-figma-text-tertiary">Por: <strong class="text-figma-text-primary">{{ $act->autor?->nombre ?? 'Admin' }}</strong></span>
                                <form action="{{ route('app_admin.actividades.destroy', $act->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta publicación del feed?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 font-semibold px-2 py-1 rounded transition-colors">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 figma-card p-8 text-center text-figma-text-tertiary text-xs italic">
                        No hay actividades publicadas en el feed aún.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
