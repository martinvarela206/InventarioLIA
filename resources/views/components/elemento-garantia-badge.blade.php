@props(['elemento'])

@if(!$elemento->fecha_vencimiento_garantia)
    <span class="text-xs text-gray-500 italic">Sin datos de garantía</span>
@else
    @php
        $vencimiento = \Carbon\Carbon::parse($elemento->fecha_vencimiento_garantia);
        $hoy = \Carbon\Carbon::today();
    @endphp
    @if($vencimiento->isPast())
        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Vencida hace {{ $vencimiento->diffForHumans($hoy, ['parts' => 1, 'syntax' => \Carbon\Carbon::DIFF_ABSOLUTE]) }}</span>
    @else
        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Vigente (vence en {{ $hoy->diffForHumans($vencimiento, ['parts' => 1, 'syntax' => \Carbon\Carbon::DIFF_ABSOLUTE]) }})</span>
    @endif
@endif
