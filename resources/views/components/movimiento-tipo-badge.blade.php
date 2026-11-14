@props(['movimiento'])

@if($movimiento->esCorreccion())
    <span class="inline-flex items-center text-xs font-bold px-2.5 py-1 rounded-xl bg-purple-50 text-purple-900 border border-purple-300 mr-1.5">Enmienda de #{{ (int)$movimiento->correccion_de_id }}</span>
@endif

@if($movimiento->esBaja())
    <span class="inline-flex items-center text-xs font-bold px-2.5 py-1 rounded-xl bg-rose-50 text-rose-800 border border-rose-300">Baja / Basura Tec.</span>
@elseif($movimiento->esReparacion())
    @php
        $estadoTxt = $movimiento->estado_ticket ? ucfirst($movimiento->estado_ticket) : 'Abierto';
    @endphp
    <span class="inline-flex items-center text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-50 text-amber-900 border border-[#fbc101]">Ticket ({{ $estadoTxt }})</span>
@elseif($movimiento->esPrestamo())
    <span class="inline-flex items-center text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-50 text-amber-900 border border-[#fbc101]">Préstamo Externo</span>
@else
    <span class="inline-flex items-center text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-50 text-amber-900 border border-[#fbc101]">Movimiento Interno</span>
@endif
