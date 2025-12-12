@forelse($cpusObsoletos as $cpu)
    <tr class="hover:bg-gray-50 block md:table-row mb-4 md:mb-0 border md:border-b rounded-lg shadow-sm md:shadow-none p-4 md:p-0">
        <td class="px-4 py-2 md:px-6 md:py-4 whitespace-nowrap block md:table-cell flex justify-between md:block">
            <span class="font-bold md:hidden text-gray-700">Nro LIA:</span>
            <a href="{{ route('elementos.show', $cpu['nro_lia']) }}" class="text-amber-600 hover:text-amber-800 font-semibold">
                {{ $cpu['nro_lia'] }}
            </a>
        </td>
        <td class="px-4 py-2 md:px-6 md:py-4 block md:table-cell">
            <span class="font-bold md:hidden text-gray-700 block mb-1">Descripción:</span>
            {{ $cpu['descripcion'] }}
        </td>
        <td class="px-4 py-2 md:px-6 md:py-4 whitespace-nowrap block md:table-cell flex justify-between md:block">
            <span class="font-bold md:hidden text-gray-700">Fecha Adq.:</span>
            {{ $cpu['fecha_adquisicion'] }}
        </td>
        <td class="px-4 py-2 md:px-6 md:py-4 whitespace-nowrap block md:table-cell flex justify-between md:block">
            <span class="font-bold md:hidden text-gray-700">Antigüedad:</span>
            <span class="px-2 py-1 text-xs font-semibold rounded {{ $cpu['antiguedad'] >= 5 ? 'bg-red-200 text-red-800' : 'bg-yellow-200 text-yellow-800' }}">
                {{ $cpu['antiguedad'] }} años
            </span>
        </td>
        <td class="px-4 py-2 md:px-6 md:py-4 block md:table-cell flex justify-between md:block">
            <span class="font-bold md:hidden text-gray-700">Ubicación:</span>
            {{ $cpu['ubicacion'] }}
        </td>
    </tr>
@empty
    <tr class="block md:table-row">
        <td colspan="5" class="px-6 py-4 text-center text-gray-500 block md:table-cell">No hay CPUs obsoletos con el año seleccionado</td>
    </tr>
@endforelse
