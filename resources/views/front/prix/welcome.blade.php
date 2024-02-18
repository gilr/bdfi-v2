@extends('front.layout')

@section('content')

    <x-bdfi-menu-prix-pays tab='welcome' :pays="$pays"/>
    <x-bdfi-menu-prix-type tab='welcome' :types="$types"/>
    <x-bdfi-menu-prix-genre tab='welcome' :genres="$genres"/>

<div class='text-2xl mt-2 md:mt-8 bold self-center'>
    <img sb-icon src="/img/{{ $icon }}" class="w-4 md:w-8 inline" title="{{ $title }}"/> <b>Prix</b>
</div>
    <div class='text-xl text-purple-800 mb-2 bold self-center pb-2'>
        Liste des prix récompensant ou ayant récompensé des oeuvres d'imaginaire
    </div>
    <div class='grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 text-base px-2 mx-2 md:mx-12 lg:mx-24 self-center sm:w-10/12'>
        @foreach($prix as $myprix)
            <div class='hover:bg-yellow-100 border-b hover:border-purple-600'>
                <a class='sm:p-0.5 md:px-0.5' href='/prix/{{ $myprix->id }}'><span class='font-semibold'>{{ $myprix->name }}</span> ({{ $myprix->country->name }})</a>
            </div>
        @endforeach
    </div>

    <div class='mx-2'>Prix par année</div>
    <x-bdfi-menu-prix-annee tab='welcome' :annees="$annees"/>

@endsection