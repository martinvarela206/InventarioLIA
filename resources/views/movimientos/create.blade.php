@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Añadir Movimiento</h1>

    <form action="{{ route('movimientos.store') }}" method="POST" class="bg-white shadow-md rounded p-6 lg:p-8 mb-6">
        @csrf
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="nro_lia">
                Elemento (Nro LIA)
            </label>
            @if(isset($nro_lia))
                <input class="w-full rounded-lg border border-gray-200 px-3 py-2 bg-gray-100 cursor-not-allowed" id="nro_lia_display" type="text" value="{{ $nro_lia }} - {{ $elementos->firstWhere('nro_lia', $nro_lia)->descripcion_texto ?? '' }}" readonly>
                <input type="hidden" name="nro_lia" value="{{ $nro_lia }}">
            @else
                <select name="nro_lia" id="nro_lia" class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#dba800]" required>
                    <option value="">Seleccione un elemento</option>
                    @foreach($elementos as $elemento)
                        <option value="{{ $elemento->nro_lia }}" {{ old('nro_lia') == $elemento->nro_lia ? 'selected' : '' }}>
                            {{ $elemento->nro_lia }} - {{ $elemento->descripcion_texto }}
                        </option>
                    @endforeach
                </select>
            @endif
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="user_display">
                Usuario
            </label>
            <input class="w-full rounded-lg border border-gray-200 px-3 py-2 bg-gray-100 cursor-not-allowed" id="user_display" type="text" value="{{ auth()->user()->nombre }}" readonly>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="estado_id">
                Estado
            </label>
            <select class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#dba800]" id="estado_id" name="estado_id" required>
                <option value="">Seleccione un estado</option>
                @foreach($estados as $estado)
                    <option value="{{ $estado->id }}" {{ old('estado_id') == $estado->id ? 'selected' : '' }}>
                        {{ ucfirst($estado->nombre) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="ubicacion_id">
                Ubicación
            </label>
            <select class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#dba800]" id="ubicacion_id" name="ubicacion_id">
                <option value="">Seleccione una ubicación</option>
                @foreach($ubicaciones as $ubicacion)
                    <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                @endforeach
            </select>
            <input type="hidden" id="ubicacion_id_hidden" name="ubicacion_id_hidden">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="fecha">
                Fecha
            </label>
            @can('manage-movements')
                <input class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#dba800]" id="fecha" type="datetime-local" name="fecha" value="{{ now()->format('Y-m-d\TH:i') }}" required>
            @else
                <input class="w-full rounded-lg border border-gray-200 px-3 py-2 bg-gray-100 cursor-not-allowed" id="fecha_display" type="datetime-local" value="{{ now()->format('Y-m-d\TH:i') }}" readonly>
                <input type="hidden" name="fecha" value="{{ now()->format('Y-m-d\TH:i') }}">
            @endcan
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="comentario">
                Comentario
            </label>
            <input class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#dba800]" id="comentario" type="text" name="comentario">
        </div>
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-[#dba800] hover:bg-[#fbc101] text-[#111] font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Crear Movimiento
            </button>
            <a class="inline-block align-baseline font-bold text-sm text-[#dba800] hover:text-[#fbc101]" href="{{ route('movimientos.index') }}">
                Cancelar
            </a>
        </div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const estadoSelect = document.getElementById('estado_id');
        const ubicacionSelect = document.getElementById('ubicacion_id');
        const ubicacionHidden = document.getElementById('ubicacion_id_hidden');
        
        if (!estadoSelect || !ubicacionSelect) return;
        
        // Find the estado and ubicacion options
        const estadoPrestadoOption = Array.from(estadoSelect.options).find(opt => opt.text.toLowerCase().includes('prestado'));
        const ubicacionPrestadoOption = Array.from(ubicacionSelect.options).find(opt => opt.text.toLowerCase().includes('prestado'));
        
        const estadoDadoDeBajaOption = Array.from(estadoSelect.options).find(opt => opt.text.toLowerCase().includes('dado de baja'));
        const ubicacionDadoDeBajaOption = Array.from(ubicacionSelect.options).find(opt => opt.text.toLowerCase().includes('dado de baja'));
        
        function handleEstadoChange() {
            const selectedEstado = estadoSelect.value;
            
            if (selectedEstado === estadoPrestadoOption?.value) {
                // Set ubicacion to Prestado and disable
                ubicacionSelect.value = ubicacionPrestadoOption?.value || '';
                ubicacionHidden.value = ubicacionPrestadoOption?.value || '';
                ubicacionSelect.disabled = true;
                ubicacionSelect.classList.add('bg-gray-100', 'cursor-not-allowed');
            } else if (selectedEstado === estadoDadoDeBajaOption?.value) {
                // Set ubicacion to Dado de baja and disable
                ubicacionSelect.value = ubicacionDadoDeBajaOption?.value || '';
                ubicacionHidden.value = ubicacionDadoDeBajaOption?.value || '';
                ubicacionSelect.disabled = true;
                ubicacionSelect.classList.add('bg-gray-100', 'cursor-not-allowed');
            } else {
                // Enable ubicacion select and clear hidden
                ubicacionHidden.value = '';
                ubicacionSelect.disabled = false;
                ubicacionSelect.classList.remove('bg-gray-100', 'cursor-not-allowed');
            }
        }
        
        estadoSelect.addEventListener('change', handleEstadoChange);
        // Check on page load
        handleEstadoChange();
    });
    </script>
@endsection
