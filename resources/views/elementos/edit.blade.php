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

        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <span class="text-xs font-semibold text-gray-500">
                Los campos marcados con <span class="text-red-500 font-bold text-sm">*</span> son obligatorios
            </span>
        </div>

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
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tipo <span class="text-red-500 font-bold">*</span></label>
                <select name="tipo_id" id="select-tipo" onchange="actualizarAtributosPorTipo()" class="w-full px-3 py-2 border rounded-lg bg-white font-semibold" required>
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

        <!-- Especificaciones Técnicas (Atributos Dinámicos Pivot) -->
        <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-200 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider">Especificaciones Técnicas del Tipo</label>
                    <p class="text-[11px] text-gray-500">Campos dinámicos según el tipo de hardware.</p>
                </div>
                <button type="button" onclick="agregarCampoSpec()" class="text-xs bg-amber-100 hover:bg-amber-200 text-amber-900 font-bold px-3 py-1 rounded-md transition-colors">
                    + Campo Ad-hoc
                </button>
            </div>

            <!-- Contenedor dinámico de atributos del tipo -->
            <div id="atributos-tipo-container" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <!-- Se llena con JS según el tipo seleccionado y los valores precargados -->
            </div>

            <div id="specs-container" class="space-y-2 pt-2">
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
    const tiposConAtributos = @json($tipos);
    const valoresActuales = @json($elemento->valoresAtributos->pluck('valor', 'atributo_id'));

    function actualizarAtributosPorTipo() {
        const select = document.getElementById('select-tipo');
        const container = document.getElementById('atributos-tipo-container');
        container.innerHTML = '';
        
        const tipoId = parseInt(select.value);
        if (!tipoId) return;

        const tipoEncontrado = tiposConAtributos.find(t => t.id === tipoId);
        if (!tipoEncontrado || !tipoEncontrado.atributos || tipoEncontrado.atributos.length === 0) {
            container.innerHTML = '<p class="text-gray-400 text-xs italic col-span-2">Este tipo no tiene atributos específicos preconfigurados. Puedes añadir campos libres con el botón superior.</p>';
            return;
        }

        tipoEncontrado.atributos.forEach(attr => {
            const div = document.createElement('div');
            div.className = 'bg-white p-3 rounded-lg border border-gray-200 shadow-2xs';
            const val = valoresActuales[attr.id] || '';
            const unidad = attr.unidad_medida ? ` <span class="text-gray-400 font-normal">(${attr.unidad_medida})</span>` : '';
            div.innerHTML = `
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">${attr.nombre}${unidad}</label>
                <input type="${attr.tipo_dato === 'numero' ? 'number' : 'text'}" name="atributos[${attr.id}]" value="${val}" placeholder="Valor para ${attr.nombre}..." class="w-full text-xs px-3 py-2 border rounded-lg bg-gray-50 focus:bg-white font-medium">
            `;
            container.appendChild(div);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        actualizarAtributosPorTipo();
    });

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
