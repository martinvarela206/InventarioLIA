@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2.5">
                <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Gestión de la Aplicación</span>
            </h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ url('/') }}" target="_blank" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm transition-all">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Ver Landing Page</span>
            </a>
        </div>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-300 text-emerald-900 text-sm font-semibold px-4 py-3 rounded-xl shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- KPI / Accesos Rápidos -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Carreras -->
        <a href="{{ route('app_admin.carreras') }}" class="bg-white rounded-2xl p-5 border border-gray-200 hover:border-amber-400 hover:shadow-md transition-all group block">
            <div class="flex justify-between items-start mb-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-100 text-amber-900 rounded-xl font-bold text-xs">
                    <svg class="w-4 h-4 text-amber-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                    Carreras
                </span>
                <span class="text-2xl font-black text-gray-900">{{ $carrerasActivas }}</span>
            </div>
            <h3 class="font-bold text-gray-900 text-sm group-hover:text-amber-800 transition-colors">Carreras Universitarias</h3>
            <p class="text-xs text-gray-500 mt-1">Oferta activa para registro con preservación histórica.</p>
            <div class="mt-4 pt-2 border-t border-gray-100 text-xs text-amber-700 font-bold flex items-center justify-between">
                <span>Gestionar lista</span>
                <span>→</span>
            </div>
        </a>

        <!-- Espacios -->
        <a href="{{ route('app_admin.espacios') }}" class="bg-white rounded-2xl p-5 border border-gray-200 hover:border-amber-400 hover:shadow-md transition-all group block">
            <div class="flex justify-between items-start mb-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-100 text-sky-900 rounded-xl font-bold text-xs">
                    <svg class="w-4 h-4 text-sky-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Espacios
                </span>
                <span class="text-2xl font-black text-gray-900">{{ $totalEspacios }}</span>
            </div>
            <h3 class="font-bold text-gray-900 text-sm group-hover:text-amber-800 transition-colors">Laboratorios del LIA</h3>
            <p class="text-xs text-gray-500 mt-1">Fotos, títulos y descripciones de las salas físicas.</p>
            <div class="mt-4 pt-2 border-t border-gray-100 text-xs text-amber-700 font-bold flex items-center justify-between">
                <span>Subir fotos y salas</span>
                <span>→</span>
            </div>
        </a>

        <!-- Personal -->
        <a href="{{ route('app_admin.personal') }}" class="bg-white rounded-2xl p-5 border border-gray-200 hover:border-amber-400 hover:shadow-md transition-all group block">
            <div class="flex justify-between items-start mb-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-100 text-purple-900 rounded-xl font-bold text-xs">
                    <svg class="w-4 h-4 text-purple-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Personal
                </span>
                <span class="text-2xl font-black text-gray-900">{{ $totalPersonal }}</span>
            </div>
            <h3 class="font-bold text-gray-900 text-sm group-hover:text-amber-800 transition-colors">Figuras Institucionales</h3>
            <p class="text-xs text-gray-500 mt-1">Director/a, coordinadores y seguridad del LIA.</p>
            <div class="mt-4 pt-2 border-t border-gray-100 text-xs text-amber-700 font-bold flex items-center justify-between">
                <span>Gestionar equipo</span>
                <span>→</span>
            </div>
        </a>

        <!-- Actividades -->
        <a href="{{ route('app_admin.actividades') }}" class="bg-white rounded-2xl p-5 border border-gray-200 hover:border-amber-400 hover:shadow-md transition-all group block">
            <div class="flex justify-between items-start mb-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-100 text-rose-900 rounded-xl font-bold text-xs">
                    <svg class="w-4 h-4 text-rose-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    Feed
                </span>
                <span class="text-2xl font-black text-gray-900">{{ $totalActividades }}</span>
            </div>
            <h3 class="font-bold text-gray-900 text-sm group-hover:text-amber-800 transition-colors">Actividades y Novedades</h3>
            <p class="text-xs text-gray-500 mt-1">Posteos estilo Instagram, eventos y anuncios.</p>
            <div class="mt-4 pt-2 border-t border-gray-100 text-xs text-amber-700 font-bold flex items-center justify-between">
                <span>Crear publicación</span>
                <span>→</span>
            </div>
        </a>
    </div>

    <!-- Secciones de Vista Previa -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Feed de Actividades Recientes -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-900 text-base flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Últimas Publicaciones en el Feed</span>
                </h2>
                <a href="{{ route('app_admin.actividades') }}" class="text-xs text-amber-800 font-bold hover:underline">Ver todas →</a>
            </div>

            @forelse($ultimasActividades as $act)
                <div class="py-3 border-b border-gray-100 last:border-none flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-900 flex items-center justify-center font-bold text-sm flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-gray-900 text-sm truncate">{{ $act->titulo }}</h4>
                        <p class="text-xs text-gray-600 line-clamp-2 mt-0.5">{{ $act->contenido }}</p>
                        <span class="text-[10px] text-gray-400 block mt-1">{{ $act->fecha_publicacion?->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            @empty
                <p class="text-xs text-gray-400 italic py-4">No hay publicaciones registradas aún.</p>
            @endforelse
        </div>

        <!-- Figuras Institucionales Destacadas -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-gray-900 text-base flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                    <span>Autoridades y Responsables LIA</span>
                </h2>
                <a href="{{ route('app_admin.personal') }}" class="text-xs text-amber-800 font-bold hover:underline">Gestionar →</a>
            </div>

            @forelse($personalDestacado as $p)
                <div class="py-2.5 border-b border-gray-100 last:border-none flex items-center justify-between">
                    <div>
                        <span class="font-bold text-gray-900 text-sm block">{{ $p->nombre }}</span>
                        <span class="text-xs text-amber-900 font-semibold">{{ $p->cargo }}</span>
                    </div>
                    <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded font-mono">Orden: {{ $p->orden }}</span>
                </div>
            @empty
                <p class="text-xs text-gray-400 italic py-4">No hay autoridades configuradas.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
