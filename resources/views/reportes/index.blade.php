@extends('layouts.app')

@section('content')
<div class="max-w-[1320px] mx-auto px-2 space-y-5">
    <!-- Header Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('dashboard.index') }}" class="hover:text-figma-brand transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-figma-text-primary">Auditoría & Análisis</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <svg class="w-5 h-5 text-figma-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Centro de Reportes y Auditorías</span>
            </h1>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->check() && (auth()->user()->isAppAdmin() || auth()->user()->hasRole('app_admin')))
                <a href="{{ route('app_admin.reportes') }}" class="figma-btn-secondary text-xs">
                    <svg class="w-3.5 h-3.5 text-figma-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Configurar Catálogo</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Barra de Filtro Rápido por Categoría (Figma Pill Ribbon) -->
    <div class="flex items-center justify-between gap-3 flex-wrap">
        <div class="figma-pill-ribbon overflow-x-auto" id="filtros-categorias">
            <button type="button" onclick="filtrarCategoria('todos', this)" class="figma-pill-item btn-categoria active">
                <span>Todos</span>
                <span class="font-mono text-[10px] font-bold px-1.5 py-0.2 rounded bg-figma-bg-deep">{{ $reportes->count() }}</span>
            </button>
            @php
                $categorias = $reportes->pluck('categoria')->unique()->filter()->values();
            @endphp
            @foreach($categorias as $cat)
                <button type="button" onclick="filtrarCategoria('{{ $cat }}', this)" class="figma-pill-item btn-categoria">
                    <span>{{ ucfirst($cat) }}</span>
                </button>
            @endforeach
        </div>
        <span class="font-mono text-[11px] text-figma-text-tertiary">
            Formatos: Pantalla interactiva • Imprimible A4 • CSV UTF-8
        </span>
    </div>

    <!-- Cuadrícula Unificada de Reportes -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="grid-reportes">
        @forelse($reportes as $rep)
            <div class="tarjeta-reporte figma-card p-4 sm:p-5 flex flex-col justify-between" data-categoria="{{ $rep->categoria ?: 'general' }}">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 rounded-lg bg-figma-bg-deep border border-figma-border-subtle flex items-center justify-center font-bold text-base">
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
                        <span class="font-mono px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-figma-bg-deep text-figma-text-secondary border border-figma-border-subtle">
                            {{ $rep->categoria ?: 'General' }}
                        </span>
                    </div>

                    <h3 class="font-bold text-figma-text-primary text-sm">{{ $rep->titulo }}</h3>
                    <p class="text-[11px] text-figma-text-tertiary mt-1 leading-relaxed">
                        {{ $rep->subtitulo ?: $rep->descripcion }}
                    </p>
                </div>

                <div class="mt-4 pt-3 border-t border-figma-border-subtle">
                    @if($rep->requiereUbicacion() || $rep->requierePuesto() || $rep->requiereTipo() || $rep->requiereNroLia() || $rep->requiereRangoFechas())
                        <form action="{{ route('reportes.show', $rep->slug) }}" method="GET" class="space-y-2 text-xs">
                            @if($rep->requiereUbicacion())
                                <div>
                                    <label class="block text-[10px] font-bold text-figma-text-secondary mb-0.5 uppercase">Sala / Espacio:</label>
                                    <select name="ubicacion_id" class="figma-input w-full px-2.5 py-1 text-xs cursor-pointer" {{ $rep->slug === 'auditoria-sala' ? 'required' : '' }}>
                                        @if($rep->slug !== 'auditoria-sala')
                                            <option value="">-- Todas las salas --</option>
                                        @endif
                                        @foreach($ubicaciones as $ub)
                                            <option value="{{ $ub->id }}">{{ $ub->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if($rep->requierePuesto())
                                <div>
                                    <label class="block text-[10px] font-bold text-figma-text-secondary mb-0.5 uppercase">Puesto:</label>
                                    <input type="text" name="puesto" placeholder="Ej. Puesto 04" class="figma-input w-full px-2.5 py-1 text-xs">
                                </div>
                            @endif

                            @if($rep->requiereTipo())
                                <div>
                                    <label class="block text-[10px] font-bold text-figma-text-secondary mb-0.5 uppercase">Tipo de Activo:</label>
                                    <select name="tipo_id" class="figma-input w-full px-2.5 py-1 text-xs cursor-pointer" required>
                                        @foreach($tipos as $tp)
                                            <option value="{{ $tp->id }}">{{ $tp->tipo }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if($rep->requiereNroLia())
                                <div>
                                    <label class="block text-[10px] font-bold text-figma-text-secondary mb-0.5 uppercase">N° LIA / Activo:</label>
                                    <input type="text" name="nro_lia" placeholder="Ej. LIA-042" class="figma-input w-full px-2.5 py-1 text-xs" required>
                                </div>
                            @endif

                            @if($rep->requiereRangoFechas())
                                <div class="grid grid-cols-2 gap-1.5">
                                    <div>
                                        <label class="block text-[10px] font-bold text-figma-text-secondary mb-0.5 uppercase">Desde:</label>
                                        <input type="date" name="fecha_desde" value="{{ \Carbon\Carbon::now()->subMonths(6)->toDateString() }}" class="figma-input w-full px-2 py-1 text-xs font-mono">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-figma-text-secondary mb-0.5 uppercase">Hasta:</label>
                                        <input type="date" name="fecha_hasta" value="{{ \Carbon\Carbon::now()->toDateString() }}" class="figma-input w-full px-2 py-1 text-xs font-mono">
                                    </div>
                                </div>
                            @endif

                            <div class="pt-1">
                                <button type="submit" class="figma-btn-primary w-full justify-center py-2 text-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Ver Reporte</span>
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="flex items-center gap-2">
                            <a href="{{ route('reportes.show', $rep->slug) }}" class="figma-btn-primary flex-1 justify-center py-2 text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Ver Reporte</span>
                            </a>
                            <a href="{{ route('reportes.exportCsv', $rep->slug) }}" class="figma-btn-secondary text-xs py-2 px-2.5" title="Descargar CSV">
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>CSV</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-figma-text-tertiary text-xs figma-panel">
                No hay reportes configurados actualmente en el catálogo.
            </div>
        @endforelse
    </div>
</div>

<script>
    function filtrarCategoria(categoria, clickedBtn) {
        document.querySelectorAll('.btn-categoria').forEach(btn => {
            btn.classList.remove('active');
        });
        if (clickedBtn) clickedBtn.classList.add('active');

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
