@extends('layouts.app')

@section('content')
<div class="max-w-[700px] mx-auto py-4 space-y-4">

    <!-- Header Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('ubicaciones.index') }}" class="hover:text-figma-brand transition-colors">Espacios Físicos</a>
                <span>/</span>
                <span class="text-figma-text-primary">Nuevo</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <span>Registrar Nuevo Espacio o Sala</span>
            </h1>
        </div>
        <div>
            <a href="{{ route('ubicaciones.index') }}" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver a Espacios</span>
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="figma-card p-3 border-rose-500/40 text-rose-400 text-xs">
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('ubicaciones.store') }}" method="POST" class="figma-panel p-5 sm:p-6 space-y-4">
        @csrf

        <div>
            <label for="nombre" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">
                Nombre de la Sala / Espacio <span class="text-rose-400">*</span>
            </label>
            <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required placeholder="Ej. Laboratorio D, Sala de Servidores 2, Depósito..." class="figma-input w-full px-3 py-1.5 text-xs font-semibold">
            <p class="text-[10px] text-figma-text-tertiary mt-1">Identificador único en el laboratorio.</p>
        </div>

        <div>
            <label for="descripcion" class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">
                Descripción / Detalle de Ubicación (Opcional)
            </label>
            <textarea name="descripcion" id="descripcion" rows="3" placeholder="Ej. Laboratorio para cátedras avanzadas en Planta Alta..." class="figma-input w-full px-3 py-1.5 text-xs">{{ old('descripcion') }}</textarea>
        </div>

        <div class="pt-3 border-t border-figma-border-subtle flex items-center justify-end gap-2">
            <a href="{{ route('ubicaciones.index') }}" class="figma-btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="figma-btn-primary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Guardar Espacio Físico</span>
            </button>
        </div>
    </form>
</div>
@endsection
