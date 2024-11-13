@extends('front.layout')

@section('content')

<div class='font-bold text-2xl mt-8 self-center'>
        {{ $results->name }}
</div>
<div class='text-base mb-8 self-center'>
    {{ $title }} - <i>Fiche n° {{ $results->id }}</i>
</div>

<div class='grid grid-cols-1 lg:grid-cols-2 gap-1 bg-gradient-to-b from-yellow-400 via-pink-500 to-purple-500 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4'>

<div class='bg-gray-100 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

    <div class='text-base mt-2 font-semibold bg-yellow-50'>
         {{ $results->name }}
    </div>

    <div class='text-base'>
        <span class='font-semibold'>{{ $results->country->name }} ({{ $results->year_start }} - {{ $results->year_end != "" ? $results->year_end : "..." }})</span>
    </div>

    <div class='text-base'>
        URL : <a class='border-b border-dotted border-red-700 hover:text-red-700 focus:text-red-900' href='{{ $results->url }}'>{{ $results->url }}</a>
    </div>

    <div class='text-base'>
        Autres noms : <span class='font-semibold'>{{ $results->alt_names }}</span>
    </div>

    <div class='text-base'>
        Attribué pour : <span class='font-semibold'>{{ $results->given_for }}</span>
    </div>

    <div class='text-base my-4 p-2 border border-yellow-500 bg-yellow-50'>
        {!! $results->information !!}
    </div>

</div>

<div class='bg-gray-100 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

    <div class='text-xl font-semibold my-2 mt-4 bold self-center m-auto'>
        @if (count($categories) != 1)
            Liste des catégories :
        @else
            Une seule catégorie :
        @endif
    </div>
    <div class='text-base px-2 mx-2 md:mx-10 self-center'>
        @foreach($categories as $categorie)
            <div class='hover:bg-yellow-100 border-b hover:border-purple-400'><a class='sm:p-0.5 md:px-0.5' href='/prix/categorie/{{ $categorie->id }}'> {{ $categorie->name }} </a></div>
        @endforeach
    </div>
</div>

</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    @if ($laureats != NULL)
        <hr class="mx-24 my-2 border-dotted border-purple-800"/>

        <div class='font-semibold text-xl my-2 sm:px-40 mt-4 bold self-center m-auto'>
            Liste des lauréats
        </div>
        <div class='text-base px-2 mx-2 md:mx-40 self-center'>
            @foreach($laureats as $laureat)
                @if ($laureat->position == 99)
                    <div class='bg-gray-300'><a class='hover:bg-yellow-100 border-b hover:border-purple-400 sm:px-0.5 md:px-1' href='/prix/annee/{{ $laureat->year }}'>{{ $laureat->year }}</a> : <i>Non attribué</i>  </div>
                @else
                    <div><a class='hover:bg-yellow-100 border-b hover:border-purple-400 sm:px-0.5 md:px-1' href='/prix/annee/{{ $laureat->year }}'>{{ $laureat->year }}</a> : {!! awardAuthors($laureat->name, $laureat->author, $laureat->author2, $laureat->author3) !!} - {{ $laureat->title }} {{ $laureat->title == "" ? $laureat->vo_title : ($laureat->vo_title == "" ? "" : "(" . $laureat->vo_title . ")") }}  </div>
                @endif
            @endforeach
        </div>
    @endif
</div>

<div class='text-l py-1 mt-5 bold self-center border-t-2 border-yellow-600'>
    Dernière mise à jour : {{ $results->updated_at }}
</div>

<x-front.info-fiche :results='$results' :content='$info'/>

@endsection