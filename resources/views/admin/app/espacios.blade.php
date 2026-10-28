@extends('layouts.app')

@section('content')
<div class="max-w-[1320px] mx-auto px-2 space-y-5">
    <!-- Header Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('app_admin.index') }}" class="hover:text-figma-brand transition-colors">Gestión App</a>
                <span>/</span>
                <span class="text-figma-text-primary">Espacios & Galerías</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Espacios y Fotos de Laboratorios</span>
                <span class="font-mono text-xs font-bold bg-figma-bg-deep text-sky-400 px-2 py-0.5 rounded border border-figma-border-subtle">
                    {{ $espacios->count() }} salas
                </span>
            </h1>
        </div>
        <div>
            <a href="{{ url('/#espacios-lia') }}" target="_blank" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Ver Portada</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="figma-card p-3 border-emerald-500/40 text-emerald-400 flex items-center gap-2 text-xs font-semibold">
            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Formulario Alta de Espacio -->
        <div class="lg:col-span-1">
            <div class="figma-panel p-4 sm:p-5 space-y-3 sticky top-16">
                <div>
                    <h2 class="font-bold text-figma-text-primary text-xs uppercase tracking-wider">Nueva Sala / Fotografía</h2>
                    <p class="text-[11px] text-figma-text-tertiary mt-0.5">Añadir una sala para la vitrina de la portada.</p>
                </div>

                <form action="{{ route('app_admin.espacios.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Nombre / Título <span class="text-rose-400">*</span></label>
                        <input type="text" name="titulo" required placeholder="Ej. Laboratorio Federico Sánchez" class="figma-input w-full px-3 py-1.5 text-xs font-medium">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Descripción del Espacio</label>
                        <textarea name="descripcion" rows="3" placeholder="Puestos equipados, finalidad..." class="figma-input w-full px-3 py-1.5 text-xs"></textarea>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Subir Fotografía</label>
                        <input type="file" name="foto" accept="image/*" class="figma-input w-full px-2 py-1 text-xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">O Ruta / URL de Imagen</label>
                        <input type="text" name="imagen_url" placeholder="/assets/notebook-wide.png" class="figma-input w-full px-3 py-1.5 text-xs font-mono">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Orden de Visualización</label>
                        <input type="number" name="orden" value="{{ $espacios->count() + 1 }}" class="figma-input w-24 px-3 py-1.5 text-xs font-mono">
                    </div>

                    <div class="pt-1">
                        <button type="submit" class="figma-btn-primary w-full justify-center py-2 text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Publicar Sala</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Listado de Espacios -->
        <div class="lg:col-span-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                @forelse($espacios as $espacio)
                    <div class="figma-card overflow-hidden flex flex-col justify-between">
                        <div class="h-36 bg-figma-bg-deep relative overflow-hidden">
                            <img src="{{ $espacio->imagen_path ?: '/assets/notebook-wide.png' }}" alt="{{ $espacio->titulo }}" class="w-full h-full object-cover" onerror="this.src='/assets/notebook-wide.png'">
                            <span class="absolute top-2 left-2 bg-black/75 font-mono text-white text-[10px] font-bold px-2 py-0.5 rounded">
                                Orden: {{ $espacio->orden }}
                            </span>
                            @if($espacio->activo)
                                <span class="absolute top-2 right-2 bg-emerald-500 font-mono text-white text-[10px] font-bold px-2 py-0.5 rounded">
                                    Visible
                                </span>
                            @else
                                <span class="absolute top-2 right-2 bg-figma-text-dim font-mono text-white text-[10px] font-bold px-2 py-0.5 rounded">
                                    Oculto
                                </span>
                            @endif
                        </div>

                        <div class="p-3.5 flex-1 flex flex-col justify-between space-y-2">
                            <div>
                                <h3 class="font-bold text-figma-text-primary text-xs mb-0.5">{{ $espacio->titulo }}</h3>
                                <p class="text-[11px] text-figma-text-tertiary line-clamp-2">{{ $espacio->descripcion ?: 'Sin descripción cargada.' }}</p>
                            </div>

                            <div class="pt-2 border-t border-figma-border-subtle flex items-center justify-between text-xs">
                                <span class="text-[10px] text-figma-text-dim font-mono truncate max-w-[130px]">{{ $espacio->imagen_path }}</span>
                                <form action="{{ route('app_admin.espacios.destroy', $espacio->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este espacio del catálogo?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 font-semibold py-1 px-2 rounded hover:bg-rose-500/10 transition-colors">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 figma-panel p-8 text-center text-figma-text-tertiary text-xs italic">
                        No hay espacios cargados aún.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
