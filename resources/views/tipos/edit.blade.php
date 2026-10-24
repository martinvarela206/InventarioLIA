@extends('layouts.app')

@section('content')
<div class="max-w-[800px] mx-auto px-2 space-y-6">

    <!-- Header -->
    <div class="border-b border-gray-200 pb-4">
        <a href="{{ route('tipos.index') }}" class="text-xs font-bold text-gray-500 hover:text-black flex items-center gap-1 mb-2">
            ← Volver a Tipos de Activos
        </a>
        <h1 class="text-2xl font-black text-gray-900 capitalize">Configurar Tipo: {{ $tipo->nombre }}</h1>
        <p class="text-xs text-gray-500 mt-0.5">
            Modifica la descripción y los atributos técnicos que aplican a los activos de este tipo.
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

    <form action="{{ route('tipos.update', $tipo->id) }}" method="POST" class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-6">
        @csrf
        @method('PUT')

        <!-- Datos Base del Tipo -->
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-800 mb-1">Nombre de la Familia / Tipo *</label>
                <input type="text" name="nombre" value="{{ old('nombre', $tipo->nombre) }}" class="w-full text-xs px-3 py-2 border rounded-lg bg-gray-50 focus:bg-white capitalize" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-800 mb-1">Descripción</label>
                <input type="text" name="descripcion" value="{{ old('descripcion', $tipo->descripcion) }}" class="w-full text-xs px-3 py-2 border rounded-lg bg-gray-50 focus:bg-white">
            </div>
        </div>

        <!-- Atributos Técnicos Específicos (Pivot tipo_atributo) -->
        <div class="pt-4 border-t border-gray-200 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-black uppercase text-gray-800 tracking-wider">Atributos Técnicos Vinculados</h3>
                    <p class="text-[11px] text-gray-500">Selecciona qué campos adicionales se deben solicitar para los activos de esta familia.</p>
                </div>
            </div>

            @php
                $asignadosIds = $tipo->atributos->pluck('id')->toArray();
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 bg-gray-50 p-4 rounded-xl border border-gray-200">
                @foreach($atributos as $attr)
                    <label class="flex items-center gap-2.5 p-2 bg-white rounded-lg border border-gray-200 hover:border-amber-400 cursor-pointer transition-all">
                        <input type="checkbox" name="atributos[]" value="{{ $attr->id }}" {{ in_array($attr->id, old('atributos', $asignadosIds)) ? 'checked' : '' }} class="rounded border-gray-300 text-amber-500 focus:ring-amber-400">
                        <div class="text-xs">
                            <span class="font-bold text-gray-800 block">{{ $attr->nombre }}</span>
                            @if($attr->unidad_medida)
                                <span class="text-[10px] text-gray-400">Unidad: {{ $attr->unidad_medida }}</span>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>

            <!-- Opción para definir un nuevo atributo en el momento -->
            <div class="p-3 bg-amber-50/60 rounded-xl border border-amber-200 space-y-2">
                <span class="text-xs font-bold text-amber-900 block">¿Deseas agregar un nuevo atributo a la plataforma?</span>
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="nuevo_atributo_nombre" value="{{ old('nuevo_atributo_nombre') }}" placeholder="Nombre del nuevo atributo..." class="text-xs px-2.5 py-1.5 border rounded-lg bg-white">
                    <input type="text" name="nuevo_atributo_unidad" value="{{ old('nuevo_atributo_unidad') }}" placeholder="Unidad (ej: Watts, Pulgadas)..." class="text-xs px-2.5 py-1.5 border rounded-lg bg-white">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="{{ route('tipos.index') }}" class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-xl">
                Cancelar
            </a>
            <button type="submit" class="px-5 py-2 text-xs font-black bg-amber-400 hover:bg-amber-500 text-black rounded-xl shadow-sm transition-all">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
