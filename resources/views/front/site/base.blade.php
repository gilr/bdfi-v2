@extends('front.layout')
@section('content')

<x-front.menu-site tab='base' />

@vite(['resources/css/app.css', 'resources/js/app.js']) {{-- Ton Vite standard --}}

{{-- Ajouter les assets Filament nécessaires --}}
@filamentStyles
@filamentScripts

<div class='text-2xl my-2 md:mt-8 bold self-center'>
    <img sb-icon src="/img/{{ $icon }}" class="w-4 md:w-8 inline" title="{{ $title }}"/> <b>Evolution de la base BDFI - Historique & stats des référencements</b>
</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    @if ($record = $results->last())
        <div class='text-base font-bold text-purple-900'>
            La dernière image statistique ({{ StrDateformat($record->date->format('Y-m-d')) }})
        </div>
        <div class='text-base ml-2 sm:ml-8'>
            <div>Auteurs : {{ $record->authors }}</div>
            <div>Séries et cycles : {{ $record->series }}</div>
            <div>Références  : {{ $record->references }}</div>
            <div>&nbsp; dont</div>
            <div>Romans et fix-up : {{ $record->novels }}</div>
            <div>Nouvelles : {{ $record->short_stories }}</div>
            <div>Recueils et anthologies : {{ $record->collections }}</div>
            <div>Revues et fanzines : {{ $record->magazines }}</div>
            <div>Guides, essais... : {{ $record->essays }}</div>
        </div>
    @else
        <div class='text-base p-20 md:px-10 mt-1 mx:4 md:mx-20 mb-2 border-b border-red-500'>
            Base de donnée inaccessible.
        </div>
    @endif

        <div class='text-base py-2 font-bold text-purple-900'>
            Nombre de références de textes, dont romans et nouvelles
        </div>
        <div>
            @livewire(\App\Livewire\StatsV1Chart::class, ['datasetType' => 'references'])
        </div>
        <div class='text-base py-2 font-bold text-purple-900'>
            Nombre d'auteurs et de séries
        </div>
        <div>
            @livewire(\App\Livewire\StatsV1Chart::class, ['datasetType' => 'authors_series'])
        </div>
        <div class='text-base py-2 font-bold text-purple-900'>
            Nombre de recueils, de revues & fanzines et de guides & essais
        </div>
        <div>
            @livewire(\App\Livewire\StatsV1Chart::class, ['datasetType' => 'collections_magazines_essays'])
        </div>
    </div>
</div>

@endsection