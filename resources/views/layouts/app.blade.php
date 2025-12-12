<!DOCTYPE html>
<html lang="es" class="{{ Request::is('/') ? 'scroll-smooth' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario LIA</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="{{ Request::is('/') ? 'bg-[#111] text-white' : 'bg-[#fef8e7] flex flex-col min-h-screen' }}">
    @include('common.navbar')

    @if(Request::is('/') || Request::routeIs('register') || Request::routeIs('login'))
        @yield('content')
    @else
        <div class="container mx-auto mt-8 p-4 grow">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
        @include('common.footer')
    @endif
</body>
</html>
