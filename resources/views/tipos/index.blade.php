@extends('layouts.app')

@section('content')
<div class="max-w-[1320px] mx-auto px-2 space-y-5">

    <!-- Header Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('dashboard.index') }}" class="hover:text-figma-brand transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-figma-text-primary">Administración</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <svg class="w-5 h-5 text-figma-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span>Tipos y Familias de Activos</span>
            </h1>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('tipos.create') }}" class="figma-btn-primary">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Nuevo Tipo</span>
            </a>
            <button type="button" onclick="document.getElementById('modalNuevoAtributo').classList.remove('hidden')" class="figma-btn-secondary">
                <svg class="w-3.5 h-3.5 text-figma-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Crear Atributo Global</span>
            </button>
        </div>
    </div>

    <!-- Tabla de Tipos y Atributos Asignados (Figma Layers Explorer) -->
    <div class="figma-panel overflow-hidden">
        <div class="p-3 border-b border-figma-border-subtle bg-figma-card/50 flex justify-between items-center text-xs">
            <span class="font-bold text-figma-text-primary uppercase tracking-wider text-[11px]">
                Catálogo de Familias de Activos ({{ $tipos->total() }})
            </span>
            <span class="font-mono text-[10px] text-figma-text-tertiary">
                Estructura normalizada • Atributos vinculados por pivot
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-figma-card/80 border-b border-figma-border-subtle text-figma-text-tertiary font-semibold uppercase text-[10px] tracking-wider">
                        <th class="py-2.5 px-3.5 w-12 text-center">#</th>
                        <th class="py-2.5 px-3.5 w-48">Tipo de Activo</th>
                        <th class="py-2.5 px-3.5">Descripción General</th>
                        <th class="py-2.5 px-3.5">Atributos Técnicos (Pivot)</th>
                        <th class="py-2.5 px-3.5 text-center w-28">Total Activos</th>
                        <th class="py-2.5 px-3.5 text-right w-36">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-figma-border-subtle text-figma-text-secondary">
                    @forelse($tipos as $idx => $tipo)
                        <tr class="hover:bg-figma-hover/50 transition-colors">
                            <td class="py-2.5 px-3.5 text-center font-mono text-figma-text-tertiary text-xs">
                                {{ $tipos->firstItem() + $idx }}
                            </td>
                            <td class="py-2.5 px-3.5">
                                <span class="font-bold text-figma-text-primary block capitalize text-xs">
                                    {{ $tipo->nombre }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3.5 text-figma-text-tertiary text-[11px]">
                                {{ $tipo->descripcion ?: '-' }}
                            </td>
                            <td class="py-2.5 px-3.5">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($tipo->atributos as $attr)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded font-mono text-[10px] font-semibold bg-figma-bg-deep text-figma-text-secondary border border-figma-border-subtle">
                                            {{ $attr->nombre }}
                                            @if($attr->unidad_medida)
                                                <span class="text-figma-text-tertiary ml-1">({{ $attr->unidad_medida }})</span>
                                            @endif
                                        </span>
                                    @empty
                                        <span class="text-figma-text-tertiary italic text-[11px]">Solo columnas base</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="py-2.5 px-3.5 text-center">
                                <span class="inline-block px-2 py-0.5 rounded font-mono text-xs font-bold {{ $tipo->elementos_count > 0 ? 'bg-figma-bg-deep text-figma-brand border border-figma-border-subtle' : 'text-figma-text-tertiary' }}">
                                    {{ $tipo->elementos_count }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3.5 text-right space-x-1 whitespace-nowrap">
                                <a href="{{ route('tipos.edit', $tipo->id) }}" class="figma-btn-secondary text-[11px] py-1 px-2">
                                    Configurar
                                </a>
                                @if($tipo->elementos_count === 0)
                                    <form action="{{ route('tipos.destroy', $tipo->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro que deseas eliminar el tipo {{ $tipo->nombre }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-rose-400 hover:text-rose-300 py-1 px-2 rounded hover:bg-rose-500/10 transition-colors">
                                            Eliminar
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-figma-text-tertiary italic text-xs">
                                No se encontraron tipos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-t border-figma-border-subtle bg-figma-card/50">
            {{ $tipos->links() }}
        </div>
    </div>
</div>

<!-- Modal para Crear Nuevo Atributo Técnico Global (Figma Studio Dialog) -->
<div id="modalNuevoAtributo" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="figma-panel max-w-md w-full p-5 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between border-b border-figma-border-subtle pb-3">
            <h3 class="font-bold text-figma-text-primary text-sm">Crear Atributo Técnico Global</h3>
            <button type="button" onclick="document.getElementById('modalNuevoAtributo').classList.add('hidden')" class="text-figma-text-tertiary hover:text-figma-text-primary text-sm font-bold" aria-label="Cerrar modal">
                ✕
            </button>
        </div>

        <form action="{{ route('atributos.store') }}" method="POST" class="space-y-3.5 text-xs">
            @csrf
            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Nombre del Atributo <span class="text-rose-400">*</span></label>
                <input type="text" name="nombre" placeholder="Ej: Potencia, Puertos, Pulgadas, Voltaje..." class="figma-input w-full px-3 py-1.5 text-xs" required>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Tipo de Dato <span class="text-rose-400">*</span></label>
                    <select name="tipo_dato" class="figma-input w-full px-3 py-1.5 text-xs cursor-pointer">
                        <option value="texto">Texto</option>
                        <option value="numero">Numérico</option>
                        <option value="selector">Selector / Opciones</option>
                        <option value="fecha">Fecha</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Unidad de Medida</label>
                    <input type="text" name="unidad_medida" placeholder="Ej: GB, GHz, Watts..." class="figma-input w-full px-3 py-1.5 text-xs">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Descripción</label>
                <input type="text" name="descripcion" placeholder="Aclaración técnica para el operador..." class="figma-input w-full px-3 py-1.5 text-xs">
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-figma-border-subtle">
                <button type="button" onclick="document.getElementById('modalNuevoAtributo').classList.add('hidden')" class="figma-btn-secondary">
                    Cancelar
                </button>
                <button type="submit" class="figma-btn-primary">
                    Guardar Atributo
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
