<button
    id="scrollToTopBtn"
    class="fixed bottom-8 right-8 z-50 hidden p-3 bg-[#dba800] rounded-full shadow-lg hover:bg-[#fbc101] transition-all duration-300 hover:scale-110 focus:outline-none border-2 border-[#111]"
    aria-label="Volver arriba"
>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#111]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
