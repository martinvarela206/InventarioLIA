<section id="inicio" class="relative w-full bg-[url('/assets/notebook-wide.png')] bg-cover bg-center h-[540px] flex justify-center px-6">
    <div class="w-full max-w-[1196px] flex justify-end items-center">
        <div class="flex flex-col max-w-[435px] py-11">
            <h2 class="text-white text-left text-xl mb-2">BIENVENIDO AL SISTEMA LIA</h2>
            <h3 class="text-white font-bold text-4xl leading-tight mb-3">Gestiona el Inventario y Tickets de Incidencias del LIA de manera segura y ordenada.</h3>
            <h3 class="text-white font-bold text-4xl leading-tight mb-3">¡Todo bajo control!</h3>
            
            <div class="flex gap-4 sm:gap-6 justify-center my-4 flex-wrap">
                @auth
                    <a href="{{ Auth::user()->hasActivePermissions() ? route('dashboard.index') : route('bienvenida') }}" class="bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold px-6 py-3 rounded-xl shadow-lg transition-all text-sm">
                        Ir al Panel
                    </a>
                @else
                    <a href="{{ route('login') }}" class="bg-[#fbc101] hover:bg-amber-400 text-black font-extrabold px-6 py-3 rounded-xl shadow-lg transition-all text-sm">
                        Iniciar Sesión
                    </a>
                    <a href="{{ route('register') }}" class="bg-[#111] hover:bg-black/85 text-[#fbc101] font-extrabold px-6 py-3 rounded-xl shadow-lg transition-all text-sm border border-black/20">
                        Registrarse
                    </a>
                @endauth
            </div>
        </div>
    </div>
</section>
