<header style="background:#fbc101;" class="w-full shadow-[0_2px_8px_rgba(0,0,0,0.12)] sticky top-0 z-50">
    <div class="mx-auto max-w-[1360px] w-full px-4 lg:px-6 py-2.5">
        <div class="flex justify-between items-center gap-3">
            <!-- Brand Logo -->
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 hover:opacity-90 transition-opacity shrink-0">
                @include('common.logo', ['variant' => 'dark', 'class' => 'w-9 h-9'])
                <span class="text-xl font-black text-black tracking-tight">Nexo LIA</span>
            </a>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="lg:hidden p-2 text-black hover:bg-black/10 rounded-xl transition-colors" aria-label="Abrir menú">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center justify-between flex-1 gap-2 xl:gap-4 ml-2 xl:ml-6">
                @if(Request::is('/'))
                    <nav class="flex items-center gap-5 xl:gap-6">
                        <a href="#inicio" class="text-black/80 hover:text-black font-bold text-sm border-b-2 border-transparent hover:border-black transition-colors">Inicio</a>
                        <a href="#sistema-lia" class="text-black/80 hover:text-black font-bold text-sm border-b-2 border-transparent hover:border-black transition-colors">Funcionalidades</a>
                        <a href="#espacios-lia" class="text-black/80 hover:text-black font-bold text-sm border-b-2 border-transparent hover:border-black transition-colors">Espacios</a>
                        <a href="#personal-lia" class="text-black/80 hover:text-black font-bold text-sm border-b-2 border-transparent hover:border-black transition-colors">Personal</a>
                        <a href="#actividades-lia" class="text-black/80 hover:text-black font-bold text-sm border-b-2 border-transparent hover:border-black transition-colors">Actividades</a>
                    </nav>

                    <div class="flex items-center gap-3 pl-4 border-l border-black/15 shrink-0">
                        @auth
                            <a href="{{ Auth::user()->hasActivePermissions() ? route('dashboard.index') : route('bienvenida') }}" class="text-black font-extrabold text-sm px-4 py-2 rounded-xl bg-black/10 hover:bg-black/15 transition-all">
                                Ir al Panel
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-black hover:bg-black/10 font-bold text-sm px-3.5 py-2 rounded-xl transition-all">
                                Iniciar Sesión
                            </a>
                            <a href="{{ route('register') }}" class="bg-black text-[#fbc101] hover:bg-black/85 font-extrabold text-sm px-4 py-2 rounded-xl transition-all shadow-sm">
                                Registrarse
                            </a>
                        @endauth
                    </div>
                @else
                    @php
                        $navItemClass = function($isActive) {
                            return $isActive 
                                ? 'bg-black text-[#fbc101] font-black text-xs xl:text-sm px-3 py-2 rounded-xl shadow-sm inline-flex items-center gap-1.5 whitespace-nowrap shrink-0 transition-all'
                                : 'text-black hover:text-black hover:bg-black/10 font-bold text-xs xl:text-sm px-2.5 py-2 rounded-xl inline-flex items-center gap-1.5 whitespace-nowrap shrink-0 transition-all';
                        };
                    @endphp

                    <nav class="flex items-center gap-1 xl:gap-1.5 py-1">
                        @auth
                            @if(Auth::user()->hasActivePermissions())
                                <a href="{{ route('dashboard.index') }}" class="{{ $navItemClass(Request::routeIs('dashboard.*')) }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                    </svg>
                                    <span>Dashboard</span>
                                </a>

                                <a href="{{ route('elementos.index') }}" class="{{ $navItemClass(Request::routeIs('elementos.*')) }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                    <span>Inventario</span>
                                </a>

                                @if(Auth::user()->canManageMovimientos())
                                    <a href="{{ route('movimientos.rapido') }}" class="{{ $navItemClass(Request::routeIs('movimientos.rapido')) }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        <span>Mov. Rápido</span>
                                    </a>
                                @endif

                                @if(Auth::user()->hasRole(['tec', 'subtec', 'item_admin', 'user_admin', 'app_admin']))
                                    <a href="{{ route('tickets.index') }}" class="{{ $navItemClass(Request::routeIs('tickets.*')) }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span>Tickets</span>
                                    </a>
                                @endif

                                @if(Auth::user()->hasRole('rev'))
                                    <a href="{{ route('reportes.index') }}" class="{{ $navItemClass(Request::routeIs('reportes.*')) }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span>Reportes</span>
                                    </a>
                                @endif

                                @php
                                    $hasAdminTools = Auth::user()->hasRole('item_admin') || Auth::user()->isAppAdmin() || Auth::user()->isAdmin();
                                    $isAdminActive = Request::routeIs('tipos.*') || Request::routeIs('ubicaciones.*') || Request::routeIs('app_admin.*') || Request::routeIs('users.*');
                                @endphp

                                @if($hasAdminTools)
                                    <div class="relative inline-block text-left" id="admin-dropdown-container">
                                        <button type="button" 
                                                id="admin-dropdown-btn" 
                                                class="{{ $isAdminActive 
                                                    ? 'bg-black text-[#fbc101] font-black text-xs xl:text-sm px-3 py-2 rounded-xl shadow-sm inline-flex items-center gap-1.5 whitespace-nowrap shrink-0 transition-all' 
                                                    : 'text-black hover:text-black hover:bg-black/10 font-bold text-xs xl:text-sm px-2.5 py-2 rounded-xl inline-flex items-center gap-1.5 whitespace-nowrap shrink-0 transition-all' }}"
                                                aria-expanded="false" 
                                                aria-haspopup="true">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span>Administración</span>
                                            <svg class="w-3.5 h-3.5 transition-transform duration-200" id="admin-dropdown-arrow" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <div id="admin-dropdown-menu" 
                                             class="hidden absolute left-0 mt-2 w-72 rounded-2xl bg-[#18181b] shadow-2xl border border-zinc-800 ring-1 ring-black/40 text-white divide-y divide-zinc-800/70 focus:outline-none z-50 p-1.5 backdrop-blur-md">
                                            @if(Auth::user()->hasRole('item_admin'))
                                                <div class="py-1">
                                                    <a href="{{ route('tipos.index') }}" 
                                                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs xl:text-sm font-semibold transition-all group {{ Request::routeIs('tipos.*') ? 'bg-black text-[#fbc101] font-bold ring-1 ring-amber-400/30' : 'text-zinc-200 hover:bg-zinc-800 hover:text-white' }}">
                                                        <span class="p-2 rounded-xl bg-zinc-900 border border-zinc-700/80 text-[#fbc101] group-hover:bg-[#fbc101] group-hover:text-black transition-colors shrink-0">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                                            </svg>
                                                        </span>
                                                        <div class="flex flex-col min-w-0">
                                                            <span class="truncate">Tipos de Hardware</span>
                                                            <span class="text-[10px] text-zinc-400 font-normal truncate">Categorías y atributos</span>
                                                        </div>
                                                    </a>

                                                    <a href="{{ route('ubicaciones.index') }}" 
                                                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs xl:text-sm font-semibold transition-all group mt-0.5 {{ Request::routeIs('ubicaciones.*') ? 'bg-black text-[#fbc101] font-bold ring-1 ring-amber-400/30' : 'text-zinc-200 hover:bg-zinc-800 hover:text-white' }}">
                                                        <span class="p-2 rounded-xl bg-zinc-900 border border-zinc-700/80 text-[#fbc101] group-hover:bg-[#fbc101] group-hover:text-black transition-colors shrink-0">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                            </svg>
                                                        </span>
                                                        <div class="flex flex-col min-w-0">
                                                            <span class="truncate">Salas y Espacios</span>
                                                            <span class="text-[10px] text-zinc-400 font-normal truncate">Distribución física LIA</span>
                                                        </div>
                                                    </a>
                                                </div>
                                            @endif

                                            @if(Auth::user()->isAppAdmin())
                                                <div class="py-1">
                                                    <a href="{{ route('app_admin.index') }}" 
                                                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs xl:text-sm font-semibold transition-all group {{ Request::routeIs('app_admin.*') ? 'bg-black text-[#fbc101] font-bold ring-1 ring-amber-400/30' : 'text-zinc-200 hover:bg-zinc-800 hover:text-white' }}">
                                                        <span class="p-2 rounded-xl bg-zinc-900 border border-zinc-700/80 text-[#fbc101] group-hover:bg-[#fbc101] group-hover:text-black transition-colors shrink-0">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            </svg>
                                                        </span>
                                                        <div class="flex flex-col min-w-0">
                                                            <span class="truncate">Gestión App</span>
                                                            <span class="text-[10px] text-zinc-400 font-normal truncate">Cargos, carreras y feed</span>
                                                        </div>
                                                    </a>
                                                </div>
                                            @endif

                                            @if(Auth::user()->isAdmin())
                                                <div class="py-1">
                                                    <a href="{{ route('users.index') }}" 
                                                       class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs xl:text-sm font-semibold transition-all group {{ Request::routeIs('users.*') ? 'bg-black text-[#fbc101] font-bold ring-1 ring-amber-400/30' : 'text-zinc-200 hover:bg-zinc-800 hover:text-white' }}">
                                                        <span class="p-2 rounded-xl bg-zinc-900 border border-zinc-700/80 text-[#fbc101] group-hover:bg-[#fbc101] group-hover:text-black transition-colors shrink-0">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                                            </svg>
                                                        </span>
                                                        <div class="flex flex-col min-w-0">
                                                            <span class="truncate">Gestión de Usuarios</span>
                                                            <span class="text-[10px] text-zinc-400 font-normal truncate">Roles y permisos</span>
                                                        </div>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endif
                        @endauth
                    </nav>

                    <!-- User Profile & Logout -->
                    <div class="flex items-center gap-1.5 pl-3 xl:pl-4 border-l border-black/15 shrink-0">
                        @auth
                            @if(Auth::user()->hasRole('registered'))
                                <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-black/10 text-black font-extrabold text-xs xl:text-sm transition-all whitespace-nowrap" title="Mi perfil de usuario">
                                    <img src="{{ Auth::user()->foto_url }}" alt="{{ Auth::user()->nombre }}" class="w-6 h-6 rounded-full object-cover border border-black/20 shadow-xs bg-white shrink-0" onerror="this.src='/assets/placeholder-user.png'">
                                    <span>{{ Auth::user()->nombre }}</span>
                                </a>
                            @else
                                <span class="inline-flex items-center gap-2 px-3 py-2 text-black font-extrabold text-xs xl:text-sm whitespace-nowrap">
                                    <img src="{{ Auth::user()->foto_url }}" alt="{{ Auth::user()->nombre }}" class="w-6 h-6 rounded-full object-cover border border-black/20 shadow-xs bg-white shrink-0" onerror="this.src='/assets/placeholder-user.png'">
                                    <span>{{ Auth::user()->nombre }}</span>
                                </span>
                            @endif

                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center p-2 rounded-xl text-black/75 hover:text-rose-800 hover:bg-black/10 transition-all" title="Cerrar sesión">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013 3v1"/>
                                    </svg>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="text-black hover:bg-black/10 font-bold text-xs xl:text-sm px-3.5 py-2 rounded-xl transition-all whitespace-nowrap">Iniciar Sesión</a>
                            <a href="{{ route('register') }}" class="bg-black text-[#fbc101] hover:bg-black/85 font-extrabold text-xs xl:text-sm px-4 py-2 rounded-xl transition-all shadow-sm whitespace-nowrap">Registrarse</a>
                        @endauth
                    </div>
                @endif
            </div>
        </div>

        <!-- Mobile Menu (Hidden by default) -->
        <div id="mobile-menu" class="hidden lg:hidden flex flex-col gap-1.5 mt-3 pb-2 border-t border-black/15 pt-3">
            @if(Request::is('/'))
                <a href="#inicio" class="text-black font-bold py-2 px-3 rounded-xl hover:bg-black/10 text-sm">Inicio</a>
                <a href="#sistema-lia" class="text-black font-bold py-2 px-3 rounded-xl hover:bg-black/10 text-sm">Funcionalidades</a>
                <a href="#espacios-lia" class="text-black font-bold py-2 px-3 rounded-xl hover:bg-black/10 text-sm">Espacios</a>
                <a href="#personal-lia" class="text-black font-bold py-2 px-3 rounded-xl hover:bg-black/10 text-sm">Personal</a>
                <a href="#actividades-lia" class="text-black font-bold py-2 px-3 rounded-xl hover:bg-black/10 text-sm">Actividades</a>
                <div class="h-px bg-black/15 my-2"></div>
                @auth
                    <a href="{{ Auth::user()->hasActivePermissions() ? route('dashboard.index') : route('bienvenida') }}" class="text-black font-black py-2.5 px-3 rounded-xl bg-black/10 text-center text-sm">Ir al Panel</a>
                @else
                    <div class="flex items-center gap-2 px-1 py-1">
                        <a href="{{ route('login') }}" class="text-black font-bold py-2 px-3 rounded-xl hover:bg-black/10 flex-1 text-center text-sm">Iniciar Sesión</a>
                        <a href="{{ route('register') }}" class="bg-black text-[#fbc101] hover:bg-black/85 font-extrabold py-2 px-3 rounded-xl flex-1 text-center text-sm shadow-sm">Registrarse</a>
                    </div>
                @endauth
            @else
                @php
                    $navMobileClass = function($isActive) {
                        return $isActive
                            ? 'bg-black text-[#fbc101] font-black py-2 px-3.5 rounded-xl flex items-center gap-2.5 text-sm shadow-sm transition-all'
                            : 'text-black font-bold py-2 px-3.5 rounded-xl hover:bg-black/10 flex items-center gap-2.5 text-sm transition-all';
                    };
                @endphp

                @auth
                    @if(Auth::user()->hasActivePermissions())
                        <a href="{{ route('dashboard.index') }}" class="{{ $navMobileClass(Request::routeIs('dashboard.*')) }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            <span>Dashboard</span>
                        </a>

                        <a href="{{ route('elementos.index') }}" class="{{ $navMobileClass(Request::routeIs('elementos.*')) }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <span>Inventario</span>
                        </a>

                        @if(Auth::user()->canManageMovimientos())
                            <a href="{{ route('movimientos.rapido') }}" class="{{ $navMobileClass(Request::routeIs('movimientos.rapido')) }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <span>Movimiento Rápido</span>
                            </a>
                        @endif

                        @if(Auth::user()->hasRole(['tec', 'subtec', 'item_admin', 'user_admin', 'app_admin']))
                            <a href="{{ route('tickets.index') }}" class="{{ $navMobileClass(Request::routeIs('tickets.*')) }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span>Bandeja de Tickets</span>
                            </a>
                        @endif

                        @if(Auth::user()->hasRole('rev'))
                            <a href="{{ route('reportes.index') }}" class="{{ $navMobileClass(Request::routeIs('reportes.*')) }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Reportes</span>
                            </a>
                        @endif

                        @if(Auth::user()->hasRole('item_admin'))
                            <a href="{{ route('tipos.index') }}" class="{{ $navMobileClass(Request::routeIs('tipos.*')) }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                <span>Tipos de Hardware</span>
                            </a>

                            <a href="{{ route('ubicaciones.index') }}" class="{{ $navMobileClass(Request::routeIs('ubicaciones.*')) }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span>Salas y Espacios</span>
                            </a>
                        @endif

                        @if(Auth::user()->isAppAdmin())
                            <a href="{{ route('app_admin.index') }}" class="{{ $navMobileClass(Request::routeIs('app_admin.*')) }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>Gestión App</span>
                            </a>
                        @endif

                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('users.index') }}" class="{{ $navMobileClass(Request::routeIs('users.*')) }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span>Usuarios</span>
                            </a>
                        @endif
                    @endif

                    <div class="h-px bg-black/15 my-2"></div>
                    <div class="flex items-center justify-between px-2 py-1">
                        @if(Auth::user()->hasRole('registered'))
                            <a href="{{ route('profile.edit') }}" class="font-bold text-sm text-black flex items-center gap-2">
                                <img src="{{ Auth::user()->foto_url }}" alt="{{ Auth::user()->nombre }}" class="w-6 h-6 rounded-full object-cover border border-black/20 shadow-xs bg-white shrink-0" onerror="this.src='/assets/placeholder-user.png'">
                                <span>{{ Auth::user()->nombre }}</span>
                            </a>
                        @else
                            <span class="font-bold text-sm text-black flex items-center gap-2">
                                <img src="{{ Auth::user()->foto_url }}" alt="{{ Auth::user()->nombre }}" class="w-6 h-6 rounded-full object-cover border border-black/20 shadow-xs bg-white shrink-0" onerror="this.src='/assets/placeholder-user.png'">
                                <span>{{ Auth::user()->nombre }}</span>
                            </span>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 font-bold px-3 py-1.5 rounded-xl shadow-xs transition-all">
                                Cerrar Sesión
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center gap-2 px-1 py-1">
                        <a href="{{ route('login') }}" class="text-black font-bold py-2 px-3 rounded-xl hover:bg-black/10 flex-1 text-center text-sm">Iniciar Sesión</a>
                        <a href="{{ route('register') }}" class="bg-black text-[#fbc101] hover:bg-black/85 font-extrabold py-2 px-3 rounded-xl flex-1 text-center text-sm shadow-sm">Registrarse</a>
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

    const adminBtn = document.getElementById('admin-dropdown-btn');
    const adminMenu = document.getElementById('admin-dropdown-menu');
    const adminArrow = document.getElementById('admin-dropdown-arrow');

    if (adminBtn && adminMenu) {
        adminBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isHidden = adminMenu.classList.contains('hidden');
            if (isHidden) {
                adminMenu.classList.remove('hidden');
                adminBtn.setAttribute('aria-expanded', 'true');
                adminArrow?.classList.add('rotate-180');
            } else {
                adminMenu.classList.add('hidden');
                adminBtn.setAttribute('aria-expanded', 'false');
                adminArrow?.classList.remove('rotate-180');
            }
        });

        document.addEventListener('click', function(e) {
            if (!adminBtn.contains(e.target) && !adminMenu.contains(e.target)) {
                adminMenu.classList.add('hidden');
                adminBtn.setAttribute('aria-expanded', 'false');
                adminArrow?.classList.remove('rotate-180');
            }
        });
    }
</script>
