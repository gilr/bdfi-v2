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

