@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Editar Elemento</h1>

    <form action="{{ route('elementos.update', $elemento->nro_lia) }}" method="POST" class="bg-white shadow-md rounded p-6 lg:p-8 mb-6">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="nro_lia">
                Nro LIA
            </label>
            <input class="w-full rounded-lg border border-gray-200 px-3 py-2 bg-gray-100 cursor-not-allowed" id="nro_lia" type="text" name="nro_lia" value="{{ $elemento->nro_lia }}" readonly>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="nro_unsj">
                Nro UNSJ
            </label>
            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="nro_unsj" type="text" name="nro_unsj" value="{{ $elemento->nro_unsj }}">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="tipo_id">
                Tipo
            </label>
            <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="tipo_id" name="tipo_id" required>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->id }}" {{ $elemento->tipo_id == $tipo->id ? 'selected' : '' }}>{{ ucfirst($tipo->nombre) }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">
                Descripción (Campos Dinámicos)
            </label>
            <div id="descripcion-container" class="space-y-2">
                <!-- Los campos se agregarán dinámicamente aquí -->
            </div>
            <button type="button" onclick="addDescripcionField()" class="mt-2 text-sm bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">
                + Agregar campo
            </button>
        </div>
        
        <script>
        let fieldCounter = 0;

        function addDescripcionField(key = '', value = '') {
            const container = document.getElementById('descripcion-container');
            const fieldId = `field-${fieldCounter++}`;
            
            const fieldDiv = document.createElement('div');
            fieldDiv.id = fieldId;
            fieldDiv.className = 'flex gap-2 items-center';
            fieldDiv.innerHTML = `
                <input type="text" 
                       name="descripcion_keys[]" 
                       placeholder="Clave (ej: RAM, Procesador)" 
                       value="${key}"
                       class="w-1/3 rounded border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#dba800]">
                <input type="text" 
                       name="descripcion_values[]" 
                       placeholder="Valor (ej: 8GB, Intel i5)" 
                       value="${value}"
                       class="flex-1 rounded border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#dba800]">
                <button type="button" 
                        onclick="removeField('${fieldId}')" 
                        class="text-red-600 hover:text-red-800 px-2 py-1 font-bold text-lg">
                    ✕
                </button>
            `;
            
            container.appendChild(fieldDiv);
        }

        function removeField(fieldId) {
            document.getElementById(fieldId).remove();
        }

        // Pre-cargar campos existentes al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            @if(is_array($elemento->descripcion) && count($elemento->descripcion) > 0)
                @foreach($elemento->descripcion as $key => $value)
                    addDescripcionField('{{ addslashes($key) }}', '{{ addslashes($value) }}');
                @endforeach
            @else
                // Si no hay descripción o no es array, agregar un campo vacío
                addDescripcionField();
            @endif
        });
        </script>
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="cantidad">
                Cantidad
            </label>
            <input class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#dba800]" id="cantidad" type="number" name="cantidad" min="1" value="{{ $elemento->cantidad }}" required>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2" for="fecha_adquisicion">
                    Fecha de Adquisición
                </label>
                <input class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#dba800]" id="fecha_adquisicion" type="date" name="fecha_adquisicion" value="{{ $elemento->fecha_adquisicion ? $elemento->fecha_adquisicion->format('Y-m-d') : '' }}">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2" for="fecha_vencimiento_garantia">
                    Vencimiento de Garantía
                </label>
                <input class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#dba800]" id="fecha_vencimiento_garantia" type="date" name="fecha_vencimiento_garantia" value="{{ $elemento->fecha_vencimiento_garantia ? $elemento->fecha_vencimiento_garantia->format('Y-m-d') : '' }}">
            </div>
        </div>
        <div class="flex items-center justify-between">
            <button class="bg-[#dba800] hover:bg-[#fbc101] text-[#111] font-semibold py-2 px-4 rounded shadow" type="submit">
                Actualizar
            </button>
            <a class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800" href="{{ route('elementos.index') }}">
                Cancelar
            </a>
        </div>
    </form>
@endsection
