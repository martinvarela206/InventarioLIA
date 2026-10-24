@extends('layouts.app')

@section('content')
<div class="max-w-[850px] mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Editar Ficha del Activo</h1>
            <p class="text-sm text-gray-600 mt-0.5">{{ $elemento->presenter()->identificadorCompleto() }}</p>
        </div>
        <a href="{{ route('elementos.show', $elemento->id) }}" class="text-sm font-semibold text-gray-600 hover:text-black">← Volver al detalle</a>
    </div>

    <form action="{{ route('elementos.update', $elemento->id) }}" method="POST" class="bg-white shadow-sm border border-gray-200 rounded-2xl p-6 lg:p-8 space-y-6">
        @csrf
        @method('PUT')

        <!-- Identificadores Oficiales -->
        <div class="bg-amber-50/60 p-4 rounded-xl border border-amber-200/80">
            <h3 class="text-xs font-bold text-amber-900 uppercase tracking-wider mb-2">
                Identificadores Oficiales de Inventario
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1" for="nro_lia">Nro LIA</label>
                    <input type="text" name="nro_lia" id="nro_lia" value="{{ old('nro_lia', $elemento->nro_lia) }}" placeholder="Ej. LIA-FS-01" class="w-full font-mono font-bold px-3 py-2 border rounded-lg bg-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1" for="nro_unsj">Nro UNSJ (Exactas)</label>
                    <input type="text" name="nro_unsj" id="nro_unsj" value="{{ old('nro_unsj', $elemento->nro_unsj) }}" placeholder="Ej. 02-05950" class="w-full font-mono font-bold px-3 py-2 border rounded-lg bg-white">
                </div>
            </div>
        </div>

        <!-- Clasificación y Marca/Modelo -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tipo *</label>
                <select name="tipo_id" class="w-full px-3 py-2 border rounded-lg bg-white font-semibold" required>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo->id }}" {{ old('tipo_id', $elemento->tipo_id) == $tipo->id ? 'selected' : '' }}>{{ ucfirst($tipo->nombre) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Marca / Fabricante</label>
                <input type="text" name="marca" value="{{ old('marca', $elemento->marca) }}" class="w-full px-3 py-2 border rounded-lg bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Modelo</label>
                <input type="text" name="modelo" value="{{ old('modelo', $elemento->modelo) }}" class="w-full px-3 py-2 border rounded-lg bg-white">
            </div>
        </div>

        <!-- Hardware & Metadata -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nro de Serie</label>
                <input type="text" name="nro_serie" value="{{ old('nro_serie', $elemento->nro_serie) }}" class="w-full font-mono text-sm px-3 py-2 border rounded-lg bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Año de Compra</label>
                <input type="number" name="anio_compra" value="{{ old('anio_compra', $elemento->anio_compra) }}" class="w-full px-3 py-2 border rounded-lg bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Cantidad</label>
                <input type="number" name="cantidad" value="{{ old('cantidad', $elemento->cantidad) }}" min="1" class="w-full px-3 py-2 border rounded-lg bg-white font-bold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Puesto Físico</label>
                <input type="text" name="puesto" value="{{ old('puesto', $elemento->puesto) }}" placeholder="Ej. Puesto 03" class="w-full px-3 py-2 border rounded-lg bg-white">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Fecha de Adquisición</label>
                <input type="date" name="fecha_adquisicion" value="{{ old('fecha_adquisicion', $elemento->fecha_adquisicion ? $elemento->fecha_adquisicion->toDateString() : '') }}" class="w-full px-3 py-2 border rounded-lg bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Vencimiento de Garantía</label>
                <input type="date" name="fecha_vencimiento_garantia" value="{{ old('fecha_vencimiento_garantia', $elemento->fecha_vencimiento_garantia ? $elemento->fecha_vencimiento_garantia->toDateString() : '') }}" class="w-full px-3 py-2 border rounded-lg bg-white">
            </div>
        </div>

        <!-- Especificaciones Dinámicas -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="block text-xs font-bold text-gray-700 uppercase">Características Técnicas (CPU, RAM, Disco)</label>
                <button type="button" onclick="agregarCampoSpec()" class="text-xs bg-amber-100 hover:bg-amber-200 text-amber-900 font-bold px-3 py-1 rounded-md transition-colors">
                    + Agregar Campo
                </button>
            </div>
            <div id="specs-container" class="space-y-2">
                @if(is_array($elemento->descripcion))
                    @foreach($elemento->descripcion as $k => $v)
                        <div class="flex gap-2 items-center">
                            <input type="text" name="descripcion_keys[]" value="{{ $k }}" placeholder="Clave" class="w-1/3 px-3 py-1.5 border rounded-lg text-sm bg-gray-50">
                            <input type="text" name="descripcion_values[]" value="{{ $v }}" placeholder="Detalle" class="flex-1 px-3 py-1.5 border rounded-lg text-sm bg-white">
                            <button type="button" onclick="this.parentElement.remove()" class="p-1.5 text-red-500 hover:text-red-700 text-sm font-bold">✕</button>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Observaciones -->
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Notas y Observaciones</label>
            <textarea name="observaciones" rows="3" class="w-full px-3 py-2 border rounded-lg bg-white text-sm">{{ old('observaciones', $elemento->observaciones) }}</textarea>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
            <a href="{{ route('elementos.show', $elemento->id) }}" class="px-5 py-2.5 rounded-xl border border-gray-300 font-semibold text-gray-600 hover:bg-gray-50 text-sm">Cancelar</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-400 hover:bg-amber-500 font-bold text-black shadow transition-all text-sm">Actualizar Ficha</button>
        </div>
    </form>
</div>

<script>
    function agregarCampoSpec(k = '', v = '') {
        const container = document.getElementById('specs-container');
        const div = document.createElement('div');
        div.className = 'flex gap-2 items-center';
        div.innerHTML = `
            <input type="text" name="descripcion_keys[]" value="${k}" placeholder="Característica" class="w-1/3 px-3 py-1.5 border rounded-lg text-sm bg-gray-50">
            <input type="text" name="descripcion_values[]" value="${v}" placeholder="Valor / Detalle" class="flex-1 px-3 py-1.5 border rounded-lg text-sm bg-white">
            <button type="button" onclick="this.parentElement.remove()" class="p-1.5 text-red-500 hover:text-red-700 text-sm font-bold">✕</button>
        `;
        container.appendChild(div);
    }
</script>
@endsection
