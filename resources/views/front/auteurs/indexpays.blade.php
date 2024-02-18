@extends('front.layout')

@section('content')

    <div class='text-xl my-2 bold self-center'>
        Index des auteurs par pays
    </div>

    <div class='grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 px-2 mx-2 md:mx-12 lg:mx-24 self-center sm:w-10/12'>
        @foreach($countries as $country)
            <div class='hover:bg-orange-100 border-b hover:border-purple-600'>
                <a class='block m-0.5 p-0.5' href='/auteurs/pays/{{ $country->name }}'>
                    @if ($country->name != "?")
                        <img class='block mx-auto m-0.5 p-0.5 border border-purple-800' src="/img/drapeaux/{{ Str::slug(remove_accents($country->name),'_') }}.png" />
                        <span class='block ml-2'>{{ $country->name }}</span>
                    @else
                        <img class='block mx-auto m-0.5 p-0.5 border border-purple-800' src="/img/drapeaux/incnat.png" />
                        <span class='block ml-2'>{{ $country->name }}</span>
                    @endif
                </a>

            </div>
        @endforeach
    </div>

@endsection