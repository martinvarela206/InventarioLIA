<!DOCTYPE html>
<html lang="es" class="{{ Request::is('/') ? 'scroll-smooth' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexo LIA — Gestión de Inventario</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        lia: {
                            canvas: '#FAF7F2', // Blanco cálido tintado institucional
                            50: '#fffbeb',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#fbc101', // Amarillo institucional LIA
                            600: '#d9a400',
                            700: '#b48300',
                            800: '#926703',
                            900: '#785407',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    </style>
</head>
<body class="{{ Request::is('/') ? 'bg-[#111] text-white' : 'bg-[#FAF7F2] text-zinc-900 antialiased flex flex-col min-h-screen selection:bg-lia-500 selection:text-black' }}">
    @include('common.navbar')

    @if(Request::is('/') || Request::routeIs('register') || Request::routeIs('login'))
        @yield('content')
    @else
        <main class="container max-w-[1360px] mx-auto mt-6 px-3 sm:px-6 grow pb-10">
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-300 text-emerald-900 px-4 py-3 rounded-xl relative mb-6 shadow-xs flex items-center gap-3 text-sm font-semibold" role="alert">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-rose-50 border border-rose-300 text-rose-900 px-4 py-3 rounded-xl relative mb-6 shadow-xs text-sm" role="alert">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Se encontraron los siguientes problemas:</span>
                    </div>
                    <ul class="list-disc list-inside ml-6 text-xs text-rose-800 font-medium space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
        @include('common.footer')
    @endif
</body>
</html>
