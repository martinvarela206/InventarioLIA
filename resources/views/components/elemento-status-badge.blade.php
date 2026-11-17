@props(['elemento'])

@php
    $estado = $elemento->estado;
    $ultimo = $elemento->ultimoMovimiento;
@endphp

@if(!$ultimo && $estado === \App\Enums\EstadoElemento::OPERATIVO)
    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold border bg-gray-100 text-gray-800 border-gray-300">Sin historial</span>
@elseif($estado === \App\Enums\EstadoElemento::PRESTADO && $ultimo)
    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold border {{ $estado->badgeClasses() }}">Prestado ({{ (int)($ultimo->diasPrestado() ?? 0) }} d)</span>
@else
    <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold border {{ $estado->badgeClasses() }}">{{ $estado->label() }}</span>
@endif
