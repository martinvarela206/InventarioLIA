@props(['elemento'])

@php
    $estado = $elemento->estado;
    $ultimo = $elemento->ultimoMovimiento;
@endphp

@if(!$ultimo && $estado === \App\Enums\EstadoElemento::OPERATIVO)
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">Sin historial</span>
@elseif($estado === \App\Enums\EstadoElemento::PRESTADO && $ultimo)
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $estado->badgeClasses() }}">Prestado ({{ (int)($ultimo->diasPrestado() ?? 0) }} d)</span>
@else
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $estado->badgeClasses() }}">{{ $estado->label() }}</span>
@endif
