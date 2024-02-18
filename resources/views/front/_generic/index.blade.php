@extends('front.layout')

@section('content')

    <x-bdfi-menu-index tab='{{ $initial }}' zone='{{ $area }}' digit='{{ $digit }}'/>

    <div class='text-2xl m-2 self-center h-12'>
        {{ $results->links() }}
    </div>
    <!--
    <div class='grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 text-base px-2 mx-2 md:mx-12 lg:mx-24 self-center sm:w-10/12'>
        @foreach($results as $result)
            <div class='hover:bg-orange-100 border-b hover:border-purple-600'>
                <a class='sm:p-0.5 md:px-0.5' href='/{{ $area }}/{{ $result->id }}'>{{ ($result->full_name ?: $result->name) }}</a>
            </div>
        @endforeach
    </div>
-->
    <div class=' columns-1 md:columns-2 xl:columns-3 text-base px-2 mx-2 md:mx-12 lg:mx-24 self-center sm:w-10/12'>
        @foreach($results as $result)
            <div class='hover:bg-orange-100 border-b hover:border-purple-600'>
                <a class='sm:p-0.5 md:px-0.5' title='{{ $result->full_name ?: $result->name }}' href='/{{ $area }}/{{ $result->id }}'>{{ $result->full_name ? Str::limit($result->full_name, 50) : Str::limit($result->name, 50) }}</a>
            </div>
        @endforeach
    </div>

    <div class='text-2xl m-2 self-center'>
        {{ $results->links() }}
    </div>
@endsection