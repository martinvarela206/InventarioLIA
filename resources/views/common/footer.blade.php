<footer class="bg-[#111] flex flex-col items-center p-4">
    <div class="flex flex-col md:flex-row justify-between w-full max-w-[1196px] text-center md:text-left">
        <div class="flex items-center gap-3 justify-center md:justify-start">
            <!-- Logo SVG embedded -->
            @include('common.logo', ['variant' => 'light', 'class' => 'w-16 h-16 block'])
            <span class="text-2xl font-bold text-[#dba800]">Sistema LIA</span>
        </div>
        <div class="flex gap-4 py-8 ml-auto flex-wrap justify-center md:justify-end">
            <img src="/assets/logo-lia.svg" alt="LIA" class="h-10 md:h-12" />
            <img src="/assets/logo-di.svg" alt="DI" class="h-10 md:h-12" />
            <img src="/assets/logo-exactas.svg" alt="Exactas" class="h-10 md:h-12" />
            <img src="/assets/logo-unsj.svg" alt="UNSJ" class="h-10 md:h-12" />
        </div>
    </div>
    <div class="h-[2px] bg-[#dba800]/50 w-full max-w-[1196px] my-8"></div>
    <div class="flex flex-col md:flex-row gap-4 items-center justify-between w-full max-w-[1196px] my-8 text-center md:text-left">
        <p class="text-xs text-[#dba800]">2025 Sistema LIA. Todos los derechos reservados.</p>
        <p class="text-xs text-[#dba800]">Diseñado y Desarrollado por Martín Varela | 2025</p>
    </div>
</footer>
