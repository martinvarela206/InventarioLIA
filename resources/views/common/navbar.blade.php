<header style="background:#fbc101;" class="w-full shadow-[0_2px_8px_rgba(0,0,0,0.15)]">
    <div class="mx-auto max-w-[1196px] w-full px-6 lg:px-16 py-3">
        <div class="flex justify-between items-center">
            <a href="{{ url('/') }}" class="flex items-center gap-3 hover:text-[#111]">
                @include('common.logo', ['variant' => 'dark', 'class' => 'w-10 h-10'])
                <span class="text-2xl font-bold text-[#111]">Sistema LIA</span>
            </a>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="lg:hidden p-2 text-[#111] hover:bg-amber-400 rounded transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center gap-8">
                @if(Request::is('/'))
                    {{-- Welcome Navbar --}}
                    <nav class="flex items-center gap-8">
                        <a href="#inicio" class="text-[#111] font-medium border-b-2 border-transparent hover:border-[#111] transition-colors">Inicio</a>
                        <a href="#sistema-lia" class="text-[#111] font-medium border-b-2 border-transparent hover:border-[#111] transition-colors">Sistema LIA</a>
                        <a href="#lia1" class="text-[#111] font-medium border-b-2 border-transparent hover:border-[#111] transition-colors">LIA</a>
                        <a href="#lia2" class="text-[#111] font-medium border-b-2 border-transparent hover:border-[#111] transition-colors">Personal</a>
                    </nav>
                @else
                    {{-- App Navbar --}}
                    <nav class="flex items-center gap-2">
                        @auth
                            @if(Auth::user()->hasRole('revisor'))
                                <a href="{{ route('dashboard.index') }}" class="text-[#111] font-medium border-b-2 border-transparent hover:border-[#111] transition-colors px-2 py-1">Dashboard</a>
                            @endif
                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('users.index') }}" class="text-[#111] font-medium border-b-2 border-transparent hover:border-[#111] transition-colors px-2 py-1">Usuarios</a>
                            @endif
                            @if(Auth::user()->hasRole('revisor'))
                                <a href="{{ route('revision.index') }}" class="text-[#111] font-medium border-b-2 border-transparent hover:border-[#111] transition-colors px-2 py-1">Revisión</a>
                            @endif
                            @if(Auth::user()->hasRole('tecnico'))
                                <a href="{{ route('elementos.index') }}" class="text-[#111] font-medium border-b-2 border-transparent hover:border-[#111] transition-colors px-2 py-1">Elementos</a>
                            @endif
                            @if(Auth::user()->hasRole('coordinador'))
                                <a href="{{ route('movimientos.index') }}" class="text-[#111] font-medium border-b-2 border-transparent hover:border-[#111] transition-colors px-2 py-1">Movimientos</a>
                            @endif
                        @endauth
                    </nav>

                    <div class="flex items-center gap-4">
                        @auth
                            <a href="{{ route('profile.edit') }}" class="text-[#111] font-semibold tracking-wide px-4 py-2 rounded-lg transition-all hover:bg-white/50 hover:shadow-sm">Bienvenido, {{ Auth::user()->nombre }}</a>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="flex items-center justify-center w-10 h-10 bg-[#c62828] rounded-full hover:bg-[#8e1c1c] transition-colors" title="Cerrar sesión">
                                    <img src="/assets/logout.svg" alt="Cerrar sesión" class="w-[22px] h-[22px] invert brightness-0" />
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="text-[#111] font-medium hover:underline">Iniciar sesión</a>
                        @endauth
                    </div>
                @endif
            </div>
        </div>

        <!-- Mobile Menu (Hidden by default) -->
        <div id="mobile-menu" class="hidden lg:hidden flex flex-col gap-4 mt-4 pb-4 border-t border-amber-600 pt-4">
            @if(Request::is('/'))
                <a href="#inicio" class="text-[#111] font-medium hover:text-white transition-colors">Inicio</a>
                <a href="#sistema-lia" class="text-[#111] font-medium hover:text-white transition-colors">Sistema LIA</a>
                <a href="#lia1" class="text-[#111] font-medium hover:text-white transition-colors">LIA</a>
                <a href="#lia2" class="text-[#111] font-medium hover:text-white transition-colors">Personal</a>
            @else
                @auth
                    @if(Auth::user()->hasRole('revisor'))
                        <a href="{{ route('dashboard.index') }}" class="text-[#111] font-medium hover:text-white transition-colors">Dashboard</a>
                    @endif
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('users.index') }}" class="text-[#111] font-medium hover:text-white transition-colors">Usuarios</a>
                    @endif
                    @if(Auth::user()->hasRole('revisor'))
                        <a href="{{ route('revision.index') }}" class="text-[#111] font-medium hover:text-white transition-colors">Revisión</a>
                    @endif
                    @if(Auth::user()->hasRole('tecnico'))
                        <a href="{{ route('elementos.index') }}" class="text-[#111] font-medium hover:text-white transition-colors">Elementos</a>
                    @endif
                    @if(Auth::user()->hasRole('coordinador'))
                        <a href="{{ route('movimientos.index') }}" class="text-[#111] font-medium hover:text-white transition-colors">Movimientos</a>
                    @endif

                    <div class="h-px bg-amber-600 my-2"></div>
                    
                    <a href="{{ route('profile.edit') }}" class="text-[#111] font-semibold tracking-wide px-4 py-2 rounded-lg transition-all hover:bg-white/50 hover:shadow-sm inline-block w-fit">Perfil ({{ Auth::user()->nombre }})</a>
                    <form action="{{ route('logout') }}" method="POST" class="inline-block block mt-2">
                        @csrf
                        <button type="submit" class="text-[#c62828] font-bold hover:text-[#8e1c1c] transition-colors">Cerrar Sesión</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-[#111] font-medium hover:underline">Iniciar sesión</a>
                @endauth
            @endif
        </div>
    </div>
</header>

<script>
    document.getElementById('mobile-menu-btn').addEventListener('click', function() {
        document.getElementById('mobile-menu').classList.toggle('hidden');
    });
</script>
