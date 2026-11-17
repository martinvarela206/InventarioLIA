@props(['fotos' => [], 'aspect' => 'aspect-[4/3]', 'maxHeight' => 'max-h-64'])

@php
    $fotosCollection = collect($fotos);
    $total = $fotosCollection->count();
@endphp

@if($total > 0)
    <div x-data="{
            active: 0,
            total: {{ $total }},
            modalOpen: false,
            modalSrc: '',
            prev() { this.active = (this.active - 1 + this.total) % this.total; },
            next() { this.active = (this.active + 1) % this.total; },
            openModal(src) { this.modalSrc = src; this.modalOpen = true; }
         }" 
         class="relative w-full overflow-hidden rounded-xl border border-gray-200 bg-gray-900 shadow-sm group">
        
        <!-- Contenedor de Diapositivas -->
        <div class="relative w-full {{ $aspect }} {{ $maxHeight }} flex items-center justify-center bg-black/40">
            @foreach($fotosCollection as $index => $foto)
                <div x-show="active === {{ $index }}" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="w-full h-full flex items-center justify-center cursor-zoom-in"
                     @click="openModal('{{ $foto->url }}')">
                    <img src="{{ $foto->url }}" 
                         alt="{{ $foto->nombre_original ?? 'Evidencia' }}" 
                         class="w-full h-full object-contain">
                </div>
            @endforeach
        </div>

        @if($total > 1)
            <!-- Flecha Anterior -->
            <button type="button" 
                    @click.stop="prev()" 
                    aria-label="Foto anterior"
                    class="absolute left-2 top-1/2 -translate-y-1/2 bg-black/60 hover:bg-black/90 text-white p-1.5 rounded-full transition-all opacity-80 hover:opacity-100 shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <!-- Flecha Siguiente -->
            <button type="button" 
                    @click.stop="next()" 
                    aria-label="Siguiente foto"
                    class="absolute right-2 top-1/2 -translate-y-1/2 bg-black/60 hover:bg-black/90 text-white p-1.5 rounded-full transition-all opacity-80 hover:opacity-100 shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>

            <!-- Indicador Contador -->
            <div class="absolute bottom-2 right-2 bg-black/70 text-white text-[11px] font-black px-2 py-0.5 rounded-md backdrop-blur-xs">
                <span x-text="active + 1"></span> / {{ $total }}
            </div>
        @endif

        <!-- Modal Lightbox Ampliado -->
        <template x-teleport="body">
            <div x-show="modalOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
                 @keydown.escape.window="modalOpen = false">
                <button type="button" 
                        @click="modalOpen = false" 
                        class="absolute top-4 right-4 bg-white/20 hover:bg-white/40 text-white p-2 rounded-full transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <div class="max-w-4xl max-h-[90vh] overflow-hidden flex items-center justify-center" @click.away="modalOpen = false">
                    <img :src="modalSrc" class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl">
                </div>
            </div>
        </template>
    </div>
@endif
