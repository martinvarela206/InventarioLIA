<button
    id="scrollToTopBtn"
    class="fixed bottom-6 right-6 z-50 hidden p-2.5 bg-figma-brand text-white rounded-xl shadow-xl hover:opacity-90 transition-all focus:outline-none border border-figma-border-subtle cursor-pointer"
    aria-label="Volver arriba"
>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
    </svg>
</button>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('scrollToTopBtn');
        const header = document.querySelector('header');

        window.addEventListener('scroll', function() {
            const threshold = header ? header.offsetHeight : 100;
            
            if (window.scrollY > threshold) {
                btn.classList.remove('hidden');
                btn.classList.add('flex');
            } else {
                btn.classList.add('hidden');
                btn.classList.remove('flex');
            }
        });

        btn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    });
</script>
