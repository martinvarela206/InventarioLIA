@extends('layouts.app')

@section('content')
<div class="max-w-[800px] mx-auto py-4 space-y-4">

    <!-- Header Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('tipos.index') }}" class="hover:text-figma-brand transition-colors">Tipos de Activos</a>
                <span>/</span>
                <span class="text-figma-text-primary capitalize">{{ $tipo->nombre }}</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <span>Configurar Tipo: {{ $tipo->nombre }}</span>
            </h1>
        </div>
        <div>
            <a href="{{ route('tipos.index') }}" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver a Tipos</span>
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

    <form action="{{ route('tipos.update', $tipo->id) }}" method="POST" class="figma-panel p-5 sm:p-6 space-y-5">
        @csrf
        @method('PUT')

        <!-- Datos Base del Tipo -->
        <div class="space-y-3.5">
            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Nombre de la Familia / Tipo <span class="text-rose-400">*</span></label>
                <input type="text" name="nombre" value="{{ old('nombre', $tipo->nombre) }}" class="figma-input w-full px-3 py-1.5 text-xs font-semibold capitalize" required>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Descripción</label>
                <input type="text" name="descripcion" value="{{ old('descripcion', $tipo->descripcion) }}" class="figma-input w-full px-3 py-1.5 text-xs">
            </div>
        </div>

        <!-- Atributos Técnicos Específicos (Pivot tipo_atributo) -->
        <div class="pt-3 border-t border-figma-border-subtle space-y-3">
            <div>
                <h3 class="text-xs font-bold uppercase text-figma-text-primary tracking-wider">Atributos Técnicos Vinculados (Pivot)</h3>
                <p class="text-[11px] text-figma-text-tertiary mt-0.5">Selecciona qué campos adicionales se deben solicitar para los activos de esta familia.</p>
            </div>

            @php
                $asignadosIds = $tipo->atributos->pluck('id')->toArray();
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 figma-card p-3">
                @foreach($atributos as $attr)
                    <label class="flex items-center gap-2.5 p-2 rounded bg-figma-bg-deep border border-figma-border-subtle hover:border-figma-brand cursor-pointer transition-colors">
                        <input type="checkbox" name="atributos[]" value="{{ $attr->id }}" {{ in_array($attr->id, old('atributos', $asignadosIds)) ? 'checked' : '' }} class="rounded text-figma-brand focus:ring-figma-brand">
                        <div class="text-xs">
                            <span class="font-bold text-figma-text-primary block">{{ $attr->nombre }}</span>
                            @if($attr->unidad_medida)
                                <span class="text-[10px] text-figma-text-tertiary">Unidad: {{ $attr->unidad_medida }}</span>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>

            <!-- Sección Dinámica para definir múltiples nuevos atributos al vuelo -->
            <div class="p-3.5 figma-card space-y-2.5 border-figma-brand/30">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-figma-text-primary block">¿Agregar nuevos atributos técnicos a la plataforma?</span>
                        <p class="text-[11px] text-figma-text-tertiary">Puedes agregar uno o más atributos y vincularlos a este tipo de activo.</p>
                    </div>
                    <button type="button" id="btn-agregar-atributo" class="figma-btn-secondary text-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Añadir fila</span>
                    </button>
                </div>

                <!-- Contenedor dinámico de filas de nuevos atributos -->
                <div id="contenedor-nuevos-atributos" class="space-y-2">
                    <div class="fila-nuevo-atributo flex flex-col sm:flex-row items-stretch sm:items-center gap-2 p-2 rounded bg-figma-bg-deep border border-figma-border-subtle">
                        <input type="text" name="nuevos_atributos_nombre[]" placeholder="Nombre (ej: Tasa de Muestreo, Interfaz, Voltaje)..." class="figma-input flex-1 px-2.5 py-1 text-xs">
                        <input type="text" name="nuevos_atributos_unidad[]" placeholder="Unidad (ej: MHz, V)..." class="figma-input sm:w-36 px-2.5 py-1 text-xs">
                        <select name="nuevos_atributos_tipo[]" class="figma-input sm:w-32 px-2.5 py-1 text-xs">
                            <option value="texto">Texto</option>
                            <option value="numero">Número</option>
                            <option value="fecha">Fecha</option>
                            <option value="booleano">Booleano</option>
                        </select>
                        <button type="button" class="btn-eliminar-fila p-1.5 text-figma-text-tertiary hover:text-rose-400 rounded transition-colors" title="Eliminar este atributo">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-figma-border-subtle">
            <a href="{{ route('tipos.index') }}" class="figma-btn-secondary text-xs">
                Cancelar
            </a>
            <button type="submit" class="figma-btn-primary text-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Guardar Cambios</span>
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const contenedor = document.getElementById('contenedor-nuevos-atributos');
        const btnAgregar = document.getElementById('btn-agregar-atributo');

        if (btnAgregar && contenedor) {
            btnAgregar.addEventListener('click', function () {
                const fila = document.createElement('div');
                fila.className = 'fila-nuevo-atributo flex flex-col sm:flex-row items-stretch sm:items-center gap-2 p-2 rounded bg-figma-bg-deep border border-figma-border-subtle';
                fila.innerHTML = `
                    <input type="text" name="nuevos_atributos_nombre[]" placeholder="Nombre (ej: Tasa de Muestreo, Interfaz)..." class="figma-input flex-1 px-2.5 py-1 text-xs">
                    <input type="text" name="nuevos_atributos_unidad[]" placeholder="Unidad (ej: MHz, V)..." class="figma-input sm:w-36 px-2.5 py-1 text-xs">
                    <select name="nuevos_atributos_tipo[]" class="figma-input sm:w-32 px-2.5 py-1 text-xs">
                        <option value="texto">Texto</option>
                        <option value="numero">Número</option>
                        <option value="fecha">Fecha</option>
                        <option value="booleano">Booleano</option>
                    </select>
                    <button type="button" class="btn-eliminar-fila p-1.5 text-figma-text-tertiary hover:text-rose-400 rounded transition-colors" title="Eliminar este atributo">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                `;
                contenedor.appendChild(fila);
                fila.querySelector('input').focus();
            });

            contenedor.addEventListener('click', function (e) {
                const btnEliminar = e.target.closest('.btn-eliminar-fila');
                if (btnEliminar) {
                    const fila = btnEliminar.closest('.fila-nuevo-atributo');
                    if (fila) {
                        const todasFilas = contenedor.querySelectorAll('.fila-nuevo-atributo');
                        if (todasFilas.length > 1) {
                            fila.remove();
                        } else {
                            fila.querySelectorAll('input').forEach(i => i.value = '');
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
