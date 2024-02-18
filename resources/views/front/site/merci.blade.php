@extends('front.layout')
@section('content')

 <x-bdfi-menu-site tab='merci' />

<div class='text-2xl my-2 md:mt-8 bold self-center'>
    <img sb-icon src="/img/{{ $icon }}" class="w-4 md:w-8 inline" title="{{ $title }}"/> <b>Merci !</b>
</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    <div class='hidden md:flex text-base p-2 m-5 mx:4 md:mx-40 bg-yellow-200 border-l-4 border-red-600'>
        Comme d'habitude, n'hésitez jamais à nous ré-écrire si vous n'avez pas eu de réponses ou si vos informations n'ont pas été prises en compte. Certains messages passent entre les mailles du filet, d'une part à cause des anti-spams, d'autre part parce que nous sommes parfois (souvent ?) débordés !
    </div>
    <div class='text-2xl m-2 self-center h-12'>
        {{ $results->links() }}
    </div>
    @foreach($results as $result)
        <div class='text-base py-0.5'>
            <span class="font-bold text-purple-900">{{ $result->name }} :</span>
            <span class="">{{ $result->information }}</span>
            <span class="text-sm"> ... Reçu le {{ StrDateformat($result->date->format('Y-m-d')) }}.</span>
        </div>
    @endforeach
    <div class='text-2xl m-2 self-center'>
        {{ $results->links() }}
    </div>
</div>
@endsection