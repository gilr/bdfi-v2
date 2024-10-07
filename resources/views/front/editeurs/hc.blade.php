@extends('front.layout')

@section('content')

    <div class='text-2xl mt-8 self-center'>
        <b>{{ $publisher->name }} : publications non rattachées à une collection</b>
    </div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

    <div class='text-base'>
        Editeur  : <span class='font-semibold'><a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/editeurs/{{ $publisher->slug }}'>{{ $publisher->name }}</a></span>
    </div>

</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    @if(count($results))
        <hr class="mx-24 my-2 border-dotted border-purple-800"/>

        <div class='text-base'>
            @foreach ($results as $publication)
                <div class='ml-2 md:ml-8'>
                    <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/ouvrages/{{ $publication->slug }}'>{{ $publication->name }}</a>,

                    <span class='hidden md:inline text-gray-800'>{{ $publication->type->getLabel() }}</span>
                    @if(count($publication->authors) > 0)
                    de
                        @foreach($publication->authors as $author)
                            @if (!$loop->first)
                                ,
                            @endif
                            <a class='text-red-800 border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/auteurs/{{ $author->slug }}'>{{ $author->fullname }}</a> <span class='hidden xl:inline'>({{ $author->pivot->role->getLabel() }})</span>
                        @endforeach
                    @endif
                     - {{ StrDateformat($publication->approximate_parution) }}
                </div>

            @endforeach
        </div>
        <hr class="mx-8 my-4 border-red-300"/>
        <div class='text-base'>
            <span class='font-semibold'>Galerie :</span>
            <div class="flex flex-wrap">
            @foreach ($results as $publication)
                <!-- zone couverture -->
                <a class='m-auto p-1 lg:p-2' href='/ouvrages/{{ $publication->slug }}'><img class='m-auto p-1 lg:p-2 border border-purple-800' src="https://www.bdfi.info/vignettes/{{ InitialeCouv($publication->cover_front) }}/v_{{ $publication->cover_front }}.jpg" alt="couv" title="Couverture {{ $publication->name }}"></a>
            @endforeach
            </div>
        </div>
    @endif
</div>
@endsection