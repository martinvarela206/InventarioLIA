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
                    <nav class="flex items-center gap-8">
                        <a href="#inicio" class="text-[#111] font-medium border-b-2 border-transparent hover:border-[#111] transition-colors">Inicio</a>
                        <a href="#sistema-lia" class="text-[#111] font-medium border-b-2 border-transparent hover:border-[#111] transition-colors">Sistema LIA</a>
                        <a href="#lia1" class="text-[#111] font-medium border-b-2 border-transparent hover:border-[#111] transition-colors">LIA</a>
                        <a href="#lia2" class="text-[#111] font-medium border-b-2 border-transparent hover:border-[#111] transition-colors">Personal</a>
                    </nav>
                @else
                    <nav class="flex items-center gap-2">
                        @auth
                            <a href="{{ route('dashboard.index') }}" class="text-[#111] font-semibold px-3 py-1.5 rounded-md hover:bg-amber-400/80 transition-colors {{ Request::routeIs('dashboard.*') ? 'bg-amber-400/90 shadow-sm' : '' }}">Dashboard</a>
                            <a href="{{ route('elementos.index') }}" class="text-[#111] font-semibold px-3 py-1.5 rounded-md hover:bg-amber-400/80 transition-colors {{ Request::routeIs('elementos.*') ? 'bg-amber-400/90 shadow-sm' : '' }}">Inventario</a>
                            @if(Auth::user()->canManageMovimientos())
                                <a href="{{ route('movimientos.rapido') }}" class="text-[#111] font-semibold px-3 py-1.5 rounded-md bg-amber-500/50 hover:bg-amber-500/80 transition-colors flex items-center gap-1.5 {{ Request::routeIs('movimientos.rapido') ? 'ring-2 ring-amber-800' : '' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    Movimiento Rápido
                                </a>
                            @endif
                            <a href="{{ route('reportes.index') }}" class="text-[#111] font-semibold px-3 py-1.5 rounded-md hover:bg-amber-400/80 transition-colors {{ Request::routeIs('reportes.*') ? 'bg-amber-400/90 shadow-sm' : '' }}">Reportes</a>
                            @if(Auth::user()->hasRole('item_admin'))
                                <a href="{{ route('tipos.index') }}" class="text-[#111] font-semibold px-3 py-1.5 rounded-md hover:bg-amber-400/80 transition-colors {{ Request::routeIs('tipos.*') ? 'bg-amber-400/90 shadow-sm' : '' }}">Tipos</a>
                            @endif
                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('users.index') }}" class="text-[#111] font-semibold px-3 py-1.5 rounded-md hover:bg-amber-400/80 transition-colors {{ Request::routeIs('users.*') ? 'bg-amber-400/90 shadow-sm' : '' }}">Usuarios</a>
                            @endif
                        @endauth
                    </nav>

                    <div class="flex items-center gap-3 pl-4 border-l border-amber-500/40">
                        @auth
                            <a href="{{ route('profile.edit') }}" class="text-[#111] font-medium text-sm px-3 py-1.5 rounded-lg bg-black/5 hover:bg-black/10 transition-all flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                {{ Auth::user()->nombre }}
                            </a>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="flex items-center justify-center w-9 h-9 bg-[#c62828] text-white rounded-full hover:bg-[#8e1c1c] transition-colors shadow-sm" title="Cerrar sesión">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="text-[#111] font-semibold px-4 py-2 rounded-lg bg-amber-400 hover:bg-amber-300 transition-colors">Iniciar sesión</a>
                        @endauth
                    </div>
                @endif
            </div>
        </div>

        <!-- Mobile Menu (Hidden by default) -->
        <div id="mobile-menu" class="hidden lg:hidden flex flex-col gap-2 mt-4 pb-2 border-t border-amber-600/30 pt-3">
            @if(Request::is('/'))
                <a href="#inicio" class="text-[#111] font-medium py-1.5 px-3 rounded hover:bg-amber-400">Inicio</a>
                <a href="#sistema-lia" class="text-[#111] font-medium py-1.5 px-3 rounded hover:bg-amber-400">Sistema LIA</a>
                <a href="#lia1" class="text-[#111] font-medium py-1.5 px-3 rounded hover:bg-amber-400">LIA</a>
                <a href="#lia2" class="text-[#111] font-medium py-1.5 px-3 rounded hover:bg-amber-400">Personal</a>
            @else
                @auth
                    <a href="{{ route('dashboard.index') }}" class="text-[#111] font-semibold py-2 px-3 rounded hover:bg-amber-400">Dashboard</a>
                    <a href="{{ route('elementos.index') }}" class="text-[#111] font-semibold py-2 px-3 rounded hover:bg-amber-400">Inventario Completo</a>
                    @if(Auth::user()->canManageMovimientos())
                        <a href="{{ route('movimientos.rapido') }}" class="text-[#111] font-semibold py-2 px-3 rounded bg-amber-500/40">⚡ Movimiento Rápido (Móvil)</a>
                    @endif
                    <a href="{{ route('movimientos.index') }}" class="text-[#111] font-semibold py-2 px-3 rounded hover:bg-amber-400">Historial de Movimientos</a>
                    <a href="{{ route('reportes.index') }}" class="text-[#111] font-semibold py-2 px-3 rounded hover:bg-amber-400">Reportes Oficiales</a>
                    @if(Auth::user()->hasRole('item_admin'))
                        <a href="{{ route('tipos.index') }}" class="text-[#111] font-semibold py-2 px-3 rounded hover:bg-amber-400">Tipos de Hardware</a>
                    @endif
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('users.index') }}" class="text-[#111] font-semibold py-2 px-3 rounded hover:bg-amber-400">Administración de Usuarios</a>
                    @endif
                    <div class="h-px bg-amber-600/40 my-2"></div>
                    <div class="flex items-center justify-between px-3 py-2">
                        <a href="{{ route('profile.edit') }}" class="font-medium text-sm text-[#111]">Perfil: {{ Auth::user()->nombre }}</a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs bg-[#c62828] text-white px-3 py-1.5 rounded font-bold">Cerrar Sesión</button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-[#111] font-semibold py-2 px-3 rounded hover:bg-amber-400">Iniciar sesión</a>
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
