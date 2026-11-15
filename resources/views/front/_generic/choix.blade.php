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
            Recherche
            <input class="px-2 border-2 border-green-500 rounded" type="text" name="s" placeholder='votre recherche...' autofocus value='{{ $text }}' required/>
            <label title="{{ $extended }}" class='border-b border-green-500 pl-1' for='large'>Recherche étendue</label>
            <input title="{{ $extended }}" class="appearance-none checked:bg-emerald-200 px-2.5 py-1.5 border-2 border-green-500 rounded" id='large' name="m" @checked($large == "on") type="checkbox"/>
            <button class="px-2 bg-emerald-200 border-2 border-green-500 rounded" type="submit">Chercher</button>

        </form>
    </div>

    <div class='text-2xl m-2 self-center h-12'>
        {{ $results->links() }}
    </div>

    <div class='columns-1 md:columns-2 xl:columns-3 text-base px-2 mx-2 md:mx-12 lg:mx-24 self-center sm:w-10/12'>
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
