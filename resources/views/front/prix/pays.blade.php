@extends('front.layout')

@section('content')

    <x-front.menu-prix-pays tab='{{ $pays }}' :pays="$listepays"/>

    <div class='text-xl text-purple-800 my-2 bold self-center py-2'>
        Liste des prix d'un pays : {{ $pays }}
    </div>
    <div class='text-base px-2 mx-2 md:mx-40 self-center'>
        @foreach($prix as $item)
            <div class='hover:bg-purple-100 border-b hover:border-purple-600'>
                <a class='sm:p-0.5 md:px-0.5' href='/prix/{{ $item->id }}'>{{ $item->name }}</a>
            </div>
        @endforeach
@endsection