<!-- ======================================================== -->
<!-- SECCIÓN DINÁMICA 1: ESPACIOS DEL LIA                      -->
<!-- ======================================================== -->
<section id="espacios-lia" class="my-16 py-12 px-4 max-w-[1196px] mx-auto border-t border-white/10">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
        <div>
            <span class="text-[#dba800] text-xs font-bold tracking-widest uppercase block mb-1">INSTALACIONES E INFRAESTRUCTURA</span>
            <h2 class="text-white font-black text-3xl sm:text-4xl">Espacios y Laboratorios del LIA</h2>
            <p class="text-white/70 text-sm mt-1 max-w-[650px]">
                Recorré nuestros laboratorios equipados con tecnología de última generación para el cursado, investigación y proyectos estudiantiles.
            </p>
        </div>
        @auth
            @if(Auth::user()->isAppAdmin())
                <a href="{{ route('app_admin.espacios') }}" class="text-xs bg-[#dba800] text-black font-extrabold px-3 py-1.5 rounded-lg hover:bg-amber-400 transition-colors inline-flex items-center gap-1.5 shadow">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Gestionar Espacios</span>
                </a>
            @endif
        @endauth
    </div>

    @if($espacios->isEmpty())
        <div class="bg-black/30 border border-white/10 rounded-2xl p-10 text-center text-white/50">
            <p class="text-sm">Actualmente no se registran espacios publicados.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($espacios as $espacio)
                <div class="bg-gradient-to-b from-white/[0.07] to-white/[0.02] border border-white/10 hover:border-amber-400/50 rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1 group flex flex-col justify-between shadow-xl">
                    <div class="h-48 sm:h-56 w-full overflow-hidden bg-black/40 relative">
                        <img src="{{ $espacio->imagen_path ?: '/assets/notebook-wide.png' }}" 
                             alt="{{ $espacio->titulo }}" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" 
                             onerror="this.src='/assets/notebook-wide.png'" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                        <span class="absolute bottom-3 left-4 bg-amber-400 text-black text-xs font-extrabold px-3 py-1 rounded-full shadow">
                            Espacio LIA #{{ $loop->iteration }}
                        </span>
                    </div>
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-white font-bold text-xl group-hover:text-amber-400 transition-colors mb-2">
                                {{ $espacio->titulo }}
                            </h3>
                            <p class="text-white/70 text-sm leading-relaxed">
                                {{ $espacio->descripcion ?: 'Instalación informática y tecnológica disponible para la comunidad de la FCEFN - UNSJ.' }}
                            </p>
                        </div>
                        <div class="mt-4 pt-4 border-t border-white/5 flex items-center justify-between text-xs text-amber-400 font-semibold">
                            <span>Laboratorio Activo</span>
                            <span class="text-white/40">FCEFN — UNSJ</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

<!-- ======================================================== -->
<!-- SECCIÓN DINÁMICA 2: PERSONAL DEL LIA                     -->
<!-- ======================================================== -->
<section id="personal-lia" class="my-16 py-12 px-4 max-w-[1196px] mx-auto border-t border-white/10">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
        <div>
            <span class="text-[#dba800] text-xs font-bold tracking-widest uppercase block mb-1">EQUIPO INSTITUCIONAL</span>
            <h2 class="text-white font-black text-3xl sm:text-4xl">Autoridades y Personal del LIA</h2>
            <p class="text-white/70 text-sm mt-1 max-w-[650px]">
                Conocé a los docentes, investigadores y coordinadores que garantizan el funcionamiento y la seguridad del Laboratorio de Informática Aplicada.
            </p>
        </div>
        @auth
            @if(Auth::user()->isAppAdmin())
                <a href="{{ route('app_admin.personal') }}" class="text-xs bg-[#dba800] text-black font-extrabold px-3 py-1.5 rounded-lg hover:bg-amber-400 transition-colors inline-flex items-center gap-1.5 shadow">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Gestionar Personal</span>
                </a>
            @endif
        @endauth
    </div>

    @if($personal->isEmpty())
        <div class="bg-black/30 border border-white/10 rounded-2xl p-10 text-center text-white/50">
            <p class="text-sm">Actualmente no se registran miembros del personal publicados.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($personal as $p)
                <div class="bg-gradient-to-b from-white/[0.08] to-white/[0.02] border border-white/10 hover:border-amber-400/50 rounded-2xl p-6 transition-all duration-300 hover:-translate-y-1 flex flex-col justify-between shadow-xl">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-16 h-16 rounded-full border-2 border-[#dba800] overflow-hidden bg-black/40 flex-shrink-0 flex items-center justify-center">
                                <img src="{{ $p->foto_url }}" alt="{{ $p->nombre }}" class="w-full h-full object-cover" onerror="this.src='/assets/placeholder-user.png'" />
                            </div>
                            <div>
                                <h3 class="text-white font-bold text-base leading-tight">{{ $p->nombre }}</h3>
                                <span class="inline-block mt-1 bg-amber-400/20 text-amber-300 border border-amber-400/40 text-[11px] font-extrabold px-2.5 py-0.5 rounded-full">
                                    {{ $p->cargo }}
                                </span>
                            </div>
                        </div>

                        <p class="text-white/80 text-xs leading-relaxed italic bg-black/20 p-3 rounded-xl border border-white/5">
                            "{{ $p->comentario ?: 'Responsable asignado al soporte académico y técnico del laboratorio.' }}"
                        </p>
                    </div>

                    @if($p->email)
                        <div class="mt-4 pt-3 border-t border-white/5 flex items-center justify-between text-[11px]">
                            <span class="text-white/50">Contacto oficial:</span>
                            <a href="mailto:{{ $p->email }}" class="text-amber-400 hover:underline font-mono">{{ $p->email }}</a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</section>

<!-- ======================================================== -->
<!-- SECCIÓN DINÁMICA 3: ACTIVIDADES DEL LIA (FEED INSTAGRAM)  -->
<!-- ======================================================== -->
<section id="actividades-lia" class="my-16 py-12 px-4 max-w-[1196px] mx-auto border-t border-white/10">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
        <div>
            <span class="text-[#dba800] text-xs font-bold tracking-widest uppercase block mb-1">NOVEDADES Y EVENTOS</span>
            <h2 class="text-white font-black text-3xl sm:text-4xl">Actividades del LIA</h2>
            <p class="text-white/70 text-sm mt-1 max-w-[650px]">
                Feed en tiempo real con las publicaciones, anuncios de mantenimiento, talleres y jornadas institucionales del LIA.
            </p>
        </div>
        @auth
            @if(Auth::user()->isAppAdmin())
                <a href="{{ route('app_admin.actividades') }}" class="text-xs bg-[#dba800] text-black font-extrabold px-3 py-1.5 rounded-lg hover:bg-amber-400 transition-colors inline-flex items-center gap-1.5 shadow">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Publicar Actividad</span>
                </a>
            @endif
        @endauth
    </div>

    @if($actividades->isEmpty())
        <div class="bg-black/30 border border-white/10 rounded-2xl p-10 text-center text-white/50">
            <p class="text-sm">Actualmente no se registran publicaciones recientes en el feed.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($actividades as $actividad)
                <!-- Tarjeta estilo post de Instagram / Feed institucional -->
                <article class="bg-[#181818] border border-white/10 hover:border-amber-400/50 rounded-2xl overflow-hidden transition-all duration-300 shadow-xl flex flex-col justify-between">
                    <!-- Cabecera del post -->
                    <div class="p-3.5 flex items-center justify-between border-b border-white/5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-[#fbc101] flex items-center justify-center p-1 border border-amber-300">
                                @include('common.logo', ['variant' => 'dark', 'class' => 'w-5 h-5'])
                            </div>
                            <div>
                                <span class="text-white font-bold text-xs block leading-none">nexo.lia.unsj</span>
                                <span class="text-white/40 text-[10px] block mt-0.5">Laboratorio de Informática</span>
                            </div>
                        </div>
                        <span class="text-white/40 text-[11px] font-mono">
                            {{ $actividad->fecha_publicacion ? $actividad->fecha_publicacion->diffForHumans() : 'Reciente' }}
                        </span>
                    </div>

                    <!-- Imagen del post (si existe) -->
                    @if($actividad->imagen_path)
                        <div class="w-full aspect-[4/3] bg-black/60 overflow-hidden">
                            <img src="{{ $actividad->imagen_path }}" 
                                 alt="{{ $actividad->titulo }}" 
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-500" 
                                 onerror="this.src='/assets/notebook-wide.png'" />
                        </div>
                    @endif

                    <!-- Contenido y descripción del post -->
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="text-white font-extrabold text-base mb-1.5 leading-snug">
                                {{ $actividad->titulo }}
                            </h3>
                            <p class="text-white/80 text-xs leading-relaxed line-clamp-4 whitespace-pre-line">
                                {{ $actividad->contenido }}
                            </p>
                        </div>

                        <!-- Footer con tags y autor -->
                        <div class="mt-4 pt-3 border-t border-white/5 flex items-center justify-between text-[11px] text-amber-400">
                            <span class="font-bold">#LIA #UNSJ #Exactas</span>
                            <span class="text-white/40 text-[10px]">
                                Por: {{ $actividad->autor?->nombre ?? 'Equipo LIA' }}
                            </span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>
