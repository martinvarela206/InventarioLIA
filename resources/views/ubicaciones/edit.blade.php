@extends('layouts.app')

@section('content')
<div class="max-w-[700px] mx-auto py-4 space-y-4">

    <!-- Header Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('ubicaciones.index') }}" class="hover:text-figma-brand transition-colors">Espacios Físicos</a>
                <span>/</span>
                <span class="text-figma-text-primary">{{ $ubicacion->nombre }}</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <span>Editar Espacio: {{ $ubicacion->nombre }}</span>
            </h1>
        </div>
        <div>
            <a href="{{ route('ubicaciones.index') }}" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver a Espacios</span>
            </a>
        </div>
    </div>

    <!-- Error Alerts -->
    @if($errors->any())
        <div class="figma-card p-3 border-rose-500/40 text-rose-400 text-xs">
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulario -->
    <form action="{{ route('ubicaciones.update', $ubicacion->id) }}" method="POST" class="figma-panel p-5 sm:p-6 space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label for="nombre" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">
                Nombre de la Sala / Espacio <span class="text-rose-400">*</span>
            </label>
            <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $ubicacion->nombre) }}" required class="figma-input w-full px-3 py-1.5 text-xs font-semibold">
            <p class="text-[10px] text-figma-text-tertiary mt-1">Debe ser único en el sistema.</p>
        </div>

        <div>
            <label for="descripcion" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">
                Descripción / Detalle de Ubicación (Opcional)
            </label>
            <textarea name="descripcion" id="descripcion" rows="3" class="figma-input w-full px-3 py-1.5 text-xs">{{ old('descripcion', $ubicacion->descripcion) }}</textarea>
            <p class="text-[10px] text-figma-text-tertiary mt-1">Información de referencia sobre la sala, capacidad o sector.</p>
        </div>

        <div class="pt-3 border-t border-figma-border-subtle flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <span class="text-xs text-figma-text-tertiary font-mono">
                Activos registrados: <span class="text-figma-text-primary font-bold">{{ $ubicacion->movimientos()->count() }} movimientos</span>
            </span>

            <div class="flex items-center gap-2 self-end sm:self-auto">
                <a href="{{ route('ubicaciones.index') }}" class="figma-btn-secondary text-xs">
                    Cancelar
                </a>
                <button type="submit" class="figma-btn-primary text-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Guardar Cambios</span>
                </button>
            </div>
        </div>
    </form>

</div>
@endsection
