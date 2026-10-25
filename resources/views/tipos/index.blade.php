@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">

    <!-- Header del Panel -->
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 pb-5">
        <div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span>Tipos de Activos</span>
            </h1>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('tipos.create') }}" class="inline-flex items-center gap-2 bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold px-4 py-2.5 rounded-xl shadow-sm transition-all text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo Tipo de Activo
            </a>
            <button type="button" onclick="document.getElementById('modalNuevoAtributo').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-4 py-2.5 rounded-xl shadow-sm transition-all text-sm">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Crear Atributo Global
            </button>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl font-bold flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 text-xs rounded-xl font-medium">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tabla de Tipos y Atributos Asignados -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="p-4 bg-gray-50/70 border-b border-gray-200 flex justify-between items-center">
            <span class="text-xs font-black uppercase text-gray-700 tracking-wider">
                Catálogo de Familias de Activos ({{ $tipos->total() }})
            </span>
            <span class="text-[11px] text-gray-500">
                La tabla base conserva únicamente columnas universales; los atributos se vinculan por pivot.
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-gray-100/80 border-b border-gray-200 text-gray-600 font-extrabold uppercase text-[10px]">
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4 w-44">Tipo de Activo</th>
                        <th class="py-3 px-4">Descripción General</th>
                        <th class="py-3 px-4">Atributos Técnicos Vinculados (Pivot)</th>
                        <th class="py-3 px-4 text-center w-28">Total Activos</th>
                        <th class="py-3 px-4 text-right w-36">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-800 font-medium">
                    @forelse($tipos as $idx => $tipo)
                        <tr class="hover:bg-amber-50/30 transition-colors">
                            <td class="py-3.5 px-4 text-center font-mono text-gray-400 font-semibold">
                                {{ $tipos->firstItem() + $idx }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-gray-900 block capitalize text-sm">
                                    {{ $tipo->nombre }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-gray-500">
                                {{ $tipo->descripcion ?: '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse($tipo->atributos as $attr)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                            {{ $attr->nombre }}
                                            @if($attr->unidad_medida)
                                                <span class="text-gray-400 font-normal ml-1">({{ $attr->unidad_medida }})</span>
                                            @endif
                                        </span>
                                    @empty
                                        <span class="text-gray-400 italic text-[11px]">Sin atributos específicos (solo columnas base)</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold {{ $tipo->elementos_count > 0 ? 'bg-amber-100 text-amber-900' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $tipo->elementos_count }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-2">
                                <a href="{{ route('tipos.edit', $tipo->id) }}" class="inline-flex items-center text-xs font-bold text-gray-700 hover:text-black py-1 px-2 rounded hover:bg-gray-100">
                                    Configurar
                                </a>
                                @if($tipo->elementos_count === 0)
                                    <form action="{{ route('tipos.destroy', $tipo->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro que deseas eliminar el tipo {{ $tipo->nombre }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-800 py-1 px-2 rounded hover:bg-red-50">
                                            Eliminar
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400 italic">
                                No se encontraron tipos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200 bg-gray-50/50">
            {{ $tipos->links() }}
        </div>
    </div>
</div>

<!-- Modal para Crear Nuevo Atributo Técnico Global -->
<div id="modalNuevoAtributo" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="font-extrabold text-gray-900 text-base">Crear Atributo Técnico Global</h3>
            <button type="button" onclick="document.getElementById('modalNuevoAtributo').classList.add('hidden')" class="text-gray-400 hover:text-black p-1 rounded-lg hover:bg-gray-100 transition-colors" aria-label="Cerrar modal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form action="{{ route('atributos.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Nombre del Atributo *</label>
                <input type="text" name="nombre" placeholder="Ej: Potencia, Puertos, Pulgadas, Voltaje..." class="w-full text-xs px-3 py-2 border rounded-lg bg-gray-50 focus:bg-white" required>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Tipo de Dato *</label>
                    <select name="tipo_dato" class="w-full text-xs px-3 py-2 border rounded-lg bg-gray-50">
                        <option value="texto">Texto</option>
                        <option value="numero">Numérico</option>
                        <option value="selector">Selector / Opciones</option>
                        <option value="fecha">Fecha</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Unidad de Medida</label>
                    <input type="text" name="unidad_medida" placeholder="Ej: GB, GHz, Watts..." class="w-full text-xs px-3 py-2 border rounded-lg bg-gray-50">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Descripción</label>
                <input type="text" name="descripcion" placeholder="Aclaración técnica para quien registre activos..." class="w-full text-xs px-3 py-2 border rounded-lg bg-gray-50">
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t">
                <button type="button" onclick="document.getElementById('modalNuevoAtributo').classList.add('hidden')" class="px-3 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-lg">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 text-xs font-bold bg-amber-400 hover:bg-amber-500 text-black rounded-lg">
                    Guardar Atributo
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
