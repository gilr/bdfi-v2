@php
    $area = '';
    $title = 'lost in space (le retour)';
    $page = '';
@endphp
@extends('front.layout')

@section('content')

    <div class='text-2xl mt-4 self-center sm:px-20 px-2 text-center'>
        <b>Soit vous avez emprunté un chemin non balisé (et nous ne vous en félicitons pas), </b>
    </div>
    <div class='text-2xl mb-4 self-center sm:px-20 px-2 text-center'>
        <b>soit vous avez suivi une de nos routes commerciales, mais une catastrophe (plus ou moins naturelle) vous a fait dévier du chemin prévu et vous a conduit à atterrir en ce lieu.</b>
    </div>

    <div class='text-lg mt-16 self-center sm:px-20 px-2'>
        Mais quoi qu'il en soit, vous êtes mal.
    </div>
    <div class='text-xs mt-8 self-center sm:px-20 px-2'>
        Et quelle qu'en soit la cause, il va bien falloir vous sortir d'ici tout seul.
    </div>

@endsection
