@extends('front.layout')

@section('content')

<div class='text-2xl mt-2 md:mt-8 bold self-center'>
    @if ((!auth()->user()) || (auth()->user() && auth()->user()->with_icons))
        <img sb-icon src="/img/{{ $icon }}" class="w-4 md:w-8 inline" title="{{ $title }}"/>
    @endif
    <b>Programme de publication</b>
</div>
    <div class='text-xl text-purple-800 mb-2 bold self-center pb-2 italic'>
        Sous toutes réserves
    </div>
    <div class='text-base px-2 mx-2 md:mx-12 lg:mx-24 self-center sm:w-10/12'>
        @php
            $previous="";
        @endphp
        @foreach($results as $result)
            @php
                $month = substr($result->approximate_parution, 0, 7);
            @endphp
            @if ($previous != $month)
                @php
                    $previous = $month;
                @endphp
                <div class="font-semibold">
                    {{ StrDateformat($month . "-00") }}
                </div>
            @endif
            <div class='pl-1 lg:pl-4'>
                <x-front.lien-ouvrage link='/ouvrages/{{ $result->slug }}'>{{ $result->name }}</x-front.lien-ouvrage>,

                {{ $result->type->getLabel() }}

                @if (count($result->authors) > 0)
                    de
                    @foreach ($result->authors as $author)
                        @if (!$loop->first)
                            ,
                        @endif
                        <x-front.lien-auteur link='/auteurs/{{ $author->slug }}'>{{ $author->fullname }}</x-front.lien-auteur>
                        @if ($author->pivot->role != App\Enums\AuthorPublicationRole::AUTHOR)
                            <span class='hidden xl:inline'>({{ $author->pivot->role->getLabel() }})</span>
                        @endif
                    @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

@endsection