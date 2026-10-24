@extends('layouts.app')

@section('content')
<div class="max-w-[800px] mx-auto px-2 space-y-6">

    <!-- Header -->
    <div class="border-b border-gray-200 pb-4">
        <a href="{{ route('tipos.index') }}" class="text-xs font-bold text-gray-500 hover:text-black flex items-center gap-1 mb-2">
            ← Volver a Tipos de Activos
        </a>
        <h1 class="text-2xl font-black text-gray-900">Crear Nuevo Tipo de Activo</h1>
        <p class="text-xs text-gray-500 mt-0.5">
            Define una nueva familia de activos y selecciona los atributos técnicos que se solicitarán al dar de alta elementos de este tipo.
        </p>
    </div>

    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 text-xs rounded-xl font-medium">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('tipos.store') }}" method="POST" class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-6">
        @csrf

        <!-- Datos Base del Tipo -->
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-800 mb-1">Nombre de la Familia / Tipo *</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ej: Router, Impresora 3D, Dron, Sensor..." class="w-full text-xs px-3 py-2 border rounded-lg bg-gray-50 focus:bg-white" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-800 mb-1">Descripción</label>
                <input type="text" name="descripcion" value="{{ old('descripcion') }}" placeholder="Breve descripción del equipamiento comprendido..." class="w-full text-xs px-3 py-2 border rounded-lg bg-gray-50 focus:bg-white">
            </div>
        </div>

        <!-- Atributos Técnicos Específicos (Pivot tipo_atributo) -->
        <div class="pt-4 border-t border-gray-200 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-black uppercase text-gray-800 tracking-wider">Atributos Técnicos que Aplican (Pivot)</h3>
                    <p class="text-[11px] text-gray-500">Marca los campos adicionales que deben registrarse para este tipo de hardware.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-gray-50 p-4 rounded-xl border border-gray-200">
                @foreach($atributos as $attr)
                    <label class="flex items-center gap-2.5 p-2 bg-white rounded-lg border border-gray-200 hover:border-amber-400 cursor-pointer transition-all">
                        <input type="checkbox" name="atributos[]" value="{{ $attr->id }}" {{ in_array($attr->id, old('atributos', [])) ? 'checked' : '' }} class="rounded border-gray-300 text-amber-500 focus:ring-amber-400">
                        <div class="text-xs">
                            <span class="font-bold text-gray-800 block">{{ $attr->nombre }}</span>
                            @if($attr->unidad_medida)
                                <span class="text-[10px] text-gray-400">Unidad: {{ $attr->unidad_medida }}</span>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>

            <!-- Sección Dinámica para definir múltiples nuevos atributos al vuelo -->
            <div class="p-4 bg-amber-50/60 rounded-xl border border-amber-200 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-amber-900 block">¿Necesitas atributos que no están en la lista? Añádelos aquí:</span>
                        <p class="text-[11px] text-amber-700">Puedes agregar todos los atributos técnicos que hagan falta y remover los que no correspondan.</p>
                    </div>
                    <button type="button" id="btn-agregar-atributo" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold bg-amber-400 hover:bg-amber-500 text-black rounded-lg shadow-sm transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        + Añadir otro atributo
                    </button>
                </div>

                <!-- Contenedor dinámico de filas de nuevos atributos -->
                <div id="contenedor-nuevos-atributos" class="space-y-2">
                    <div class="fila-nuevo-atributo flex flex-col sm:flex-row items-stretch sm:items-center gap-2 p-2 bg-white rounded-lg border border-amber-200 shadow-2xs">
                        <input type="text" name="nuevos_atributos_nombre[]" placeholder="Nombre (ej: Tasa de Muestreo, Interfaz, Voltaje)..." class="flex-1 text-xs px-2.5 py-1.5 border border-gray-300 rounded-lg bg-white focus:ring-1 focus:ring-amber-400">
                        <input type="text" name="nuevos_atributos_unidad[]" placeholder="Unidad (ej: MHz, V, Pulgadas)..." class="sm:w-44 text-xs px-2.5 py-1.5 border border-gray-300 rounded-lg bg-white focus:ring-1 focus:ring-amber-400">
                        <select name="nuevos_atributos_tipo[]" class="sm:w-36 text-xs px-2.5 py-1.5 border border-gray-300 rounded-lg bg-white font-medium text-gray-700">
                            <option value="texto">Texto</option>
                            <option value="numero">Número</option>
                            <option value="fecha">Fecha</option>
                            <option value="booleano">Booleano (Sí/No)</option>
                        </select>
                        <button type="button" class="btn-eliminar-fila p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors flex items-center justify-center self-end sm:self-center" title="Eliminar este atributo">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="{{ route('tipos.index') }}" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-xl">
                Cancelar
            </a>
            <button type="submit" class="px-5 py-2 text-xs font-black bg-amber-400 hover:bg-amber-500 text-black rounded-xl shadow-sm transition-all">
                Crear Familia y Guardar Atributos
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
                fila.className = 'fila-nuevo-atributo flex flex-col sm:flex-row items-stretch sm:items-center gap-2 p-2 bg-white rounded-lg border border-amber-200 shadow-2xs animate-fadeIn';
                fila.innerHTML = `
                    <input type="text" name="nuevos_atributos_nombre[]" placeholder="Nombre (ej: Tasa de Muestreo, Interfaz, Voltaje)..." class="flex-1 text-xs px-2.5 py-1.5 border border-gray-300 rounded-lg bg-white focus:ring-1 focus:ring-amber-400">
                    <input type="text" name="nuevos_atributos_unidad[]" placeholder="Unidad (ej: MHz, V, Pulgadas)..." class="sm:w-44 text-xs px-2.5 py-1.5 border border-gray-300 rounded-lg bg-white focus:ring-1 focus:ring-amber-400">
                    <select name="nuevos_atributos_tipo[]" class="sm:w-36 text-xs px-2.5 py-1.5 border border-gray-300 rounded-lg bg-white font-medium text-gray-700">
                        <option value="texto">Texto</option>
                        <option value="numero">Número</option>
                        <option value="fecha">Fecha</option>
                        <option value="booleano">Booleano (Sí/No)</option>
                    </select>
                    <button type="button" class="btn-eliminar-fila p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors flex items-center justify-center self-end sm:self-center" title="Eliminar este atributo">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                `;
                contenedor.appendChild(fila);
                fila.querySelector('input').focus();
            });

            // Delegación de eventos para eliminar filas
            contenedor.addEventListener('click', function (e) {
                const btnEliminar = e.target.closest('.btn-eliminar-fila');
                if (btnEliminar) {
                    const fila = btnEliminar.closest('.fila-nuevo-atributo');
                    if (fila) {
                        const todasFilas = contenedor.querySelectorAll('.fila-nuevo-atributo');
                        if (todasFilas.length > 1) {
                            fila.remove();
                        } else {
                            // Si es la única fila, limpiar los campos en lugar de remover el nodo
                            fila.querySelectorAll('input').forEach(i => i.value = '');
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
