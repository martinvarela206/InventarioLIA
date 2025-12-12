@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Editar Movimiento</h1>

    <form action="{{ route('movimientos.update', $movimiento->id) }}" method="POST" class="bg-white shadow-md rounded p-6 lg:p-8 mb-6">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="nro_lia">
                Elemento (Nro LIA)
            </label>
            <select class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#dba800]" id="nro_lia" name="nro_lia" required>
                @foreach($elementos as $elemento)
                    <option value="{{ $elemento->nro_lia }}" {{ $movimiento->nro_lia == $elemento->nro_lia ? 'selected' : '' }}>{{ $elemento->nro_lia }} - {{ $elemento->descripcion_texto }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="user_id">
                Usuario
            </label>
            <select class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#dba800]" id="user_id" name="user_id" required>
                @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->id }}" {{ $movimiento->user_id == $usuario->id ? 'selected' : '' }}>{{ $usuario->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="estado_id">
                Estado
            </label>
            <select class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#dba800]" id="estado_id" name="estado_id" required>
                @foreach($estados as $estado)
                    <option value="{{ $estado->id }}" {{ $movimiento->estado_id == $estado->id ? 'selected' : '' }}>
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
                    <option value="{{ $ubicacion->id }}" {{ $movimiento->ubicacion_id == $ubicacion->id ? 'selected' : '' }}>{{ $ubicacion->nombre }}</option>
                @endforeach
            </select>
            <input type="hidden" id="ubicacion_id_hidden" name="ubicacion_id_hidden">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="fecha">
                Fecha
            </label>
            <input class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#dba800]" id="fecha" type="datetime-local" name="fecha" value="{{ $movimiento->fecha->format('Y-m-d\TH:i') }}" required>
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="comentario">
                Comentario
            </label>
            <input class="w-full rounded-lg border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#dba800]" id="comentario" type="text" name="comentario" value="{{ $movimiento->comentario }}">
        </div>
        <div class="flex items-center justify-between">
            <button class="bg-[#dba800] hover:bg-[#fbc101] text-[#111] font-semibold py-2 px-4 rounded shadow" type="submit">
                Actualizar
            </button>
            <a class="inline-block align-baseline font-bold text-sm text-gray-600 hover:text-gray-800" href="{{ route('movimientos.index') }}">
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
