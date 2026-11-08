@props(['movimiento'])

@if($movimiento->esPrestamo())
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Préstamo</span>
@else
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Interno</span>
@endif
