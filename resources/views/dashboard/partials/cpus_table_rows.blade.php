@forelse($cpusObsoletos as $cpu)
    <tr class="hover:bg-figma-bg-deep/50 block md:table-row mb-3 md:mb-0 border-b border-figma-border-subtle p-3 md:p-0 transition-colors">
        <td class="px-3 py-2 md:px-4 md:py-2.5 whitespace-nowrap block md:table-cell flex justify-between md:block">
            <span class="font-bold md:hidden text-figma-text-tertiary">Nro LIA:</span>
            <a href="{{ route('elementos.show', $cpu['nro_lia']) }}" class="text-figma-brand hover:underline font-mono font-bold">
                {{ $cpu['nro_lia'] }}
            </a>
        </td>
        <td class="px-3 py-2 md:px-4 md:py-2.5 block md:table-cell text-figma-text-primary">
            <span class="font-bold md:hidden text-figma-text-tertiary block mb-1">Descripción:</span>
            {{ $cpu['descripcion'] }}
        </td>
        <td class="px-3 py-2 md:px-4 md:py-2.5 whitespace-nowrap block md:table-cell flex justify-between md:block font-mono text-figma-text-secondary">
            <span class="font-bold md:hidden text-figma-text-tertiary">Fecha Adq.:</span>
            {{ $cpu['fecha_adquisicion'] }}
        </td>
        <td class="px-3 py-2 md:px-4 md:py-2.5 whitespace-nowrap block md:table-cell flex justify-between md:block font-mono">
            <span class="font-bold md:hidden text-figma-text-tertiary">Antigüedad:</span>
            <span class="px-2 py-0.5 text-[10px] font-bold rounded {{ $cpu['antiguedad'] >= 5 ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-amber-500/15 text-amber-400 border border-amber-500/30' }}">
                {{ $cpu['antiguedad'] }} años
            </span>
        </td>
        <td class="px-3 py-2 md:px-4 md:py-2.5 block md:table-cell flex justify-between md:block text-figma-text-secondary">
            <span class="font-bold md:hidden text-figma-text-tertiary">Ubicación:</span>
            {{ $cpu['ubicacion'] }}
        </td>
    </tr>
@empty
    <tr class="block md:table-row">
        <td colspan="5" class="px-4 py-4 text-center text-figma-text-tertiary block md:table-cell italic text-xs">No hay CPUs obsoletos con el año seleccionado</td>
    </tr>
@endforelse
