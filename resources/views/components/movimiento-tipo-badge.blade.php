@props(['movimiento'])

@if($movimiento->esCorreccion())
    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-purple-100 text-purple-900 border border-purple-300 mr-1.5">Enmienda de #{{ (int)$movimiento->correccion_de_id }}</span>
@endif

@if($movimiento->esBaja())
    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-300">Baja / Basura Tec.</span>
@elseif($movimiento->esReparacion())
    @php
        $estadoTxt = $movimiento->estado_ticket ? ucfirst($movimiento->estado_ticket) : 'Abierto';
    @endphp
    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300">Ticket ({{ $estadoTxt }})</span>
@elseif($movimiento->esPrestamo())
    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-300">Préstamo Externo</span>
@else
    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">Movimiento Interno</span>
@endif
