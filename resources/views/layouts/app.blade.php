<!DOCTYPE html>
<html lang="es" data-theme="dark" class="{{ Request::is('/') ? 'scroll-smooth' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexo LIA — Figma Studio Design System</title>
    
    <!-- Tipografía Figma: Plus Jakarta Sans & JetBrains Mono para datos tabulares -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: ['class', '[data-theme="dark"]'],
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', '-apple-system', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'Menlo', 'Monaco', 'monospace'],
                    },
                    colors: {
                        figma: {
                            deep: 'var(--figma-bg-deep)',
                            canvas: 'var(--figma-bg-canvas)',
                            panel: 'var(--figma-bg-panel)',
                            card: 'var(--figma-bg-card)',
                            hover: 'var(--figma-bg-hover)',
                            input: 'var(--figma-bg-input)',
                            select: 'var(--figma-bg-select)',
                            border: {
                                subtle: 'var(--figma-border-subtle)',
                                strong: 'var(--figma-border-strong)',
                                focus: 'var(--figma-border-focus)',
                            },
                            text: {
                                primary: 'var(--figma-text-primary)',
                                secondary: 'var(--figma-text-secondary)',
                                tertiary: 'var(--figma-text-tertiary)',
                                dim: 'var(--figma-text-dim)',
                            },
                            brand: {
                                DEFAULT: 'var(--figma-brand)',
                                hover: 'var(--figma-brand-hover)',
                            }
                        }
                    }
                }
            }
        }
    </script>

    <style>
        :root, [data-theme="dark"] {
            /* Superficies Dark (Figma Studio) */
            --figma-bg-deep: #1e1e1e;
            --figma-bg-canvas: #141414;
            --figma-bg-panel: #242424;
            --figma-bg-card: #2c2c2c;
            --figma-bg-hover: #333333;
            --figma-bg-input: #1b1b1b;
            --figma-bg-select: rgba(13, 153, 255, 0.18);

            /* Bordes & Separadores */
            --figma-border-subtle: rgba(255, 255, 255, 0.08);
            --figma-border-strong: rgba(255, 255, 255, 0.18);
            --figma-border-focus: #0d99ff;

            /* Texto & Luminancia */
            --figma-text-primary: #ffffff;
            --figma-text-secondary: #b3b3b3;
            --figma-text-tertiary: #757575;
            --figma-text-dim: #4d4d4d;

            /* Marca & Semántica Figma */
            --figma-brand: #0d99ff;
            --figma-brand-hover: #007be5;
            --figma-success: #10b981;
            --figma-warning: #f59e0b;
            --figma-danger: #ef4444;
            --figma-purple: #8b5cf6;
        }

        [data-theme="light"] {
            /* Superficies Light (Figma Studio) */
            --figma-bg-deep: #f5f5f5;
            --figma-bg-canvas: #e8e8e8;
            --figma-bg-panel: #ffffff;
            --figma-bg-card: #f9f9f9;
            --figma-bg-hover: #f0f0f0;
            --figma-bg-input: #f5f5f5;
            --figma-bg-select: rgba(13, 153, 255, 0.12);

            /* Bordes & Separadores */
            --figma-border-subtle: rgba(0, 0, 0, 0.08);
            --figma-border-strong: rgba(0, 0, 0, 0.16);
            --figma-border-focus: #0d99ff;

            /* Texto WCAG AAA */
            --figma-text-primary: #111111;
            --figma-text-secondary: #2d2d2d;
            --figma-text-tertiary: #545454;
            --figma-text-dim: #767676;

            /* Marca */
            --figma-brand: #0d99ff;
            --figma-brand-hover: #007be5;
            --figma-success: #059669;
            --figma-warning: #d97706;
            --figma-danger: #dc2626;
            --figma-purple: #7c3aed;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            font-feature-settings: "kern" 1;
            letter-spacing: -0.01em;
            background-color: var(--figma-bg-canvas);
            color: var(--figma-text-primary);
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
            font-feature-settings: "tnum" 1;
        }

        /* Figma Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: var(--figma-border-strong);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--figma-text-dim);
        }

        /* Figma Studio Component Utilities */
        .figma-panel {
            background-color: var(--figma-bg-panel);
            border: 1px solid var(--figma-border-subtle);
            border-radius: 10px;
        }
        .figma-card {
            background-color: var(--figma-bg-card);
            border: 1px solid var(--figma-border-subtle);
            border-radius: 8px;
            transition: all 0.12s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .figma-card:hover {
            border-color: var(--figma-border-strong);
        }
        .figma-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: var(--figma-brand);
            color: #ffffff;
            font-weight: 600;
            font-size: 12px;
            padding: 7px 14px;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.12);
            transition: background-color 0.12s ease;
        }
        .figma-btn-primary:hover {
            background-color: var(--figma-brand-hover);
        }
        .figma-btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background-color: var(--figma-bg-card);
            color: var(--figma-text-primary);
            border: 1px solid var(--figma-border-subtle);
            font-weight: 600;
            font-size: 12px;
            padding: 7px 13px;
            border-radius: 8px;
            transition: all 0.12s ease;
        }
        .figma-btn-secondary:hover {
            background-color: var(--figma-bg-hover);
            border-color: var(--figma-border-strong);
        }
        .figma-input {
            background-color: var(--figma-bg-input);
            color: var(--figma-text-primary);
            border: 1px solid var(--figma-border-subtle);
            border-radius: 8px;
            font-size: 12px;
            transition: border-color 0.12s ease, box-shadow 0.12s ease;
        }
        .figma-input:focus {
            outline: none;
            border-color: var(--figma-brand);
            box-shadow: 0 0 0 1px var(--figma-brand);
        }
        .figma-pill-ribbon {
            display: inline-flex;
            align-items: center;
            background-color: var(--figma-bg-panel);
            border: 1px solid var(--figma-border-subtle);
            border-radius: 8px;
            padding: 3px;
            gap: 2px;
        }
        .figma-pill-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--figma-text-secondary);
            transition: all 0.1s ease;
        }
        .figma-pill-item:hover {
            color: var(--figma-text-primary);
            background-color: var(--figma-bg-hover);
        }
        .figma-pill-item.active {
            background-color: var(--figma-bg-card);
            color: var(--figma-text-primary);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 0 0 1px var(--figma-border-subtle);
        }
    </style>

    <script>
        // Inicializar tema antes del render para evitar flickering
        const savedTheme = localStorage.getItem('figma_theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        function toggleFigmaTheme() {
            const current = document.documentElement.getAttribute('data-theme') || 'dark';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('figma_theme', next);
            const iconSun = document.getElementById('theme-icon-sun');
            const iconMoon = document.getElementById('theme-icon-moon');
            if (iconSun && iconMoon) {
                if (next === 'light') {
                    iconSun.classList.remove('hidden');
                    iconMoon.classList.add('hidden');
                } else {
                    iconSun.classList.add('hidden');
                    iconMoon.classList.remove('hidden');
                }
            }
        }
    </script>
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-[#0d99ff] selection:text-white transition-colors duration-150">
    @include('common.navbar')

    @if(Request::is('/') || Request::routeIs('register') || Request::routeIs('login'))
        @yield('content')
    @else
        <main class="container max-w-[1400px] mx-auto mt-5 px-3 sm:px-6 grow pb-10">
            @if(session('success'))
                <div class="bg-[var(--figma-bg-card)] border border-emerald-500/40 text-emerald-400 px-4 py-2.5 rounded-lg mb-5 shadow-xs flex items-center gap-2.5 text-xs font-medium" role="alert">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                    <span class="text-[var(--figma-text-primary)]">{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-[var(--figma-bg-card)] border border-rose-500/40 text-rose-400 px-4 py-3 rounded-lg mb-5 shadow-xs text-xs" role="alert">
                    <div class="flex items-center gap-2 font-bold mb-1.5 text-rose-400">
                        <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                        <span>Atención requerida:</span>
                    </div>
                    <ul class="list-disc list-inside ml-4 text-[var(--figma-text-secondary)] space-y-0.5">
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
