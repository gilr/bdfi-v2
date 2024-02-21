@extends('front.layout')
@section('content')

<x-front.menu-site tab='contact' />

<div class='text-2xl my-2 md:mt-8 bold self-center'>
    <img sb-icon src="/img/{{ $icon }}" class="w-4 md:w-8 inline" title="{{ $title }}"/> <b>Contacter le site ou ses membres</b>
</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

    <div class='text-base p-2 m-5 mx:4 md:mx-40 bg-yellow-200 border-l-4 border-red-600'>
        Par messagerie
    </div>
    <div class='text-base p-1 mt-1 md:px-10 mx:4 md:mx-20'>
        Adresses e-mail des administrateurs :
        <br />
        christian --- point --- moulin (@) orange --- point --- fr
        <br />
        gilles --- point --- richardot (@) free --- point --- fr
    </div>
    <div class='text-base p-2 m-5 mx:4 md:mx-40 bg-yellow-200 border-l-4 border-red-600'>
        Via le forum
    </div>
    <div class='text-base p-1 mt-1 md:px-10 mx:4 md:mx-20'>
        Vous pourrez discuter avec l'ensemble des membres en rejoignant notre
        <a class='font-semibold border-purple-300 border-b hover:bg-orange-100 border-b hover:border-purple-600' href='/forums'>Forum</a> (il faudra vous inscrire).
    </div>
    <div class='text-base p-2 m-5 mx:4 md:mx-40 bg-yellow-200 border-l-4 border-red-600'>
        En s'inscrivant / se connectant sur le site
    </div>
    <div class='text-base p-1 mt-1 md:px-10 mx:4 md:mx-20'>
         (A venir) Après être inscrit ou connecté (utiliser les liens en haut à droite ou l'icone en bas de la sidebar), vous aurez la possibilité de nous contacter via un système de ticket
         (messages, corrections, aides, idées) et ensuite de suivre vos échanges.
    </div>
    <div class='text-base p-2 m-5 mx:4 md:mx-40 bg-yellow-200 border-l-4 border-red-600'>
        Par formulaire direct (sans inscription)
    </div>
    <div class='text-base p-1 mt-1 md:px-10 mx:4 md:mx-20'>
        (à venir)
    </div>

</div>
@endsection