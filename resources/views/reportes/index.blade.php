@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Header -->
    <div class="border-b border-gray-200 pb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2.5">
                <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Centro de Reportes y Auditorías — Nexo LIA</span>
            </h1>
            <p class="text-xs text-gray-500 mt-1">
                Catálogo unificado de reportes patrimoniales, trazabilidad física y auditorías in situ. Disponibles para visualización, impresión formal A4 y exportación CSV (BOM UTF-8).
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->check() && (auth()->user()->isAppAdmin() || auth()->user()->hasRole('app_admin')))
                <a href="{{ route('app_admin.reportes') }}" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-3.5 py-2 rounded-xl text-xs shadow-xs transition-all">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Configurar Catálogo de Reportes</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Barra de Filtro Rápido por Categoría -->
    <div class="flex items-center justify-between gap-3 flex-wrap">
        <div class="flex items-center gap-1.5 flex-wrap" id="filtros-categorias">
            <button type="button" onclick="filtrarCategoria('todos')" class="btn-categoria active px-3 py-1 rounded-full text-xs font-bold transition-all bg-amber-500 text-black shadow-xs">
                Todos ({{ $reportes->count() }})
            </button>
            @php
                $categorias = $reportes->pluck('categoria')->unique()->filter()->values();
            @endphp
            @foreach($categorias as $cat)
                <button type="button" onclick="filtrarCategoria('{{ $cat }}')" class="btn-categoria px-3 py-1 rounded-full text-xs font-bold transition-all bg-gray-100 hover:bg-gray-200 text-gray-700">
                    {{ ucfirst($cat) }}
                </button>
            @endforeach
        </div>
        <span class="text-xs text-amber-800 font-semibold bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200">
            Catálogo Unificado (Reportes Personalizados y Guardados)
        </span>
    </div>

    <!-- Cuadrícula Unificada de Reportes -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="grid-reportes">
        @forelse($reportes as $rep)
            <div class="tarjeta-reporte bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col justify-between hover:border-amber-400 hover:shadow-md transition-all" data-categoria="{{ $rep->categoria ?: 'general' }}">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-900 border border-amber-200/60 flex items-center justify-center font-bold text-lg">
                            @if($rep->icono == 'cubes') 📦
                            @elseif($rep->icono == 'building') 🏢
                            @elseif($rep->icono == 'location') 📍
                            @elseif($rep->icono == 'wrench') 🔧
                            @elseif($rep->icono == 'arrows-right-left') 🔄
                            @elseif($rep->icono == 'gift') 🎁
                            @elseif($rep->icono == 'calendar-plus') 📅
                            @elseif($rep->icono == 'clock') ⏱️
                            @elseif($rep->icono == 'shield') 🛡️
                            @elseif($rep->icono == 'check-circle') ✅
                            @elseif($rep->icono == 'trash') 🗑️
                            @else 📊
                            @endif
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-700 border border-gray-200">
                            {{ $rep->categoria ?: 'General' }}
                        </span>
                    </div>

                    <h3 class="font-bold text-gray-900 text-base">{{ $rep->titulo }}</h3>
                    <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                        {{ $rep->subtitulo ?: $rep->descripcion }}
                    </p>
                </div>

                <div class="mt-5 pt-4 border-t border-gray-100">
                    @if($rep->requiereUbicacion() || $rep->requierePuesto() || $rep->requiereTipo() || $rep->requiereNroLia() || $rep->requiereRangoFechas())
                        <form action="{{ route('reportes.show', $rep->slug) }}" method="GET" class="space-y-2">
                            @if($rep->requiereUbicacion())
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Sala / Armario:</label>
                                    <select name="ubicacion_id" class="w-full text-xs px-2.5 py-1.5 border border-gray-300 rounded-xl bg-gray-50 font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400" {{ $rep->slug === 'auditoria-sala' ? 'required' : '' }}>
                                        @if($rep->slug !== 'auditoria-sala')
                                            <option value="">-- Todas las salas y armarios --</option>
                                        @endif
                                        @foreach($ubicaciones as $ub)
                                            <option value="{{ $ub->id }}">{{ $ub->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if($rep->requierePuesto())
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Puesto de Trabajo:</label>
                                    <input type="text" name="puesto" placeholder="Ej. Puesto 04" class="w-full text-xs px-2.5 py-1.5 border border-gray-300 rounded-xl bg-gray-50 font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                                </div>
                            @endif

                            @if($rep->requiereTipo())
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Tipo de Activo:</label>
                                    <select name="tipo_id" class="w-full text-xs px-2.5 py-1.5 border border-gray-300 rounded-xl bg-gray-50 font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400" required>
                                        @foreach($tipos as $tp)
                                            <option value="{{ $tp->id }}">{{ $tp->tipo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if($rep->requiereNroLia())
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-600 mb-0.5">N° LIA / Activo:</label>
                                    <input type="text" name="nro_lia" placeholder="Ej. LIA-042 o UNSJ" class="w-full text-xs px-2.5 py-1.5 border border-gray-300 rounded-xl bg-gray-50 font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400" required>
                                </div>
                            @endif

                            @if($rep->requiereRangoFechas())
                                <div class="grid grid-cols-2 gap-1.5">
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Desde:</label>
                                        <input type="date" name="fecha_desde" value="{{ \Carbon\Carbon::now()->subMonths(6)->toDateString() }}" class="w-full text-xs px-2 py-1.5 border border-gray-300 rounded-xl bg-gray-50 font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-600 mb-0.5">Hasta:</label>
                                        <input type="date" name="fecha_hasta" value="{{ \Carbon\Carbon::now()->toDateString() }}" class="w-full text-xs px-2 py-1.5 border border-gray-300 rounded-xl bg-gray-50 font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                                    </div>
                                </div>
                            @endif

                            <div class="pt-1 flex items-center gap-2">
                                <button type="submit" class="flex-1 text-center py-2 px-3 bg-[#fbc101] hover:bg-amber-400 font-extrabold text-black rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Ver Reporte</span>
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="flex items-center gap-2">
                            <a href="{{ route('reportes.show', $rep->slug) }}" class="flex-1 text-center py-2 px-3 bg-[#fbc101] hover:bg-amber-400 font-extrabold text-black rounded-xl text-xs transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Ver Reporte</span>
                            </a>
                            <a href="{{ route('reportes.exportCsv', $rep->slug) }}" class="p-2 border border-gray-300 hover:bg-gray-50 rounded-xl text-gray-700 text-xs font-bold inline-flex items-center gap-1 shadow-xs" title="Descargar CSV">
                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                CSV
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-gray-400 text-sm bg-white rounded-2xl border border-gray-200">
                No hay reportes configurados actualmente en el catálogo.
            </div>
        @endforelse
    </div>
</div>

<script>
    function filtrarCategoria(categoria) {
        // Actualizar estilos de botones
        document.querySelectorAll('.btn-categoria').forEach(btn => {
            btn.classList.remove('active', 'bg-amber-500', 'text-black', 'shadow-xs');
            btn.classList.add('bg-gray-100', 'text-gray-700');
        });
        event.target.classList.add('active', 'bg-amber-500', 'text-black', 'shadow-xs');
        event.target.classList.remove('bg-gray-100', 'text-gray-700');

        // Filtrar tarjetas
        const tarjetas = document.querySelectorAll('.tarjeta-reporte');
        tarjetas.forEach(t => {
            if (categoria === 'todos' || t.dataset.categoria === categoria) {
                t.classList.remove('hidden');
            } else {
                t.classList.add('hidden');
            }
        });
    }
</script>
@endsection
