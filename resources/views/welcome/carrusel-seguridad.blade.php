@if(isset($avisos) && $avisos->isNotEmpty())
<div class="relative z-30 -mt-16 sm:-mt-20 max-w-[1196px] mx-auto px-4" id="carrusel-seguridad-container">
    <div class="bg-[#18181b]/92 backdrop-blur-xl border border-white/10 rounded-3xl p-6 sm:p-8 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.7)] text-white transition-all hover:border-amber-400/40">
        <!-- Barra de Título y Controles -->
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-white/10">
            <div class="flex items-center gap-2.5">
                <span class="p-1.5 bg-amber-400/20 text-[#fbc101] rounded-xl border border-amber-400/30 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </span>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] uppercase font-black tracking-widest text-[#fbc101]">Cultura de Seguridad LIA</span>
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                    </div>
                    <h3 class="text-xs font-semibold text-gray-300">Recomendaciones y Buenas Prácticas Informáticas</h3>
                </div>
            </div>

            <!-- Flechas de navegación y contador -->
            <div class="flex items-center gap-2">
                <span id="carrusel-contador" class="text-xs font-mono font-bold text-gray-400 mr-2">1 / {{ $avisos->count() }}</span>
                <button type="button" onclick="cambiarSlide(-1)" class="p-2 rounded-xl bg-white/5 hover:bg-white/15 border border-white/10 transition-colors text-white" title="Anterior">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <button type="button" onclick="cambiarSlide(1)" class="p-2 rounded-xl bg-white/5 hover:bg-white/15 border border-white/10 transition-colors text-white" title="Siguiente">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </button>
            </div>
        </div>

        <!-- Slides -->
        <div class="relative overflow-hidden min-h-[110px] sm:min-h-[85px]" id="carrusel-slides-wrapper">
            @foreach($avisos as $index => $aviso)
                @php
                    $severidadColors = [
                        'tip' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                        'info' => 'bg-sky-500/20 text-sky-300 border-sky-500/30',
                        'warning' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                        'danger' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                    ];
                    $badgeStyle = $severidadColors[$aviso->severidad] ?? 'bg-white/10 text-gray-300 border-white/10';
                @endphp
                <div class="carrusel-slide transition-all duration-500 {{ $index === 0 ? 'opacity-100 block' : 'opacity-0 hidden' }}" data-index="{{ $index }}">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1.5 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $badgeStyle }}">
                                    {{ $aviso->severidad === 'tip' ? 'Consejo' : ($aviso->severidad === 'warning' ? 'Advertencia' : ($aviso->severidad === 'danger' ? 'Crítico' : 'Información')) }}
                                </span>
                                <h4 class="text-base sm:text-lg font-black text-white tracking-tight">{{ $aviso->titulo }}</h4>
                            </div>
                            <p class="text-xs sm:text-sm text-gray-300 font-light leading-relaxed">
                                {{ $aviso->descripcion }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Indicadores (Dots) -->
        <div class="flex items-center justify-center gap-1.5 mt-4 pt-3 border-t border-white/5">
            @foreach($avisos as $index => $aviso)
                <button type="button" onclick="irASlide({{ $index }})" class="carrusel-dot h-1.5 rounded-full transition-all duration-300 {{ $index === 0 ? 'w-6 bg-[#fbc101]' : 'w-2 bg-white/20 hover:bg-white/40' }}" data-index="{{ $index }}"></button>
            @endforeach
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const slides = document.querySelectorAll('.carrusel-slide');
    const dots = document.querySelectorAll('.carrusel-dot');
    const contador = document.getElementById('carrusel-contador');
    const total = slides.length;
    if (total <= 1) return;

    let currentIndex = 0;
    let timer = null;

    function renderSlide(index) {
        slides.forEach((s, i) => {
            if (i === index) {
                s.classList.remove('hidden', 'opacity-0');
                s.classList.add('block', 'opacity-100');
            } else {
                s.classList.remove('block', 'opacity-100');
                s.classList.add('hidden', 'opacity-0');
            }
        });

        dots.forEach((d, i) => {
            if (i === index) {
                d.className = 'carrusel-dot h-1.5 rounded-full transition-all duration-300 w-6 bg-[#fbc101]';
            } else {
                d.className = 'carrusel-dot h-1.5 rounded-full transition-all duration-300 w-2 bg-white/20 hover:bg-white/40';
            }
        });

        if (contador) {
            contador.textContent = `${index + 1} / ${total}`;
        }
        currentIndex = index;
    }

    window.cambiarSlide = function (dir) {
        let nextIndex = (currentIndex + dir + total) % total;
        renderSlide(nextIndex);
        reiniciarTimer();
    };

    window.irASlide = function (idx) {
        renderSlide(idx);
        reiniciarTimer();
    };

    function startAutoSlide() {
        timer = setInterval(() => {
            cambiarSlide(1);
        }, 6000);
    }

    function reiniciarTimer() {
        if (timer) clearInterval(timer);
        startAutoSlide();
    }

    const container = document.getElementById('carrusel-seguridad-container');
    if (container) {
        container.addEventListener('mouseenter', () => clearInterval(timer));
        container.addEventListener('mouseleave', () => reiniciarTimer());
    }

    startAutoSlide();
});
</script>
@endif
