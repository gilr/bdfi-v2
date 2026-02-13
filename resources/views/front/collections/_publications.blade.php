@foreach ($results->publications as $publication)
    <div class='ml-2 md:ml-8'>
        @if (isset($suivie) && $suivie)
            @if (auth()->user()->statusPublication($publication->id))
                <x-front.display-icon-pub-owned />
            @else
                <x-front.display-icon-pub-missing />
            @endif
        @endif
        @if($publication->pivot->number)
            {{ $publication->pivot->number }}.
        @endif
        <x-front.lien-ouvrage link='/ouvrages/{{ $publication->slug }}'>{!! $publication->name !!}</x-front.lien-ouvrage> -

        <span class='hidden md:inline text-gray-800'>{{ $publication->type->getLabel() }}</span>
        @if(count($publication->authors) > 0)
        de
            @foreach($publication->authors as $author)
                @if (!$loop->first)
                    ,
                @endif
                <x-front.lien-auteur link='/auteurs/{{ $author->slug }}'>{{ $author->fullname }}</x-front.lien-auteur>
                {{--
                @if ($author->pivot->role != App\Enums\AuthorPublicationRole::AUTHOR)
                    <span class='hidden xl:inline'>({{ $author->pivot->role->getLabel() }})</span>
                @endif
                --}}
            @endforeach
        @endif
         - {{ StrDateYear($publication->approximate_parution) }}
    </div>
@endforeach