@extends('front.layout')
@section('content')

<x-front.menu-site tab='accueil' />

<div class='text-2xl my-2 md:mt-8 bold self-center'>
    @if ((!auth()->user()) || (auth()->user() && auth()->user()->with_icons))
        <img sb-icon src="/img/{{ $icon }}" class="w-4 md:w-8 inline" title="{{ $title }}"/>
    @endif
    <b>Tableau de bord BDFI</b>
</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

    <div class='text-lg font-bold text-purple-900'>
        Derniers ouvrages
    </div>
    <div class='text-base p-2 m-5 mx:4 md:mx-40 bg-yellow-200 border-l-4 border-red-600'>
        Les derniers référencements d'ouvrages.
    </div>
    <div class='grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 text-base px-2 mx-2 md:mx-12 lg:mx-24 self-center sm:w-10/12'>
        @foreach($results as $result)
            <div class='hover:bg-orange-100 border-b hover:border-purple-600'>
                <a class='sm:p-0.5 md:px-0.5' href='/ouvrages/{{ $result->slug }}'>{{ ($result->full_name ?: $result->name) }}</a>
            </div>
        @endforeach
    </div>

    <div class='text-lg font-bold text-purple-900'>
        Dernières modifications
    </div>
    <div class='text-base p-2 m-5 mx:4 md:mx-40 bg-yellow-200 border-l-4 border-red-600'>
        Les toutes dernières modifications effectuées sur la base.
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        @foreach($results2 as $result)
        <?php
            $href = "/nova/resources/". Str::plural(Str::kebab(class_basename($result->revisionable_type))) . "/" . $result->revisionable_id;
            $pubtitle = class_exists($class = $result->revisionable_type) ? $class::withTrashed()->find($result->revisionable_id)->name : "...";
            $old = Str::limit($result->old_value, 40, "...");
            $new = Str::limit($result->new_value, 40, "...");
        ?>
            <div class='border-b'>
                {{ $result->created_at }} -
                <span class='font-semibold'>{{ $pubtitle }}</span> :
                @if(isset($result->key))
                    {{ $result->key }}
                @endif
                (de "{{ $old }}"" à "{{ $new }}"")
            </div>
        @endforeach
    </div>

</div>

@endsection