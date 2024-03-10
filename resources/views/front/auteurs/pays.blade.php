@extends('front.layout')

@section('content')

    @includeIf('front.'. $area. '._submenu')

    <div class='text-2xl mx-2 mt-2 self-center'>
        {{ $pays }}
    </div>
    <div class='text-2xl mx-2 mt-2 self-center'>
        <img class='m-auto p-0.5 lg:p-1 border border-purple-800' src="/img/drapeaux/{{ Str::slug(remove_accents($pays),'_') }}.png" />
    </div>
    <div class='text-2xl m-2 self-center h-12'>
        {{ $results->links() }}
    </div>

    <div class='grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 text-base px-2 mx-2 md:mx-12 lg:mx-24 self-center sm:w-10/12'>
        @foreach($results as $result)
            <div class='hover:bg-orange-100 border-b hover:border-purple-600'>
                <a class='sm:p-0.5 md:px-0.5' href='/{{ $area }}/{{ $result->id }}'>{{ ($result->full_name ?: $result->name) }}</a>
            </div>
        @endforeach
    </div>
    <div class='text-2xl m-2 self-center'>
        {{ $results->links() }}
    </div>

@endsection