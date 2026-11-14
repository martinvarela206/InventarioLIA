@props(['movimiento'])

@if($movimiento->esPrestamo())
    <span class="inline-flex items-center text-xs font-bold px-2.5 py-1 rounded-xl bg-amber-50 text-amber-900 border border-[#fbc101]">Préstamo</span>
@else
    <span class="inline-flex items-center text-xs font-bold px-2.5 py-1 rounded-xl bg-gray-50 text-gray-800 border border-gray-300">Interno</span>
@endif
