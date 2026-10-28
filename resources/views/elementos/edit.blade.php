@extends('layouts.app')

@section('content')
<div class="max-w-[850px] mx-auto py-4 space-y-4">
    <!-- Header Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('elementos.index') }}" class="hover:text-figma-brand transition-colors">Inventario</a>
                <span>/</span>
                <a href="{{ route('elementos.show', $elemento->id) }}" class="hover:text-figma-brand transition-colors font-mono">{{ $elemento->presenter()->identificadorCompleto() }}</a>
                <span>/</span>
                <span class="text-figma-text-primary">Editar</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <span>Editar Ficha del Activo</span>
            </h1>
            <p class="text-xs text-figma-text-secondary mt-0.5 font-mono">{{ $elemento->presenter()->identificadorCompleto() }}</p>
        </div>
        <div>
            <a href="{{ route('elementos.show', $elemento->id) }}" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver al Detalle</span>
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="figma-card p-3 border-rose-500/40 text-rose-400 text-xs">
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('elementos.update', $elemento->id) }}" method="POST" class="figma-panel p-5 sm:p-6 space-y-4">
        @csrf
        @method('PUT')

        <!-- Identificadores Oficiales -->
        <div class="figma-card p-3.5 space-y-2 border-figma-brand/30">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-figma-text-primary uppercase tracking-wider">
                    Identificadores Oficiales de Inventario
                </span>
                <span class="text-[10px] text-figma-brand font-mono font-bold">Oficial LIA / UNSJ</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1" for="nro_lia">Nro LIA</label>
                    <input type="text" name="nro_lia" id="nro_lia" value="{{ old('nro_lia', $elemento->nro_lia) }}" placeholder="Ej. LIA-FS-01" class="figma-input w-full px-2.5 py-1.5 text-xs font-mono font-bold">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1" for="nro_unsj">Nro UNSJ (Exactas)</label>
                    <input type="text" name="nro_unsj" id="nro_unsj" value="{{ old('nro_unsj', $elemento->nro_unsj) }}" placeholder="Ej. 02-05950" class="figma-input w-full px-2.5 py-1.5 text-xs font-mono font-bold">
                </div>
            </div>
        </div>

        <!-- Clasificación y Marca/Modelo -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Tipo <span class="text-rose-400">*</span></label>
                <select name="tipo_id" id="select-tipo" onchange="actualizarAtributosPorTipo()" class="figma-input w-full px-2.5 py-1.5 text-xs font-semibold" required>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo->id }}" {{ old('tipo_id', $elemento->tipo_id) == $tipo->id ? 'selected' : '' }}>{{ ucfirst($tipo->nombre) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Marca / Fabricante</label>
                <input type="text" name="marca" value="{{ old('marca', $elemento->marca) }}" class="figma-input w-full px-2.5 py-1.5 text-xs font-semibold">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Modelo</label>
                <input type="text" name="modelo" value="{{ old('modelo', $elemento->modelo) }}" class="figma-input w-full px-2.5 py-1.5 text-xs font-semibold">
            </div>
        </div>

        <!-- Hardware & Metadata -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Nro de Serie</label>
                <input type="text" name="nro_serie" value="{{ old('nro_serie', $elemento->nro_serie) }}" class="figma-input w-full px-2.5 py-1.5 text-xs font-mono">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Año de Compra</label>
                <input type="number" name="anio_compra" value="{{ old('anio_compra', $elemento->anio_compra) }}" class="figma-input w-full px-2.5 py-1.5 text-xs font-mono">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Cantidad</label>
                <input type="number" name="cantidad" value="{{ old('cantidad', $elemento->cantidad) }}" min="1" class="figma-input w-full px-2.5 py-1.5 text-xs font-mono font-bold">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Puesto Físico</label>
                <input type="text" name="puesto" value="{{ old('puesto', $elemento->puesto) }}" placeholder="Ej. Puesto 03" class="figma-input w-full px-2.5 py-1.5 text-xs">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Fecha de Adquisición</label>
                <input type="date" name="fecha_adquisicion" value="{{ old('fecha_adquisicion', $elemento->fecha_adquisicion ? $elemento->fecha_adquisicion->toDateString() : '') }}" class="figma-input w-full px-2.5 py-1.5 text-xs font-mono">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Vencimiento de Garantía</label>
                <input type="date" name="fecha_vencimiento_garantia" value="{{ old('fecha_vencimiento_garantia', $elemento->fecha_vencimiento_garantia ? $elemento->fecha_vencimiento_garantia->toDateString() : '') }}" class="figma-input w-full px-2.5 py-1.5 text-xs font-mono">
            </div>
        </div>

        <!-- Especificaciones Técnicas (Atributos Dinámicos Pivot) -->
        <div class="figma-card p-3.5 space-y-2.5">
            <div class="flex items-center justify-between">
                <div>
                    <label class="block text-xs font-bold text-figma-text-primary uppercase tracking-wider">Especificaciones Técnicas del Tipo</label>
                    <p class="text-[10px] text-figma-text-tertiary">Campos dinámicos según el tipo de hardware.</p>
                </div>
                <button type="button" onclick="agregarCampoSpec()" class="figma-btn-secondary text-[11px] py-1 px-2.5">
                    + Campo Ad-hoc
                </button>
            </div>

            <!-- Contenedor dinámico de atributos del tipo -->
            <div id="atributos-tipo-container" class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                <!-- Se llena con JS según el tipo seleccionado y los valores precargados -->
            </div>

            <div id="specs-container" class="space-y-2 pt-1">
                @if(is_array($elemento->descripcion))
                    @foreach($elemento->descripcion as $k => $v)
                        <div class="flex gap-2 items-center">
                            <input type="text" name="descripcion_keys[]" value="{{ $k }}" placeholder="Clave" class="figma-input w-1/3 px-2 py-1 text-xs">
                            <input type="text" name="descripcion_values[]" value="{{ $v }}" placeholder="Detalle" class="figma-input flex-1 px-2 py-1 text-xs font-mono">
                            <button type="button" onclick="this.parentElement.remove()" class="p-1 text-figma-text-tertiary hover:text-rose-400 transition-colors" title="Quitar fila">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Observaciones -->
        <div>
            <label class="block text-[11px] font-bold text-figma-text-secondary uppercase mb-1">Notas y Observaciones</label>
            <textarea name="observaciones" rows="3" class="figma-input w-full px-2.5 py-1.5 text-xs">{{ old('observaciones', $elemento->observaciones) }}</textarea>
        </div>

        <div class="pt-3 border-t border-figma-border-subtle flex items-center justify-end gap-2">
            <a href="{{ route('elementos.show', $elemento->id) }}" class="figma-btn-secondary text-xs">
                Cancelar
            </a>
            <button type="submit" class="figma-btn-primary text-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Actualizar Ficha</span>
            </button>
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
            container.innerHTML = '<p class="text-figma-text-tertiary text-xs italic col-span-2">Este tipo no tiene atributos específicos preconfigurados. Puedes añadir campos libres con el botón superior.</p>';
            return;
        }

        tipoEncontrado.atributos.forEach(attr => {
            const div = document.createElement('div');
            div.className = 'p-2.5 rounded bg-figma-bg-deep border border-figma-border-subtle';
            const val = valoresActuales[attr.id] || '';
            const unidad = attr.unidad_medida ? ` <span class="text-figma-text-tertiary font-normal text-[10px]">(${attr.unidad_medida})</span>` : '';
            div.innerHTML = `
                <label class="block text-[10px] font-bold text-figma-text-secondary uppercase mb-1">${attr.nombre}${unidad}</label>
                <input type="${attr.tipo_dato === 'numero' ? 'number' : 'text'}" name="atributos[${attr.id}]" value="${val}" placeholder="Valor para ${attr.nombre}..." class="figma-input w-full text-xs px-2 py-1 font-mono">
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
            <input type="text" name="descripcion_keys[]" value="${k}" placeholder="Característica" class="figma-input w-1/3 px-2 py-1 text-xs">
            <input type="text" name="descripcion_values[]" value="${v}" placeholder="Valor / Detalle" class="figma-input flex-1 px-2 py-1 text-xs font-mono">
            <button type="button" onclick="this.parentElement.remove()" class="p-1 text-figma-text-tertiary hover:text-rose-400 transition-colors" title="Quitar fila">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        `;
        container.appendChild(div);
    }
</script>
@endsection
