@extends('layouts.app')

@section('content')
<div class="max-w-[850px] mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Alta de Nuevo Activo de Inventario</h1>
            <p class="text-sm text-gray-600 mt-0.5">Asignación oficial y registro del primer movimiento en salas del LIA</p>
        </div>
        <a href="{{ route('elementos.index') }}" class="text-sm font-semibold text-gray-600 hover:text-black">← Volver al catálogo</a>
    </div>

    <form action="{{ route('elementos.store') }}" method="POST" class="bg-white shadow-sm border border-gray-200 rounded-2xl p-6 lg:p-8 space-y-6">
        @csrf

        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <span class="text-xs font-semibold text-gray-500">
                Los campos marcados con <span class="text-red-500 font-bold text-sm">*</span> son obligatorios
            </span>
        </div>

        <!-- 1. IDENTIFICACIÓN COMPUESTA OFICIAL -->
        <div class="bg-amber-50/60 p-4 rounded-xl border border-amber-200/80">
            <h3 class="text-xs font-bold text-amber-900 uppercase tracking-wider mb-2">
                1. Identificadores Oficiales (Cero Autogeneración)
            </h3>
            <p class="text-xs text-amber-800 mb-3">
                Los identificadores son asignados exclusivamente por la autoridad. Complete al menos uno (si no posee, déjelo vacío o S/N).
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1" for="nro_lia">Nro LIA</label>
                    <input type="text" name="nro_lia" id="nro_lia" value="{{ old('nro_lia') }}" placeholder="Ej. LIA-FS-01" class="w-full font-mono font-bold px-3 py-2 border rounded-lg bg-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1" for="nro_unsj">Nro UNSJ (Exactas)</label>
                    <input type="text" name="nro_unsj" id="nro_unsj" value="{{ old('nro_unsj') }}" placeholder="Ej. 02-05950" class="w-full font-mono font-bold px-3 py-2 border rounded-lg bg-white">
                </div>
            </div>
        </div>

        <!-- 2. CLASIFICACIÓN Y DISPOSITIVO -->
        <div>
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">2. Clasificación y Fabricante</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tipo de Activo <span class="text-red-500 font-bold">*</span></label>
                    <select name="tipo_id" id="select-tipo" onchange="actualizarAtributosPorTipo()" class="w-full px-3 py-2 border rounded-lg bg-white font-semibold" required>
                        <option value="">Seleccione tipo...</option>
                        @foreach($tipos as $tipo)
                            <option value="{{ $tipo->id }}" {{ old('tipo_id') == $tipo->id ? 'selected' : '' }}>{{ ucfirst($tipo->nombre) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Marca / Fabricante</label>
                    <input type="text" name="marca" value="{{ old('marca') }}" placeholder="Ej. Dell, Asus, Bangho" class="w-full px-3 py-2 border rounded-lg bg-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Modelo</label>
                    <input type="text" name="modelo" value="{{ old('modelo') }}" placeholder="Ej. OptiPlex 3080" class="w-full px-3 py-2 border rounded-lg bg-white">
                </div>
            </div>
        </div>

        <!-- 3. DATOS TÉCNICOS Y TRAZABILIDAD -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nro de Serie</label>
                <input type="text" name="nro_serie" value="{{ old('nro_serie') }}" placeholder="S/N del fabricante" class="w-full font-mono text-sm px-3 py-2 border rounded-lg bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Año de Compra</label>
                <input type="number" name="anio_compra" value="{{ old('anio_compra', date('Y')) }}" min="1980" max="{{ date('Y')+1 }}" class="w-full px-3 py-2 border rounded-lg bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Cantidad</label>
                <input type="number" name="cantidad" value="{{ old('cantidad', 1 grading) }}" min="1" class="w-full px-3 py-2 border rounded-lg bg-white font-bold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Puesto Físico</label>
                <input type="text" name="puesto" value="{{ old('puesto') }}" placeholder="Ej. Puesto 04" class="w-full px-3 py-2 border rounded-lg bg-white">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Fecha de Adquisición</label>
                <input type="date" name="fecha_adquisicion" value="{{ old('fecha_adquisicion', date('Y-m-d')) }}" class="w-full px-3 py-2 border rounded-lg bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Vencimiento de Garantía</label>
                <input type="date" name="fecha_vencimiento_garantia" value="{{ old('fecha_vencimiento_garantia') }}" class="w-full px-3 py-2 border rounded-lg bg-white">
            </div>
        </div>

        <!-- 4. MOVIMIENTO INICIAL OBLIGATORIO -->
        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">4. Asignación y Movimiento Inicial Automático</h3>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Sala o Armario Inicial <span class="text-red-500 font-bold">*</span></label>
                <select name="ubicacion_id" class="w-full px-3 py-2 border rounded-lg bg-white font-semibold">
                    @foreach($ubicaciones as $ub)
                        <option value="{{ $ub->id }}" {{ $ub->nombre === 'Armario de HW' ? 'selected' : '' }}>{{ $ub->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- 5. ESPECIFICACIONES TÉCNICAS (ATRIBUTOS DINÁMICOS PIVOT) -->
        <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-200 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <label class="block text-xs font-bold text-gray-800 uppercase tracking-wider">5. Especificaciones Técnicas del Tipo</label>
                    <p class="text-[11px] text-gray-500">Campos dinámicos según el tipo de hardware seleccionado.</p>
                </div>
                <button type="button" onclick="agregarCampoSpec()" class="text-xs bg-amber-100 hover:bg-amber-200 text-amber-900 font-bold px-3 py-1 rounded-md transition-colors">
                    + Campo Ad-hoc
                </button>
            </div>

            <!-- Contenedor dinámico de atributos del tipo -->
            <div id="atributos-tipo-container" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <!-- Se llena automáticamente con JS según el tipo seleccionado -->
            </div>

            <!-- Campos libres adicionales -->
            <div id="specs-container" class="space-y-2 pt-2">
            </div>
        </div>

        <!-- 6. OBSERVACIONES -->
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Notas y Observaciones</label>
            <textarea name="observaciones" rows="3" placeholder="Detalles de instalación o notas patrimoniales..." class="w-full px-3 py-2 border rounded-lg bg-white text-sm">{{ old('observaciones') }}</textarea>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
            <a href="{{ route('elementos.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 font-semibold text-gray-600 hover:bg-gray-50 text-sm">Cancelar</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#fbc101] hover:bg-amber-400 font-bold text-black shadow transition-all text-sm">Guardar Activo y Generar Movimiento</button>
        </div>
    </form>
</div>

<script>
    const tiposConAtributos = @json($tipos);

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
            const unidad = attr.unidad_medida ? ` <span class="text-gray-400 font-normal">(${attr.unidad_medida})</span>` : '';
            div.innerHTML = `
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">${attr.nombre}${unidad}</label>
                <input type="${attr.tipo_dato === 'numero' ? 'number' : 'text'}" name="atributos[${attr.id}]" placeholder="Valor para ${attr.nombre}..." class="w-full text-xs px-3 py-2 border rounded-lg bg-gray-50 focus:bg-white font-medium">
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
