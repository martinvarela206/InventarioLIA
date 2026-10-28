@extends('layouts.app')

@section('content')
<div class="max-w-[1320px] mx-auto px-2 space-y-5">
    <!-- Header Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-figma-border-subtle">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-figma-text-tertiary uppercase tracking-wider mb-0.5">
                <a href="{{ route('dashboard.index') }}" class="hover:text-figma-brand transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-figma-text-primary">Administración General</span>
            </div>
            <h1 class="text-xl font-bold text-figma-text-primary flex items-center gap-2">
                <svg class="w-5 h-5 text-figma-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Gestión del LIA y Contenidos de Plataforma</span>
            </h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ url('/') }}" target="_blank" class="figma-btn-secondary text-xs">
                <svg class="w-3.5 h-3.5 text-figma-text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Ver Portada LIA</span>
            </a>
        </div>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="figma-card p-3 border-emerald-500/40 text-emerald-400 flex items-center gap-2 text-xs font-semibold">
            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- KPI / Accesos Rápidos (Figma Artboards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-6 gap-3">
        <!-- Carreras -->
        <a href="{{ route('app_admin.carreras') }}" class="figma-card p-4 block group hover:-translate-y-0.5">
            <div class="flex justify-between items-start mb-2">
                <span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-figma-bg-deep text-figma-brand border border-figma-border-subtle">
                    Carreras
                </span>
                <span class="text-2xl font-bold font-mono text-figma-text-primary">{{ $carrerasActivas }}</span>
            </div>
            <h3 class="font-bold text-figma-text-primary text-xs group-hover:text-figma-brand transition-colors">Carreras Universitarias</h3>
            <p class="text-[11px] text-figma-text-tertiary mt-1">Oferta académica para perfiles con trazabilidad.</p>
            <div class="mt-3 pt-2 border-t border-figma-border-subtle text-[11px] text-figma-text-secondary group-hover:text-figma-brand font-medium flex items-center justify-between">
                <span>Gestionar</span>
                <span>→</span>
            </div>
        </a>

        <!-- Cargos del Laboratorio -->
        <a href="{{ route('app_admin.cargos') }}" class="figma-card p-4 block group hover:-translate-y-0.5">
            <div class="flex justify-between items-start mb-2">
                <span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-figma-bg-deep text-indigo-400 border border-figma-border-subtle">
                    Cargos
                </span>
                <span class="text-2xl font-bold font-mono text-indigo-400">{{ $cargosActivos }} <span class="text-xs font-normal text-figma-text-tertiary">/ {{ $totalCargos }}</span></span>
            </div>
            <h3 class="font-bold text-figma-text-primary text-xs group-hover:text-figma-brand transition-colors">Cargos en Laboratorio</h3>
            <p class="text-[11px] text-figma-text-tertiary mt-1">Estructura funcional de colaboradores.</p>
            <div class="mt-3 pt-2 border-t border-figma-border-subtle text-[11px] text-figma-text-secondary group-hover:text-figma-brand font-medium flex items-center justify-between">
                <span>Gestionar</span>
                <span>→</span>
            </div>
        </a>

        <!-- Espacios -->
        <a href="{{ route('app_admin.espacios') }}" class="figma-card p-4 block group hover:-translate-y-0.5">
            <div class="flex justify-between items-start mb-2">
                <span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-figma-bg-deep text-sky-400 border border-figma-border-subtle">
                    Espacios
                </span>
                <span class="text-2xl font-bold font-mono text-sky-400">{{ $totalEspacios }}</span>
            </div>
            <h3 class="font-bold text-figma-text-primary text-xs group-hover:text-figma-brand transition-colors">Salas del LIA</h3>
            <p class="text-[11px] text-figma-text-tertiary mt-1">Fotografías y descripciones para la portada.</p>
            <div class="mt-3 pt-2 border-t border-figma-border-subtle text-[11px] text-figma-text-secondary group-hover:text-figma-brand font-medium flex items-center justify-between">
                <span>Gestionar</span>
                <span>→</span>
            </div>
        </a>

        <!-- Personal -->
        <a href="{{ route('app_admin.personal') }}" class="figma-card p-4 block group hover:-translate-y-0.5">
            <div class="flex justify-between items-start mb-2">
                <span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-figma-bg-deep text-purple-400 border border-figma-border-subtle">
                    Personal
                </span>
                <span class="text-2xl font-bold font-mono text-purple-400">{{ $totalPersonal }}</span>
            </div>
            <h3 class="font-bold text-figma-text-primary text-xs group-hover:text-figma-brand transition-colors">Figuras Institucionales</h3>
            <p class="text-[11px] text-figma-text-tertiary mt-1">Directores, coordinadores y autoridades.</p>
            <div class="mt-3 pt-2 border-t border-figma-border-subtle text-[11px] text-figma-text-secondary group-hover:text-figma-brand font-medium flex items-center justify-between">
                <span>Gestionar</span>
                <span>→</span>
            </div>
        </a>

        <!-- Feed / Actividades -->
        <a href="{{ route('app_admin.actividades') }}" class="figma-card p-4 block group hover:-translate-y-0.5">
            <div class="flex justify-between items-start mb-2">
                <span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-figma-bg-deep text-rose-400 border border-figma-border-subtle">
                    Feed
                </span>
                <span class="text-2xl font-bold font-mono text-rose-400">{{ $totalActividades }}</span>
            </div>
            <h3 class="font-bold text-figma-text-primary text-xs group-hover:text-figma-brand transition-colors">Actividades y Novedades</h3>
            <p class="text-[11px] text-figma-text-tertiary mt-1">Eventos, cursos y anuncios oficiales.</p>
            <div class="mt-3 pt-2 border-t border-figma-border-subtle text-[11px] text-figma-text-secondary group-hover:text-figma-brand font-medium flex items-center justify-between">
                <span>Gestionar</span>
                <span>→</span>
            </div>
        </a>

        <!-- Reportes -->
        <a href="{{ route('app_admin.reportes') }}" class="figma-card p-4 block group hover:-translate-y-0.5">
            <div class="flex justify-between items-start mb-2">
                <span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-figma-bg-deep text-emerald-400 border border-figma-border-subtle">
                    Reportes
                </span>
                <span class="text-2xl font-bold font-mono text-emerald-400">{{ $reportesActivos }} <span class="text-xs font-normal text-figma-text-tertiary">/ {{ $totalReportes }}</span></span>
            </div>
            <h3 class="font-bold text-figma-text-primary text-xs group-hover:text-figma-brand transition-colors">Catálogo de Reportes</h3>
            <p class="text-[11px] text-figma-text-tertiary mt-1">Parámetros de auditoría y reportes guardados.</p>
            <div class="mt-3 pt-2 border-t border-figma-border-subtle text-[11px] text-figma-text-secondary group-hover:text-figma-brand font-medium flex items-center justify-between">
                <span>Configurar</span>
                <span>→</span>
            </div>
        </a>
    </div>

    <!-- Secciones de Vista Previa -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Feed de Actividades Recientes -->
        <div class="figma-panel p-4 sm:p-5 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-figma-border-subtle">
                <h2 class="font-bold text-figma-text-primary text-xs uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-figma-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Últimas Publicaciones</span>
                </h2>
                <a href="{{ route('app_admin.actividades') }}" class="text-xs text-figma-text-secondary hover:text-figma-brand font-medium">Ver todas →</a>
            </div>

            @forelse($ultimasActividades as $act)
                <div class="py-2.5 border-b border-figma-border-subtle last:border-none flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-figma-bg-deep border border-figma-border-subtle text-figma-text-secondary flex items-center justify-center font-bold text-xs flex-shrink-0">
                        📰
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-figma-text-primary text-xs truncate">{{ $act->titulo }}</h4>
                        <p class="text-[11px] text-figma-text-tertiary line-clamp-1 mt-0.5">{{ $act->contenido }}</p>
                        <span class="font-mono text-[10px] text-figma-text-dim block mt-0.5">{{ $act->fecha_publicacion?->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            @empty
                <p class="text-xs text-figma-text-tertiary italic py-3">No hay publicaciones registradas aún.</p>
            @endforelse
        </div>

        <!-- Figuras Institucionales Destacadas -->
        <div class="figma-panel p-4 sm:p-5 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-figma-border-subtle">
                <h2 class="font-bold text-figma-text-primary text-xs uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                    <span>Autoridades y Responsables LIA</span>
                </h2>
                <a href="{{ route('app_admin.personal') }}" class="text-xs text-figma-text-secondary hover:text-figma-brand font-medium">Gestionar →</a>
            </div>

            @forelse($personalDestacado as $p)
                <div class="py-2.5 border-b border-figma-border-subtle last:border-none flex items-center justify-between">
                    <div>
                        <span class="font-bold text-figma-text-primary text-xs block">{{ $p->nombre }}</span>
                        <span class="text-[11px] text-figma-text-secondary">{{ $p->cargo }}</span>
                    </div>
                    <span class="text-[10px] bg-figma-bg-deep border border-figma-border-subtle text-figma-text-secondary px-2 py-0.5 rounded font-mono">Orden: {{ $p->orden }}</span>
                </div>
            @empty
                <p class="text-xs text-figma-text-tertiary italic py-3">No hay autoridades configuradas.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
