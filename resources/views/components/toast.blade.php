<div x-data="toastManager()" 
     x-init="init()" 
     @toast.window="add($event.detail)"
     class="fixed top-5 right-5 z-[99999] flex flex-col gap-2.5 max-w-sm sm:max-w-md w-full px-4 pointer-events-none"
     aria-live="polite">
    <template x-for="item in toasts" :key="item.id">
        <div x-show="item.visible"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 -translate-y-3 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-x-4 scale-95"
             @mouseenter="pause(item)"
             @mouseleave="resume(item)"
             :class="{
                'border-emerald-300 bg-white/95 text-emerald-950 shadow-emerald-500/10': item.type === 'success',
                'border-rose-300 bg-white/95 text-rose-950 shadow-rose-500/10': item.type === 'error',
                'border-amber-300 bg-white/95 text-amber-950 shadow-amber-500/10': item.type === 'warning',
                'border-sky-300 bg-white/95 text-sky-950 shadow-sky-500/10': item.type === 'info'
             }"
             class="pointer-events-auto relative overflow-hidden rounded-2xl border shadow-xl p-4 flex items-start gap-3 backdrop-blur-md transition-all">
             
            <!-- Icon -->
            <div class="shrink-0 mt-0.5">
                <template x-if="item.type === 'success'">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </template>
                <template x-if="item.type === 'error'">
                    <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                </template>
                <template x-if="item.type === 'warning'">
                    <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                </template>
                <template x-if="item.type === 'info'">
                    <div class="w-8 h-8 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center shadow-2xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </template>
            </div>

            <!-- Content -->
            <div class="flex-1 pr-2 pt-0.5">
                <p class="text-xs font-black tracking-wide uppercase mb-0.5"
                   :class="{
                       'text-emerald-700': item.type === 'success',
                       'text-rose-700': item.type === 'error',
                       'text-amber-700': item.type === 'warning',
                       'text-sky-700': item.type === 'info'
                   }"
                   x-text="item.title || (item.type === 'success' ? 'Éxito' : (item.type === 'error' ? 'Atención' : (item.type === 'warning' ? 'Advertencia' : 'Información')))">
                </p>
                <p class="text-xs font-bold text-gray-800 leading-snug break-words" x-text="item.message"></p>
            </div>

            <!-- Close Button -->
            <button type="button" 
                    @click="remove(item.id)" 
                    class="shrink-0 p-1 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
                    aria-label="Cerrar notificación">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <!-- Progress Bar -->
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-100 overflow-hidden">
                <div class="h-full transition-all ease-linear"
                     :class="{
                         'bg-emerald-500': item.type === 'success',
                         'bg-rose-500': item.type === 'error',
                         'bg-amber-500': item.type === 'warning',
                         'bg-sky-500': item.type === 'info'
                     }"
                     :style="`width: ${item.progress}%; transition-duration: 50ms;`">
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function toastManager() {
    return {
        toasts: [],
        nextId: 1,
        init() {
            @if(session('success'))
                this.add({ type: 'success', message: @json(session('success')) });
            @endif
            @if(session('error'))
                this.add({ type: 'error', message: @json(session('error')) });
            @endif
            @if(session('warning'))
                this.add({ type: 'warning', message: @json(session('warning')) });
            @endif
            @if(session('info'))
                this.add({ type: 'info', message: @json(session('info')) });
            @endif
            @if(session('status') && !is_array(session('status')))
                this.add({ type: 'info', message: @json(session('status')) });
            @endif
        },
        add({ type = 'info', title = null, message = '', duration = 4500 }) {
            if (!message) return;
            const id = this.nextId++;
            const toast = {
                id,
                type,
                title,
                message,
                visible: true,
                duration,
                remaining: duration,
                progress: 100,
                paused: false,
                lastTick: Date.now(),
                intervalId: null
            };

            const interval = 50;
            toast.intervalId = setInterval(() => {
                if (!toast.paused) {
                    const now = Date.now();
                    const delta = now - toast.lastTick;
                    toast.remaining -= delta;
                    toast.progress = Math.max(0, (toast.remaining / toast.duration) * 100);
                    if (toast.remaining <= 0) {
                        clearInterval(toast.intervalId);
                        this.remove(id);
                    }
                }
                toast.lastTick = Date.now();
            }, interval);

            this.toasts.push(toast);
        },
        pause(toast) {
            toast.paused = true;
        },
        resume(toast) {
            toast.lastTick = Date.now();
            toast.paused = false;
        },
        remove(id) {
            const idx = this.toasts.findIndex(t => t.id === id);
            if (idx !== -1) {
                const toast = this.toasts[idx];
                if (toast.intervalId) clearInterval(toast.intervalId);
                toast.visible = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 300);
            }
        }
    };
}
</script>
