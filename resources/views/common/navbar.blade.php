<header class="w-full bg-[var(--figma-bg-panel)] border-b border-[var(--figma-border-subtle)] sticky top-0 z-50 backdrop-blur-md">
    <div class="mx-auto max-w-[1400px] w-full px-3 sm:px-6 h-[50px] flex items-center justify-between gap-3">
        <!-- Left: Brand Logo & Workspace Info -->
        <div class="flex items-center gap-3">
            <a href="{{ url('/') }}" class="flex items-center gap-2 hover:opacity-90 transition-opacity shrink-0 group">
                <div class="w-7 h-7 rounded-lg bg-[var(--figma-bg-hover)] border border-[var(--figma-border-strong)] flex items-center justify-center p-1 group-hover:border-[var(--figma-brand)] transition-colors">
                    @include('common.logo', ['variant' => 'dark', 'class' => 'w-full h-full'])
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-sm font-bold text-[var(--figma-text-primary)] tracking-tight">Nexo LIA</span>
                    <span class="text-[10px] font-mono font-medium px-1.5 py-0.2 rounded bg-[var(--figma-bg-input)] border border-[var(--figma-border-subtle)] text-[var(--figma-text-tertiary)] uppercase">Studio</span>
                </div>
            </a>
        </div>

        <!-- Center: Figma Tool Ribbon / Navigation (Desktop) -->
        <div class="hidden lg:flex items-center gap-1">
            @if(Request::is('/'))
                <nav class="flex items-center gap-1">
                    <a href="#inicio" class="text-xs font-medium px-2.5 py-1.5 rounded-md text-[var(--figma-text-secondary)] hover:text-[var(--figma-text-primary)] hover:bg-[var(--figma-bg-hover)] transition-colors">Inicio</a>
                    <a href="#sistema-lia" class="text-xs font-medium px-2.5 py-1.5 rounded-md text-[var(--figma-text-secondary)] hover:text-[var(--figma-text-primary)] hover:bg-[var(--figma-bg-hover)] transition-colors">Funcionalidades</a>
                    <a href="#espacios-lia" class="text-xs font-medium px-2.5 py-1.5 rounded-md text-[var(--figma-text-secondary)] hover:text-[var(--figma-text-primary)] hover:bg-[var(--figma-bg-hover)] transition-colors">Espacios</a>
                    <a href="#personal-lia" class="text-xs font-medium px-2.5 py-1.5 rounded-md text-[var(--figma-text-secondary)] hover:text-[var(--figma-text-primary)] hover:bg-[var(--figma-bg-hover)] transition-colors">Personal</a>
                    <a href="#actividades-lia" class="text-xs font-medium px-2.5 py-1.5 rounded-md text-[var(--figma-text-secondary)] hover:text-[var(--figma-text-primary)] hover:bg-[var(--figma-bg-hover)] transition-colors">Actividades</a>
                </nav>
            @else
                @php
                    $figmaNavClass = function($isActive) {
                        return $isActive 
                            ? 'bg-[var(--figma-bg-select)] text-[var(--figma-brand)] border border-[var(--figma-brand)]/30 font-semibold text-xs px-2.5 py-1.5 rounded-md inline-flex items-center gap-1.5 whitespace-nowrap transition-all shadow-2xs'
                            : 'text-[var(--figma-text-secondary)] hover:text-[var(--figma-text-primary)] hover:bg-[var(--figma-bg-hover)] font-medium text-xs px-2.5 py-1.5 rounded-md inline-flex items-center gap-1.5 whitespace-nowrap transition-all';
                    };
                @endphp

                <nav class="flex items-center gap-1 p-0.5 rounded-lg bg-[var(--figma-bg-input)] border border-[var(--figma-border-subtle)]">
                    @auth
                        @if(Auth::user()->hasActivePermissions())
                            <a href="{{ route('dashboard.index') }}" class="{{ $figmaNavClass(Request::routeIs('dashboard.*')) }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                <span>Dashboard</span>
                            </a>

                            <a href="{{ route('elementos.index') }}" class="{{ $figmaNavClass(Request::routeIs('elementos.*')) }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <span>Inventario</span>
                            </a>

                            @if(Auth::user()->canManageMovimientos())
                                <a href="{{ route('movimientos.rapido') }}" class="{{ $figmaNavClass(Request::routeIs('movimientos.rapido')) }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    <span>Movimiento Rápido</span>
                                </a>
                            @endif

                            @if(Auth::user()->hasRole(['tec', 'subtec', 'item_admin', 'user_admin', 'app_admin']))
                                <a href="{{ route('tickets.index') }}" class="{{ $figmaNavClass(Request::routeIs('tickets.*')) }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Tickets</span>
                                </a>
                            @endif

                            @if(Auth::user()->hasRole('rev'))
                                <a href="{{ route('reportes.index') }}" class="{{ $figmaNavClass(Request::routeIs('reportes.*')) }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
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
                                                ? 'bg-[var(--figma-bg-select)] text-[var(--figma-brand)] border border-[var(--figma-brand)]/30 font-semibold text-xs px-2.5 py-1.5 rounded-md inline-flex items-center gap-1.5 whitespace-nowrap transition-all' 
                                                : 'text-[var(--figma-text-secondary)] hover:text-[var(--figma-text-primary)] hover:bg-[var(--figma-bg-hover)] font-medium text-xs px-2.5 py-1.5 rounded-md inline-flex items-center gap-1.5 whitespace-nowrap transition-all' }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        <span>Administración</span>
                                        <svg class="w-3 h-3 text-[var(--figma-text-tertiary)]" id="admin-dropdown-arrow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                    </button>

                                    <!-- Figma Context Menu -->
                                    <div id="admin-dropdown-menu" 
                                         class="hidden absolute left-0 mt-2 w-64 rounded-xl bg-[var(--figma-bg-card)] shadow-2xl border border-[var(--figma-border-strong)] divide-y divide-[var(--figma-border-subtle)] focus:outline-none z-50 p-1 text-xs">
                                        @if(Auth::user()->hasRole('item_admin'))
                                            <div class="py-1">
                                                <a href="{{ route('tipos.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[var(--figma-text-secondary)] hover:text-[var(--figma-text-primary)] hover:bg-[var(--figma-bg-hover)] transition-colors">
                                                    <span class="p-1 rounded bg-[var(--figma-bg-input)] text-[var(--figma-brand)]">🏷️</span>
                                                    <span>Tipos de Hardware</span>
                                                </a>
                                                <a href="{{ route('ubicaciones.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[var(--figma-text-secondary)] hover:text-[var(--figma-text-primary)] hover:bg-[var(--figma-bg-hover)] transition-colors">
                                                    <span class="p-1 rounded bg-[var(--figma-bg-input)] text-[var(--figma-brand)]">📍</span>
                                                    <span>Salas y Espacios</span>
                                                </a>
                                            </div>
                                        @endif

                                        @if(Auth::user()->isAppAdmin())
                                            <div class="py-1">
                                                <a href="{{ route('app_admin.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[var(--figma-text-secondary)] hover:text-[var(--figma-text-primary)] hover:bg-[var(--figma-bg-hover)] transition-colors">
                                                    <span class="p-1 rounded bg-[var(--figma-bg-input)] text-[var(--figma-brand)]">⚙️</span>
                                                    <span>Gestión del LIA</span>
                                                </a>
                                            </div>
                                        @endif

                                        @if(Auth::user()->isAdmin())
                                            <div class="py-1">
                                                <a href="{{ route('users.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[var(--figma-text-secondary)] hover:text-[var(--figma-text-primary)] hover:bg-[var(--figma-bg-hover)] transition-colors">
                                                    <span class="p-1 rounded bg-[var(--figma-bg-input)] text-[var(--figma-brand)]">👥</span>
                                                    <span>Gestión de Usuarios</span>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif
                    @endauth
                </nav>
            @endif
        </div>

        <!-- Right: Theme Switcher (Figma Circle 50%), Profile & Actions -->
        <div class="flex items-center gap-2">
            <!-- Theme Toggle Button (Figma Circle 50%) -->
            <button id="theme-toggle-btn" 
                    onclick="toggleFigmaTheme()" 
                    class="w-7 h-7 rounded-full bg-[var(--figma-bg-hover)] hover:bg-[var(--figma-bg-card)] border border-[var(--figma-border-strong)] text-[var(--figma-text-secondary)] hover:text-[var(--figma-text-primary)] flex items-center justify-center transition-colors cursor-pointer" 
                    title="Alternar Modo Oscuro / Claro (Figma Studio)" 
                    aria-label="Cambiar tema">
                <svg id="theme-icon-moon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                <svg id="theme-icon-sun" class="w-3.5 h-3.5 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </button>

            @auth
                <!-- User Profile Pill -->
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-2 py-1 rounded-lg hover:bg-[var(--figma-bg-hover)] text-xs font-medium text-[var(--figma-text-primary)] transition-colors">
                    <img src="{{ Auth::user()->foto_url }}" alt="{{ Auth::user()->nombre }}" class="w-6 h-6 rounded-full object-cover border border-[var(--figma-border-strong)] bg-[var(--figma-bg-input)]" onerror="this.src='/assets/placeholder-user.png'">
                    <span class="hidden sm:inline font-medium text-xs">{{ Auth::user()->nombre }}</span>
                </a>

                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="p-1.5 rounded-lg text-[var(--figma-text-tertiary)] hover:text-rose-400 hover:bg-[var(--figma-bg-hover)] transition-colors cursor-pointer" title="Cerrar sesión">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-xs font-medium text-[var(--figma-text-secondary)] hover:text-[var(--figma-text-primary)] px-2.5 py-1.5 rounded-md hover:bg-[var(--figma-bg-hover)] transition-colors">Iniciar Sesión</a>
                <a href="{{ route('register') }}" class="bg-[var(--figma-brand)] hover:bg-[var(--figma-brand-hover)] text-white text-xs font-semibold px-3 py-1.5 rounded-md transition-colors shadow-2xs">Registrarse</a>
            @endauth

            <!-- Mobile Hamburger -->
            <button id="mobile-menu-btn" class="lg:hidden p-1.5 text-[var(--figma-text-secondary)] hover:text-[var(--figma-text-primary)] hover:bg-[var(--figma-bg-hover)] rounded-lg transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
            </button>
        </div>
    </div>

    <!-- Mobile Drawer -->
    <div id="mobile-menu" class="hidden lg:hidden bg-[var(--figma-bg-panel)] border-t border-[var(--figma-border-subtle)] px-4 py-3 space-y-1 text-xs">
        @auth
            <a href="{{ route('dashboard.index') }}" class="block py-2 px-3 rounded-lg text-[var(--figma-text-secondary)] hover:bg-[var(--figma-bg-hover)]">Dashboard</a>
            <a href="{{ route('elementos.index') }}" class="block py-2 px-3 rounded-lg text-[var(--figma-text-secondary)] hover:bg-[var(--figma-bg-hover)]">Inventario</a>
            @if(Auth::user()->canManageMovimientos())
                <a href="{{ route('movimientos.rapido') }}" class="block py-2 px-3 rounded-lg text-[var(--figma-text-secondary)] hover:bg-[var(--figma-bg-hover)]">Movimiento Rápido</a>
            @endif
            <a href="{{ route('tickets.index') }}" class="block py-2 px-3 rounded-lg text-[var(--figma-text-secondary)] hover:bg-[var(--figma-bg-hover)]">Tickets</a>
            <a href="{{ route('reportes.index') }}" class="block py-2 px-3 rounded-lg text-[var(--figma-text-secondary)] hover:bg-[var(--figma-bg-hover)]">Reportes</a>
        @endauth
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
                adminArrow?.classList.add('rotate-180');
            } else {
                adminMenu.classList.add('hidden');
                adminArrow?.classList.remove('rotate-180');
            }
        });

        document.addEventListener('click', function(e) {
            if (!adminBtn.contains(e.target) && !adminMenu.contains(e.target)) {
                adminMenu.classList.add('hidden');
                adminArrow?.classList.remove('rotate-180');
            }
        });
    }
</script>
