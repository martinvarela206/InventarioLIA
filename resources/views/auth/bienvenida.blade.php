@extends('layouts.app')

@section('content')
<div class="max-w-[760px] mx-auto py-8 px-4">
    <div class="figma-panel shadow-2xl overflow-hidden space-y-6">
        <!-- Banner superior con identidad institucional LIA -->
        <div class="p-8 text-center bg-figma-bg-deep border-b border-figma-border-subtle relative">
            <div class="inline-flex p-3 rounded-2xl mb-2 bg-figma-brand/15 text-figma-brand border border-figma-brand/30">
                <span class="font-mono font-bold text-xl">LIA</span>
            </div>
            <h1 class="text-2xl font-bold text-figma-text-primary tracking-tight">¡Bienvenido a Nexo LIA!</h1>
            <p class="text-xs text-figma-text-secondary mt-1">Plataforma de Control y Gestión Patrimonial del Laboratorio de Informática Aplicada</p>
        </div>

        <!-- Contenido principal -->
        <div class="p-6 sm:p-8 space-y-6">
            <!-- Mensaje de estado de la cuenta -->
            <div class="figma-card p-4 flex items-start gap-3.5 border-amber-500/30 bg-amber-500/5">
                <div class="p-2 bg-amber-500/15 text-amber-400 rounded-lg shrink-0 mt-0.5 border border-amber-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-figma-text-primary">Cuenta Registrada: Esperando Asignación de Permisos</h2>
                    <p class="text-xs text-figma-text-secondary mt-1 leading-relaxed">
                        Hola <strong class="text-figma-text-primary">{{ Auth::user()->nombre }}</strong>, tu cuenta ha sido creada y verificada exitosamente en el sistema. 
                        Actualmente te encuentras con el rol de <span class="bg-figma-bg-deep text-figma-text-primary px-1.5 py-0.5 rounded font-mono font-bold border border-figma-border-subtle">registered</span>. 
                        Para acceder a los módulos de inventario, auditoría o gestión operativa, un administrador del laboratorio debe asignarte los permisos correspondientes a tu rol (revisor, técnico o administrador).
                    </p>
                </div>
            </div>

            <!-- Panel Carrusel de Concientización y Buenas Prácticas Informáticas -->
            @if(isset($avisos) && $avisos->isNotEmpty())
                <div class="figma-panel p-5 space-y-3 border-figma-border-subtle">
                    <div class="flex items-center justify-between pb-2 border-b border-figma-border-subtle">
                        <div class="flex items-center gap-2">
                            <span class="p-1 bg-figma-brand/15 text-figma-brand rounded text-xs font-bold">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </span>
                            <span class="text-xs font-bold uppercase text-figma-brand tracking-wider">Cultura de Ciberseguridad LIA</span>
                        </div>
                        <span class="text-[11px] text-figma-text-tertiary font-mono">Buenas Prácticas</span>
                    </div>

                    <div id="carrusel-bienvenida" class="space-y-2">
                        @foreach($avisos as $i => $av)
                            <div class="aviso-bienvenida-item {{ $i === 0 ? 'block' : 'hidden' }}" data-idx="{{ $i }}">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase {{ $av->severidad === 'warning' ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : ($av->severidad === 'danger' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30') }}">
                                        {{ $av->severidad === 'tip' ? 'Consejo' : ($av->severidad === 'warning' ? 'Advertencia' : ($av->severidad === 'danger' ? 'Crítico' : 'Información')) }}
                                    </span>
                                    <h4 class="text-xs font-bold text-figma-text-primary">{{ $av->titulo }}</h4>
                                </div>
                                <p class="text-xs text-figma-text-secondary leading-relaxed">{{ $av->descripcion }}</p>
                            </div>
                        @endforeach
                    </div>

                    @if($avisos->count() > 1)
                        <div class="flex items-center justify-between pt-2 border-t border-figma-border-subtle text-[11px] text-figma-text-tertiary">
                            <div class="flex items-center gap-1">
                                @foreach($avisos as $i => $av)
                                    <span class="dot-bienvenida h-1 rounded-full transition-all {{ $i === 0 ? 'bg-figma-brand w-4' : 'bg-figma-border-subtle w-1.5' }}" data-idx="{{ $i }}"></span>
                                @endforeach
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" onclick="slideBienvenida(-1)" class="px-2 py-0.5 figma-btn-secondary text-xs">&larr;</button>
                                <button type="button" onclick="slideBienvenida(1)" class="px-2 py-0.5 figma-btn-secondary text-xs">&rarr;</button>
                            </div>
                        </div>
                    @endif
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const items = document.querySelectorAll('.aviso-bienvenida-item');
                    const dots = document.querySelectorAll('.dot-bienvenida');
                    if (items.length <= 1) return;
                    let current = 0;

                    window.slideBienvenida = function(dir) {
                        items[current].classList.remove('block');
                        items[current].classList.add('hidden');
                        if (dots[current]) {
                            dots[current].className = 'dot-bienvenida h-1 rounded-full transition-all bg-figma-border-subtle w-1.5';
                        }

                        current = (current + dir + items.length) % items.length;

                        items[current].classList.remove('hidden');
                        items[current].classList.add('block');
                        if (dots[current]) {
                            dots[current].className = 'dot-bienvenida h-1 rounded-full transition-all bg-figma-brand w-4';
                        }
                    };

                    setInterval(() => window.slideBienvenida(1), 7000);
                });
                </script>
            @endif

            <!-- Resumen de datos del usuario -->
            <div class="figma-panel p-5 space-y-3">
                <h3 class="text-[10px] font-bold uppercase tracking-wider text-figma-text-tertiary border-b border-figma-border-subtle pb-2">Información de tu Usuario</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <span class="text-figma-text-tertiary text-[11px] block">Nombre Completo:</span>
                        <span class="font-bold text-figma-text-primary text-xs block mt-0.5">{{ Auth::user()->nombre }}</span>
                    </div>
                    <div>
                        <span class="text-figma-text-tertiary text-[11px] block">Correo Electrónico:</span>
                        <span class="font-mono text-figma-text-primary text-xs block mt-0.5">{{ Auth::user()->email }}</span>
                    </div>
                    <div>
                        <span class="text-figma-text-tertiary text-[11px] block">DNI / Documento:</span>
                        <span class="font-mono text-figma-text-secondary block mt-0.5">{{ Auth::user()->dni ?: 'No cargado' }}</span>
                    </div>
                    <div>
                        <span class="text-figma-text-tertiary text-[11px] block">Carrera / Área:</span>
                        <span class="text-figma-text-secondary block mt-0.5">{{ Auth::user()->carrera ?: 'No especificada' }}</span>
                    </div>
                    <div>
                        <span class="text-figma-text-tertiary text-[11px] block">Fecha de Registro:</span>
                        <span class="font-mono text-figma-text-secondary block mt-0.5">{{ Auth::user()->created_at ? Auth::user()->created_at->format('d/m/Y H:i') : date('d/m/Y') }}</span>
                    </div>
                    <div>
                        <span class="text-figma-text-tertiary text-[11px] block">Estado del Acceso:</span>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                            Pendiente de Autorización
                        </span>
                    </div>
                </div>
            </div>

            <!-- Acciones permitidas -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-figma-border-subtle">
                <a href="{{ route('profile.edit') }}" class="figma-btn-primary w-full sm:w-auto">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Completar / Editar mi Perfil</span>
                </a>

                <form action="{{ route('logout') }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" class="figma-btn-secondary w-full sm:w-auto">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
