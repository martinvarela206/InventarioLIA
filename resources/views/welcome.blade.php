@extends('layouts.app')

@section('content')
    @include('welcome.hero')
    @include('welcome.carrusel-seguridad')
    @include('welcome.how')
    @include('welcome.lia')
    @include('welcome.cards')
    @include('welcome.secciones-dinamicas')
    @include('common.footer')
    @include('common.scroll-to-top')
@endsection
