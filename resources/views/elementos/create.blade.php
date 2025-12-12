@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Añadir Elemento</h1>

    <form action="{{ route('elementos.store') }}" method="POST" class="bg-white shadow-md rounded p-6 lg:p-8 mb-6">
        @csrf
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="nro_lia">
                Nro LIA
            </label>
            <input class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#dba800]" id="nro_lia" type="text" name="nro_lia" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="nro_unsj">
                Nro UNSJ
            </label>
            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="nro_unsj" type="text" name="nro_unsj">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="tipo_id">
                Tipo
            </label>
            <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="tipo_id" name="tipo_id" required>
                <option value="">Seleccione un tipo</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->id }}">{{ ucfirst($tipo->nombre) }}</option>
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

        // Agregar un campo inicial al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            addDescripcionField();
        });
        </script>
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="cantidad">
                Cantidad
            </label>
            <input class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#dba800]" id="cantidad" type="number" name="cantidad" min="1" value="1" required>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2" for="fecha_adquisicion">
                    Fecha de Adquisición
                </label>
                <input class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#dba800]" id="fecha_adquisicion" type="date" name="fecha_adquisicion">
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2" for="fecha_vencimiento_garantia">
                    Vencimiento de Garantía
                </label>
                <input class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#dba800]" id="fecha_vencimiento_garantia" type="date" name="fecha_vencimiento_garantia">
            </div>
        </div>
        <div class="flex items-center justify-between">
            <button class="bg-[#dba800] hover:bg-[#fbc101] text-[#111] font-semibold py-2 px-4 rounded shadow" type="submit">
                Guardar
            </button>
            <a class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800" href="{{ route('elementos.index') }}">
                Cancelar
            </a>
        </div>
    </form>
@endsection
