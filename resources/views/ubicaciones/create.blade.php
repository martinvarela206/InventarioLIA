@extends('layouts.app')

@section('content')
<div class="max-w-[700px] mx-auto px-2 space-y-6">

    <!-- Header -->
    <div class="border-b border-gray-200 pb-4">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-800 bg-amber-100 px-2.5 py-1 rounded-md">
                Nuevo Espacio
            </span>
            <span class="text-xs text-gray-500 font-semibold">item_admin</span>
        </div>
        <h1 class="text-2xl font-black text-gray-900 mt-1">Registrar Nuevo Espacio o Sala Física</h1>
        <p class="text-xs text-gray-500 mt-0.5">
            Añade un nuevo laboratorio, oficina o depósito para asignación y traslado de activos.
        </p>
    </div>

    <!-- Error Alerts -->
    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 text-xs rounded-xl font-medium">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulario -->
    <form action="{{ route('ubicaciones.store') }}" method="POST" class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-5">
        @csrf

        <div>
            <label for="nombre" class="block text-xs font-bold text-gray-700 uppercase mb-1">
                Nombre de la Sala / Espacio <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required placeholder="Ej. Laboratorio D, Sala de Servidores 2, Depósito..." class="w-full px-3.5 py-2.5 border rounded-xl bg-white text-sm font-semibold focus:ring-2 focus:ring-amber-400 focus:outline-none @error('nombre') border-red-500 @enderror">
            <p class="text-[11px] text-gray-400 mt-1">Debe ser único en el sistema.</p>
        </div>

        <div>
            <label for="descripcion" class="block text-xs font-bold text-gray-700 uppercase mb-1">
                Descripción / Detalle de Ubicación (Opcional)
            </label>
            <textarea name="descripcion" id="descripcion" rows="3" placeholder="Ej. Laboratorio de Práctica para Cátedras de Inteligencia Artificial en Planta Alta..." class="w-full px-3.5 py-2.5 border rounded-xl bg-white text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none @error('descripcion') border-red-500 @enderror">{{ old('descripcion') }}</textarea>
            <p class="text-[11px] text-gray-400 mt-1">Información de referencia sobre la sala, capacidad o sector.</p>
        </div>

        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
            <a href="{{ route('ubicaciones.index') }}" class="px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-100 rounded-xl transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-5 py-2.5 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold rounded-xl text-xs shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Guardar Espacio Físico
            </button>
        </div>
    </form>

</div>
@endsection
