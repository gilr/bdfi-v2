@foreach ($results->publications as $publication)
    <div class='ml-2 md:ml-8'>
        @if ($suivie)
            @if (auth()->user()->statusPublication($publication->id))
                <svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 fill-green-300 inline"><rect x="0" fill="none" width="20" height="20"/><g><path d="M5 17h13v2H5c-1.66 0-3-1.34-3-3V4c0-1.66 1.34-3 3-3h13v14H5c-.55 0-1 .45-1 1s.45 1 1 1zm2-3.5v-11c0-.28-.22-.5-.5-.5s-.5.22-.5.5v11c0 .28.22.5.5.5s.5-.22.5-.5z"/></g></svg>
            @else
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 fill-red-300 inline"><circle cx="12" cy="12" r="10" stroke="#33363F" stroke-width="1" stroke-linecap="round"/><path d="M7.88124 16.2441C8.37391 15.8174 9.02309 15.5091 9.72265 15.3072C10.4301 15.103 11.2142 15 12 15C12.7858 15 13.5699 15.103 14.2774 15.3072C14.9769 15.5091 15.6261 15.8174 16.1188 16.2441" stroke="#33363F" stroke-width="1" stroke-linecap="round"/><circle cx="9" cy="10" r="1.25" fill="#33363F" stroke="#33363F" stroke-width="0.5" stroke-linecap="round"/><circle cx="15" cy="10" r="1.25" fill="#33363F" stroke="#33363F" stroke-width="0.5" stroke-linecap="round"/></svg>
            @endif
        @endif
        @if($publication->pivot->number)
            {{ $publication->pivot->number }} -
        @endif
        <x-front.lien-ouvrage link='/ouvrages/{{ $publication->id }}'>{{ $publication->name }}</x-front.lien-ouvrage>,

        <span class='hidden md:inline text-gray-800'>{{ $publication->type->getLabel() }}</span>
        @if(count($publication->authors) > 0)
        de
            @foreach($publication->authors as $author)
                @if (!$loop->first)
                    ,
                @endif
                <x-front.lien-auteur link='/auteurs/{{ $author->id }}'>{{ $author->fullname }}</x-front.lien-auteur>
                @if ($author->pivot->role != App\Enums\AuthorPublicationRole::AUTHOR)
                    <span class='hidden xl:inline'>({{ $author->pivot->role->getLabel() }})</span>
                @endif
            @endforeach
        @endif
         - {{ StrDateformat($publication->approximate_parution) }}
    </div>
@endforeach