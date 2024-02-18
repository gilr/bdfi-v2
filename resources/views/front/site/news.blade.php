@extends('front.layout')

@section('content')

 <x-bdfi-menu-site tab='news' />

<div class='text-2xl my-2 md:mt-8 bold self-center'>
    <img sb-icon src="/img/{{ $icon }}" class="w-4 md:w-8 inline" title="{{ $title }}"/> <b>Evolutions et nouveautés du site</b>
</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    <div class='hidden md:flex text-base p-2 m-5 mx:4 md:mx-12 lg:mx-40 bg-yellow-200 border-l-4 border-red-600'>
        Liste des évolutions majeures, remerciements groupés, points périodiques, en commençant par les plus récents. Les remerciements individuels sont indiqués en page "remerciements".
    </div>
    <div class='text-2xl m-2 self-center h-12'>
        {{ $results->links() }}
    </div>
    @foreach($results as $result)
        <div class='text-base'>
            <div class="font-bold text-purple-900">{{ $result->date->format('Y') }} - {{ $result->name }}</div>
            <div class="pl-2 sm:pl-20">{{ $result->information }}</div>
            <div class="text-sm pl-1 sm:pl-10">Publié dans <span class='italic'>{{ $result->type->getLabel() }}</span> , le {{ StrDateformat($result->date->format('Y-m-d')) }}.</div>
        </div>
    @endforeach
    <div class='text-2xl m-2 self-center'>
        {{ $results->links() }}
    </div>
</div>

@endsection