@extends('layouts.app')

@section('content')
<div class="max-w-[1240px] mx-auto px-2 space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                <span>⚙️ Panel de Gestión de la Aplicación</span>
                <span class="text-xs bg-amber-100 text-amber-900 font-extrabold px-2.5 py-0.5 rounded-full border border-amber-300">
                    Rol: app_admin
                </span>
            </h1>
            <p class="text-sm text-gray-600 mt-0.5">
                Configuración institucional, carreras universitarias y administración del contenido dinámico de la landing page.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ url('/') }}" target="_blank" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-3.5 py-2 rounded-xl text-xs shadow-sm transition-all">
                <span>🌐 Ver Landing Page</span>
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
                <span class="p-2.5 bg-amber-100 text-amber-900 rounded-xl font-bold text-sm">🎓 Carreras</span>
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
                <span class="p-2.5 bg-sky-100 text-sky-900 rounded-xl font-bold text-sm">🏢 Espacios</span>
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
                <span class="p-2.5 bg-purple-100 text-purple-900 rounded-xl font-bold text-sm">👥 Personal</span>
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
                <span class="p-2.5 bg-rose-100 text-rose-900 rounded-xl font-bold text-sm">📢 Feed</span>
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
                    <span>📸 Últimas Publicaciones en el Feed</span>
                </h2>
                <a href="{{ route('app_admin.actividades') }}" class="text-xs text-amber-800 font-bold hover:underline">Ver todas →</a>
            </div>

            @forelse($ultimasActividades as $act)
                <div class="py-3 border-b border-gray-100 last:border-none flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-900 flex items-center justify-center font-bold text-sm flex-shrink-0">
                        📢
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
                    <span>🏛️ Autoridades y Responsables LIA</span>
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
