<header style="background:#fbc101;" class="w-full shadow-[0_2px_8px_rgba(0,0,0,0.15)] sticky top-0 z-50">
    <div class="mx-auto max-w-[1240px] w-full px-4 lg:px-8 py-3">
        <div class="flex justify-between items-center">
            <a href="{{ url('/') }}" class="flex items-center gap-3 hover:text-[#111]">
                @include('common.logo', ['variant' => 'dark', 'class' => 'w-10 h-10'])
                <span class="text-2xl font-bold text-[#111] tracking-tight">Nexo LIA</span>
            </a>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="lg:hidden p-2 text-[#111] hover:bg-amber-400 rounded transition-colors" aria-label="Abrir menú">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-7 h-7">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center gap-6">
                @if(Request::is('/'))
                    <nav class="flex items-center gap-6">
                        <a href="#inicio" class="text-[#111] font-semibold text-sm border-b-2 border-transparent hover:border-[#111] transition-colors">Inicio</a>
                        <a href="#sistema-lia" class="text-[#111] font-semibold text-sm border-b-2 border-transparent hover:border-[#111] transition-colors">Funcionalidades</a>
                        <a href="#espacios-lia" class="text-[#111] font-semibold text-sm border-b-2 border-transparent hover:border-[#111] transition-colors">Espacios</a>
                        <a href="#personal-lia" class="text-[#111] font-semibold text-sm border-b-2 border-transparent hover:border-[#111] transition-colors">Personal</a>
                        <a href="#actividades-lia" class="text-[#111] font-semibold text-sm border-b-2 border-transparent hover:border-[#111] transition-colors">Actividades</a>
                    </nav>

                    <div class="flex items-center gap-3 pl-4 border-l border-amber-600/40">
                        @auth
                            <a href="{{ Auth::user()->hasActivePermissions() ? route('dashboard.index') : route('bienvenida') }}" class="text-[#111] font-bold text-sm px-4 py-2 rounded-xl bg-black/10 hover:bg-black/15 transition-all">
                                Ir al Panel
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-[#111] hover:text-black font-bold text-sm px-3.5 py-2 rounded-xl hover:bg-black/10 transition-all">
                                Iniciar Sesión
                            </a>
                            <a href="{{ route('register') }}" class="bg-[#111] text-[#fbc101] hover:bg-black/85 font-extrabold text-sm px-4 py-2 rounded-xl transition-all shadow-sm border border-black/20">
                                Registrarse
                            </a>
                        @endauth
                    </div>
                @else
                    <nav class="flex items-center gap-2">
                        @auth
                            @if(Auth::user()->hasActivePermissions())
                                <a href="{{ route('dashboard.index') }}" class="text-[#111] font-semibold px-3 py-1.5 rounded-lg hover:bg-amber-400/80 transition-colors {{ Request::routeIs('dashboard.*') ? 'bg-amber-400/90 shadow-sm font-bold' : '' }}">Dashboard</a>
                                <a href="{{ route('elementos.index') }}" class="text-[#111] font-semibold px-3 py-1.5 rounded-lg hover:bg-amber-400/80 transition-colors {{ Request::routeIs('elementos.*') ? 'bg-amber-400/90 shadow-sm font-bold' : '' }}">Inventario</a>
                                @if(Auth::user()->canManageMovimientos())
                                    <a href="{{ route('movimientos.rapido') }}" class="text-[#111] font-semibold px-3 py-1.5 rounded-lg bg-amber-500/50 hover:bg-amber-500/80 transition-colors flex items-center gap-1.5 {{ Request::routeIs('movimientos.rapido') ? 'ring-2 ring-amber-800 font-bold' : '' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        Movimiento Rápido
                                    </a>
                                @endif
                                @if(Auth::user()->hasRole('rev'))
                                    <a href="{{ route('reportes.index') }}" class="text-[#111] font-semibold px-3 py-1.5 rounded-lg hover:bg-amber-400/80 transition-colors {{ Request::routeIs('reportes.*') ? 'bg-amber-400/90 shadow-sm font-bold' : '' }}">Reportes</a>
                                @endif
                                @if(Auth::user()->hasRole('item_admin'))
                                    <a href="{{ route('tipos.index') }}" class="text-[#111] font-semibold px-3 py-1.5 rounded-lg hover:bg-amber-400/80 transition-colors {{ Request::routeIs('tipos.*') ? 'bg-amber-400/90 shadow-sm font-bold' : '' }}">Tipos</a>
                                    <a href="{{ route('ubicaciones.index') }}" class="text-[#111] font-semibold px-3 py-1.5 rounded-lg hover:bg-amber-400/80 transition-colors {{ Request::routeIs('ubicaciones.*') ? 'bg-amber-400/90 shadow-sm font-bold' : '' }}">Salas</a>
                                @endif
                                @if(Auth::user()->isAppAdmin())
                                    <a href="{{ route('app_admin.index') }}" class="text-[#111] font-semibold px-3 py-1.5 rounded-lg bg-amber-500/30 hover:bg-amber-500/60 transition-colors flex items-center gap-1.5 {{ Request::routeIs('app_admin.*') ? 'bg-amber-500 shadow-sm font-bold' : '' }}">
                                        <svg class="w-4 h-4 text-[#111]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Gestión App
                                    </a>
                                @endif
                                @if(Auth::user()->isAdmin())
                                    <a href="{{ route('users.index') }}" class="text-[#111] font-semibold px-3 py-1.5 rounded-lg hover:bg-amber-400/80 transition-colors {{ Request::routeIs('users.*') ? 'bg-amber-400/90 shadow-sm font-bold' : '' }}">Usuarios</a>
                                @endif
                            @endif
                        @endauth
                    </nav>

                    <div class="flex items-center gap-3 pl-4 border-l border-amber-500/40">
                        @auth
                            @if(Auth::user()->hasRole('registered'))
                                <a href="{{ route('profile.edit') }}" class="text-[#111] font-medium text-sm px-3 py-1.5 rounded-lg bg-black/5 hover:bg-black/10 transition-all flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                    {{ Auth::user()->nombre }}
                                </a>
                            @else
                                <span class="text-[#111] font-medium text-sm px-3 py-1.5 rounded-lg bg-black/5 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-gray-600"></span>
                                    {{ Auth::user()->nombre }}
                                </span>
                            @endif
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="flex items-center justify-center w-9 h-9 bg-[#c62828] text-white rounded-full hover:bg-[#8e1c1c] transition-colors shadow-sm" title="Cerrar sesión">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="text-[#111] hover:text-black font-bold text-sm px-3.5 py-2 rounded-xl hover:bg-black/10 transition-all">Iniciar Sesión</a>
                            <a href="{{ route('register') }}" class="bg-[#111] text-[#fbc101] hover:bg-black/85 font-extrabold text-sm px-4 py-2 rounded-xl transition-all shadow-sm border border-black/20">Registrarse</a>
                        @endauth
                    </div>
                @endif
            </div>
        </div>

        <!-- Mobile Menu (Hidden by default) -->
        <div id="mobile-menu" class="hidden lg:hidden flex flex-col gap-2 mt-4 pb-2 border-t border-amber-600/30 pt-3">
            @if(Request::is('/'))
                <a href="#inicio" class="text-[#111] font-semibold py-1.5 px-3 rounded-lg hover:bg-amber-400">Inicio</a>
                <a href="#sistema-lia" class="text-[#111] font-semibold py-1.5 px-3 rounded-lg hover:bg-amber-400">Funcionalidades</a>
                <a href="#espacios-lia" class="text-[#111] font-semibold py-1.5 px-3 rounded-lg hover:bg-amber-400">Espacios</a>
                <a href="#personal-lia" class="text-[#111] font-semibold py-1.5 px-3 rounded-lg hover:bg-amber-400">Personal</a>
                <a href="#actividades-lia" class="text-[#111] font-semibold py-1.5 px-3 rounded-lg hover:bg-amber-400">Actividades</a>
                <div class="h-px bg-amber-600/40 my-2"></div>
                @auth
                    <a href="{{ Auth::user()->hasActivePermissions() ? route('dashboard.index') : route('bienvenida') }}" class="text-[#111] font-bold py-2 px-3 rounded-xl bg-amber-400 text-center">Ir al Panel</a>
                @else
                    <div class="flex items-center gap-2 px-1 py-1">
                        <a href="{{ route('login') }}" class="text-[#111] font-bold py-2 px-3 rounded-xl bg-amber-400/80 hover:bg-amber-400 flex-1 text-center text-sm">Iniciar Sesión</a>
                        <a href="{{ route('register') }}" class="bg-[#111] text-[#fbc101] hover:bg-black/85 font-extrabold py-2 px-3 rounded-xl flex-1 text-center text-sm shadow-sm border border-black/20">Registrarse</a>
                    </div>
                @endauth
            @else
                @auth
                    @if(Auth::user()->hasActivePermissions())
                        <a href="{{ route('dashboard.index') }}" class="text-[#111] font-semibold py-2 px-3 rounded-lg hover:bg-amber-400">Dashboard</a>
                        <a href="{{ route('elementos.index') }}" class="text-[#111] font-semibold py-2 px-3 rounded-lg hover:bg-amber-400">Inventario Completo</a>
                        @if(Auth::user()->canManageMovimientos())
                            <a href="{{ route('movimientos.rapido') }}" class="text-[#111] font-semibold py-2 px-3 rounded-lg bg-amber-500/40 flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-950" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <span>Movimiento Rápido (Móvil)</span>
                            </a>
                        @endif
                        <a href="{{ route('movimientos.index') }}" class="text-[#111] font-semibold py-2 px-3 rounded-lg hover:bg-amber-400">Historial de Movimientos</a>
                        @if(Auth::user()->hasRole('rev'))
                            <a href="{{ route('reportes.index') }}" class="text-[#111] font-semibold py-2 px-3 rounded-lg hover:bg-amber-400">Reportes Oficiales</a>
                        @endif
                        @if(Auth::user()->hasRole('item_admin'))
                            <a href="{{ route('tipos.index') }}" class="text-[#111] font-semibold py-2 px-3 rounded-lg hover:bg-amber-400">Tipos de Hardware</a>
                            <a href="{{ route('ubicaciones.index') }}" class="text-[#111] font-semibold py-2 px-3 rounded-lg hover:bg-amber-400">Salas y Espacios</a>
                        @endif
                        @if(Auth::user()->isAppAdmin())
                            <a href="{{ route('app_admin.index') }}" class="text-[#111] font-semibold py-2 px-3 rounded-lg bg-amber-400/60 font-bold flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-950" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>Gestión App</span>
                            </a>
                        @endif
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('users.index') }}" class="text-[#111] font-semibold py-2 px-3 rounded-lg hover:bg-amber-400">Administración de Usuarios</a>
                        @endif
                    @endif
                    <div class="h-px bg-amber-600/40 my-2"></div>
                    <div class="flex items-center justify-between px-3 py-2">
                        @if(Auth::user()->hasRole('registered'))
                            <a href="{{ route('profile.edit') }}" class="font-medium text-sm text-[#111]">Perfil: {{ Auth::user()->nombre }}</a>
                        @else
                            <span class="font-medium text-sm text-[#111]">Perfil: {{ Auth::user()->nombre }}</span>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs bg-[#c62828] text-white px-3 py-1.5 rounded-lg font-bold">Cerrar Sesión</button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center gap-2 px-1 py-1">
                        <a href="{{ route('login') }}" class="text-[#111] font-bold py-2 px-3 rounded-xl bg-amber-400/80 hover:bg-amber-400 flex-1 text-center text-sm">Iniciar Sesión</a>
                        <a href="{{ route('register') }}" class="bg-[#111] text-[#fbc101] hover:bg-black/85 font-extrabold py-2 px-3 rounded-xl flex-1 text-center text-sm shadow-sm border border-black/20">Registrarse</a>
                    </div>
                @endauth
            @endif
        </div>
    </div>
</header>

<script>
    document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
        document.getElementById('mobile-menu')?.classList.toggle('hidden');
    });
</script>
