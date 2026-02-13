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
        <b>soit vous avez suivi une de nos routes commerciales, mais un évènement inattendu vous a fait dévier du chemin prévu, traverser un champ d'astéroides, et atterrir en catastrophe en ce lieu perdu.</b>
    </div>

    <div class='text-xl mt-16 self-center sm:px-20 px-2'>
        Quoi qu'il en soit, vous êtes mal.
    </div>
    <div class='text-xs mt-8 self-center sm:px-20 px-2'>
        Et il va bien falloir vous sortir d'ici tout seul...
    </div>

@endsection
