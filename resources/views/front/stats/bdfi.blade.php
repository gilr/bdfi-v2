@extends('front.layout')

@section('content')

 <x-front.menu-stats tab='bdfi' />

@vite(['resources/css/app.css', 'resources/js/app.js']) {{-- Ton Vite standard --}}

{{-- Ajouter les assets Filament nécessaires --}}
@filamentStyles
@filamentScripts

<div class='text-2xl my-2 md:mt-8 bold self-center'>
    <img sb-icon src="/img/{{ $icon }}" class="w-4 md:w-8 inline" title="{{ $title }}"/> <b>Historique BDFI</b>
</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    <div class='hidden md:flex text-base p-2 m-5 mx:4 md:mx-12 lg:mx-40 bg-yellow-200 border-l-4 border-red-600'>
        Evolution du nombre de référencements de romans et de nouvelles. Pour plus de détails, voir la zone "Le site".
    </div>
    <div class='text-base'>
        <div>
            @livewire(\App\Livewire\StatsV1Chart::class, ['datasetType' => 'default'])
        </div>
    </div>
</div>

@endsection