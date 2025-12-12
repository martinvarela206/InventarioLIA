@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <h2 class="text-2xl font-bold mb-4">Editar Usuario: {{ $user->nombre }}</h2>

            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="nombre" class="block text-gray-700 text-sm font-bold mb-2">Nombre y Apellido</label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $user->nombre) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    @error('nombre')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Add more fields here if necessary, e.g., email password reset (though not requested) -->

                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-amber-700 hover:bg-[#fbc101] hover:text-[#111] text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition-colors">
                        Actualizar Usuario
                    </button>
                    <a href="{{ route('users.index') }}" class="inline-block align-baseline font-bold text-sm text-amber-700 hover:text-amber-900">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
