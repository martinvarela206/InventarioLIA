<footer class="bg-[#111] text-white w-full overflow-hidden py-8 px-4 sm:px-6 border-t border-black/40">
    <div class="max-w-[1360px] mx-auto flex flex-col md:flex-row items-center justify-between gap-6 text-center md:text-left">
        <div class="flex items-center gap-3">
            @include('common.logo', ['variant' => 'light', 'class' => 'w-12 h-12 sm:w-14 sm:h-14 shrink-0 block'])
            <div class="flex flex-col text-left">
                <span class="text-xl sm:text-2xl font-black text-[#fbc101] tracking-tight">Nexo LIA</span>
                <span class="text-xs text-zinc-400 font-medium">Laboratorio de Informática Aplicada — FCEFN, UNSJ</span>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-center md:justify-end gap-3 sm:gap-5 py-2">
            <img src="/assets/logo-lia.svg" alt="LIA" class="h-8 sm:h-10 object-contain hover:opacity-100 opacity-90 transition-opacity" />
            <img src="/assets/logo-di.svg" alt="Departamento de Informática" class="h-8 sm:h-10 object-contain hover:opacity-100 opacity-90 transition-opacity" />
            <img src="/assets/logo-exactas.svg" alt="Exactas UNSJ" class="h-8 sm:h-10 object-contain hover:opacity-100 opacity-90 transition-opacity" />
            <img src="/assets/logo-unsj.svg" alt="UNSJ" class="h-8 sm:h-10 object-contain hover:opacity-100 opacity-90 transition-opacity" />
        </div>
    </div>

    <div class="h-px bg-zinc-800 w-full max-w-[1360px] mx-auto my-6"></div>

    <div class="max-w-[1360px] mx-auto flex flex-col sm:flex-row gap-3 items-center justify-between text-center sm:text-left text-xs text-zinc-500 font-medium">
        <p>© {{ date('Y') }} Nexo LIA. Sistema de Gestión de Inventario y Trazabilidad.</p>
        <p>Diseñado y Desarrollado por <span class="text-zinc-300 font-semibold">Martín Varela</span> | UNSJ</p>
    </div>
</footer>

