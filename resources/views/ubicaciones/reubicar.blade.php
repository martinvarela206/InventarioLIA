@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-6xl">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-lg uppercase tracking-wider">
                    Asistente de Reubicación Patrimonial
                </span>
                <span class="text-xs text-gray-400 font-medium">• Exclusivo Administrador de Activos (asset_admin)</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Reubicación de Activos en Limbo</h1>
            <p class="text-sm text-gray-600 mt-1">
                Gestione la reubicación de los activos pertenecientes a salas archivadas o renombradas para restaurar su estado operativo regular.
            </p>
        </div>
        <div>
            <a href="{{ route('ubicaciones.index') }}" class="px-4 py-2 border border-gray-300 rounded-xl text-xs font-bold text-gray-700 bg-white hover:bg-amber-50/50 shadow-2xs transition-all inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Volver a Espacios Físicos</span>
            </a>
        </div>
    </div>

    <!-- Banner Informativo -->
    @if($origen)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-6 shadow-2xs">
        <div class="flex items-start gap-3">
            <div class="p-2 bg-amber-100 rounded-xl text-amber-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="flex-1 text-xs">
                <p class="font-bold text-amber-900 text-sm mb-1">
                    Sala de Origen: <span class="underline">{{ $origen->nombre }}</span> (Archivada / Histórica)
                </p>
                <p class="text-amber-800">
                    Se encontraron <strong class="font-extrabold text-amber-950">{{ $activos->count() }}</strong> activos en estado de <span class="font-bold text-amber-900">limbo por reubicación</span>.
                    @if($destinoSugerido)
                        El sistema sugiere como nuevo destino preferente: <strong>{{ $destinoSugerido->nombre }}</strong>.
                    @endif
                </p>
            </div>
        </div>
    </div>
    @endif

    @if($activos->isEmpty())
        <div class="bg-white border border-gray-200 rounded-2xl p-12 text-center shadow-2xs">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h3 class="text-base font-bold text-gray-800 mb-1">No hay activos en limbo pendientes de reubicación</h3>
            <p class="text-xs text-gray-500 mb-4">Todos los activos asociados a salas archivadas han sido reasignados o regularizados.</p>
            <a href="{{ route('ubicaciones.index') }}" class="px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-xl transition-all">
                Regresar al Catálogo de Espacios
            </a>
        </div>
    @else
        <!-- OPCIÓN 1: Reubicación Rápida en Lote -->
        <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-6 shadow-2xs">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-2 flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-amber-400 rounded-full inline-block"></span>
                <span>Opción 1: Traslado Masivo a un Destino Común</span>
            </h2>
            <p class="text-xs text-gray-500 mb-4">Reubique todos los {{ $activos->count() }} activos seleccionados hacia un único laboratorio o depósito de destino.</p>

            <form action="{{ route('ubicaciones.reubicar.store') }}" method="POST" class="space-y-4">
                @csrf
                @foreach($activos as $a)
                    <input type="hidden" name="items[]" value="{{ $a->id }}">
                @endforeach

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label for="ubicacion_comun_id" class="block text-xs font-bold text-gray-700 uppercase mb-1">Laboratorio o Espacio de Destino Común</label>
                        <select name="ubicacion_comun_id" id="ubicacion_comun_id" required class="w-full px-3.5 py-2.5 border rounded-xl text-sm bg-white font-medium focus:ring-2 focus:ring-amber-400 focus:outline-none">
                            <option value="">Seleccione el nuevo espacio físico...</option>
                            @foreach($ubicacionesActivas as $u)
                                <option value="{{ $u->id }}" {{ old('ubicacion_comun_id', $destinoSugerido?->id) == $u->id ? 'selected' : '' }}>
                                    {{ $u->nombre }} {{ $destinoSugerido?->id == $u->id ? '(Sugerido)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="w-full bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold py-2.5 px-4 rounded-xl shadow-2xs transition-colors text-sm">
                            Trasladar Todos los {{ $activos->count() }} Equipos
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- OPCIÓN 2: Reubicación Discriminada por Fila -->
        <div class="bg-white border border-gray-200 rounded-2xl shadow-2xs overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-1 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-blue-500 rounded-full inline-block"></span>
                        <span>Opción 2: Reubicación Discriminada (Destinos Independientes)</span>
                    </h2>
                    <p class="text-xs text-gray-500">Defina individualmente el laboratorio o puesto de destino para cada activo.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="btn-copiar-primer-destino" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition-colors">
                        Copiar primer destino a todas
                    </button>
                </div>
            </div>

            <form action="{{ route('ubicaciones.reubicar.store') }}" method="POST">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-700">
                        <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-[11px] border-b border-gray-100">
                            <tr>
                                <th class="py-3 px-4">Identificador LIA / UNSJ</th>
                                <th class="py-3 px-4">Tipo y Equipo</th>
                                <th class="py-3 px-4">Estado</th>
                                <th class="py-3 px-4">Puesto Anterior</th>
                                <th class="py-3 px-4">Nuevo Destino</th>
                                <th class="py-3 px-4">Nuevo Puesto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($activos as $idx => $activo)
                            <tr class="hover:bg-gray-50/60 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-gray-900">
                                    <input type="hidden" name="items[{{ $idx }}][elemento_id]" value="{{ $activo->id }}">
                                    <span>{{ $activo->nro_lia ?: 'S/N' }}</span>
                                    @if($activo->nro_unsj)
                                        <span class="text-[10px] text-gray-400 block font-normal">{{ $activo->nro_unsj }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-gray-800 block">{{ $activo->tipo?->nombre }}</span>
                                    <span class="text-gray-500 text-[11px]">{{ $activo->marca }} {{ $activo->modelo }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <x-elemento-status-badge :elemento="$activo" />
                                </td>
                                <td class="py-3.5 px-4 text-gray-500">
                                    {{ $activo->ultimoMovimiento?->puesto ?: 'S/P' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <select name="items[{{ $idx }}][ubicacion_id]" class="destino-select w-full px-2.5 py-1.5 border rounded-lg text-xs bg-white focus:ring-1 focus:ring-amber-400 focus:outline-none">
                                        @foreach($ubicacionesActivas as $u)
                                            <option value="{{ $u->id }}" {{ $destinoSugerido?->id == $u->id ? 'selected' : '' }}>
                                                {{ $u->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="py-3.5 px-4">
                                    <input type="text" name="items[{{ $idx }}][puesto]" placeholder="ej: Puesto 04" value="{{ $activo->ultimoMovimiento?->puesto }}" class="w-28 px-2.5 py-1.5 border rounded-lg text-xs bg-white focus:ring-1 focus:ring-amber-400 focus:outline-none">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-xs text-gray-400 font-medium">Reubicando {{ $activos->count() }} elementos</span>
                    <button type="submit" class="bg-gray-900 hover:bg-black text-white font-extrabold py-2 px-5 rounded-xl shadow-2xs transition-colors text-xs">
                        Aplicar Reubicación Discriminada
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnCopiar = document.getElementById('btn-copiar-primer-destino');
        if (btnCopiar) {
            btnCopiar.addEventListener('click', function() {
                const selects = document.querySelectorAll('.destino-select');
                if (selects.length > 1) {
                    const firstVal = selects[0].value;
                    selects.forEach((sel, idx) => {
                        if (idx > 0) sel.value = firstVal;
                    });
                }
            });
        }
    });
</script>
@endsection
