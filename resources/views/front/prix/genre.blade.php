@extends('front.layout')

@section('content')

    <x-bdfi-menu-prix-genre tab='{{ $genre }}' :genres="$genres"/>

    <div class='text-xl text-purple-800 my-2 bold self-center py-2'>
        Liste des prix et catégories concernant le genre : {{ App\Enums\AwardCategoryGenre::from($genre)->GetLabel() }}
    </div>
    <div class='text-base px-2 mx-2 md:mx-40 self-center'>
        @foreach($categories as $categorie)
            <div class='hover:bg-purple-100 border-b hover:border-purple-600'>
                <a class='sm:p-0.5 md:px-0.5' href='/prix/categorie/{{ $categorie->id }}'> {{ $categorie->award->name }} - {{ $categorie->name }} </a>
            </div>
        @endforeach
    </div>
@endsection