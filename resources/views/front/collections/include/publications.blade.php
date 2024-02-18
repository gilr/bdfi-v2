@foreach ($results->publications as $publication)
    <div class='ml-2 md:ml-8'>
        @if($publication->pivot->number)
            {{ $publication->pivot->number }} -
        @endif
        <x-bdfi-lien-ouvrage link='/ouvrages/{{ $publication->id }}'>{{ $publication->name }}</x-bdfi-lien-ouvrage>,

        <span class='hidden md:inline text-gray-800'>{{ $publication->type->getLabel() }}</span>
        @if(count($publication->authors) > 0)
        de
            @foreach($publication->authors as $author)
                @if (!$loop->first)
                    ,
                @endif
                <x-bdfi-lien-auteur link='/auteurs/{{ $author->id }}'>{{ $author->fullname }}</x-bdfi-lien-auteur>
                @if ($author->pivot->role != App\Enums\AuthorPublicationRole::AUTHOR)
                    <span class='hidden xl:inline'>({{ $author->pivot->role->getLabel() }})</span>
                @endif
            @endforeach
        @endif
         - {{ StrDateformat($publication->approximate_parution) }}
    </div>
@endforeach