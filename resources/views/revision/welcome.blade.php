@extends('layouts.app')

@section('content')
<div class="min-h-[calc(100vh-200px)] flex flex-col items-center justify-center text-center">
    <h1 class="text-4xl font-bold text-[#dba800] mb-4">Bienvenido al Inventario LIA</h1>
    <p class="text-xl text-gray-600 max-w-2xl">
        Has ingresado al sistema. Si necesitas acceso a funciones específicas, por favor contacta al administrador para que te asigne los roles correspondientes.
    </p>
    <div class="mt-8">
        <img src="/assets/logo-lia.svg" alt="Logo LIA" class="w-32 h-32 mx-auto opacity-50">
    </div>
</div>
@endsection
