<section id="inicio" class="relative w-full bg-[url('/assets/notebook-wide.png')] bg-cover bg-center h-[540px] flex justify-center px-6">
    <div class="w-full max-w-[1196px] flex justify-end items-center">
        <div class="flex flex-col max-w-[435px] py-11">
            <h2 class="text-white text-left text-xl mb-2">BIENVENIDO AL SISTEMA LIA</h2>
            <h3 class="text-white font-bold text-4xl leading-tight mb-3">Gestiona el Inventario y Tickets de Incidencias del LIA de manera segura y ordenada.</h3>
            <h3 class="text-white font-bold text-4xl leading-tight mb-3">¡Todo bajo control!</h3>
            
            <div class="flex gap-6 justify-center my-4">
                @auth
                    <a href="{{ route('revision.index') }}" class="bg-[#dba800] text-[#111] px-6 py-2.5 rounded border-4 border-[#dba800] font-medium hover:bg-[#fbc101] hover:border-[#fbc101] transition-all duration-200">
                        Ir al Panel
                    </a>
                @else
                    <a href="{{ route('login') }}" class="bg-[#dba800] text-[#111] px-6 py-2.5 rounded border-4 border-[#dba800] font-medium hover:bg-[#fbc101] hover:border-[#fbc101] transition-all duration-200">
                        Ingresar
                    </a>
                    <!-- <a href="{{ route('register') }}" class="bg-black/25 backdrop-blur-md text-[#dba800] px-6 py-2.5 rounded border-4 border-[#dba800] font-medium hover:bg-[#fbc101] hover:text-[#111] hover:border-[#fbc101] transition-all duration-200">
                        Registrarse
                    </a> -->
                @endauth
            </div>
        </div>
    </div>
</section>
