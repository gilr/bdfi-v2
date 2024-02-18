@extends('front.layout')

@section('content')

    <x-bdfi-menu-index tab='{{ strtolower(substr($results->name, 0, 1)) }}' zone='{{ $area }}' digit='{{ $digit }}'/>

    <div class='text-2xl my-4 self-center'>
        <b>{{ ($results->full_name ?: $results->name) }}</b>
    <span class='text-lg mb-8 self-center'>
        - Fiche <i>{{ $title }}</i>
    @auth
    @if (auth()->user()->hasGuestRole())
        <span class='text-blue-900 bg-sky-200 shadow-sm shadow-blue-600 rounded-sm px-1'>n° {{ $results->id }}</span>
    @endif
    @endauth
    </span>
    </div>

    @include('front.'. $area. '.fiche')

    <div class='text-sm py-1 mt-5 bold self-center border-t-2 border-yellow-600'>
        Dernière mise à jour : {{ $results->updated_at }}
    </div>

    <x-bdfi-info-fiche filament='{{ $filament }}' area='{{ $area }}' :results='$results'/>

@endsection
