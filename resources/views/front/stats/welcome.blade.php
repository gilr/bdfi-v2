@extends('front.layout')
@section('content')

<x-front.menu-stats tab='accueil' />

<div class='text-2xl my-2 md:mt-8 bold self-center'>
    @if ((!auth()->user()) || (auth()->user() && auth()->user()->with_icons))
        <img sb-icon src="/img/{{ $icon }}" class="w-4 md:w-8 inline" title="{{ $title }}"/>
    @endif
    <b>Quelques chiffres sur la base BDFI</b>
</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    <div>
        @livewire(\App\Livewire\StatsOverview::class)
    </div>
    <div class='text-base p-2 m-5 mx:4 md:mx-40 bg-yellow-200 border-l-4 border-red-600'>
        Ces chiffres sont extraits en temps réel de notre base de référencement.
    </div>

    <div class='text-lg font-bold text-purple-900'>
        Détails sur les {{ $results['pubs'] }} publications :
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        <div class='border-b'><span class="font-semibold text-purple-900">{{ $results['roman'] + $results['fiction'] }}</span> ouvrages contenant un roman ou texte seul</div>
        <div class='border-b'><span class="font-semibold text-purple-900">{{ $results['compilation'] + $results['omnibus'] }}</span> compilations de textes (recueils, anthologies, omnibus...)</div>
        <div class='border-b'><span class="font-semibold text-purple-900">{{ $results['non-fiction'] }}</span> ouvrages non fiction (guides, encyclopédies, essais...)</div>
        <div class='border-b'><span class="font-semibold text-purple-900">{{ $results['periodique'] }}</span> périodiques (revues, fanzines, magazines, journaux...)</div>
    </div>

</div>

@endsection