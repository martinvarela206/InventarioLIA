<div id="toast-container" 
     class="fixed top-5 right-5 z-[99999] flex flex-col gap-2.5 max-w-sm sm:max-w-md w-full px-4 pointer-events-none"
     aria-live="polite">
    @php
        $toasts = [];
        if (session('success')) {
            $toasts[] = ['type' => 'success', 'title' => 'Éxito', 'message' => session('success')];
        }
        if (session('error')) {
            $toasts[] = ['type' => 'error', 'title' => 'Atención', 'message' => session('error')];
        }
        if (session('warning')) {
            $toasts[] = ['type' => 'warning', 'title' => 'Advertencia', 'message' => session('warning')];
        }
        if (session('info')) {
            $toasts[] = ['type' => 'info', 'title' => 'Información', 'message' => session('info')];
        }
        if (session('status') && !is_array(session('status'))) {
            $toasts[] = ['type' => 'info', 'title' => 'Información', 'message' => session('status')];
        }
    @endphp

    @foreach($toasts as $index => $t)
        @php
            $type = $t['type'];
            $borderBgClass = match($type) {
                'success' => 'border-emerald-300 bg-white/95 text-emerald-950 shadow-emerald-500/10',
                'error'   => 'border-rose-300 bg-white/95 text-rose-950 shadow-rose-500/10',
                'warning' => 'border-amber-300 bg-white/95 text-amber-950 shadow-amber-500/10',
                default   => 'border-sky-300 bg-white/95 text-sky-950 shadow-sky-500/10',
            };
            $iconBgClass = match($type) {
                'success' => 'bg-emerald-100 text-emerald-700',
                'error'   => 'bg-rose-100 text-rose-700',
                'warning' => 'bg-amber-100 text-amber-700',
                default   => 'bg-sky-100 text-sky-700',
            };
            $titleColorClass = match($type) {
                'success' => 'text-emerald-700',
                'error'   => 'text-rose-700',
                'warning' => 'text-amber-700',
                default   => 'text-sky-700',
            };
            $progressClass = match($type) {
                'success' => 'bg-emerald-500',
                'error'   => 'bg-rose-500',
                'warning' => 'bg-amber-500',
                default   => 'bg-sky-500',
            };
        @endphp

        <div class="toast-item pointer-events-auto relative overflow-hidden rounded-2xl border shadow-xl p-4 flex items-start gap-3 backdrop-blur-md transition-all duration-300 ease-out opacity-100 translate-y-0 scale-100 {{ $borderBgClass }}"
             data-toast-id="toast-init-{{ $index }}"
             data-duration="4500">
             
            <!-- Icon -->
            <div class="shrink-0 mt-0.5">
                <div class="w-8 h-8 rounded-xl {{ $iconBgClass }} flex items-center justify-center shadow-2xs">
                    @if($type === 'success')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    @elseif($type === 'error')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    @elseif($type === 'warning')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 pr-2 pt-0.5">
                <p class="text-xs font-black tracking-wide uppercase mb-0.5 {{ $titleColorClass }}">
                    {{ $t['title'] }}
                </p>
                <p class="text-xs font-bold text-gray-800 leading-snug break-words">
                    {{ $t['message'] }}
                </p>
            </div>

            <!-- Close Button -->
            <button type="button" 
                    onclick="window.dismissToast(this.closest('.toast-item'))" 
                    class="shrink-0 p-1 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
                    aria-label="Cerrar notificación">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <!-- Progress Bar -->
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-100 overflow-hidden">
                <div class="toast-progress h-full {{ $progressClass }} w-full"></div>
            </div>
        </div>
    @endforeach
</div>

<script>
(function() {
    function setupToast(el, duration = 4500) {
        if (!el || el.dataset.toastActive) return;
        el.dataset.toastActive = 'true';

        const progressBar = el.querySelector('.toast-progress');
        let remaining = duration;
        let paused = false;
        let lastTick = Date.now();

        const interval = 50;
        const timerId = setInterval(() => {
            if (!paused) {
                const now = Date.now();
                remaining -= (now - lastTick);
                if (progressBar) {
                    const pct = Math.max(0, (remaining / duration) * 100);
                    progressBar.style.width = pct + '%';
                }
                if (remaining <= 0) {
                    clearInterval(timerId);
                    window.dismissToast(el);
                }
            }
            lastTick = Date.now();
        }, interval);

        el.addEventListener('mouseenter', () => { paused = true; });
        el.addEventListener('mouseleave', () => { lastTick = Date.now(); paused = false; });
        el._timerId = timerId;
    }

    window.dismissToast = function(el) {
        if (!el) return;
        if (el._timerId) clearInterval(el._timerId);
        el.classList.add('opacity-0', 'translate-x-4', 'scale-95');
        setTimeout(() => {
            if (el.parentNode) el.parentNode.removeChild(el);
        }, 300);
    };

    window.showToast = function({ type = 'info', title = null, message = '', duration = 4500 }) {
        if (!message) return;
        const container = document.getElementById('toast-container');
        if (!container) return;

        const defaultTitles = {
            success: 'Éxito',
            error: 'Atención',
            warning: 'Advertencia',
            info: 'Información'
        };
        title = title || defaultTitles[type] || 'Información';

        const styles = {
            success: {
                borderBg: 'border-emerald-300 bg-white/95 text-emerald-950 shadow-emerald-500/10',
                iconBg: 'bg-emerald-100 text-emerald-700',
                titleColor: 'text-emerald-700',
                progress: 'bg-emerald-500',
                iconSvg: '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>'
            },
            error: {
                borderBg: 'border-rose-300 bg-white/95 text-rose-950 shadow-rose-500/10',
                iconBg: 'bg-rose-100 text-rose-700',
                titleColor: 'text-rose-700',
                progress: 'bg-rose-500',
                iconSvg: '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>'
            },
            warning: {
                borderBg: 'border-amber-300 bg-white/95 text-amber-950 shadow-amber-500/10',
                iconBg: 'bg-amber-100 text-amber-700',
                titleColor: 'text-amber-700',
                progress: 'bg-amber-500',
                iconSvg: '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>'
            },
            info: {
                borderBg: 'border-sky-300 bg-white/95 text-sky-950 shadow-sky-500/10',
                iconBg: 'bg-sky-100 text-sky-700',
                titleColor: 'text-sky-700',
                progress: 'bg-sky-500',
                iconSvg: '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            }
        };

        const s = styles[type] || styles.info;
        const toast = document.createElement('div');
        toast.className = `toast-item pointer-events-auto relative overflow-hidden rounded-2xl border shadow-xl p-4 flex items-start gap-3 backdrop-blur-md transition-all duration-300 ease-out opacity-0 -translate-y-3 scale-95 ${s.borderBg}`;
        toast.innerHTML = `
            <div class="shrink-0 mt-0.5">
                <div class="w-8 h-8 rounded-xl ${s.iconBg} flex items-center justify-center shadow-2xs">
                    ${s.iconSvg}
                </div>
            </div>
            <div class="flex-1 pr-2 pt-0.5">
                <p class="text-xs font-black tracking-wide uppercase mb-0.5 ${s.titleColor}">
                    ${title}
                </p>
                <p class="text-xs font-bold text-gray-800 leading-snug break-words">
                    ${message}
                </p>
            </div>
            <button type="button" 
                    onclick="window.dismissToast(this.closest('.toast-item'))" 
                    class="shrink-0 p-1 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
                    aria-label="Cerrar notificación">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-100 overflow-hidden">
                <div class="toast-progress h-full ${s.progress} w-full"></div>
            </div>
        `;

        container.appendChild(toast);
        setTimeout(() => {
            toast.classList.remove('opacity-0', '-translate-y-3', 'scale-95');
            toast.classList.add('opacity-100', 'translate-y-0', 'scale-100');
            setupToast(toast, duration);
        }, 20);
    };

    window.addEventListener('toast', (e) => {
        if (e.detail) window.showToast(e.detail);
    });

    function initExistingToasts() {
        document.querySelectorAll('#toast-container .toast-item').forEach(el => {
            const dur = parseInt(el.dataset.duration || 4500, 10);
            setupToast(el, dur);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initExistingToasts);
    } else {
        initExistingToasts();
    }
})();
</script>
