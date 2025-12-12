@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-amber-700 mb-8">Dashboard - Inventario LIA</h1>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Elementos -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Total Elementos</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $totalElementos }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Elementos Prestados -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-amber-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Prestados</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $totalPrestados }}</p>
                </div>
                <div class="bg-amber-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- CPUs Obsoletos -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">CPUs Obsoletos</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $cpusObsoletos->count() }}</p>
                </div>
                <div class="bg-red-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Usuarios Activos -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Usuarios Activos</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $actividadUsuarios->count() }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Elementos por Tipo/Ubicación -> Elementos Varios (No CPU) -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Elementos Varios por Ubicación</h2>
            <div class="relative h-80">
                <canvas id="tipoUbicacionChart"></canvas>
            </div>
        </div>

        <!-- Distribución Prestados -> CPUs por Ubicación -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">CPUs Disponibles por Ubicación</h2>
            <div class="relative h-80">
                <canvas id="prestadosChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Elementos Prestados Table -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Elementos Actualmente Prestados</h2>
        <div class="">
            <table class="min-w-full block md:table">
                <thead class="bg-amber-100 hidden md:table-header-group">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Nro LIA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Descripción</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Prestado a</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tiempo</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 block md:table-row-group">
                    @forelse($elementosPrestados as $elemento)
                        <tr class="hover:bg-gray-50 block md:table-row mb-4 md:mb-0 border md:border-b rounded-lg shadow-sm md:shadow-none p-4 md:p-0">
                            <td class="px-4 py-2 md:px-6 md:py-4 whitespace-nowrap block md:table-cell flex justify-between md:block">
                                <span class="font-bold md:hidden text-gray-700">Nro LIA:</span>
                                <a href="{{ route('elementos.show', $elemento['nro_lia']) }}" class="text-amber-600 hover:text-amber-800 font-semibold">
                                    {{ $elemento['nro_lia'] }}
                                </a>
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-4 whitespace-nowrap block md:table-cell flex justify-between md:block">
                                <span class="font-bold md:hidden text-gray-700">Tipo:</span>
                                {{ $elemento['tipo'] }}
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-4 block md:table-cell">
                                <span class="font-bold md:hidden text-gray-700 block mb-1">Descripción:</span>
                                {{ $elemento['descripcion'] }}
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-4 block md:table-cell flex justify-between md:block">
                                <span class="font-bold md:hidden text-gray-700">Prestado a:</span>
                                {{ $elemento['comentario'] }}
                            </td>
                            <td class="px-4 py-2 md:px-6 md:py-4 whitespace-nowrap block md:table-cell flex justify-between md:block">
                                <span class="font-bold md:hidden text-gray-700">Tiempo:</span>
                                <span class="px-2 py-1 text-xs font-semibold rounded {{ $elemento['dias_prestado'] > 30 ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $elemento['dias_prestado'] }} días
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr class="block md:table-row">
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500 block md:table-cell">No hay elementos prestados actualmente</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- CPUs Obsoletos -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">CPUs Obsoletos</h2>
            <div class="flex items-center gap-2">
                <label for="year" class="text-sm text-gray-600">Año límite:</label>
                <input type="number" name="year" id="year" value="{{ $yearThreshold }}" min="2000" max="{{ now()->year }}" 
                       class="border border-gray-300 rounded px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                       onchange="updateCpusTable(this.value)">
            </div>
        </div>
        <div class="">
            <table class="min-w-full block md:table">
                <thead class="bg-red-100 hidden md:table-header-group">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Nro LIA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Descripción</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Fecha Adquisición</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Antigüedad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Ubicación</th>
                    </tr>
                </thead>
                <tbody id="cpusTableBody" class="bg-white divide-y divide-gray-200 block md:table-row-group">
                    @include('dashboard.partials.cpus_table_rows')
                </tbody>
            </table>
        </div>
    </div>

    <!-- Actividad de Usuarios -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Actividad de Usuarios</h2>
        <div class="relative h-80">
            <canvas id="actividadUsuariosChart"></canvas>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // 1. Elementos por Tipo y Ubicación (Ahora: Elementos NO CPU Dispo por Ubicación)
    // Se muestra en el gráfico de barras que antes era 'tipoUbicacionChart'
    const noCpuData = @json($elementosNoCpuPorUbicacion);
    const labelsNoCpu = noCpuData.map(item => item.ubicacion);
    const dataNoCpu = noCpuData.map(item => item.cantidad);

    new Chart(document.getElementById('tipoUbicacionChart'), {
        type: 'bar',
        data: {
            labels: labelsNoCpu,
            datasets: [{
                label: 'Elementos Disponibles (No CPU)',
                data: dataNoCpu,
                backgroundColor: 'rgba(219, 168, 0, 0.7)',
                borderColor: 'rgba(219, 168, 0, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                     display: true,
                     text: 'Elementos Varios Disponibles por Ubicación'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // 2. Distribución Prestados (Ahora: CPUs Disponibles por Ubicación)
    // Se muestra en el gráfico Doughnut que antes era 'prestadosChart'
    const cpusDispoData = @json($cpusPorUbicacion);
    const labelsCpu = cpusDispoData.map(item => item.ubicacion);
    const dataCpu = cpusDispoData.map(item => item.cantidad);
    
    // Generate colors dynamically if possible, or use a palette.
    // Amber Palette for consistency + some variations
    const bgColors = [
        'rgba(251, 191, 36, 0.8)', // amber-400
        'rgba(245, 158, 11, 0.8)', // amber-500
        'rgba(217, 119, 6, 0.8)',  // amber-600
        'rgba(180, 83, 9, 0.8)',   // amber-700
        'rgba(146, 64, 14, 0.8)',  // amber-800
        'rgba(120, 53, 15, 0.8)',  // amber-900
        'rgba(252, 211, 77, 0.8)', // amber-300
    ];
    const borderColors = bgColors.map(c => c.replace('0.8', '1'));

    new Chart(document.getElementById('prestadosChart'), {
        type: 'doughnut',
        data: {
            labels: labelsCpu,
            datasets: [{
                data: dataCpu,
                backgroundColor: bgColors.slice(0, labelsCpu.length),
                borderColor: borderColors.slice(0, labelsCpu.length),
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                     display: true,
                     text: 'CPUs Disponibles por Ubicación'
                }
            }
        }
    });

    // Actividad de Usuarios
    const actividadData = @json($actividadUsuarios);
    new Chart(document.getElementById('actividadUsuariosChart'), {
        type: 'bar',
        data: {
            labels: actividadData.map(u => u.nombre),
            datasets: [{
                label: 'Movimientos Realizados',
                data: actividadData.map(u => u.movimientos),
                backgroundColor: 'rgba(59, 130, 246, 0.7)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    function updateCpusTable(year) {
        const tbody = document.getElementById('cpusTableBody');
        tbody.style.opacity = '0.5';
        
        fetch(`{{ route('dashboard.index') }}?year=${year}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            tbody.innerHTML = html;
            tbody.style.opacity = '1';
        })
        .catch(error => {
            console.error('Error updating table:', error);
            tbody.style.opacity = '1';
        });
    }
</script>
@endsection
