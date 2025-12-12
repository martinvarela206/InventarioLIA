@extends('layouts.app')

@section('content')
    <div class="flex flex-col lg:flex-row gap-6">
        <div class="flex-1 bg-white shadow-md rounded p-6">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold">Detalle del Elemento</h1>
                @can('write-data')
                    <a href="{{ route('elementos.edit', $elemento->nro_lia) }}" class="text-purple-600 hover:text-purple-800 transform hover:scale-110 transition-transform" title="Editar elemento">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </a>
                @endcan
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
                <div><strong class="block text-sm text-gray-500">Nro LIA</strong><div class="mt-1">{{ $elemento->nro_lia }}</div></div>
                <div><strong class="block text-sm text-gray-500">Nro UNSJ</strong><div class="mt-1">{{ $elemento->nro_unsj }}</div></div>
                <div><strong class="block text-sm text-gray-500">Tipo</strong><div class="mt-1">{{ strtoupper($elemento->tipo?->nombre ?? 'N/A') }}</div></div>
                <div><strong class="block text-sm text-gray-500">Cantidad</strong><div class="mt-1">{{ $elemento->cantidad }}</div></div>
                <div><strong class="block text-sm text-gray-500">Adquisición</strong><div class="mt-1">{{ $elemento->fecha_adquisicion ? $elemento->fecha_adquisicion->format('d/m/Y') : '-' }}</div></div>
                <div><strong class="block text-sm text-gray-500">Garantía Vence</strong><div class="mt-1 {{ $elemento->fecha_vencimiento_garantia && $elemento->fecha_vencimiento_garantia->isPast() ? 'text-red-600 font-semibold' : 'text-green-600' }}">{{ $elemento->fecha_vencimiento_garantia ? $elemento->fecha_vencimiento_garantia->format('d/m/Y') : '-' }}</div></div>
                <div class="md:col-span-2">
                    <strong class="block text-sm text-gray-500">Descripción</strong>
                    <div class="mt-1">
                        @if(is_array($elemento->descripcion) && count($elemento->descripcion) > 0)
                            <dl class="grid grid-cols-2 gap-x-4 gap-y-2">
                                @foreach($elemento->descripcion as $key => $value)
                                    <dt class="font-semibold text-gray-700">{{ $key }}:</dt>
                                    <dd class="text-gray-900">{{ $value }}</dd>
                                @endforeach
                            </dl>
                        @else
                            <span class="text-gray-400">Sin descripción</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- right column removed: controls moved above the full movements table -->
    </div>
    <div class="flex items-center justify-between mt-6 mb-4">
        <h2 class="text-xl font-bold">Movimientos asociados</h2>
        @can('write-data')
            <a href="{{ route('movimientos.create', ['nro_lia' => $elemento->nro_lia]) }}" class="inline-flex items-center gap-2 bg-[#dba800] hover:bg-[#fbc101] text-[#111] font-semibold py-2 px-3 rounded shadow text-sm">Añadir Movimiento</a>
        @endcan
    </div>

    <div class="bg-white shadow-md rounded overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead>
                <tr class="bg-amber-300 text-amber-900 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">Fecha</th>
                    <th class="py-3 px-6 text-left">Estado</th>
                    <th class="py-3 px-6 text-left">Ubicación</th>
                    <th class="py-3 px-6 text-left">Usuario</th>
                    <th class="py-3 px-6 text-left">Comentario</th>
                    @can('manage-movements')
                        <th class="py-3 px-6 text-center">Acciones</th>
                    @endcan
                </tr>
            </thead>
            <tbody class="text-gray-800 text-sm font-light">
                @foreach($movimientos as $m)
                    @php $isLast = isset($ultimoMovimiento) && $ultimoMovimiento && $m->id === $ultimoMovimiento->id; @endphp
                    <tr class="odd:bg-amber-50 even:bg-amber-100 border-b border-gray-300 hover:bg-amber-200 {{ $isLast ? 'bg-amber-300 border-l-4 border-amber-600' : '' }}">
                        <td class="py-3 px-6 text-left whitespace-nowrap">
                            {{ $m->fecha->format('Y-m-d H:i') }}
                            @if($isLast)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-700 text-white">Último</span>
                            @endif
                        </td>
                        <td class="py-3 px-6 text-left">{{ ucfirst($m->estado?->nombre ?? 'N/A') }}</td>
                        <td class="py-3 px-6 text-left"><span class="{{ $isLast ? 'font-semibold text-amber-900' : '' }}">{{ $m->ubicacion?->nombre ?? 'Sin ubicación' }}</span></td>
                        <td class="py-3 px-6 text-left">{{ $m->usuario->nombre ?? $m->user_id ?? 'N/A' }}</td>
                        <td class="py-3 px-6 text-left">{{ $m->comentario }}</td>
                        @can('manage-movements')
                            <td class="py-3 px-6 text-center">
                                <div class="flex item-center justify-center">
                                    <a href="{{ route('movimientos.edit', $m->id) }}" class="w-4 mr-2 transform hover:text-purple-500 hover:scale-110">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('movimientos.destroy', $m->id) }}" method="POST" onsubmit="return confirm('¿Seguro que desea eliminar este movimiento?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-4 mr-2 transform hover:text-red-500 hover:scale-110">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        @endcan
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-6">
        @if(request('from') == 'revision')
            <a href="{{ route('revision.index') }}" class="text-[#111] underline">Volver a Revisión</a>
        @else
            <a href="{{ route('elementos.index') }}" class="text-[#111] underline">Volver a Elementos</a>
        @endif
    </div>
@endsection
