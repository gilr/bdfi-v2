@extends('front.layout')

@section('content')

    <div class='text-2xl mt-8 self-center'>
        Résultats de la recherche
    </div>
    <div class='text-lg mb-2 self-center'>
        Dans : {{ $title }}
    </div>

    <div class='text-lg mx-2 my-1 md:my-4 bold self-center'>
        <form action="{{ route($area . '.search') }}" method="GET">
            Recherche au sein de cette zone :
            <input class="px-2 border-2 border-green-500 rounded" type="text" name="s" placeholder='votre recherche...' value='{{ $text }}' required/>
            <input class="appearance-none checked:bg-lime-300 px-2.5 py-1.5 border-2 border-green-500 rounded" id='large' name="m" @checked($large == "on") type="checkbox"/><label class='border-b border-green-500 pl-1' for='large'>Recherche élargie</label>
            <button class="px-2 bg-emerald-200 border-2 border-green-500 rounded" type="submit">Search</button>
        </form>
    </div>

    <div class='text-2xl m-2 self-center h-12'>
        {{ $results->links() }}
    </div>

    <div class='grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 text-base px-2 mx-2 md:mx-12 lg:mx-24 self-center sm:w-10/12'>
        @foreach($results as $result)
            <div class='hover:bg-orange-100 border-b hover:border-purple-600'>
                @if ($area == "collections")
                    <x-front.display-icon-v2beta-if value='{{ $result->is_in_v2beta }}' />
                @endif
                <a class='sm:p-0.5 md:px-0.5' title='{!! $result->full_name ?: $result->name !!}' href='/{{ $area }}/{{ $result->slug }}'>{!! $result->full_name ? Str::limit($result->full_name, 50) : Str::limit($result->name, 50) !!}</a>
                @if (isset($result->alt_names) && $result->alt_names)
                    ({!! $result->alt_names !!})
                @endif
            </div>
        @endforeach
    </div>
    <div class='text-2xl m-2 self-center'>
        {{ $results->links() }}
    </div>

@endsection
