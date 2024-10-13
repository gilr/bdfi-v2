@extends('front.layout')

@section('content')

    <x-front.menu-index tab='9' zone='{{ $area }}' digit='{{ $digit }}'/>

    <div class='text-2xl my-2 md:mt-8 bold self-center'>
        @if ((!auth()->user()) || (auth()->user() && auth()->user()->with_icons))
            <img sb-icon src="/img/{{ $icon }}" class="w-4 md:w-8 inline" title="{{ $title }}"/>
        @endif
        <b>Liste des collections de la version V2 Bêta</b>
    </div>

    <div class='text-base p-4 m-4 bg-sky-100 self-center border border-blue-400'>
        Cette page présente la liste de toutes les collections et sélections d'ouvrages incluses dans la version courante V2 Bêta</b>
    </div>

    <div class='grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 text-base px-2 mx-2 md:mx-12 lg:mx-24 self-center sm:w-10/12'>
        @foreach($results as $result)
            <div class='hover:bg-orange-100 border-b hover:border-purple-600'>
                <x-front.display-icon-v2beta-if value='{{ $result->is_in_v2beta }}' />
                <a class='sm:p-0.5 md:px-0.5' title='{{ $result->full_name ?: $result->name }}' href='/collections/{{ $result->slug }}'>{{ $result->full_name ? Str::limit($result->full_name, 50) : Str::limit($result->name, 50) }}</a>
            </div>
        @endforeach
    </div>

@endsection
